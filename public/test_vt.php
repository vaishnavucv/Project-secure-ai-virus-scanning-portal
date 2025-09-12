<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../vt.php';
require_admin();

header('Content-Type: text/plain');

$key = vt_get_active_api_key();
if (!$key) {
    echo "No active API key configured.\n";
    exit;
}

echo "Active key present. Checking analyses endpoint with a dummy id...\n";
$err = null;
$r = vt_get_analysis('dummy-id', $err);
if ($r) {
    echo "Unexpected success.\n";
} else {
    echo "Expected failure message: " . ($err ?: 'unknown') . "\n";
}

echo "All good if you see an HTTP 404/400 or authorization-related message. If you see HTTP 401 or 403, the key is invalid or lacks scope.\n";


