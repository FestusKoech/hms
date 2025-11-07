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
    if(!Auth::check()) $this->redirect('/');
    if(!in_array(Auth::user()['role'], ['pharmacist','admin'])) exit('Forbidden');
    // list all un-dispensed prescription items with patient + drug
    $sql="SELECT i.id as item_id, p.id as prescription_id, pt.first_name, pt.last_name, d.name as drug, i.duration_days
          FROM prescription_items i 
          JOIN prescriptions p ON p.id=i.prescription_id
          JOIN patients pt ON pt.id=p.patient_id
          JOIN drugs d ON d.id=i.drug_id
          WHERE i.dispensed=0
          ORDER BY i.id DESC";
    $items = \App\Core\DB::pdo()->query($sql)->fetchAll();
    $this->view('pharmacy/fulfill',['items'=>$items,'csrf'=>Csrf::token()]);
  }

  public function fulfillAction(): void {
  if(!Auth::check()) $this->redirect('/');
  if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
  if(!in_array(Auth::user()['role'], ['pharmacist','admin'])) { header('HTTP/1.1 403 Forbidden'); exit('Forbidden'); }

  $itemId = (int)($_POST['item_id'] ?? 0);
  $qty    = max(1, (int)($_POST['qty'] ?? 1));

  // 1) fetch drug id & ensure still pending
  $st = \App\Core\DB::pdo()->prepare(
    "SELECT drug_id, dispensed FROM prescription_items WHERE id=? LIMIT 1"
  );
  $st->execute([$itemId]);
  $row = $st->fetch();

  if(!$row){
    $_SESSION['flash'] = 'Prescription item not found.';
    $this->redirect('/pharmacy/fulfill');
  }
  if((int)$row['dispensed'] === 1){
    $_SESSION['flash'] = 'Already dispensed.';
    $this->redirect('/pharmacy/fulfill');
  }

  $drugId = (int)$row['drug_id'];

  // 2) decrement stock (no negative)
  \App\Models\Drug::decrementStock($drugId, $qty);

  // 3) mark dispensed
  \App\Models\PrescriptionItem::markDispensed($itemId);

  // 4) feedback
  $_SESSION['flash'] = 'Dispensed successfully.';
  $this->redirect('/pharmacy/fulfill');
}

}
