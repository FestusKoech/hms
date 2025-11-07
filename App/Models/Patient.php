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
  public static function create(array $d): int {
    $sql="INSERT INTO patients(patient_no,first_name,last_name,dob,sex,contact,address,emergency_contact,created_by)
          VALUES(?,?,?,?,?,?,?,?,?)";
    DB::pdo()->prepare($sql)->execute([
      $d['patient_no'],$d['first_name'],$d['last_name'],$d['dob']??null,$d['sex']??null,
      $d['contact']??null,$d['address']??null,$d['emergency_contact']??null,$d['created_by']
    ]);
    return (int)DB::pdo()->lastInsertId();
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
