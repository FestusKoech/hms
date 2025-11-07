<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf,Gate};
use App\Models\{Patient};

final class PatientsController extends Controller {
  public function index(): void {
    if(!Auth::check()) $this->redirect('/');
    $page = max(1,(int)($_GET['page'] ?? 1));
    $res = Patient::paginate($page, 15);
    $this->view('patients/index', $res);
  }
  public function create(): void {
    if(!Auth::check()) $this->redirect('/');
    $this->view('patients/create',['csrf'=>\App\Core\Csrf::token()]);
  }
  public function store(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    $id = Patient::create([
      'patient_no'=>trim($_POST['patient_no']),
      'first_name'=>trim($_POST['first_name']),
      'last_name'=>trim($_POST['last_name']),
      'dob'=>$_POST['dob'] ?: null,
      'sex'=>$_POST['sex'] ?: null,
      'contact'=>$_POST['contact'] ?: null,
      'address'=>$_POST['address'] ?: null,
      'emergency_contact'=>$_POST['emergency_contact'] ?: null,
      'created_by'=>\App\Core\Auth::user()['id']
    ]);
    $this->redirect('/patients');
  }
  public function show(): void {
    if(!Auth::check()) $this->redirect('/');
    $p = Patient::find((int)($_GET['id'] ?? 0));
    $this->view('patients/show',['p'=>$p]);
  }
  public function edit(): void {
    if(!Auth::check()) $this->redirect('/');
    $p = Patient::find((int)($_GET['id'] ?? 0));
    $this->view('patients/edit',['p'=>$p,'csrf'=>\App\Core\Csrf::token()]);
  }
  public function update(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    Patient::update((int)$_POST['id'], [
      'patient_no'=>trim($_POST['patient_no']),
      'first_name'=>trim($_POST['first_name']),
      'last_name'=>trim($_POST['last_name']),
      'dob'=>$_POST['dob'] ?: null,
      'sex'=>$_POST['sex'] ?: null,
      'contact'=>$_POST['contact'] ?: null,
      'address'=>$_POST['address'] ?: null,
      'emergency_contact'=>$_POST['emergency_contact'] ?: null,
    ]);
    $this->redirect('/patients');
  }
  public function delete(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    Patient::delete((int)$_POST['id']);
    $this->redirect('/patients');
  }
}
