<?php
namespace App\Core;
final class Gate {
  public static function requireRole(array $roles): void {
    $u = Auth::user();
    if(!$u || !in_array($u['role'], $roles, true)) {
      header('HTTP/1.1 403 Forbidden'); exit('Forbidden');
    }
  }
}
