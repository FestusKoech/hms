<?php
namespace App\Controllers;

use App\Core\{Controller, Auth, Csrf};
use App\Models\{Patient, LabReport, LabOrder, Prescription, PrescriptionItem, PatientReport, LabTest, Drug};

final class DoctorController extends Controller {
  public function dashboard(): void {
    if(!Auth::check()) $this->redirect('/');
    $this->view('doctor/dashboard');
  }

  public function searchForm(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $this->view('doctor/search', ['csrf'=>Csrf::token(), 'q'=>'', 'items'=>[]]);
  }

  public function searchRun(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $q = trim($_POST['q'] ?? '');
    $items = $q ? Patient::search($q) : [];
    $this->view('doctor/search', ['csrf'=>Csrf::token(), 'q'=>$q, 'items'=>$items]);
  }

  public function patientView(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $id = (int)($_GET['id'] ?? 0);
    $p = Patient::find($id);
    if(!$p) $this->redirect('/doctor/search');

    $rxStmt = \App\Core\DB::pdo()->prepare("
      SELECT pr.id, pr.created_at, u.name AS doctor
      FROM prescriptions pr JOIN users u ON u.id=pr.doctor_id
      WHERE pr.patient_id=? ORDER BY pr.id DESC
    ");
    $rxStmt->execute([$id]); $prescriptions = $rxStmt->fetchAll();

    $pending_lab = LabOrder::forPatientPending($id);
    $lab_reports = LabReport::forPatient($id);
    $patient_reports = PatientReport::forPatient($id);

    $this->view('doctor/patient_show', [
      'p'=>$p, 'prescriptions'=>$prescriptions,
      'pending_lab'=>$pending_lab, 'lab_reports'=>$lab_reports,
      'patient_reports'=>$patient_reports
    ]);
  }

  public function prescribeForm(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $pid = (int)($_GET['patient_id'] ?? 0);
    $drugs = Drug::all();
    $this->view('doctor/prescribe', ['csrf'=>Csrf::token(),'patient_id'=>$pid,'drugs'=>$drugs]);
  }

  public function prescribeStore(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $patientId = (int)$_POST['patient_id'];
    $prescriptionId = Prescription::create($patientId, Auth::user()['id'], $_POST['notes'] ?? null);
    foreach($_POST['items'] as $row){
      if(empty($row['drug_id'])) continue;
      PrescriptionItem::add($prescriptionId,(int)$row['drug_id'],trim($row['dosage']??''),trim($row['frequency']??''),(int)($row['duration']??0));
    }
    $this->redirect('/doctor/patient?id='.$patientId);
  }

  public function addPatientReportForm(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $pid=(int)($_GET['patient_id'] ?? 0);
    $this->view('doctor/patient_report_create',['csrf'=>Csrf::token(),'patient_id'=>$pid]);
  }

  public function addPatientReportStore(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    PatientReport::create((int)$_POST['patient_id'], Auth::user()['id'], trim($_POST['title']??'Report'), trim($_POST['body']??''));
    $this->redirect('/doctor/patient?id='.(int)$_POST['patient_id']);
  }

  public function labOrderForm(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $pid=(int)($_GET['patient_id'] ?? 0);
    $tests = LabTest::all();
    $this->view('doctor/lab_order',['csrf'=>Csrf::token(),'patient_id'=>$pid,'tests'=>$tests]);
  }

  public function labOrderStore(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    LabOrder::create((int)$_POST['patient_id'], (int)$_POST['test_id'], Auth::user()['id']);
    $_SESSION['flash']='Lab order created.';
    $this->redirect('/doctor/patient?id='.(int)$_POST['patient_id']);
  }

  public function labReports(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $completed = LabReport::latest(100);
    $pending   = LabOrder::pending(100);
    $this->view('doctor/lab_reports', ['completed'=>$completed,'pending'=>$pending]);
  }

  public function labReportShow(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $id=(int)($_GET['id'] ?? 0);
    $r = LabReport::find($id);
    if(!$r){ header('HTTP/1.0 404 Not Found'); exit('Report not found'); }
    $this->view('doctor/lab_report_show', ['r'=>$r]);
  }
}
