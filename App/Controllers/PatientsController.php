<?php
namespace App\Controllers;

use App\Core\{Controller,Auth,Csrf};
use App\Models\{Patient};

final class PatientsController extends Controller {

  public function index(): void {
    if(!Auth::check()) $this->redirect('/');

    $q = trim($_GET['q'] ?? '');
    $page = max(1,(int)($_GET['page'] ?? 1));
    $per = 15;

    if($q !== '' && method_exists(Patient::class, 'search')){
      $rows = Patient::search($q, 100);
      $res = ['data'=>$rows,'total'=>count($rows),'page'=>1,'per'=>count($rows)];
    } else {
      $res = Patient::paginate($page, $per);
    }

    $res['csrf'] = Csrf::token();
    $res['q']    = $q;
    $res['role'] = Auth::user()['role'] ?? '';

    $this->view('patients/index', $res);
  }

  public function create(): void {
    if(!Auth::check()) $this->redirect('/');
    $this->view('patients/create', ['csrf'=>Csrf::token()]);
  }

  public function store(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');

    \App\Models\Patient::create([
      'patient_no'=>trim($_POST['patient_no']),
      'first_name'=>trim($_POST['first_name']),
      'last_name' =>trim($_POST['last_name']),
      'dob'       =>$_POST['dob'] ?: null,
      'sex'       =>$_POST['sex'] ?: null,
      'contact'   =>$_POST['contact'] ?: null,
      'address'   =>$_POST['address'] ?: null,
      'emergency_contact'=>$_POST['emergency_contact'] ?: null,
      'created_by'=>Auth::user()['id']
    ]);

    $this->redirect('/patients');
  }

  public function show(): void {
    if(!Auth::check()) $this->redirect('/');
    $id = (int)($_GET['id'] ?? 0);
    $p = \App\Models\Patient::find($id);
    if(!$p){ header('HTTP/1.0 404 Not Found'); exit('Patient not found'); }

    $this->view('patients/show', [
      'p'=>$p,
      'role'=>Auth::user()['role'] ?? '',
      'csrf'=>Csrf::token()
    ]);
  }

  public function edit(): void {
    if(!Auth::check()) $this->redirect('/');
    $id = (int)($_GET['id'] ?? 0);
    $p = \App\Models\Patient::find($id);
    if(!$p){ header('HTTP/1.0 404 Not Found'); exit('Patient not found'); }

    $this->view('patients/edit', ['p'=>$p,'csrf'=>Csrf::token()]);
  }

  public function update(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');

    \App\Models\Patient::update((int)$_POST['id'], [
      'patient_no'       => trim($_POST['patient_no']),
      'first_name'       => trim($_POST['first_name']),
      'last_name'        => trim($_POST['last_name']),
      'dob'              => $_POST['dob'] ?: null,
      'sex'              => $_POST['sex'] ?: null,
      'contact'          => $_POST['contact'] ?: null,
      'address'          => $_POST['address'] ?: null,
      'emergency_contact'=> $_POST['emergency_contact'] ?: null,
    ]);

    $this->redirect('/patients/show?id='.(int)$_POST['id']);
  }

  public function delete(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    \App\Models\Patient::delete((int)$_POST['id']);
    $this->redirect('/patients');
  }
}
