<?php
namespace App\Models;
use App\Core\DB;

final class PatientReport {
  public static function create(int $patientId,int $doctorId,string $title,string $body): int {
    DB::pdo()->prepare("INSERT INTO patient_reports(patient_id,doctor_id,title,body) VALUES(?,?,?,?)")
      ->execute([$patientId,$doctorId,$title,$body]);
    return (int)DB::pdo()->lastInsertId();
  }
  public static function forPatient(int $patientId): array {
    $sql="SELECT pr.*, u.name AS doctor FROM patient_reports pr JOIN users u ON u.id=pr.doctor_id WHERE pr.patient_id=? ORDER BY pr.id DESC";
    $st=DB::pdo()->prepare($sql); $st->execute([$patientId]); return $st->fetchAll();
  }
}
