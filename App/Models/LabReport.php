<?php
namespace App\Models;
use App\Core\DB;

final class LabReport {
  public static function create(array $d): int {
    $sql="INSERT INTO lab_reports(patient_id,test_id,ordered_by,result_value,result_text,reported_by,reported_at)
          VALUES(?,?,?,?,?,?,?)";
    DB::pdo()->prepare($sql)->execute([
      $d['patient_id'],$d['test_id'],$d['ordered_by'],$d['result_value']??null,$d['result_text']??null,$d['reported_by']??null,$d['reported_at']??null
    ]);
    return (int)DB::pdo()->lastInsertId();
  }

  public static function find(int $id): ?array {
  $sql="SELECT lr.*, lt.name AS test_name, p.first_name, p.last_name, u1.name AS ordered_by_name, u2.name AS reported_by_name
        FROM lab_reports lr
        JOIN lab_tests lt ON lt.id=lr.test_id
        JOIN patients p ON p.id=lr.patient_id
        JOIN users u1 ON u1.id=lr.ordered_by
        LEFT JOIN users u2 ON u2.id=lr.reported_by
        WHERE lr.id=? LIMIT 1";
  $st=\App\Core\DB::pdo()->prepare($sql); $st->execute([$id]); $r=$st->fetch(); return $r ?: null;
}


  public static function forPatient(int $patientId): array {
    $sql="SELECT lr.*, lt.name AS test_name, u.name AS doctor FROM lab_reports lr
         JOIN lab_tests lt ON lt.id=lr.test_id
         JOIN users u ON u.id=lr.ordered_by
         WHERE lr.patient_id=? ORDER BY lr.id DESC";
    $st=DB::pdo()->prepare($sql); $st->execute([$patientId]); return $st->fetchAll();
  }
  public static function latest(int $limit=50): array {
    $sql="SELECT lr.id, p.first_name, p.last_name, lt.name AS test_name, lr.result_value, lr.reported_at
          FROM lab_reports lr JOIN patients p ON p.id=lr.patient_id JOIN lab_tests lt ON lt.id=lr.test_id
          ORDER BY lr.id DESC LIMIT ?";
    $st=DB::pdo()->prepare($sql); $st->bindValue(1,$limit,\PDO::PARAM_INT); $st->execute(); return $st->fetchAll();
  }
}
