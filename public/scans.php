<?php
require_once __DIR__ . '/../auth.php';
require_login();
$user = current_user();
$pdo = get_pdo_connection();
$stmt = $pdo->prepare('SELECT id, original_filename, verdict, malicious_count, suspicious_count, undetected_count, created_at FROM file_scans WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);
$scans = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Scans - <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7fb; margin: 0; }
        header { background:#111827; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; }
        a { color: #2f6fed; text-decoration: none; }
        .container { max-width: 960px; margin: 24px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        h1 { margin: 0 0 16px; font-size: 22px; }
        table { width:100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom:1px solid #e5e7eb; text-align:left; padding:10px; font-size:14px; }
        th { background:#f9fafb; }
        .tag { padding:2px 8px; border-radius:9999px; font-size:12px; }
        .tag.malicious { background:#fdecea; color:#b00020; }
        .tag.suspicious { background:#fff4e5; color:#92400e; }
        .tag.clean { background:#e6f4ea; color:#1e7e34; }
    </style>
    </head>
<body>
    <header>
        <div>My Scans</div>
        <nav>
            <a href="/">Home</a> | <a href="/upload.php">Upload & Scan</a> | <a href="/logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <h1>Previous Scan Results</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>File</th>
                    <th>Verdict</th>
                    <th>Mal</th>
                    <th>Sus</th>
                    <th>Undet</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scans as $s): ?>
                    <?php $v = $s['verdict'] ?: 'clean'; ?>
                    <tr>
                        <td><?php echo (int)$s['id']; ?></td>
                        <td><?php echo htmlspecialchars($s['original_filename']); ?></td>
                        <td><span class="tag <?php echo htmlspecialchars($v); ?>"><?php echo htmlspecialchars($v); ?></span></td>
                        <td><?php echo (int)$s['malicious_count']; ?></td>
                        <td><?php echo (int)$s['suspicious_count']; ?></td>
                        <td><?php echo (int)$s['undetected_count']; ?></td>
                        <td><?php echo htmlspecialchars($s['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>


