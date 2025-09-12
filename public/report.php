<?php
require_once __DIR__ . '/../auth.php';
require_login();
$user = current_user();
$pdo = get_pdo_connection();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM file_scans WHERE id = ?');
$stmt->execute([$id]);
$scan = $stmt->fetch();
if (!$scan) { http_response_code(404); echo 'Not found'; exit; }
if ($user['role'] !== 'admin' && $scan['user_id'] !== $user['id']) { http_response_code(403); echo 'Forbidden'; exit; }

$vt = $scan['vt_response'] ? json_decode($scan['vt_response'], true) : null;
$attr = $vt['data']['attributes'] ?? [];
$stats = $attr['stats'] ?? [];
$engines = $attr['results'] ?? ($attr['last_analysis_results'] ?? []); // VT uses results/last_analysis_results

$filePath = __DIR__ . '/../uploads/' . $scan['stored_path'];
$fileExists = is_file($filePath);
$hashes = [
    'md5' => $fileExists ? md5_file($filePath) : null,
    'sha1' => $fileExists ? sha1_file($filePath) : null,
    'sha256' => $fileExists ? hash_file('sha256', $filePath) : null,
];
$size = $fileExists ? filesize($filePath) : null;
$mime = $fileExists ? (function($p){ $finfo = finfo_open(FILEINFO_MIME_TYPE); $m = finfo_file($finfo, $p); finfo_close($finfo); return $m; })($filePath) : null;

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Build top engines list
$topEngines = [];
if (is_array($engines)) {
    foreach ($engines as $name => $r) {
        $cat = $r['category'] ?? '';
        if ($cat === 'malicious' || $cat === 'suspicious') {
            $topEngines[] = [
                'name' => $name,
                'result' => $r['result'] ?? '',
                'category' => $cat,
                'engine_update' => $r['engine_update'] ?? '',
            ];
        }
    }
}
usort($topEngines, function($a,$b){ return strcmp($a['name'],$b['name']); });

$positives = (int)($stats['malicious'] ?? 0) + (int)($stats['suspicious'] ?? 0);
$totalEngines = is_array($engines) ? count($engines) : 0;
$risk = $totalEngines ? (int)round(($positives / $totalEngines) * 100) : 0;

$verdict = $scan['verdict'] ?: 'undetermined';
$conf = $positives >= 5 ? 'High' : ($positives >= 2 ? 'Medium' : 'Low');

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan Report - <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7fb; margin: 0; }
        header { background:#111827; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; }
        a { color: #2f6fed; text-decoration: none; }
        .container { max-width: 1100px; margin: 24px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        h2 { margin-top: 24px; }
        table { width:100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom:1px solid #e5e7eb; text-align:left; padding:8px; font-size:14px; vertical-align: top; }
        th { background:#f9fafb; width: 260px; }
        .tag { padding:2px 8px; border-radius:9999px; font-size:12px; }
        .tag.malicious { background:#fdecea; color:#b00020; }
        .tag.suspicious { background:#fff4e5; color:#92400e; }
        .tag.clean { background:#e6f4ea; color:#1e7e34; }
    </style>
    </head>
<body>
    <header>
        <div>Scan Report</div>
        <nav>
            <a href="/scans.php">Back to My Scans</a>
            <?php if ($user['role'] === 'admin'): ?> | <a href="/admin.php?tab=scans">Admin Scans</a><?php endif; ?>
        </nav>
    </header>
    <div class="container">
        <h2>1) Report Header & Audit Trail</h2>
        <table>
            <tr><th>Report ID</th><td><?php echo (int)$scan['id']; ?></td></tr>
            <tr><th>Generated</th><td><?php echo h($scan['created_at']); ?> UTC</td></tr>
            <tr><th>VT analysis_id</th><td><?php echo h($scan['vt_analysis_id']); ?></td></tr>
            <tr><th>Status</th><td><?php echo h($scan['status']); ?></td></tr>
            <tr><th>Log excerpt</th><td><pre style="white-space:pre-wrap; background:#0b1020; color:#d1d5db; padding:8px; border-radius:6px; max-height:180px; overflow:auto;"><?php echo h(mb_substr($scan['log'] ?? '', 0, 2000)); ?></pre></td></tr>
        </table>

        <h2>2) File Identity & Provenance</h2>
        <table>
            <tr><th>Filename</th><td><?php echo h($scan['original_filename']); ?></td></tr>
            <tr><th>Size</th><td><?php echo $size !== null ? number_format($size) . ' bytes' : '-'; ?></td></tr>
            <tr><th>MIME</th><td><?php echo h($mime ?: '-'); ?></td></tr>
            <tr><th>Hashes</th><td>
                MD5: <?php echo h($hashes['md5'] ?: '-'); ?><br>
                SHA-1: <?php echo h($hashes['sha1'] ?: '-'); ?><br>
                SHA-256: <?php echo h($hashes['sha256'] ?: '-'); ?>
            </td></tr>
        </table>

        <h2>3) Security Verdict & Scoring</h2>
        <table>
            <tr><th>Disposition</th><td><span class="tag <?php echo h($verdict === 'clean' ? 'clean' : $verdict); ?>"><?php echo h($verdict); ?></span></td></tr>
            <tr><th>Confidence</th><td><?php echo h($conf); ?></td></tr>
            <tr><th>Aggregate</th><td><?php echo $totalEngines ? ($positives . '/' . $totalEngines) : '-'; ?>, risk <?php echo $risk; ?>/100</td></tr>
            <tr><th>Top engines detecting</th><td>
                <?php if ($topEngines): ?>
                    <ul>
                        <?php foreach (array_slice($topEngines,0,10) as $e): ?>
                            <li><?php echo h($e['name'] . ' → ' . $e['category'] . ' → ' . $e['result']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: echo '-'; endif; ?>
            </td></tr>
        </table>

        <h2>4) Engine Detections</h2>
        <table>
            <thead>
                <tr><th>Engine</th><th>Category</th><th>Result</th><th>Updated</th></tr>
            </thead>
            <tbody>
                <?php if (is_array($engines)) foreach ($engines as $name => $r): ?>
                    <tr>
                        <td><?php echo h($name); ?></td>
                        <td><?php echo h($r['category'] ?? ''); ?></td>
                        <td><?php echo h($r['result'] ?? ''); ?></td>
                        <td><?php echo h($r['engine_update'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>7) Indicators of Compromise</h2>
        <p>For dynamic/network IOCs, VirusTotal returns data primarily for premium plans. If present in the JSON, you can extend this section to parse it.</p>

        <h2>12) Links</h2>
        <table>
            <tr><th>VT Permalink</th><td><?php echo $hashes['sha256'] ? '<a href="https://www.virustotal.com/gui/file/' . h($hashes['sha256']) . '" target="_blank">Open in VirusTotal</a>' : '-'; ?></td></tr>
        </table>
    </div>
</body>
</html>


