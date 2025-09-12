<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../vt.php';
require_login();
$user = current_user();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$pdo = get_pdo_connection();
$log = '';
function log_step(&$log, $msg) { $log .= '[' . date('H:i:s') . "] " . $msg . "\n"; }

try {
    if (!vt_get_active_api_key()) {
        http_response_code(500);
        echo json_encode(['error' => 'API key not configured']);
        exit;
    }

    $upload = $_FILES['file'];
    if ($upload['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload error code: ' . (int)$upload['error']]);
        exit;
    }

    $original = $upload['name'];
    $ext = pathinfo($original, PATHINFO_EXTENSION);
    $safeName = bin2hex(random_bytes(8)) . ($ext ? ('.' . $ext) : '');
    $targetDir = __DIR__ . '/../uploads';
    if (!is_dir($targetDir)) { mkdir($targetDir, 0775, true); }
    $storedPath = $targetDir . '/' . $safeName;

    log_step($log, 'Saving upload');
    if (!move_uploaded_file($upload['tmp_name'], $storedPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save uploaded file']);
        exit;
    }

    log_step($log, 'Submitting file to VirusTotal');
    $err = null;
    $analysisId = vt_upload_file($storedPath, $err);
    if (!$analysisId) {
        if ($err) { log_step($log, 'Error: ' . $err); }
        $stmt = $pdo->prepare('INSERT INTO file_scans (user_id, original_filename, stored_path, vt_analysis_id, status, log, error_message) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$user['id'], $original, $safeName, '', 'submit_failed', $log, $err ?: 'Failed to submit file to VirusTotal']);
        echo json_encode(['ok' => true, 'scan_id' => (int)$pdo->lastInsertId()]);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO file_scans (user_id, original_filename, stored_path, vt_analysis_id, status, log) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user['id'], $original, $safeName, $analysisId, 'submitted', $log]);
    $scanId = (int)$pdo->lastInsertId();

    echo json_encode(['ok' => true, 'scan_id' => $scanId]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}


