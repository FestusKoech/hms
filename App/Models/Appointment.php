<?php
namespace App\Models;
use App\Core\DB;

final class Appointment {
  public static function list(): array {
    $sql="SELECT a.*, p.first_name, p.last_name, u.name AS doctor
          FROM appointments a
          JOIN patients p ON p.id=a.patient_id
          LEFT JOIN users u ON u.id=a.doctor_id
          ORDER BY a.starts_at DESC";
    return DB::pdo()->query($sql)->fetchAll();
  }

  public static function create(array $d): void {
    $sql="INSERT INTO appointments(patient_id,doctor_id,starts_at,ends_at,reason,status)
          VALUES(?,?,?,?,?,?)";
    DB::pdo()->prepare($sql)->execute([
      (int)$d['patient_id'],
      $d['doctor_id'] !== null ? (int)$d['doctor_id'] : null,
      $d['starts_at'],
      $d['ends_at'],
      $d['reason'] ?? null,
      'scheduled'
    ]);
  }

  public static function listUnclaimed(): array {
    $st = DB::pdo()->prepare("
      SELECT a.*, p.first_name, p.last_name
      FROM appointments a
      JOIN patients p ON p.id=a.patient_id
      WHERE a.status='scheduled' AND a.doctor_id IS NULL
      ORDER BY a.starts_at ASC
      LIMIT 200
    ");
    $st->execute();
    return $st->fetchAll();
  }

  public static function claim(int $appointmentId, int $doctorId): bool {
    $st = DB::pdo()->prepare("
      UPDATE appointments
      SET doctor_id = :doc, status='in_progress'
      WHERE id=:id AND doctor_id IS NULL AND status='scheduled'
    ");
    $st->execute([':doc'=>$doctorId,':id'=>$appointmentId]);
    return $st->rowCount() === 1;
  }

  public static function markCompletedByPatientLatestOpen(int $patientId): void {
    // Complete the latest open (scheduled/in_progress) appt for this patient
    $pdo = DB::pdo();
    $id = (int)$pdo->prepare("
      SELECT id FROM appointments
      WHERE patient_id=? AND status IN ('scheduled','in_progress')
      ORDER BY starts_at DESC LIMIT 1
    ")->execute([$patientId]) ?: 0;
  }
}
