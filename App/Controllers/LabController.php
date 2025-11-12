<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf};
use App\Models\{Patient,LabTest,LabReport};

final class LabController extends Controller {
  public function index(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['labtech','admin', 'doctor'])) exit('Forbidden');
    $this->view('lab/index',['items'=>LabReport::latest(50)]);
  }

  

  public function create(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');
    $pdo = \App\Core\DB::pdo();
    $patients = $pdo->query("SELECT id,first_name,last_name FROM patients ORDER BY first_name")->fetchAll();
    $tests = LabTest::all();
    $this->view('lab/create',['csrf'=>Csrf::token(),'patients'=>$patients,'tests'=>$tests]);
  }

public function orders(): void {
  if(!\App\Core\Auth::check()) $this->redirect('/');
  if(!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');
  $items = \App\Models\LabOrder::pending(100);
  $this->view('lab/orders', ['items'=>$items]);
}

public function reportFromOrderForm(): void {
  if(!\App\Core\Auth::check()) $this->redirect('/');
  if(!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');
  $orderId=(int)($_GET['order_id'] ?? 0);
  $o=\App\Models\LabOrder::find($orderId);
  if(!$o) $this->redirect('/lab/orders');

  $this->view('lab/report_from_order', ['csrf'=>\App\Core\Csrf::token(),'order'=>$o]);
}

public function reportFromOrderStore(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!\App\Core\Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  if (!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

  $orderId = (int)($_POST['order_id'] ?? 0);
  if ($orderId <= 0) $this->redirect('/lab/orders');

  $order = \App\Models\LabOrder::find($orderId);
  if (!$order) $this->redirect('/lab/orders');

  $pdo   = \App\Core\DB::pdo();
  $user  = \App\Core\Auth::user();
  $now   = date('Y-m-d H:i:s');
  $value = $_POST['result_value'] ?? null;
  $text  = $_POST['result_text']  ?? null;

  // --- Feature-detect if lab_reports.order_id exists ---
  $hasOrderId = false;
  try {
    // Will throw if column doesn't exist
    $pdo->query("SELECT order_id FROM lab_reports LIMIT 1");
    $hasOrderId = true;
  } catch (\Throwable $e) { /* no order_id column */ }

  // --- If a report already exists for this order, update it instead of creating a new one ---
  if ($hasOrderId) {
    $check = $pdo->prepare("SELECT id FROM lab_reports WHERE order_id = ? LIMIT 1");
    $check->execute([$orderId]);
    $existingId = $check->fetchColumn();

    if ($existingId) {
      $upd = $pdo->prepare("
        UPDATE lab_reports
        SET result_value = ?, result_text = ?, reported_by = ?, reported_at = ?
        WHERE id = ?
      ");
      $upd->execute([$value, $text, $user['id'], $now, $existingId]);

      \App\Models\LabOrder::markReported($orderId);
      $_SESSION['flash'] = 'Report updated for this order.';
      $this->redirect('/lab/report?id=' . (int)$existingId);
      return;
    }
  }

  // --- Otherwise: create fresh report (legacy-compatible) ---
  $labReportId = \App\Models\LabReport::create([
    'patient_id'   => $order['patient_id'],
    'test_id'      => $order['test_id'],
    'ordered_by'   => $order['ordered_by'] ?? null,
    'result_value' => $value,
    'result_text'  => $text,
    'reported_by'  => $user['id'],
    'reported_at'  => $now,
  ]);

  // Stamp order_id if the column exists (no-op if absent)
  if ($hasOrderId) {
    $stmt = $pdo->prepare("UPDATE lab_reports SET order_id = ? WHERE id = ?");
    $stmt->execute([$orderId, $labReportId]);
  }

  \App\Models\LabOrder::markReported($orderId);
  $_SESSION['flash'] = 'Report saved for the order.';
  $this->redirect('/lab/report?id='.(int)$labReportId);
}


//Search funtion in lab
public function search(): void {
  // roles: allow labtech/admin; add 'doctor' here if you want read-only access
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

  $q = trim($_GET['q'] ?? '');
  $results = [];

  if ($q !== '') {
    // normalize inputs
    $like    = '%'.$q.'%';
    $idMaybe = ctype_digit($q) ? (int)$q : -1;

    // If user typed something like P013, match it exactly & via LIKE
    $qUpper   = strtoupper($q);
    $codeEq   = preg_match('/^P\d+$/i', $qUpper) ? $qUpper : 'NO_MATCH_CODE_EQ';
    $codeLike = '%'.$qUpper.'%';

    $st = \App\Core\DB::pdo()->prepare("
      SELECT id,
             first_name,
             last_name,
             CONCAT('P', LPAD(id, 3, '0')) AS code
      FROM patients
      WHERE first_name LIKE ?
         OR last_name  LIKE ?
         OR id = ?
         OR CONCAT('P', LPAD(id, 3, '0')) = ?
         OR CONCAT('P', LPAD(id, 3, '0')) LIKE ?
      ORDER BY id DESC
      LIMIT 25
    ");
    $st->execute([$like, $like, $idMaybe, $codeEq, $codeLike]);
    $results = $st->fetchAll();
  }

  $this->view('lab/search', ['q'=>$q, 'results'=>$results]);
}


//Patient view
public function patientPanel(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

  $pid = (int)($_GET['id'] ?? 0);
  if ($pid <= 0) $this->redirect('/lab/search');

  // Patient header (minimal)
  $ps = \App\Core\DB::pdo()->prepare("
  SELECT id, first_name, last_name,
         CONCAT('P', LPAD(id, 3, '0')) AS code
  FROM patients WHERE id=?
");
  $ps->execute([$pid]);
  $patient = $ps->fetch();
  if (!$patient) { $_SESSION['flash']='Patient not found.'; $this->redirect('/lab/search'); }

  // Orders for this patient
  $os = \App\Core\DB::pdo()->prepare("
    SELECT o.id, o.status, o.created_at, lt.name AS test_name
    FROM lab_orders o
    JOIN lab_tests lt ON lt.id=o.test_id
    WHERE o.patient_id=?
    ORDER BY o.id DESC
    LIMIT 50
  ");
  $os->execute([$pid]);
  $orders = $os->fetchAll();

  $this->view('lab/patient', ['patient'=>$patient, 'orders'=>$orders]);
}

//pending results
public function pending(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

 $st = \App\Core\DB::pdo()->query("
  SELECT o.id, o.patient_id, lt.name AS test_name, o.created_at,
         p.first_name, p.last_name,
         CONCAT('P', LPAD(p.id, 3, '0')) AS code
  FROM lab_orders o
  JOIN lab_tests lt ON lt.id=o.test_id
  JOIN patients p   ON p.id=o.patient_id
  WHERE o.status='ordered'
  ORDER BY o.created_at DESC
  LIMIT 100
");

  $rows = $st->fetchAll();

  $this->view('lab/pending', ['rows'=>$rows]);
}


//completed lab orders
public function completed(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

 $st = \App\Core\DB::pdo()->query("
  SELECT r.id AS report_id, r.patient_id, r.reported_at, r.reported_by,
         lt.name AS test_name,
         p.first_name, p.last_name,
         CONCAT('P', LPAD(p.id, 3, '0')) AS code,
         u.name AS reported_by_name
  FROM lab_reports r
  JOIN lab_tests lt ON lt.id=r.test_id
  JOIN patients p   ON p.id=r.patient_id
  LEFT JOIN users u ON u.id=r.reported_by
  ORDER BY r.reported_at DESC
  LIMIT 100
");
  $rows = $st->fetchAll();

  $this->view('lab/completed', ['rows'=>$rows]);
}
  public function store(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if(!in_array(Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

    LabReport::create([
      'patient_id'=>(int)$_POST['patient_id'],
      'test_id'   =>(int)$_POST['test_id'],
      'ordered_by'=>(int)$_POST['ordered_by'],       // doctor id (select)
      'result_value'=>$_POST['result_value'] ?: null,
      'result_text' =>$_POST['result_text'] ?: null,
      'reported_by'=>Auth::user()['id'],
      'reported_at'=>date('Y-m-d H:i:s')
    ]);
    $this->redirect('/lab');
  }

//Report show
public function reportShow(): void {
  // allow labtech/admin; add 'doctor' if you want read-only doctor access
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

  $rid = (int)($_GET['id'] ?? 0);
  if ($rid <= 0) $this->redirect('/lab/completed');

  $st = \App\Core\DB::pdo()->prepare("
    SELECT r.id, r.patient_id, r.test_id, r.order_id,
           r.result_value, r.result_text, r.reported_by, r.reported_at,
           lt.name AS test_name,
           p.first_name, p.last_name,
           CONCAT('P', LPAD(p.id, 3, '0')) AS code,
           u.name AS reported_by_name
    FROM lab_reports r
    JOIN lab_tests lt ON lt.id = r.test_id
    JOIN patients  p  ON p.id  = r.patient_id
    LEFT JOIN users u ON u.id  = r.reported_by
    WHERE r.id = ?
    LIMIT 1
  ");
  $st->execute([$rid]);
  $rep = $st->fetch();

  if (!$rep) {
    $_SESSION['flash'] = 'Report not found.';
    $this->redirect('/lab/completed');
  }

  $this->view('lab/report_show', ['rep' => $rep]);
}

public function testCreate(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  $role = \App\Core\Auth::user()['role'] ?? '';
  if (!in_array($role, ['doctor','labtech','admin'])) exit('Forbidden');

  if (!\App\Core\Csrf::check($_POST['_token'] ?? '')) { http_response_code(419); exit('CSRF'); }

  $name      = trim($_POST['name'] ?? '');
  $infection = trim($_POST['infection'] ?? ''); // optional
  $patientId = (int)($_POST['patient_id'] ?? 0); // to return back to the same form

  if ($name === '') {
    $_SESSION['flash'] = 'Test name is required.';
    $this->redirect('/doctor/lab-order?patient_id=' . $patientId);
  }

  // Create (returns new test id)
  $testId = \App\Models\LabTest::create($name, $infection !== '' ? $infection : null);

  $_SESSION['flash'] = 'New lab test added.';
  // Redirect back to lab-order with the new test preselected
  $this->redirect('/doctor/lab-order?patient_id=' . $patientId . '&test_id=' . (int)$testId);
}


}
