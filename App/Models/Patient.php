<?php
namespace App\Models;
use App\Core\DB;

final class Patient {
  public static function paginate(int $page=1,int $per=15): array {
    $off = ($page-1)*$per;
    $st = DB::pdo()->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM patients ORDER BY id DESC LIMIT {$per} OFFSET {$off}");
    $st->execute(); $data = $st->fetchAll();
    $total = (int)DB::pdo()->query("SELECT FOUND_ROWS()")->fetchColumn();
    return ['data'=>$data,'total'=>$total,'page'=>$page,'per'=>$per];
  }
 public static function create(array $d): int
    {
        $pdo = DB::pdo();

        // Normalize inputs
        $patientNo = isset($d['patient_no']) ? trim((string)$d['patient_no']) : '';
        if ($patientNo === '') {
            $patientNo = self::generatePatientNo($pdo); // <-- ensure not ''
        }

        // OPTIONAL: normalize truly optional fields to NULL (avoid '')
        $phone       = isset($d['phone']) ? trim((string)$d['phone']) : null;
        $national_id = isset($d['national_id']) ? trim((string)$d['national_id']) : null;
        $email       = isset($d['email']) ? trim((string)$d['email']) : null;

        $sql = "INSERT INTO patients
                (patient_no, first_name, last_name, gender, dob, phone, national_id, email, created_at)
                VALUES (:patient_no, :first_name, :last_name, :gender, :dob, :phone, :national_id, :email, NOW())";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':patient_no' => $patientNo,
                ':first_name' => trim((string)($d['first_name'] ?? '')),
                ':last_name'  => trim((string)($d['last_name'] ?? '')),
                ':gender'     => trim((string)($d['gender'] ?? '')),
                ':dob'        => ($d['dob'] ?? null) ?: null,
                ':phone'      => $phone !== '' ? $phone : null,
                ':national_id'=> $national_id !== '' ? $national_id : null,
                ':email'      => $email !== '' ? $email : null,
            ]);
            return (int)$pdo->lastInsertId();

        } catch (PDOException $e) {
            // Duplicate key handling (patient_no / phone / national_id ... depending on your indexes)
            if ($e->errorInfo[0] === '23000' && (int)$e->errorInfo[1] === 1062) {
                // Derive which unique key failed (MySQL includes it in the message)
                $msg = $e->getMessage();
                if (stripos($msg, 'patient_no') !== false) {
                    throw new \RuntimeException('Duplicate patient number. Please try again.', 1062, $e);
                }
                if (stripos($msg, 'phone') !== false) {
                    throw new \RuntimeException('This phone number is already registered.', 1062, $e);
                }
                if (stripos($msg, 'national_id') !== false) {
                    throw new \RuntimeException('This national ID is already registered.', 1062, $e);
                }
                throw new \RuntimeException('Duplicate record detected.', 1062, $e);
            }
            throw $e;
        }
    }

    private static function generatePatientNo(PDO $pdo): string
    {
        // Pattern: PYYMM-#### (e.g., P2511-0384)
        $prefix = 'P' . date('ym') . '-';

        // Try a few times to avoid race duplicates
        for ($i = 0; $i < 5; $i++) {
            $suffix = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $code   = $prefix . $suffix;

            $st = $pdo->prepare("SELECT 1 FROM patients WHERE patient_no = ?");
            $st->execute([$code]);
            if ($st->fetchColumn() === false) {
                return $code; // unique
            }
        }
        // Last resort: ultra-unique suffix
        return $prefix . substr(bin2hex(random_bytes(3)), 0, 6);
    }

  public static function find(int $id): ?array {
    $st = DB::pdo()->prepare("SELECT * FROM patients WHERE id=?"); $st->execute([$id]); $r=$st->fetch();
    return $r ?: null;
  }
  public static function update(int $id,array $d): void {
    $sql="UPDATE patients SET patient_no=?,first_name=?,last_name=?,dob=?,sex=?,contact=?,address=?,emergency_contact=? WHERE id=?";
    DB::pdo()->prepare($sql)->execute([
      $d['patient_no'],$d['first_name'],$d['last_name'],$d['dob']??null,$d['sex']??null,
      $d['contact']??null,$d['address']??null,$d['emergency_contact']??null,$id
    ]);
  }


  public static function search(string $q, int $limit=50): array {
  $q = '%'.$q.'%';
  $sql="SELECT id, patient_no, first_name, last_name, contact FROM patients
        WHERE patient_no LIKE ? OR first_name LIKE ? OR last_name LIKE ?
        ORDER BY last_name, first_name LIMIT ?";
  $st=\App\Core\DB::pdo()->prepare($sql);
  $st->bindValue(1,$q); $st->bindValue(2,$q); $st->bindValue(3,$q);
  $st->bindValue(4,$limit,\PDO::PARAM_INT);
  $st->execute(); return $st->fetchAll();
}

  public static function delete(int $id): void {
    DB::pdo()->prepare("DELETE FROM patients WHERE id=?")->execute([$id]);
  }
}


