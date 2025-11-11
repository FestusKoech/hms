<?php
function log_action(PDO $pdo, array $opts): void {
    $sql = "INSERT INTO activity_log
        (happened_at, actor_id, actor_role, area, action, patient_id, subject_id, subject_type, details_json, ip_addr)
        VALUES (CURRENT_TIMESTAMP(6), :actor_id, :actor_role, :area, :action, :patient_id, :subject_id, :subject_type, :details_json, :ip_addr)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':actor_id'     => $opts['actor_id'],
        ':actor_role'   => $opts['actor_role'],
        ':area'         => $opts['area'],
        ':action'       => $opts['action'],
        ':patient_id'   => $opts['patient_id']   ?? null,
        ':subject_id'   => $opts['subject_id']   ?? null,
        ':subject_type' => $opts['subject_type'] ?? null,
        ':details_json' => isset($opts['details']) ? json_encode($opts['details'], JSON_UNESCAPED_UNICODE) : null,
        ':ip_addr'      => isset($_SERVER['REMOTE_ADDR']) ? inet_pton($_SERVER['REMOTE_ADDR']) : null,
    ]);
}
