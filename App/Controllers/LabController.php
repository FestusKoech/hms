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
  if(!\App\Core\Auth::check()) $this->redirect('/');
  if(!\App\Core\Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  if(!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');

  $orderId=(int)$_POST['order_id'];
  $order=\App\Models\LabOrder::find($orderId);
  if(!$order) $this->redirect('/lab/orders');

  \App\Models\LabReport::create([
    'patient_id'=>$order['patient_id'],
    'test_id'=>$order['test_id'],
    'ordered_by'=>$order['ordered_by'],
    'result_value'=>$_POST['result_value'] ?? null,
    'result_text'=>$_POST['result_text'] ?? null,
    'reported_by'=>\App\Core\Auth::user()['id'],
    'reported_at'=>date('Y-m-d H:i:s')
  ]);
  \App\Models\LabOrder::markReported($orderId);

  $_SESSION['flash']='Report saved for the order.';
  $this->redirect('/lab/orders');
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
