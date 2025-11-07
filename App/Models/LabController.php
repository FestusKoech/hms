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

  public function reportShow(): void {
  if(!\App\Core\Auth::check()) $this->redirect('/');
  if(!in_array(\App\Core\Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');
  $id=(int)($_GET['id'] ?? 0);
  $r=\App\Models\LabReport::find($id);
  if(!$r){ header('HTTP/1.0 404 Not Found'); exit('Report not found'); }
  $this->view('lab/report_show', ['r'=>$r]);
}


  public function create(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['labtech','admin'])) exit('Forbidden');
    $pdo = \App\Core\DB::pdo();
    $patients = $pdo->query("SELECT id,first_name,last_name FROM patients ORDER BY first_name")->fetchAll();
    $tests = LabTest::all();
    $this->view('lab/create',['csrf'=>Csrf::token(),'patients'=>$patients,'tests'=>$tests]);
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
