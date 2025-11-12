<?php
namespace App\Models;
use App\Core\DB;

final class LabTest {
  public static function all(): array {
    return DB::pdo()->query("SELECT * FROM lab_tests ORDER BY name")->fetchAll();
  }

  public static function create(string $name, ?string $infection = null): int {
    $pdo = DB::pdo();

    try {
      $sql = "INSERT INTO lab_tests (name, infection) VALUES (?, ?)";
      $st  = $pdo->prepare($sql);
      $st->execute([$name, $infection]);
    } catch (\Throwable $e) {
      $sql = "INSERT INTO lab_tests (name) VALUES (?)";
      $st  = $pdo->prepare($sql);
      $st->execute([$name]);
    }

    return (int)$pdo->lastInsertId();
  }
}
