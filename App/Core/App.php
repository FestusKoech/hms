<?php
namespace App\Core;
use App\Controllers\{DashboardController,AuthController,PatientsController,AppointmentsController};

final class App {
  public function run(): void {
    if(session_status()!==PHP_SESSION_ACTIVE) session_start();
    $r = new Router();

    // Auth
    $r->get('/', [AuthController::class,'showLogin']);
    $r->post('/login', [AuthController::class,'login']);
    $r->get('/logout', [AuthController::class,'logout']);

    // Dashboard
    $r->get('/dashboard', [DashboardController::class,'index']);

    // Patients
    $r->get('/patients', [PatientsController::class,'index']);
    $r->get('/patients/create', [PatientsController::class,'create']);
    $r->post('/patients/store', [PatientsController::class,'store']);
    $r->get('/patients/show', [PatientsController::class,'show']);     // ?id=
    $r->get('/patients/edit', [PatientsController::class,'edit']);     // ?id=
    $r->post('/patients/update', [PatientsController::class,'update']); // id hidden
    $r->post('/patients/delete', [PatientsController::class,'delete']); // id hidden

    // Appointments
    $r->get('/appointments', [AppointmentsController::class,'index']);
    $r->get('/appointments/create', [AppointmentsController::class,'create']);
    $r->post('/appointments/store', [AppointmentsController::class,'store']);
    $r->post('/appointments/delete', [AppointmentsController::class,'delete']);

    // DOCTOR
$r->get('/doctor', [\App\Controllers\DoctorController::class,'dashboard']);
$r->get('/doctor/prescribe', [\App\Controllers\DoctorController::class,'prescribeForm']);         // ?patient_id=
$r->post('/doctor/prescribe', [\App\Controllers\DoctorController::class,'prescribeStore']);
$r->get('/doctor/patient-report', [\App\Controllers\DoctorController::class,'addPatientReportForm']); // ?patient_id=
$r->post('/doctor/patient-report', [\App\Controllers\DoctorController::class,'addPatientReportStore']);
$r->get('/doctor/lab-reports', [\App\Controllers\DoctorController::class,'labReports']);

// LAB (lab_technician)
$r->get('/lab', [\App\Controllers\LabController::class,'index']);
$r->get('/lab/create', [\App\Controllers\LabController::class,'create']);
$r->post('/lab/store', [\App\Controllers\LabController::class,'store']);

// PHARMACY
$r->get('/pharmacy/drugs', [\App\Controllers\PharmacyController::class,'drugs']);
$r->get('/pharmacy/drugs/create', [\App\Controllers\PharmacyController::class,'drugCreate']);
$r->post('/pharmacy/drugs/store', [\App\Controllers\PharmacyController::class,'drugStore']);
$r->get('/pharmacy/fulfill', [\App\Controllers\PharmacyController::class,'fulfillList']);
$r->post('/pharmacy/fulfill', [\App\Controllers\PharmacyController::class,'fulfillAction']);

// RECEPTION
$r->get('/reception/patients', [\App\Controllers\ReceptionController::class,'patients']);
$r->get('/reception/patients/create', [\App\Controllers\ReceptionController::class,'create']);
$r->post('/reception/patients/store', [\App\Controllers\ReceptionController::class,'store']);


// ADMIN: Manage Staff
$r->get('/admin/users', [\App\Controllers\AdminUsersController::class,'index']);
$r->get('/admin/users/create', [\App\Controllers\AdminUsersController::class,'create']);
$r->post('/admin/users/store', [\App\Controllers\AdminUsersController::class,'store']);
$r->get('/admin/users/edit', [\App\Controllers\AdminUsersController::class,'edit']); // ?id=
$r->post('/admin/users/update', [\App\Controllers\AdminUsersController::class,'update']);
$r->post('/admin/users/delete', [\App\Controllers\AdminUsersController::class,'delete']);


    $r->dispatch();
  }
}
