<?php
namespace App\Models;
use App\Core\DB;

final class PrescriptionItem {
  public static function add(int $prescriptionId,int $drugId,string $dosage,string $frequency,int $duration): void {
    DB::pdo()->prepare(
      "INSERT INTO prescription_items(prescription_id,drug_id,dosage,frequency,duration_days)
       VALUES(?,?,?,?,?)"
    )->execute([$prescriptionId,$drugId,$dosage,$frequency,$duration]);
  }

  public static function lines(int $prescriptionId): array {
    $sql="SELECT i.*, d.name, d.strength
          FROM prescription_items i
          JOIN drugs d ON d.id=i.drug_id
          WHERE i.prescription_id=?";
    $st=DB::pdo()->prepare($sql);
    $st->execute([$prescriptionId]);
    return $st->fetchAll();
  }

  public static function markDispensed(int $itemId): void {
    DB::pdo()->prepare("UPDATE prescription_items SET dispensed=1 WHERE id=?")->execute([$itemId]);
  }
}
