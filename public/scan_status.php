<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../vt.php';
require_login();

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error' => 'Invalid id']); exit; }

$pdo = get_pdo_connection();
$stmt = $pdo->prepare('SELECT id, user_id, vt_analysis_id, status, log FROM file_scans WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); echo json_encode(['error' => 'Not found']); exit; }

$log = $row['log'] ?? '';
function log_step(&$log, $msg) { $log .= '[' . date('H:i:s') . "] " . $msg . "\n"; }

if ($row['status'] === 'submitted' || $row['status'] === 'in_progress') {
    log_step($log, 'Checking analysis status');
    $err = null;
    $vt = vt_get_analysis($row['vt_analysis_id'], $err);
    if ($vt) {
        $parsed = parse_vt_verdict($vt);
        $status = $parsed['status'];
        if ($status === 'completed') {
            $stmtU = $pdo->prepare('UPDATE file_scans SET status = ?, verdict = ?, malicious_count = ?, suspicious_count = ?, undetected_count = ?, vt_response = ?, log = ? WHERE id = ?');
            $stmtU->execute([
                $status,
                $parsed['verdict'],
                $parsed['malicious'],
                $parsed['suspicious'],
                $parsed['undetected'],
                json_encode($vt),
                $log,
                $row['id']
            ]);
        } else {
            $stmtU = $pdo->prepare('UPDATE file_scans SET status = ?, log = ? WHERE id = ?');
            $stmtU->execute(['in_progress', $log, $row['id']]);
        }
    } else {
        if ($err) { log_step($log, 'Error: ' . $err); }
        $stmtU = $pdo->prepare('UPDATE file_scans SET error_message = ?, log = ? WHERE id = ?');
        $stmtU->execute([$err ?: 'Failed to retrieve analysis', $log, $row['id']]);
    }
}

$stmt2 = $pdo->prepare('SELECT status, verdict, malicious_count, suspicious_count, undetected_count, log, error_message FROM file_scans WHERE id = ?');
$stmt2->execute([$row['id']]);
$cur = $stmt2->fetch();
echo json_encode(['ok' => true, 'scan' => $cur]);


