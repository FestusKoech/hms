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

  \App\Core\Helpers::clearErrors();
  \App\Core\Helpers::setOld($_POST);

  $patient_id = (int)($_POST['patient_id'] ?? 0);
  $doctor_id  = trim($_POST['doctor_id'] ?? '') === '' ? null : (int)$_POST['doctor_id'];
  $starts_at  = trim($_POST['starts_at'] ?? '');
  $ends_at    = trim($_POST['ends_at'] ?? '');
  $reason     = trim($_POST['reason'] ?? '');

  if ($patient_id <= 0) \App\Core\Helpers::setError('patient_id','Select a patient.');
  if ($starts_at === '') \App\Core\Helpers::setError('starts_at','Start time is required.');
  if ($ends_at === '')   \App\Core\Helpers::setError('ends_at','End time is required.');

  if (!empty(\App\Core\Helpers::errors())) {
    \App\Core\Helpers::flash('Please fix the errors below.','danger');
    return $this->view('appointments/create', [
      'patients'=>\App\Core\DB::pdo()->query("SELECT id,first_name,last_name FROM patients ORDER BY first_name")->fetchAll(),
      'doctors' =>\App\Core\DB::pdo()->query("SELECT id,name FROM users WHERE role='doctor' ORDER BY name")->fetchAll(),
      'csrf'=>\App\Core\Csrf::token()
    ]);
  }

  try {
    \App\Models\Appointment::create([
      'patient_id'=>$patient_id,
      'doctor_id' =>$doctor_id, // can be null
      'starts_at' =>$starts_at,
      'ends_at'   =>$ends_at,
      'reason'    =>$reason ?: null,
    ]);
    \App\Core\Helpers::clearOld();
    \App\Core\Helpers::flash('Appointment created.','success');
    return $this->redirect('/appointments');
  } catch (\PDOException $e) {
    // Duplicate key (patient_id, starts_at)
    if ($e->errorInfo[0]==='23000' && (int)$e->errorInfo[1]===1062) {
      \App\Core\Helpers::setError('starts_at','This patient is already booked at that start time.');
      \App\Core\Helpers::flash('Duplicate booking prevented.','warning');
      return $this->view('appointments/create', [
        'patients'=>\App\Core\DB::pdo()->query("SELECT id,first_name,last_name FROM patients ORDER BY first_name")->fetchAll(),
        'doctors' =>\App\Core\DB::pdo()->query("SELECT id,name FROM users WHERE role='doctor' ORDER BY name")->fetchAll(),
        'csrf'=>\App\Core\Csrf::token()
      ]);
    }
    \App\Core\Helpers::flash('Error creating appointment: '.$e->getMessage(),'danger');
    return $this->redirect('/appointments/create');
  }
}
  public function delete(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    Appointment::delete((int)$_POST['id']);
    $this->redirect('/appointments');
  }



//Complete appointment


   /** Complete appointment (by id or latest scheduled for patient) */
  public function complete(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');

    $patientId     = (int)($_POST['patient_id'] ?? 0);
    $appointmentId = (int)($_POST['appointment_id'] ?? 0);

    $pdo = \App\Core\DB::pdo();

    if ($appointmentId > 0) {
      // complete by id
      $st = $pdo->prepare("UPDATE appointments SET status='completed', completed_at=NOW() WHERE id=? AND status='scheduled'");
      $st->execute([$appointmentId]);
    } elseif ($patientId > 0) {
      // complete latest scheduled for patient
      $st = $pdo->prepare("
        UPDATE appointments
        SET status='completed', completed_at=NOW()
        WHERE id = (
          SELECT id FROM appointments
          WHERE patient_id=? AND status='scheduled'
          ORDER BY starts_at DESC
          LIMIT 1
        )
      ");
      $st->execute([$patientId]);
    } else {
      $_SESSION['flash'] = 'No patient or appointment specified.';
      $this->redirect('/appointments');
    }

    $_SESSION['flash'] = 'Appointment marked as completed.';
    $back = $_POST['_back'] ?? '/appointments';
    $this->redirect($back);
  }


}
