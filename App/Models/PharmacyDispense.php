<?php
namespace App\Models;
use App\Core\DB;

final class PharmacyDispense {
  public static function log(int $itemId,int $qty,int $userId): void {
    DB::pdo()->prepare("INSERT INTO pharmacy_dispenses(prescription_item_id,qty,dispensed_by,dispensed_at) VALUES(?,?,?,NOW())")
      ->execute([$itemId,$qty,$userId]);
  }
  public static function latest(int $limit=100): array {
    $sql="SELECT d.id, d.qty, d.dispensed_at, pt.first_name, pt.last_name, dg.name AS drug
          FROM pharmacy_dispenses d
          JOIN prescription_items i ON i.id=d.prescription_item_id
          JOIN prescriptions p ON p.id=i.prescription_id
          JOIN patients pt ON pt.id=p.patient_id
          JOIN drugs dg ON dg.id=i.drug_id
          ORDER BY d.id DESC LIMIT ?";
    $st=\App\Core\DB::pdo()->prepare($sql); $st->bindValue(1,$limit,\PDO::PARAM_INT); $st->execute(); return $st->fetchAll();
  }
}
