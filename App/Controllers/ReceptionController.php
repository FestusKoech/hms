<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf};
use App\Models\{Patient};

final class ReceptionController extends Controller {
public function patients(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');

  $q   = trim($_GET['q'] ?? '');
  $pid = (int)($_GET['patient_id'] ?? 0);
  $results = [];

  if ($q !== '') {
    $like = '%'.$q.'%';
    $idMaybe = ctype_digit($q) ? (int)$q : 0;
    $st = \App\Core\DB::pdo()->prepare("
      SELECT id, first_name, last_name, code
      FROM patients
      WHERE first_name LIKE ? OR last_name LIKE ? OR code LIKE ? OR id = ?
      ORDER BY id DESC
      LIMIT 25
    ");
    $st->execute([$like,$like,$like,$idMaybe]);
    $results = $st->fetchAll();
  }

  $patient = null;
  if ($pid > 0) {
    $st2 = \App\Core\DB::pdo()->prepare("
      SELECT id, first_name, last_name, code
      FROM patients
      WHERE id = ?
    ");
    $st2->execute([$pid]);
    $patient = $st2->fetch();
  }

  $this->view('reception/patients', [
    'csrf'    => \App\Core\Csrf::token(),
    'q'       => $q,
    'results' => $results,
    'patient' => $patient
  ]);
}


//Create appointment
public function apptCreate(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');

  $pid = (int)($_GET['patient_id'] ?? 0);

  $this->view('reception/appt_create', [
    'csrf'       => \App\Core\Csrf::token(),
    'patient_id' => $pid
  ]);
}


//Store appointments
public function apptStore(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!\App\Core\Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  if (!in_array(\App\Core\Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');

  $pid      = (int)$_POST['patient_id'];
  $doctorId = (int)$_POST['doctor_id'];
  $starts   = trim($_POST['starts_at'] ?? '');
  $ends     = trim($_POST['ends_at'] ?? '');

  $st = \App\Core\DB::pdo()->prepare("
    INSERT INTO appointments(patient_id, doctor_id, starts_at, ends_at, status)
    VALUES(?,?,?,?, 'scheduled')
  ");
  $st->execute([$pid,$doctorId,$starts,$ends]);

  $_SESSION['flash'] = 'Appointment scheduled.';
  $this->redirect('/reception/appointments?patient_id='.$pid);
}


//display appointments
public function apptList(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');

  $pid = (int)($_GET['patient_id'] ?? 0);
  $rows = [];

  if ($pid > 0) {
    $st = \App\Core\DB::pdo()->prepare("
      SELECT a.id, a.starts_at, a.ends_at, a.status, u.name AS doctor
      FROM appointments a
      JOIN users u ON u.id = a.doctor_id
      WHERE a.patient_id=?
      ORDER BY a.starts_at DESC
    ");
    $st->execute([$pid]);
    $rows = $st->fetchAll();
  }

  $this->view('reception/appt_list', [
    'patient_id' => $pid,
    'rows'       => $rows
  ]);
}



public function apptEdit(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');

  $id = (int)($_GET['id'] ?? 0);
  $st = \App\Core\DB::pdo()->prepare("
    SELECT a.id, a.patient_id, a.doctor_id, a.starts_at, a.ends_at, a.status, u.name AS doctor
    FROM appointments a JOIN users u ON u.id=a.doctor_id WHERE a.id=?
  ");
  $st->execute([$id]);
  $a = $st->fetch();
  if (!$a) { $_SESSION['flash']='Appointment not found.'; $this->redirect('/reception/patients'); }

  // Doctors for dropdown
  $docs = \App\Core\DB::pdo()->query("SELECT id,name FROM users WHERE role='doctor' ORDER BY name")->fetchAll();

  $this->view('reception/appt_edit', [
    'csrf' => \App\Core\Csrf::token(),
    'appt' => $a,
    'doctors' => $docs
  ]);
}

public function apptUpdate(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!\App\Core\Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  if (!in_array(\App\Core\Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');

  $id        = (int)$_POST['id'];
  $patientId = (int)$_POST['patient_id'];
  $doctorId  = (int)$_POST['doctor_id'];
  $starts    = trim($_POST['starts_at'] ?? '');
  $ends      = trim($_POST['ends_at'] ?? '');

  // Basic validation: ends > starts
  if (strtotime($ends) <= strtotime($starts)) {
    $_SESSION['flash'] = 'End time must be after start time.';
    $this->redirect('/reception/appointments/edit?id='.$id);
  }

  $st = \App\Core\DB::pdo()->prepare("
    UPDATE appointments SET doctor_id=?, starts_at=?, ends_at=? WHERE id=?
  ");
  $st->execute([$doctorId, $starts, $ends, $id]);

  $_SESSION['flash'] = 'Appointment updated.';
  $this->redirect('/reception/appointments?patient_id='.$patientId);
}
public function apptCancel(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!\App\Core\Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  if (!in_array(\App\Core\Auth::user()['role'], ['receptionist','admin'])) exit('Forbidden');

  $id        = (int)$_POST['id'];
  $patientId = (int)$_POST['patient_id'];

  \App\Core\DB::pdo()->prepare("UPDATE appointments SET status='cancelled' WHERE id=?")->execute([$id]);

  $_SESSION['flash'] = 'Appointment cancelled.';
  $this->redirect('/reception/appointments?patient_id='.$patientId);
}

/** Printable slip (no medical info) */
public function apptSlip(): void {
  if (!\App\Core\Auth::check()) $this->redirect('/');
  if (!in_array(\App\Core\Auth::user()['role'], ['receptionist','admin','doctor'])) exit('Forbidden');

  $id = (int)($_GET['id'] ?? 0);
  $st = \App\Core\DB::pdo()->prepare("
    SELECT a.id, a.patient_id, a.starts_at, a.ends_at, a.status,
           u.name AS doctor,
           p.first_name, p.last_name, p.code
    FROM appointments a
    JOIN users u ON u.id=a.doctor_id
    JOIN patients p ON p.id=a.patient_id
    WHERE a.id=?
  ");
  $st->execute([$id]);
  $a = $st->fetch();
  if (!$a) { echo 'Appointment not found.'; return; }

  $this->view('reception/appt_slip', ['a' => $a]);
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
