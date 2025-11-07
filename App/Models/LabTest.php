<?php
namespace App\Models;
use App\Core\DB;

final class LabTest {
  public static function all(): array { return DB::pdo()->query("SELECT * FROM lab_tests ORDER BY name")->fetchAll(); }
}
