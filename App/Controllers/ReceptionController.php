<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf};
use App\Models\{Patient};

final class ReceptionController extends Controller {
  public function patients(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');
    $page = max(1,(int)($_GET['page'] ?? 1));
    $res = Patient::paginate($page, 20);
    $this->view('reception/patients',$res);
  }
  public function create(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');
    $this->view('reception/create',['csrf'=>Csrf::token()]);
  }
  public function store(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if(!in_array(Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');
    \App\Models\Patient::create([
      'patient_no'=>trim($_POST['patient_no']),
      'first_name'=>trim($_POST['first_name']),
      'last_name'=>trim($_POST['last_name']),
      'dob'=>$_POST['dob'] ?: null,
      'sex'=>$_POST['sex'] ?: null,
      'contact'=>$_POST['contact'] ?: null,
      'address'=>$_POST['address'] ?: null,
      'emergency_contact'=>$_POST['emergency_contact'] ?: null,
      'created_by'=>Auth::user()['id']
    ]);
    $this->redirect('/reception/patients');
  }
}
