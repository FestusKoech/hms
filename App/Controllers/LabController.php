<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf};
use App\Models\{Patient,LabTest,LabReport};

final class LabController extends Controller {
  public function index(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');
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

  $user = \App\Core\Auth::user();
  $now  = date('Y-m-d H:i:s');

  // 1) Create the lab report (your current behavior)
  $labReportId = \App\Models\LabReport::create([
    'patient_id'   => $order['patient_id'],
    'test_id'      => $order['test_id'],
    'ordered_by'   => $order['ordered_by'],
    'result_value' => $_POST['result_value'] ?? null,
    'result_text'  => $_POST['result_text'] ?? null,
    'reported_by'  => $user['id'],
    'reported_at'  => $now,
  ]);

  // 2) Mark the order reported (your current behavior)
  \App\Models\LabOrder::markReported($orderId);

  // 3) OPTIONAL stamps (only if columns exist) — wrapped in try/catch so it never breaks your current schema
  $pdo = \App\Core\DB::pdo();
  try {
    // Link report to order if `order_id` exists on lab_reports
    $pdo->exec("UPDATE lab_reports SET order_id = order_id WHERE 1=0"); // feature-detect column
    $stmt = $pdo->prepare("UPDATE lab_reports SET order_id=? WHERE id=?");
    $stmt->execute([$orderId, $labReportId]);
  } catch (\Throwable $e) {
    // Column doesn't exist or no perms — ignore silently
  }

  try {
    // Stamp who added the findings if columns exist (added_by_user_id, added_by_role)
    $pdo->exec("UPDATE lab_reports SET added_by_user_id = added_by_user_id WHERE 1=0");
    $stmt = $pdo->prepare("UPDATE lab_reports SET added_by_user_id=?, added_by_role=? WHERE id=?");
    $stmt->execute([$user['id'], 'lab', $labReportId]);
  } catch (\Throwable $e) {
    // Columns not present — ignore
  }

  // 4) Flash + redirect
  $_SESSION['flash'] = 'Report saved for the order.';
  $this->redirect('/lab/orders');
}


//Search funtion in lab
public function search(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

  $q = trim($_GET['q'] ?? '');
  $results = [];
  if ($q !== '') {
    $like = '%'.$q.'%';
    $idMaybe = ctype_digit($q) ? (int)$q : 0;
    $st = \App\Core\DB::pdo()->prepare("
      SELECT id, first_name, last_name, code
      FROM patients
      WHERE first_name LIKE ? OR last_name LIKE ? OR code LIKE ? OR id=?
      ORDER BY id DESC
      LIMIT 25
    ");
    $st->execute([$like,$like,$like,$idMaybe]);
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
  $ps = \App\Core\DB::pdo()->prepare("SELECT id, first_name, last_name, code FROM patients WHERE id=?");
  $ps->execute([$pid]);
  $patient = $ps->fetch();
  if (!$patient) { $_SESSION['flash']='Patient not found.'; $this->redirect('/lab/search'); }

  // Orders for this patient
  $os = \App\Core.DB::pdo()->prepare("
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
           p.first_name, p.last_name, p.code
    FROM lab_orders o
    JOIN lab_tests lt ON lt.id=o.test_id
    JOIN patients p ON p.id=o.patient_id
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
           p.first_name, p.last_name, p.code,
           u.name AS reported_by_name
    FROM lab_reports r
    JOIN lab_tests lt  ON lt.id=r.test_id
    JOIN patients p    ON p.id=r.patient_id
    LEFT JOIN users u  ON u.id=r.reported_by
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
}
