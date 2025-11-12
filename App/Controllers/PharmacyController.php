<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf};
use App\Models\{Drug,PrescriptionItem};

final class PharmacyController extends Controller {
  public function drugs(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['pharmacist','admin'])) exit('Forbidden');
    $this->view('pharmacy/drugs',['items'=>Drug::all(),'csrf'=>Csrf::token()]);
  }

  public function drugCreate(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['pharmacist','admin'])) exit('Forbidden');
    $this->view('pharmacy/drug_create',['csrf'=>Csrf::token()]);
  }

  public function drugStore(): void {
    if(!Auth::check()) $this->redirect('/');
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    if(!in_array(Auth::user()['role'], ['pharmacist','admin'])) exit('Forbidden');
    Drug::create($_POST);
    $this->redirect('/pharmacy/drugs');
  }

public function fulfillList(): void {
  if(!\App\Core\Auth::check()) $this->redirect('/');
  if(!in_array(\App\Core\Auth::user()['role'], ['pharmacist','admin'])) exit('Forbidden');

  $sql="SELECT i.id as item_id, p.id as prescription_id, pt.first_name, pt.last_name, d.name as drug,
               i.duration_days, i.dispensed
        FROM prescription_items i 
        JOIN prescriptions p ON p.id=i.prescription_id
        JOIN patients pt ON pt.id=p.patient_id
        JOIN drugs d ON d.id=i.drug_id
        ORDER BY i.id DESC";
  $items = \App\Core\DB::pdo()->query($sql)->fetchAll();

  $history = \App\Models\PharmacyDispense::latest(50);
  $this->view('pharmacy/fulfill',['items'=>$items,'history'=>$history,'csrf'=>\App\Core\Csrf::token()]);
}


public function fulfillAction(): void {
  if(!\App\Core\Auth::check()) $this->redirect('/');
  if(!\App\Core\Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  if(!in_array(\App\Core\Auth::user()['role'], ['pharmacist','admin'])) exit('Forbidden');

  $itemId = (int)($_POST['item_id'] ?? 0);
  $qty    = max(1, (int)($_POST['qty'] ?? 1));

  $st = \App\Core\DB::pdo()->prepare("SELECT drug_id, dispensed FROM prescription_items WHERE id=? LIMIT 1");
  $st->execute([$itemId]); $row=$st->fetch();
  if(!$row){ $_SESSION['flash']='Item not found.'; $this->redirect('/pharmacy/fulfill'); }

  $drugId=(int)$row['drug_id'];
  \App\Models\Drug::decrementStock($drugId,$qty);

  // keep it visible but mark dispensed
  if((int)$row['dispensed']===0){
    \App\Models\PrescriptionItem::markDispensed($itemId);
  }

  // log history
  \App\Models\PharmacyDispense::log($itemId,$qty,\App\Core\Auth::user()['id']);

  $_SESSION['flash']='Dispense recorded.';
  $_SESSION['checkout_patient_id'] = (int)($_POST['patient_id'] ?? 0);
$_SESSION['checkout_back'] = APP_URL . '/pharmacy/fulfill';
  $this->redirect('/pharmacy/fulfill');
}





public function dispense(): void
{
    \App\Core\Csrf::validateOrThrow($_POST['_token'] ?? '');

    $prescriptionId = (int)($_POST['prescription_id'] ?? 0);
    $patientId      = (int)($_POST['patient_id'] ?? 0);

    $pdo = \App\Core\DB::pdo();

    try {
        // 1) Mark prescription dispensed
        $stmt = $pdo->prepare("UPDATE prescriptions SET status='dispensed', dispensed_at=NOW() WHERE id=:id");
        $stmt->execute([':id' => $prescriptionId]);

        // 2) Complete the patient's latest open appointment (scheduled or in_progress)
        if ($patientId > 0) {
            $stmt = $pdo->prepare("
                SELECT id FROM appointments
                WHERE patient_id = :pid AND status IN ('scheduled','in_progress')
                ORDER BY starts_at DESC
                LIMIT 1
            ");
            $stmt->execute([':pid' => $patientId]);
            $appointmentId = (int)$stmt->fetchColumn();

            if ($appointmentId > 0) {
                $pdo->prepare("UPDATE appointments SET status='completed' WHERE id=:id")
                    ->execute([':id' => $appointmentId]);
            }
        }

        // 3) If AJAX, respond JSON; otherwise redirect
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            return;
        }

        $_SESSION['flash'] = 'Drugs dispensed.';
        header('Location: ' . APP_URL . '/pharmacy/fulfill'); exit;

    } catch (\Throwable $e) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }
        $_SESSION['flash'] = 'Error: ' . $e->getMessage();
        header('Location: ' . APP_URL . '/pharmacy/fulfill'); exit;
    }
}



}
