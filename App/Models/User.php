<?php
namespace App\Models;
use App\Core\DB;

final class User {
  public static function findByEmail(string $email): ?array {
    $st = DB::pdo()->prepare("SELECT * FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();
    return $u ?: null;
  }
}
