<?php
namespace App\Core;
use App\Controllers\{DashboardController,AuthController,PatientsController,AppointmentsController};

final class App {
  public function run(): void {
    if(session_status()!==PHP_SESSION_ACTIVE) session_start();
 $r = new Router();

// Auth
$r->get('/',        [AuthController::class,'showLogin']);
$r->post('/login',  [AuthController::class,'login']);
$r->get('/logout',  [AuthController::class,'logout']);

// Dashboard
$r->get('/dashboard', [DashboardController::class,'index']);
$r->get('/doctor',    [\App\Controllers\DoctorController::class,'dashboard']);

// Patients
$r->get('/patients',          [PatientsController::class,'index']);
$r->get('/patients/create',   [PatientsController::class,'create']);
$r->post('/patients/store',   [PatientsController::class,'store']);
$r->get('/patients/show',     [PatientsController::class,'show']);      // ?id=
$r->get('/patients/edit',     [PatientsController::class,'edit']);      // ?id=
$r->post('/patients/update',  [PatientsController::class,'update']);    // hidden id
$r->post('/patients/delete',  [PatientsController::class,'delete']);    // hidden id

// Appointments
$r->get('/appointments',         [\App\Controllers\AppointmentsController::class,'index']);
$r->get('/appointments/create',  [\App\Controllers\AppointmentsController::class,'create']);
$r->post('/appointments/store',  [\App\Controllers\AppointmentsController::class,'store']);
$r->post('/appointments/delete', [\App\Controllers\AppointmentsController::class,'delete']);
$r->get('/reception',                       [\App\Controllers\ReceptionController::class,'patients']); // optional landing
$r->get('/reception/appointments/create',   [\App\Controllers\ReceptionController::class,'apptCreate']); // ?patient_id=
$r->post('/reception/appointments/store',   [\App\Controllers\ReceptionController::class,'apptStore']);
$r->get('/reception/appointments',          [\App\Controllers\ReceptionController::class,'apptList']);   // ?patient_id=
$r->get('/reception/patients',         [\App\Controllers\ReceptionController::class,'patients']);
$r->get('/reception/appointments/create', [\App\Controllers\ReceptionController::class,'apptCreate']); // ?patient_id=
$r->post('/reception/appointments/store', [\App\Controllers\ReceptionController::class,'apptStore']);
$r->get('/reception/appointments',        [\App\Controllers\ReceptionController::class,'apptList']);   // ?patient_id=
$r->get('/reception/appointments/edit',    [\App\Controllers\ReceptionController::class,'apptEdit']);   // ?id=
$r->post('/reception/appointments/update', [\App\Controllers\ReceptionController::class,'apptUpdate']);
$r->post('/reception/appointments/cancel', [\App\Controllers\ReceptionController::class,'apptCancel']); // hidden id, patient_id
$r->get('/reception/appointments/slip',    [\App\Controllers\ReceptionController::class,'apptSlip']);   // ?id=




// Doctor tools
$r->get('/doctor/search',          [\App\Controllers\DoctorController::class,'searchForm']);
$r->post('/doctor/search',         [\App\Controllers\DoctorController::class,'searchRun']);
$r->get('/doctor/patient',         [\App\Controllers\DoctorController::class,'patientView']);   // ?id=

$r->get('/doctor/prescribe',       [\App\Controllers\DoctorController::class,'prescribeForm']); // ?patient_id=
$r->post('/doctor/prescribe',      [\App\Controllers\DoctorController::class,'prescribeStore']);

$r->get('/doctor/patient-report',  [\App\Controllers\DoctorController::class,'addPatientReportForm']); // ?patient_id=
$r->post('/doctor/patient-report', [\App\Controllers\DoctorController::class,'addPatientReportStore']);

$r->get('/doctor/lab-order',       [\App\Controllers\DoctorController::class,'labOrderForm']);  // ?patient_id=
$r->post('/doctor/lab-order',      [\App\Controllers\DoctorController::class,'labOrderStore']);
$r->get('/lab/search',   [\App\Controllers\LabController::class,'search']);       // GET ?q=
$r->get('/lab/patient',  [\App\Controllers\LabController::class,'patientPanel']); // GET ?id=
$r->get('/lab/pending',  [\App\Controllers\LabController::class,'pending']);
$r->get('/lab/completed',[\App\Controllers\LabController::class,'completed']);


$r->get('/doctor/lab-reports',     [\App\Controllers\DoctorController::class,'labReports']);
$r->get('/doctor/lab-report',      [\App\Controllers\DoctorController::class,'labReportShow']); // ?id=

// Lab (lab_technician)
$r->get('/lab',                    [\App\Controllers\LabController::class,'index']);
$r->get('/lab/create',             [\App\Controllers\LabController::class,'create']);
$r->post('/lab/store',             [\App\Controllers\LabController::class,'store']);

$r->get('/lab/orders',             [\App\Controllers\LabController::class,'orders']);
$r->get('/lab/report-from-order',  [\App\Controllers\LabController::class,'reportFromOrderForm']); // ?order_id=
$r->post('/lab/report-from-order', [\App\Controllers\LabController::class,'reportFromOrderStore']);

$r->get('/lab/report',             [\App\Controllers\LabController::class,'reportShow']);         // ?id=

// Pharmacy
$r->get('/pharmacy/drugs',         [\App\Controllers\PharmacyController::class,'drugs']);
$r->get('/pharmacy/drugs/create',  [\App\Controllers\PharmacyController::class,'drugCreate']);
$r->post('/pharmacy/drugs/store',  [\App\Controllers\PharmacyController::class,'drugStore']);
$r->get('/pharmacy/fulfill',       [\App\Controllers\PharmacyController::class,'fulfillList']);
$r->post('/pharmacy/fulfill',      [\App\Controllers\PharmacyController::class,'fulfillAction']);

// Reception
$r->get('/reception/patients',       [\App\Controllers\ReceptionController::class,'patients']);
$r->get('/reception/patients/create',[\App\Controllers\ReceptionController::class,'create']);
$r->post('/reception/patients/store',[\App\Controllers\ReceptionController::class,'store']);

// Admin: Manage Staff
$r->get('/admin/users',           [\App\Controllers\AdminUsersController::class,'index']);
$r->get('/admin/users/create',    [\App\Controllers\AdminUsersController::class,'create']);
$r->post('/admin/users/store',    [\App\Controllers\AdminUsersController::class,'store']);
$r->get('/admin/users/edit',      [\App\Controllers\AdminUsersController::class,'edit']);   // ?id=
$r->post('/admin/users/update',   [\App\Controllers\AdminUsersController::class,'update']);
$r->post('/admin/users/delete',   [\App\Controllers\AdminUsersController::class,'delete']);
    $r->dispatch();
  }
}
