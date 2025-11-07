<?php
namespace App\Core;
final class Csrf {
  public static function token(): string {
    if(session_status()!==PHP_SESSION_ACTIVE) session_start();
    $_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
  }
  public static function check(?string $token): bool {
    if(session_status()!==PHP_SESSION_ACTIVE) session_start();
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$token);
  }
}
