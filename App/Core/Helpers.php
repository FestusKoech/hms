<?php
namespace App\Core;

use PDO;

final class Helpers
{
    // Simple flash store
    public static function flash(string $message, string $type='success'): void {
        $_SESSION['flash'] = ['msg'=>$message, 'type'=>$type];
    }
    // Retrieve & clear flash
    public static function takeFlash(): ?array {
        if (!empty($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
        return null;
    }

    // Form error bag
    public static function setError(string $field, string $msg): void { $_SESSION['errors'][$field] = $msg; }
    public static function errors(): array { return $_SESSION['errors'] ?? []; }
    public static function clearErrors(): void { unset($_SESSION['errors']); }

    // Old input
    public static function setOld(array $data): void { $_SESSION['old'] = $data; }
    public static function old(string $key, $default='') {
        return htmlspecialchars((string)($_SESSION['old'][$key] ?? $default));
    }
    public static function clearOld(): void { unset($_SESSION['old']); }

    // Activity log (ported from your previous global function)
    public static function logAction(PDO $pdo, array $opts): void {
        $sql = "INSERT INTO activity_log
            (happened_at, actor_id, actor_role, area, action, patient_id, subject_id, subject_type, details_json, ip_addr)
            VALUES (CURRENT_TIMESTAMP(6), :actor_id, :actor_role, :area, :action, :patient_id, :subject_id, :subject_type, :details_json, :ip_addr)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':actor_id'     => $opts['actor_id']    ?? null,
            ':actor_role'   => $opts['actor_role']  ?? null,
            ':area'         => $opts['area']        ?? null,
            ':action'       => $opts['action']      ?? null,
            ':patient_id'   => $opts['patient_id']  ?? null,
            ':subject_id'   => $opts['subject_id']  ?? null,
            ':subject_type' => $opts['subject_type']?? null,
            ':details_json' => isset($opts['details']) ? json_encode($opts['details'], JSON_UNESCAPED_UNICODE) : null,
            ':ip_addr'      => isset($_SERVER['REMOTE_ADDR']) ? inet_pton($_SERVER['REMOTE_ADDR']) : null,
        ]);
    }
}

// (Optional) keep a global function if some older files still call it
if (!function_exists('log_action')) {
    function log_action(PDO $pdo, array $opts): void {
        \App\Core\Helpers::logAction($pdo, $opts);
    }
}

