<?php
namespace App\Models;
use PDO;

class AppointmentModel {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function create(array $data): int {
        // $data: patient_id, starts_at, ends_at, reason (nullable)
        $sql = "INSERT INTO appointments (patient_id, doctor_id, starts_at, ends_at, reason, status)
                VALUES (:patient_id, NULL, :starts_at, :ends_at, :reason, 'scheduled')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':patient_id' => (int)$data['patient_id'],
            ':starts_at'  => $data['starts_at'],
            ':ends_at'    => $data['ends_at'],
            ':reason'     => $data['reason'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function listScheduledUnclaimed(?string $since = null): array {
        $since = $since ?? date('Y-m-d 00:00:00');
        $sql = "SELECT a.*, p.first_name, p.last_name, p.code
                FROM appointments a
                JOIN patients p ON p.id = a.patient_id
                WHERE a.status='scheduled' AND a.doctor_id IS NULL
                  AND a.starts_at >= :since
                ORDER BY a.starts_at ASC
                LIMIT 200";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':since'=>$since]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listMyInProgress(int $doctorId): array {
        $sql = "SELECT a.*, p.first_name, p.last_name, p.code
                FROM appointments a
                JOIN patients p ON p.id = a.patient_id
                WHERE a.status IN ('scheduled','in_progress')
                  AND (a.doctor_id = :doc OR (a.doctor_id IS NULL AND a.status='scheduled'))
                ORDER BY a.starts_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':doc'=>$doctorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function claim(int $appointmentId, int $doctorId): bool {
        $sql = "UPDATE appointments
                SET doctor_id = :doc, status = 'in_progress'
                WHERE id = :id AND doctor_id IS NULL AND status='scheduled'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':doc'=>$doctorId, ':id'=>$appointmentId]);
        return $stmt->rowCount() === 1;
    }

    public function markCompleted(int $appointmentId): bool {
        $stmt = $this->pdo->prepare("UPDATE appointments SET status='completed' WHERE id=:id");
        $stmt->execute([':id'=>$appointmentId]);
        return $stmt->rowCount() === 1;
    }
}
