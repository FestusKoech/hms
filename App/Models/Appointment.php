<?php
namespace App\Models;
use App\Core\DB;

final class Appointment {
  public static function list(): array {
    $sql="SELECT a.*, p.first_name, p.last_name, u.name AS doctor
          FROM appointments a
          JOIN patients p ON p.id=a.patient_id
          JOIN users u ON u.id=a.doctor_id
          ORDER BY a.starts_at DESC";
    return DB::pdo()->query($sql)->fetchAll();
  }
  public static function create(array $d): void {
    $sql="INSERT INTO appointments(patient_id,doctor_id,starts_at,ends_at,reason,status) VALUES(?,?,?,?,?,?)";
    DB::pdo()->prepare($sql)->execute([$d['patient_id'],$d['doctor_id'],$d['starts_at'],$d['ends_at'],$d['reason']??null,'scheduled']);
  }
  public static function delete(int $id): void {
    DB::pdo()->prepare("DELETE FROM appointments WHERE id=?")->execute([$id]);
  }
}
