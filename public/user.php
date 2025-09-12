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
    <title>User Dashboard - <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7fb; margin: 0; }
        header { background:#111827; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; }
        a { color: #2f6fed; text-decoration: none; }
        .container { max-width: 960px; margin: 24px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        h1 { margin: 0 0 16px; font-size: 22px; }
        p { color:#374151; }
    </style>
    </head>
<body>
    <header>
        <div>User Dashboard</div>
        <nav>
            <a href="/">Home</a> | <a href="/upload.php">Upload & Scan</a> | <a href="/scans.php">My Scans</a> | <a href="/logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['email']); ?></h1>
        <p>This is a simple protected page for role <strong><?php echo htmlspecialchars($user['role']); ?></strong>.</p>
        <?php if ($user['role'] === 'admin'): ?>
            <p><a href="/admin.php">Go to Admin Panel</a></p>
        <?php endif; ?>
    </div>
</body>
</html>


