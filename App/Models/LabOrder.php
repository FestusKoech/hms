<?php
namespace App\Models;
use App\Core\DB;

final class LabOrder {
 public static function create(int $patientId, int $testId, int $doctorId, ?string $notes = null): int {
  $pdo = DB::pdo();

  // Try inserting with notes first; if the column doesn't exist, fall back.
  try {
    $sql = "INSERT INTO lab_orders (patient_id, test_id, ordered_by, status, order_notes)
            VALUES (?, ?, ?, 'ordered', ?)";
    $st  = $pdo->prepare($sql);
    $st->execute([$patientId, $testId, $doctorId, $notes]);
  } catch (\Throwable $e) {
    // Fallback path for schemas without `order_notes`
    $sql = "INSERT INTO lab_orders (patient_id, test_id, ordered_by, status)
            VALUES (?, ?, ?, 'ordered')";
    $st  = $pdo->prepare($sql);
    $st->execute([$patientId, $testId, $doctorId]);
  }

  return (int)$pdo->lastInsertId();
}
  public static function pending(int $limit=100): array {
    $sql="SELECT o.id, o.created_at, p.first_name, p.last_name, lt.name AS test_name, u.name AS doctor
         FROM lab_orders o
         JOIN patients p ON p.id=o.patient_id
         JOIN lab_tests lt ON lt.id=o.test_id
         JOIN users u ON u.id=o.ordered_by
         WHERE o.status='ordered'
         ORDER BY o.id DESC LIMIT ?";
    $st=DB::pdo()->prepare($sql); $st->bindValue(1,$limit,\PDO::PARAM_INT); $st->execute(); return $st->fetchAll();
  }
  public static function find(int $id): ?array {
    $st=DB::pdo()->prepare("SELECT * FROM lab_orders WHERE id=?"); $st->execute([$id]); $r=$st->fetch(); return $r?:null;
  }

  public static function forPatientPending(int $patientId): array {
  $sql="SELECT o.id, o.created_at, lt.name AS test_name
        FROM lab_orders o
        JOIN lab_tests lt ON lt.id=o.test_id
        WHERE o.patient_id=? AND o.status='ordered'
        ORDER BY o.id DESC";
  $st=\App\Core\DB::pdo()->prepare($sql); $st->execute([$patientId]); return $st->fetchAll();
}


  public static function markReported(int $id): void {
    DB::pdo()->prepare("UPDATE lab_orders SET status='reported' WHERE id=?")->execute([$id]);
  }
}
