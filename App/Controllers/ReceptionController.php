<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;

final class ReceptionController extends Controller
{
    /** List/search patients and show “Add Schedule” button per row */
    public function patients(): void
{
    if (!\App\Core\Auth::check()) $this->redirect('/');
    if (!in_array(\App\Core\Auth::user()['role'] ?? '', ['receptionist','admin'])) exit('Forbidden');

    $q = trim($_GET['q'] ?? '');
    $pdo = \App\Core\DB::pdo();
    $results = [];

    if ($q !== '') {
        $like = '%'.$q.'%';
        $idMaybe = ctype_digit($q) ? (int)$q : 0;

        // ✅ matches your table exactly (NO gender/phone/email)
        $st = $pdo->prepare("
            SELECT id, patient_no, first_name, last_name, dob, sex, contact, address, emergency_contact
            FROM patients
            WHERE first_name LIKE ? OR last_name LIKE ? OR patient_no LIKE ? OR id = ?
            ORDER BY id DESC
            LIMIT 50
        ");
        $st->execute([$like, $like, $like, $idMaybe]);
        $results = $st->fetchAll();
    } else {
        // Recent patients default view
        $results = $pdo->query("
            SELECT id, patient_no, first_name, last_name, dob, sex, contact, address, emergency_contact
            FROM patients
            ORDER BY id DESC
            LIMIT 50
        ")->fetchAll();
    }

    $this->view('reception/patients', [
        'q'       => $q,
        'results' => $results,
        'csrf'    => \App\Core\Csrf::token(),
    ]);
}
    /** Quick schedule from Reception (no doctor required) */
    public function quickSchedule(): void
{
    // CSRF (support both styles your app might use)
    if (method_exists(\App\Core\Csrf::class, 'validateOrThrow')) {
        \App\Core\Csrf::validateOrThrow($_POST['_token'] ?? '');
    } else {
        if (!\App\Core\Csrf::check($_POST['_token'] ?? '')) { http_response_code(419); exit('CSRF'); }
    }

    if (!\App\Core\Auth::check()) $this->redirect('/');
    if (!in_array(\App\Core\Auth::user()['role'] ?? '', ['receptionist','admin'])) exit('Forbidden');

    $patientId = (int)($_POST['patient_id'] ?? 0);
    $starts    = trim($_POST['starts_at'] ?? '');
    $ends      = trim($_POST['ends_at'] ?? '');
    $reason    = trim($_POST['reason'] ?? '');

    if ($patientId <= 0 || $starts === '') {
        $_SESSION['flash'] = 'Patient and start time are required.';
        header('Location: ' . APP_URL . '/reception/patients'); exit;
    }

    if ($ends === '') {
        $t = strtotime($starts);
        if ($t !== false) $ends = date('Y-m-d H:i:s', $t + 30*60); // default +30min
    }

    try {
        \App\Core\DB::pdo()->prepare("
            INSERT INTO appointments (patient_id, doctor_id, starts_at, ends_at, reason, status, created_at)
            VALUES (:pid, NULL, :start, :end, :reason, 'scheduled', NOW())
        ")->execute([
            ':pid'   => $patientId,
            ':start' => $starts,
            ':end'   => $ends ?: $starts,
            ':reason'=> $reason ?: null,
        ]);

        $_SESSION['flash'] = 'Appointment scheduled.';
    } catch (\PDOException $e) {
        if ($e->errorInfo[0] === '23000' && (int)$e->errorInfo[1] === 1062) {
            $_SESSION['flash'] = 'This patient already has an appointment at that time.';
        } else {
            $_SESSION['flash'] = 'Error scheduling: ' . $e->getMessage();
        }
    }

    header('Location: ' . APP_URL . '/reception/patients'); exit;


    }
}
