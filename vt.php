<?php
require_once __DIR__ . '/db.php';

function vt_curl_available(): bool {
    return function_exists('curl_init');
}

function vt_get_active_api_key(): ?string {
    $pdo = get_pdo_connection();
    $stmt = $pdo->query('SELECT api_key FROM api_keys WHERE is_active = TRUE ORDER BY id DESC LIMIT 1');
    $row = $stmt->fetch();
    return $row['api_key'] ?? null;
}

function vt_upload_file(string $filePath, ?string &$errorMessage = null): ?string {
    if (!vt_curl_available()) { return null; }
    $apiKey = vt_get_active_api_key();
    if (!$apiKey) { return null; }

    $size = filesize($filePath);
    $useLarge = $size !== false && $size > 32 * 1024 * 1024; // >32MB uses large upload

    $uploadUrl = 'https://www.virustotal.com/api/v3/files';
    if ($useLarge) {
        // Fetch a one-time large upload URL
        $ch0 = curl_init();
        curl_setopt_array($ch0, [
            CURLOPT_URL => 'https://www.virustotal.com/api/v3/files/upload_url',
            CURLOPT_HTTPHEADER => [ 'x-apikey: ' . $apiKey ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);
        $resp0 = curl_exec($ch0);
        if ($resp0 === false) { $errorMessage = 'cURL error (get upload_url): ' . curl_error($ch0); curl_close($ch0); return null; }
        $code0 = curl_getinfo($ch0, CURLINFO_HTTP_CODE);
        curl_close($ch0);
        if ($code0 < 200 || $code0 >= 300) { $errorMessage = 'HTTP ' . $code0 . ' from upload_url endpoint'; return null; }
        $data0 = json_decode($resp0, true);
        $uploadUrl = $data0['data'] ?? $uploadUrl;
    }

    $ch = curl_init();
    $cfile = new CURLFile($filePath);
    curl_setopt_array($ch, [
        CURLOPT_URL => $uploadUrl,
        CURLOPT_HTTPHEADER => [ 'x-apikey: ' . $apiKey ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [ 'file' => $cfile ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 600,
    ]);
    $response = curl_exec($ch);
    if ($response === false) { $errorMessage = 'cURL error: ' . curl_error($ch); curl_close($ch); return null; }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code < 200 || $code >= 300) { $errorMessage = 'HTTP ' . $code . ' from upload endpoint: ' . substr($response, 0, 300); return null; }
    $data = json_decode($response, true);
    if (!isset($data['data']['id'])) { $errorMessage = 'Unexpected VT response'; return null; }
    return $data['data']['id'];
}

function vt_get_analysis(string $analysisId, ?string &$errorMessage = null): ?array {
    if (!vt_curl_available()) { return null; }
    $apiKey = vt_get_active_api_key();
    if (!$apiKey) { return null; }
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.virustotal.com/api/v3/analyses/' . urlencode($analysisId),
        CURLOPT_HTTPHEADER => [ 'x-apikey: ' . $apiKey ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    ]);
    $response = curl_exec($ch);
    if ($response === false) { $errorMessage = 'cURL error: ' . curl_error($ch); curl_close($ch); return null; }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code < 200 || $code >= 300) { $errorMessage = 'HTTP ' . $code . ' from analyses endpoint: ' . substr($response, 0, 300); return null; }
    return json_decode($response, true);
}

function vt_scan_and_wait(string $filePath, int $maxAttempts = 15, int $sleepSeconds = 2, ?string &$errorMessage = null): ?array {
    $analysisId = vt_upload_file($filePath, $errorMessage);
    if (!$analysisId) { return null; }
    for ($i = 0; $i < $maxAttempts; $i++) {
        $result = vt_get_analysis($analysisId, $errorMessage);
        if (!$result) { return null; }
        $status = $result['data']['attributes']['status'] ?? '';
        if ($status === 'completed') { return $result; }
        sleep($sleepSeconds);
    }
    return $result ?? null;
}

function parse_vt_verdict(array $vt): array {
    $attr = $vt['data']['attributes'] ?? [];
    $stats = $attr['stats'] ?? [];
    $mal = (int)($stats['malicious'] ?? 0);
    $sus = (int)($stats['suspicious'] ?? 0);
    $und = (int)($stats['undetected'] ?? 0);
    $verdict = 'clean';
    if ($mal > 0) { $verdict = 'malicious'; }
    else if ($sus > 0) { $verdict = 'suspicious'; }
    return [
        'status' => $attr['status'] ?? 'unknown',
        'verdict' => $verdict,
        'malicious' => $mal,
        'suspicious' => $sus,
        'undetected' => $und,
    ];
}


