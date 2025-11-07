<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf,Gate};
use App\Models\{Patient,Drug,Prescription,PrescriptionItem,LabReport,LabTest,PatientReport};

final class DoctorController extends Controller {
  public function dashboard(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $this->view('doctor/index', []);
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

    // multiple items
    foreach($_POST['items'] as $row){
      if(empty($row['drug_id'])) continue;
      PrescriptionItem::add($prescriptionId,(int)$row['drug_id'],trim($row['dosage']),trim($row['frequency']),(int)$row['duration']);
    }
    $this->redirect('/patients/show?id='.$patientId);
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
    PatientReport::create((int)$_POST['patient_id'], Auth::user()['id'], trim($_POST['title']), trim($_POST['body']));
    $this->redirect('/patients/show?id='.(int)$_POST['patient_id']);
  }

  public function labReports(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['doctor','admin'])) exit('Forbidden');
    $reports = LabReport::latest(50);
    $this->view('doctor/lab_reports', ['items'=>$reports]);
  }
}
