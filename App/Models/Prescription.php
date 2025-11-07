<?php
namespace App\Models;
use App\Core\DB;

final class Prescription {
  public static function create(int $patientId,int $doctorId,?string $notes): int {
    DB::pdo()->prepare(
      "INSERT INTO prescriptions(patient_id,doctor_id,notes) VALUES(?,?,?)"
    )->execute([$patientId,$doctorId,$notes]);
    return (int)DB::pdo()->lastInsertId();
  }

  public static function forPatient(int $patientId): array {
    $sql="SELECT pr.*, u.name AS doctor
          FROM prescriptions pr
          JOIN users u ON u.id=pr.doctor_id
          WHERE pr.patient_id=?
          ORDER BY pr.id DESC";
    $st=DB::pdo()->prepare($sql);
    $st->execute([$patientId]);
    return $st->fetchAll();
  }
}
