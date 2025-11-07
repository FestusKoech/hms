<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf};
use App\Models\{Appointment,Patient,User};

final class AppointmentsController extends Controller {
  public function index(): void {
    if(!Auth::check()) $this->redirect('/');
    $this->view('appointments/index', ['items'=>Appointment::list()]);
  }
  public function create(): void {
    if(!Auth::check()) $this->redirect('/');
    // simple lists (replace with proper repos as you expand)
    $pdo = \App\Core\DB::pdo();
    $patients = $pdo->query("SELECT id,first_name,last_name FROM patients ORDER BY first_name")->fetchAll();
    $doctors  = $pdo->query("SELECT id,name FROM users WHERE role='doctor' ORDER BY name")->fetchAll();
    $this->view('appointments/create', ['patients'=>$patients,'doctors'=>$doctors,'csrf'=>\App\Core\Csrf::token()]);
  }
  public function store(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    Appointment::create([
      'patient_id'=>(int)$_POST['patient_id'],
      'doctor_id'=>(int)$_POST['doctor_id'],
      'starts_at'=>$_POST['starts_at'],
      'ends_at'  =>$_POST['ends_at'],
      'reason'   =>$_POST['reason'] ?: null
    ]);
    $this->redirect('/appointments');
  }
  public function delete(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    Appointment::delete((int)$_POST['id']);
    $this->redirect('/appointments');
  }
}
