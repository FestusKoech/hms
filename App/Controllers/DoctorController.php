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
public function addPatientReportForm(): void {
  if (!Auth::check()) $this->redirect('/');
  if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

  $pid          = (int)($_GET['patient_id'] ?? 0);
  $labReportId  = isset($_GET['lab_report_id']) ? (int)$_GET['lab_report_id'] : null;
  $prefillTitle = trim($_GET['title'] ?? 'Report');

  $this->view('doctor/patient_report_create', [
    'csrf'          => Csrf::token(),
    'patient_id'    => $pid,
    'lab_report_id' => $labReportId,
    'title'         => $prefillTitle,
  ]);
}


public function addPatientReportStore(): void {
  if (!Auth::check()) $this->redirect('/');
  if (!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

  $patientId    = (int)$_POST['patient_id'];
  $doctorId     = Auth::user()['id'];
  $title        = trim($_POST['title'] ?? 'Report');
  $body         = trim($_POST['body'] ?? '');
  $labReportId  = isset($_POST['lab_report_id']) ? (int)$_POST['lab_report_id'] : null;

  // Create doctor report
  $patientReportId = PatientReport::create($patientId, $doctorId, $title, $body);

  // If the schema has lab_report_id (it does in your working code), link it.
  if ($labReportId) {
    $st = DB::pdo()->prepare("UPDATE patient_reports SET lab_report_id=? WHERE id=?");
    $st->execute([$labReportId, $patientReportId]);
  }

  $_SESSION['flash'] = 'Patient report saved.';
  $this->redirect('/doctor/patient?id='.$patientId);
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
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $pid   = (int)($_GET['patient_id'] ?? 0);
    $tests = LabTest::all();

    $this->view('doctor/lab_order', [
      'csrf' => Csrf::token(),
      'patient_id' => $pid,
      'tests' => $tests
    ]);
  }

  public function labOrderStore(): void {
    if (!Auth::check()) $this->redirect('/');
    if (!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if (!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');

    $pid  = (int)$_POST['patient_id'];
    $test = (int)$_POST['test_id'];

    LabOrder::create($pid, $test, Auth::user()['id']);
    $_SESSION['flash'] = 'Lab order created successfully.';
    $this->redirect('/doctor/patient?id='.$pid);
  }
}
