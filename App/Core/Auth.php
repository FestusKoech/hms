<?php
namespace App\Core;
use App\Models\User;

final class Auth {
  public static function user(): ?array {
    if(session_status()!==PHP_SESSION_ACTIVE) session_start();
    return $_SESSION['user'] ?? null;
  }

  public static function attempt(string $email, string $password): bool {
    $u = User::findByEmail($email);
    if(!$u) return false;
    if(!password_verify($password, $u['password'])) return false;
    if(session_status()!==PHP_SESSION_ACTIVE) session_start();
    $_SESSION['user'] = ['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
    return true;
  }

  public static function logout(): void {
    if(session_status()!==PHP_SESSION_ACTIVE) session_start();
    session_destroy();
  }

  public static function check(): bool { return self::user() !== null; }
}
