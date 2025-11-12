<?php
namespace App\Controllers;

use App\Core\{Controller, Auth, Csrf, DB};
use App\Models\{
  Patient, LabReport, LabOrder,
  Prescription, PrescriptionItem,
  PatientReport, LabTest, Drug
};

final class DoctorController extends Controller
{
  /** Dashboard: KPIs + charts */
  public function dashboard(): void {
    if (!Auth::check()) $this->redirect('/');

    // --- KPI: Patients ---
    $totalPatients = (int) DB::pdo()->query("SELECT COUNT(*) FROM patients")->fetchColumn();

    // --- KPI: Pending lab orders (status = 'ordered' or 'pending') ---
    // Adjust to your actual status values if different
    $pendingLabs = (int) DB::pdo()->query("
      SELECT COUNT(*) FROM lab_orders WHERE status IN ('ordered','pending')
    ")->fetchColumn();

    // --- KPI: Completed Lab Reports in last 30 days ---
    $completedLabs = (int) DB::pdo()->query("
      SELECT COUNT(*) FROM lab_reports
      WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    // --- KPI: Prescriptions (pending vs dispensed) in last 30 days ---
    $pendingRx = (int) DB::pdo()->query("
      SELECT COUNT(*) FROM prescription_items i
      JOIN prescriptions p ON p.id = i.prescription_id
      WHERE i.dispensed = 0
        AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    $dispensedRx = (int) DB::pdo()->query("
      SELECT COUNT(*) FROM prescription_items i
      JOIN prescriptions p ON p.id = i.prescription_id
      WHERE i.dispensed = 1
        AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    // --- KPI: Low stock drugs (requires drugs.stock INT column) ---
    $lowStock = (int) DB::pdo()->query("SELECT COUNT(*) FROM drugs WHERE stock <= 10")->fetchColumn();

    // --- Visits chart (last 7 days) from appointments.starts_at ---
    $rows = DB::pdo()->query("
      SELECT DATE(starts_at) d, COUNT(*) c
      FROM appointments
      WHERE starts_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
      GROUP BY DATE(starts_at)
      ORDER BY d
    ")->fetchAll();

    $labels = [];
    $values = [];
    $map = [];
    foreach ($rows as $r) { $map[$r['d']] = (int)$r['c']; }

    $period = new \DatePeriod(new \DateTime('-6 days 00:00:00'), new \DateInterval('P1D'), 7);
    foreach ($period as $dt) {
      $key = $dt->format('Y-m-d');
      $labels[] = $dt->format('D');
      $values[] = $map[$key] ?? 0;
    }

    $this->view('doctor/dashboard', [
      'stats' => [
        'patients'      => $totalPatients,
        'pendingLabs'   => $pendingLabs,
        'completedLabs' => $completedLabs,
        'pendingRx'     => $pendingRx,
        'dispensedRx'   => $dispensedRx,
        'lowStock'      => $lowStock,
      ],
      'chart' => [
        'labels' => $labels,
        'values' => $values,
      ]
    ]);
  }


  //Lab result
  /** Doctor → View lab result + jump to Doctor Report
 *  GET /doctor/lab-result?order_id=123
 */
/** Doctor → View lab result (schema-aware) */
public function labResult(): void {
  if (!Auth::check()) $this->redirect('/');
  $role = Auth::user()['role'] ?? '';
  if (!in_array($role, ['doctor','admin'])) exit('Forbidden');

  $orderId = (int)($_GET['order_id'] ?? 0);
  if ($orderId <= 0) { header('HTTP/1.0 404 Not Found'); exit('Order not found'); }

  $pdo = DB::pdo();

  // owner filter only if column exists and user isn't admin
  $ownerSql = '';
  $ownerArg = [];
  if ($this->hasColumn('lab_orders','doctor_id') && $role !== 'admin') {
    $ownerSql = ' AND o.doctor_id = :doc ';
    $ownerArg[':doc'] = (int)Auth::user()['id'];
  }

  // Load order
  $os = $pdo->prepare("
    SELECT o.*,
           p.id AS patient_id, p.first_name, p.last_name,
           lt.name AS test_name
    FROM lab_orders o
    JOIN patients p  ON p.id = o.patient_id
    JOIN lab_tests lt ON lt.id = o.test_id
    WHERE o.id = :oid {$ownerSql}
    LIMIT 1
  ");
  $args = array_merge([':oid' => $orderId], $ownerArg);
  $os->execute($args);
  $order = $os->fetch();
  if (!$order) { header('HTTP/1.0 404 Not Found'); exit('Order not found'); }

  // Column presence
  $hasOrderId      = $this->hasColumn('lab_reports', 'order_id');
  $hasTestId       = $this->hasColumn('lab_reports', 'test_id');
  $hasPatientId    = $this->hasColumn('lab_reports', 'patient_id');
  $hasTechnicianId = $this->hasColumn('lab_reports', 'technician_id');
  $hasReportedAt   = $this->hasColumn('lab_reports', 'reported_at');

  // SELECT list + optional join to technician user
  $select = "r.id, r.result_value, r.result_text, COALESCE(r.reported_at, r.created_at) AS reported_at";
  if ($hasOrderId)   $select .= ", r.order_id";
  if ($hasTestId)    $select .= ", r.test_id";
  if ($hasPatientId) $select .= ", r.patient_id";

  $joinUser = "";
  if ($hasTechnicianId) {
    $select  .= ", u.name AS technician_name";
    $joinUser = "LEFT JOIN users u ON u.id = r.technician_id";
  } else {
    $select  .= ", NULL AS technician_name";
  }

  // Prefer by order_id
  $report = null;
  if ($hasOrderId) {
    $rs = $pdo->prepare("
      SELECT {$select}
      FROM lab_reports r
      {$joinUser}
      WHERE r.order_id = :oid
      ORDER BY " . ($hasReportedAt ? "r.reported_at" : "r.created_at") . " DESC, r.id DESC
      LIMIT 1
    ");
    $rs->execute([':oid' => $orderId]);
    $report = $rs->fetch() ?: null;
  }

  // Fallback: patient + test
  if (!$report && $hasPatientId && $hasTestId) {
    $rs = $pdo->prepare("
      SELECT {$select}
      FROM lab_reports r
      {$joinUser}
      WHERE r.patient_id = :pid AND r.test_id = :tid
      ORDER BY " . ($hasReportedAt ? "r.reported_at" : "r.created_at") . " DESC, r.id DESC
      LIMIT 1
    ");
    $rs->execute([':pid' => (int)$order['patient_id'], ':tid' => (int)$order['test_id']]);
    $report = $rs->fetch() ?: null;
  }

  // Fallback: patient only
  if (!$report && $hasPatientId) {
    $rs = $pdo->prepare("
      SELECT {$select}
      FROM lab_reports r
      {$joinUser}
      WHERE r.patient_id = :pid
      ORDER BY " . ($hasReportedAt ? "r.reported_at" : "r.created_at") . " DESC, r.id DESC
      LIMIT 1
    ");
    $rs->execute([':pid' => (int)$order['patient_id']]);
    $report = $rs->fetch() ?: null;
  }

  $this->view('doctor/lab_result_view', [
    'order'  => $order,
    'report' => $report,
    'csrf'   => Csrf::token(),
  ]);
}

  /** Search form (used by Quick Links) */
  public function searchForm(): void {
    if (!Auth::check()) $this->redirect('/');
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $this->view('doctor/search', [
      'csrf' => Csrf::token(),
      'q' => '',
      'items' => []
    ]);
  }

  /** Execute search (used by Quick Links) */
  public function searchRun(): void {
    if (!Auth::check()) $this->redirect('/');
    if (!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $q = trim($_POST['q'] ?? '');
    $items = $q ? Patient::search($q) : [];

    $this->view('doctor/search', [
      'csrf' => Csrf::token(),
      'q' => $q,
      'items' => $items
    ]);
  }

  /** Patient profile view (linked from various places) */
  public function patientView(): void {
    if (!Auth::check()) $this->redirect('/');
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $id = (int)($_GET['id'] ?? 0);
    $p = Patient::find($id);
    if (!$p) $this->redirect('/doctor/search');

    // Prescriptions summary
    $rxStmt = DB::pdo()->prepare("
      SELECT pr.id, pr.created_at, u.name AS doctor
      FROM prescriptions pr
      JOIN users u ON u.id = pr.doctor_id
      WHERE pr.patient_id = ?
      ORDER BY pr.id DESC
    ");
    $rxStmt->execute([$id]);
    $prescriptions = $rxStmt->fetchAll();

    // Pending + Completed lab data
    $pending_lab     = LabOrder::forPatientPending($id);
    $lab_reports     = LabReport::forPatient($id);
    $patient_reports = PatientReport::forPatient($id);

    $this->view('doctor/patient_show', [
      'p' => $p,
      'prescriptions' => $prescriptions,
      'pending_lab' => $pending_lab,
      'lab_reports' => $lab_reports,
      'patient_reports' => $patient_reports
    ]);
  }

  /** Prescribe form */
  public function prescribeForm(): void {
    if (!Auth::check()) $this->redirect('/');
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $pid = (int)($_GET['patient_id'] ?? 0);
    $drugs = Drug::all();

    $this->view('doctor/prescribe', [
      'csrf' => Csrf::token(),
      'patient_id' => $pid,
      'drugs' => $drugs
    ]);
  }

  /** Save prescription + items; redirect to patient profile with flash */
  public function prescribeStore(): void {
    if (!Auth::check()) $this->redirect('/');
    if (!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $patientId = (int)$_POST['patient_id'];
    $prescriptionId = Prescription::create(
      $patientId,
      Auth::user()['id'],
      $_POST['notes'] ?? null
    );

    foreach($_POST['items'] as $row){
      if (empty($row['drug_id'])) continue;
      PrescriptionItem::add(
        $prescriptionId,
        (int)$row['drug_id'],
        trim($row['dosage'] ?? ''),
        trim($row['frequency'] ?? ''),
        (int)($row['duration'] ?? 0)
      );
    }

    $_SESSION['flash'] = 'Prescription created successfully.';
    $this->redirect('/doctor/patient?id=' . $patientId);
  }

  /** Create a doctor patient report (note) */
public function patientReportForm(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  $role = \App\Core\Auth::user()['role'] ?? '';
  if (!in_array($role, ['doctor','admin'])) exit('Forbidden');

  $pid  = (int)($_GET['patient_id'] ?? 0);
  $lrid = (int)($_GET['lab_report_id'] ?? 0);
  if ($pid <= 0) $this->redirect('/doctor/appointments');

  // patient header
  $ps = \App\Core\DB::pdo()->prepare("
    SELECT id, first_name, last_name, CONCAT('P', LPAD(id,3,'0')) AS code
    FROM patients WHERE id=?
  ");
  $ps->execute([$pid]);
  $patient = $ps->fetch();
  if (!$patient) { $_SESSION['flash']='Patient not found.'; $this->redirect('/doctor/appointments'); }

  // optional lab report context
  $rep = null;
  if ($lrid > 0) {
    $rs = \App\Core\DB::pdo()->prepare("
      SELECT r.id, r.result_value, r.result_text, r.reported_at, lt.name AS test_name
      FROM lab_reports r
      JOIN lab_tests lt ON lt.id=r.test_id
      WHERE r.id=?
    ");
    $rs->execute([$lrid]);
    $rep = $rs->fetch() ?: null;
  }

  $this->view('doctor/patient_report', [
    'csrf'    => \App\Core\Csrf::token(),
    'patient' => $patient,
    'report'  => $rep,
  ]);
}



public function patientReportStore(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!\App\Core\Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  $role = \App\Core\Auth::user()['role'] ?? '';
  if (!in_array($role, ['doctor','admin'])) exit('Forbidden');

  $pid   = (int)($_POST['patient_id'] ?? 0);
  $lrid  = (int)($_POST['lab_report_id'] ?? 0);
  $note  = trim($_POST['doctor_report'] ?? '');
  $uid   = \App\Core\Auth::user()['id'] ?? null;
  $uname = \App\Core\Auth::user()['name'] ?? 'Doctor';
  $now   = date('Y-m-d H:i:s');

  if ($pid <= 0 || $note === '') {
    $_SESSION['flash'] = 'Write a report before saving.';
    $this->redirect('/doctor/patient-report?patient_id='.$pid.'&lab_report_id='.$lrid);
  }

  $pdo = \App\Core\DB::pdo();
  $saved = false;

  // Preferred: separate table if present
  try {
    $pdo->query("SELECT 1 FROM doctor_reports LIMIT 1");
    $ins = $pdo->prepare("
      INSERT INTO doctor_reports (patient_id, lab_report_id, doctor_id, note_text, created_at)
      VALUES (?, ?, ?, ?, ?)
    ");
    $ins->execute([$pid, ($lrid ?: null), $uid, $note, $now]);
    $saved = true;
  } catch (\Throwable $e) {
    // Fallback: append into lab_reports.result_text (no schema change)
    if ($lrid > 0) {
      $appended = "\n\n--- Doctor Report (".$uname.", ".$now.") ---\n".$note;
      $upd = $pdo->prepare("UPDATE lab_reports SET result_text = CONCAT(COALESCE(result_text,''), ?) WHERE id=?");
      $upd->execute([$appended, $lrid]);
      $saved = true;
    }
  }

  $_SESSION['flash'] = $saved ? 'Doctor report saved.' : 'Unable to save doctor report.';
  $redirect = ($lrid > 0) ? (APP_URL.'/lab/report?id='.$lrid) : (APP_URL.'/doctor/appointments');
  header('Location: '.$redirect);
  exit;
}


  /** Overview list of lab reports and pending orders */
  public function labReports(): void {
    if (!Auth::check()) $this->redirect('/');
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $completed = LabReport::latest(100);
    $pending   = LabOrder::pending(100);

    $this->view('doctor/lab_reports', [
      'completed' => $completed,
      'pending'   => $pending
    ]);
  }

  /** Single lab report */
  public function labReportShow(): void {
    if (!Auth::check()) $this->redirect('/');
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $id = (int)($_GET['id'] ?? 0);
    $r  = LabReport::find($id);
    if (!$r) { header('HTTP/1.0 404 Not Found'); exit('Report not found'); }

    $this->view('doctor/lab_report_show', ['r' => $r]);
  }

  /** Lab order form + store */
  public function labOrderForm(): void {
    if (!Auth::check()) $this->redirect('/');
    $role = Auth::user()['role'] ?? '';
    if (!in_array($role, ['doctor','admin'])) exit('Forbidden');

   $pid = (int)($_GET['patient_id'] ?? $_GET['id'] ?? 0);
    if ($pid <= 0) {
      $_SESSION['flash'] = 'Select a patient first.';
      $this->redirect('/doctor'); // or your doctor dashboard/patient search
    }

    // Minimal patient header (privacy-preserving)
    $ps = \App\Core\DB::pdo()->prepare("
      SELECT id, first_name, last_name, CONCAT('P', LPAD(id,3,'0')) AS code
      FROM patients WHERE id=?
    ");
    $ps->execute([$pid]);
    $patient = $ps->fetch();
    if (!$patient) {
      $_SESSION['flash'] = 'Patient not found.';
      $this->redirect('/doctor');
    }

    $tests = LabTest::all(); // assumes returns id, name
    $this->view('doctor/lab_order_new', [
      'csrf'    => Csrf::token(),
      'patient' => $patient,
      'tests'   => $tests
    ]);
  }

  public function labOrderStore(): void {
  if (!Auth::check()) $this->redirect('/');
  if (!Csrf::check($_POST['_token'] ?? '')) { http_response_code(419); exit('CSRF'); }
  $role = Auth::user()['role'] ?? '';
  if (!in_array($role, ['doctor','admin'])) exit('Forbidden');

  $pid   = (int)($_POST['patient_id'] ?? 0);
  $test  = (int)($_POST['test_id'] ?? 0);
  $notes = trim($_POST['order_notes'] ?? '');

  if ($pid <= 0 || $test <= 0) {
    $_SESSION['flash'] = 'Please choose a patient and a test.';
    $this->redirect('/doctor/lab-order?patient_id=' . $pid);
  }

  // Make sure patient exists
  $exists = DB::pdo()->prepare("SELECT 1 FROM patients WHERE id=?");
  $exists->execute([$pid]);
  if (!$exists->fetchColumn()) {
    $_SESSION['flash'] = 'Patient not found.';
    $this->redirect('/doctor/search');
  }

  try {
    // Insert (your LabOrder model should return insert id)
    $orderId = LabOrder::create($pid, $test, Auth::user()['id'], $notes !== '' ? $notes : null);

    if ($orderId) {
      $_SESSION['flash'] = 'Lab order placed.';
      // After placing, send doctor to their orders list (or lab pending page if you prefer)
      $this->redirect('/doctor/lab-orders?status=ordered');
    } else {
      $_SESSION['flash'] = 'Failed to create lab order.';
      $this->redirect('/doctor/lab-order?patient_id=' . $pid);
    }
  } catch (\Throwable $e) {
    // error_log('LabOrder create failed: '.$e->getMessage()); // enable while debugging
    $_SESSION['flash'] = 'Error while creating order. Check DB columns/constraints.';
    $this->redirect('/doctor/lab-order?patient_id=' . $pid);
  }
}
  /** Doctor → Lab Orders list (schema-aware doctor scoping) */
public function labOrders(): void {
  if (!Auth::check()) $this->redirect('/');
  $role = Auth::user()['role'] ?? '';
  if (!in_array($role, ['doctor','admin'])) exit('Forbidden');

  $status   = isset($_GET['status']) && in_array($_GET['status'], ['ordered','pending','in_progress','completed','reported'], true)
            ? $_GET['status'] : '';
  $q        = trim($_GET['q'] ?? '');
  $page     = max(1, (int)($_GET['page'] ?? 1));
  $perPage  = 12;
  $offset   = ($page - 1) * $perPage;

  $pdo   = DB::pdo();
  $where = " WHERE 1=1 ";
  $args  = [];

  // doctor scoping ONLY if the column exists and user isn't admin
  if ($this->hasColumn('lab_orders','doctor_id') && $role !== 'admin') {
    $where .= " AND o.doctor_id = :doc ";
    $args[':doc'] = (int)Auth::user()['id'];
  }

  if ($status !== '') { $where .= " AND o.status = :st "; $args[':st'] = $status; }
  if ($q !== '') {
    $where .= " AND (CONCAT(p.first_name,' ',p.last_name) LIKE :q OR p.first_name LIKE :q OR p.last_name LIKE :q OR lt.name LIKE :q) ";
    $args[':q'] = "%{$q}%";
  }

  // count
  $cnt = $pdo->prepare("
    SELECT COUNT(*)
    FROM lab_orders o
    JOIN patients p  ON p.id = o.patient_id
    JOIN lab_tests lt ON lt.id = o.test_id
    {$where}
  ");
  $cnt->execute($args);
  $total = (int)$cnt->fetchColumn();

  // page data
  $sql = "
    SELECT o.*,
           p.id AS patient_id, p.first_name, p.last_name,
           lt.name AS test_name
    FROM lab_orders o
    JOIN patients p  ON p.id = o.patient_id
    JOIN lab_tests lt ON lt.id = o.test_id
    {$where}
    ORDER BY o.created_at DESC, o.id DESC
    LIMIT :lim OFFSET :off
  ";
  $st = $pdo->prepare($sql);
  foreach ($args as $k => $v) $st->bindValue($k, $v);
  $st->bindValue(':lim', $perPage, \PDO::PARAM_INT);
  $st->bindValue(':off', $offset,  \PDO::PARAM_INT);
  $st->execute();
  $rows = $st->fetchAll();

  $this->view('doctor/lab_orders', [
    'rows'   => $rows,
    'status' => $status,
    'q'      => $q,
    'page'   => $page,
    'pages'  => (int)ceil($total / $perPage),
    'csrf'   => Csrf::token(),
  ]);
}

private function hasColumn(string $table, string $column): bool {
  $st = DB::pdo()->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
  $st->execute([$column]);
  return (bool)$st->fetch();
}


  //Doctor's appts
  public function appointments(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  $role = \App\Core\Auth::user()['role'] ?? '';
  if (!in_array($role, ['doctor','admin'])) exit('Forbidden');

  $doctorId = \App\Core\Auth::user()['id'];

  // Fetch without assuming column names for date/time
  $sql = "
    SELECT
      a.*,                      -- we'll derive the 'when' in PHP
      p.id   AS patient_id,
      p.first_name,
      p.last_name
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    WHERE a.doctor_id = ?
    LIMIT 1000
  ";
  $st = \App\Core\DB::pdo()->prepare($sql);
  $st->execute([$doctorId]);
  $raw = $st->fetchAll();

  $todayStart = date('Y-m-d 00:00:00');

  // Normalize rows for the view: id, scheduled_at, status, patient_id, first_name, last_name, code
  $rows = [];
  foreach ($raw as $a) {
    // Derive the "when" field from whatever exists
    $when = null;
    if (!empty($a['scheduled_at'])) {
      $when = $a['scheduled_at'];
    } elseif (!empty($a['appointment_at'])) {
      $when = $a['appointment_at'];
    } elseif (!empty($a['date_time'])) {
      $when = $a['date_time'];
    } elseif (!empty($a['appointment_date']) && !empty($a['appointment_time'])) {
      $when = $a['appointment_date'].' '.$a['appointment_time'];
    } elseif (!empty($a['date']) && !empty($a['time'])) {
      $when = $a['date'].' '.$a['time'];
    } elseif (!empty($a['date'])) {
      $when = $a['date'].' 00:00:00';
    } elseif (!empty($a['created_at'])) {
      $when = $a['created_at']; // fallback
    }

    if ($when === null) continue;          // skip rows with no usable time
    if ($when < $todayStart) continue;     // only upcoming/today; remove this if you want all

    $pid = (int)$a['patient_id'];
    $rows[] = [
      'id'          => (int)$a['id'],
      'scheduled_at'=> $when,
      'status'      => $a['status'] ?? 'scheduled',
      'patient_id'  => $pid,
      'first_name'  => $a['first_name'] ?? '',
      'last_name'   => $a['last_name'] ?? '',
      'code'        => 'P'.str_pad((string)$pid, 3, '0', STR_PAD_LEFT),
    ];
  }

  // Sort by soonest first
  usort($rows, function($a, $b){
    return strcmp($a['scheduled_at'], $b['scheduled_at']);
  });

  $this->view('doctor/appointments', ['rows' => $rows]);
}

//Display appointments in doctor's console
public function patientsScheduled(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  $role = \App\Core\Auth::user()['role'] ?? '';
  if (!in_array($role, ['doctor','admin'])) exit('Forbidden');

  $doctorId = \App\Core\Auth::user()['id'];

  // 1) Fetch without assuming column names for time fields
  $sql = "
    SELECT
      a.*,                       -- we will interpret time fields in PHP
      p.id   AS patient_id,
      p.first_name,
      p.last_name
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    WHERE a.doctor_id = ?
    LIMIT 1000
  ";
  $st = \App\Core\DB::pdo()->prepare($sql);
  $st->execute([$doctorId]);
  $raw = $st->fetchAll();

  // 2) Helper to compute a DateTime string from whatever columns exist
  $todayStart = date('Y-m-d 00:00:00');

  $rowsByPatient = []; // patient_id => ['id','first_name','last_name','code','next_visit','upcoming_count']

  foreach ($raw as $a) {
    // Build a "when" string from available fields
    $when = null;

    // Common patterns: scheduled_at, appointment_at, date_time, date+time, created_at
    if (!empty($a['scheduled_at'])) {
      $when = $a['scheduled_at'];
    } elseif (!empty($a['appointment_at'])) {
      $when = $a['appointment_at'];
    } elseif (!empty($a['date_time'])) {
      $when = $a['date_time'];
    } elseif (!empty($a['appointment_date']) && !empty($a['appointment_time'])) {
      $when = $a['appointment_date'] . ' ' . $a['appointment_time'];
    } elseif (!empty($a['date']) && !empty($a['time'])) {
      $when = $a['date'] . ' ' . $a['time'];
    } elseif (!empty($a['date'])) {
      $when = $a['date'] . ' 00:00:00';
    } elseif (!empty($a['created_at'])) {
      $when = $a['created_at']; // last resort
    }

    // Normalize to Y-m-d H:i:s if possible
    // If it's a date only, it's fine; comparisons below are string-based on this format.
    // Skip past/invalid if we only want upcoming; if you want all, remove this check.
    if ($when === null) continue;

    // Keep only today and future
    if ($when < $todayStart) continue;

    $pid = (int)$a['patient_id'];
    $first = $a['first_name'] ?? '';
    $last  = $a['last_name'] ?? '';

    if (!isset($rowsByPatient[$pid])) {
      $rowsByPatient[$pid] = [
        'id' => $pid,
        'first_name' => $first,
        'last_name'  => $last,
        'code' => 'P' . str_pad((string)$pid, 3, '0', STR_PAD_LEFT),
        'next_visit' => $when,
        'upcoming_count' => 1,
      ];
    } else {
      // Update next_visit if this is sooner
      if ($when < $rowsByPatient[$pid]['next_visit']) {
        $rowsByPatient[$pid]['next_visit'] = $when;
      }
      $rowsByPatient[$pid]['upcoming_count']++;
    }
  }

  // 3) Sort by next_visit ascending
  usort($rowsByPatient, function($a, $b){
    return strcmp($a['next_visit'], $b['next_visit']);
  });

  $this->view('doctor/patients_scheduled', ['rows' => $rowsByPatient]);
}



}
