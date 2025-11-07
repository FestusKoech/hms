<?php
namespace App\Models;
use App\Core\DB;

final class Drug {
  public static function all(): array {
    return DB::pdo()->query("SELECT * FROM drugs ORDER BY name")->fetchAll();
  }
  public static function find(int $id): ?array {
    $st=DB::pdo()->prepare("SELECT * FROM drugs WHERE id=?"); $st->execute([$id]); $r=$st->fetch(); return $r ?: null;
  }
  public static function create(array $d): int {
    $sql="INSERT INTO drugs(sku,name,form,strength,qty_on_hand,reorder_level) VALUES(?,?,?,?,?,?)";
    DB::pdo()->prepare($sql)->execute([$d['sku'],$d['name'],$d['form']??null,$d['strength']??null,$d['qty_on_hand']??0,$d['reorder_level']??0]);
    return (int)DB::pdo()->lastInsertId();
  }
  public static function update(int $id,array $d): void {
    $sql="UPDATE drugs SET sku=?,name=?,form=?,strength=?,qty_on_hand=?,reorder_level=? WHERE id=?";
    DB::pdo()->prepare($sql)->execute([$d['sku'],$d['name'],$d['form']??null,$d['strength']??null,$d['qty_on_hand']??0,$d['reorder_level']??0,$id]);
  }
  public static function decrementStock(int $drugId,int $qty): void {
    DB::pdo()->prepare("UPDATE drugs SET qty_on_hand = GREATEST(qty_on_hand-?,0) WHERE id=?")->execute([$qty,$drugId]);
  }
}
