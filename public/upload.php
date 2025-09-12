<?php
require_once __DIR__ . '/../auth.php';
require_login();
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload & Scan - <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7fb; margin: 0; }
        header { background:#111827; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; }
        a { color: #2f6fed; text-decoration: none; }
        .container { max-width: 960px; margin: 24px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        h1 { margin: 0 0 16px; font-size: 22px; }
        .alert { padding:10px 12px; border-radius:6px; margin-bottom:12px; }
        .alert.success { background:#e6f4ea; color:#1e7e34; }
        .alert.error { background:#fdecea; color:#b00020; }
        table { width:100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom:1px solid #e5e7eb; text-align:left; padding:10px; font-size:14px; }
        th { background:#f9fafb; }
    </style>
    </head>
<body>
    <header>
        <div>Upload & Scan</div>
        <nav>
            <a href="/">Home</a> | <a href="/scans.php">My Scans</a> | <a href="/logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <h1>Scan a File</h1>
        <div id="alert" class="alert error" style="display:none;"></div>
        <form id="scanForm" method="post" enctype="multipart/form-data" onsubmit="return false;">
            <input type="file" name="file" required>
            <button id="startBtn" type="submit" style="margin-left:12px; padding:10px 12px; background:#111827; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Upload & Scan</button>
        </form>

        <div id="progress" style="margin-top:24px; display:none;">
            <h2>Real-time Log</h2>
            <pre id="logBox" style="background:#0b1020; color:#d1d5db; padding:12px; border-radius:6px; max-height:260px; overflow:auto;">Waiting...</pre>
            <table style="margin-top:12px;">
                <tr><th>Status</th><td id="st">-</td></tr>
                <tr><th>Verdict</th><td id="vd">-</td></tr>
                <tr><th>Malicious</th><td id="ma">-</td></tr>
                <tr><th>Suspicious</th><td id="su">-</td></tr>
                <tr><th>Undetected</th><td id="un">-</td></tr>
            </table>
        </div>
    </div>
<script>
const form = document.getElementById('scanForm');
const alertBox = document.getElementById('alert');
const progress = document.getElementById('progress');
const logBox = document.getElementById('logBox');
const st = document.getElementById('st');
const vd = document.getElementById('vd');
const ma = document.getElementById('ma');
const su = document.getElementById('su');
const un = document.getElementById('un');
const startBtn = document.getElementById('startBtn');

let pollHandle = null;

form.addEventListener('submit', async () => {
    alertBox.style.display = 'none';
    startBtn.disabled = true;
    progress.style.display = 'block';
    logBox.textContent = 'Uploading...\n';
    st.textContent = '-'; vd.textContent = '-'; ma.textContent = su.textContent = un.textContent = '-';

    const fd = new FormData(form);
    try {
        const res = await fetch('/scan_start.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.ok) throw new Error(data.error || 'Failed to start');
        const id = data.scan_id;
        // Begin polling
        pollHandle = setInterval(async () => {
            try {
                const sres = await fetch('/scan_status.php?id=' + encodeURIComponent(id));
                const sdata = await sres.json();
                if (!sdata.ok) throw new Error('status error');
                const sc = sdata.scan;
                if (sc.log) logBox.textContent = sc.log;
                if (sc.status) st.textContent = sc.status;
                if (sc.verdict) vd.textContent = sc.verdict;
                if (sc.malicious_count !== null) ma.textContent = sc.malicious_count;
                if (sc.suspicious_count !== null) su.textContent = sc.suspicious_count;
                if (sc.undetected_count !== null) un.textContent = sc.undetected_count;
                if (sc.status === 'completed' || sc.status === 'submit_failed') {
                    clearInterval(pollHandle);
                    startBtn.disabled = false;
                }
                if (sc.error_message) {
                    alertBox.textContent = sc.error_message;
                    alertBox.style.display = 'block';
                }
            } catch (e) {
                console.error(e);
            }
        }, 2000);
    } catch (e) {
        alertBox.textContent = e.message;
        alertBox.style.display = 'block';
        startBtn.disabled = false;
    }
});
</script>
</body>
</html>


