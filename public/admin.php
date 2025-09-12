<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$message = '';
$error = '';
$tab = $_GET['tab'] ?? 'users';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    if ($email && $password) {
        if (create_user($email, $password, $role)) {
            $message = 'User created successfully';
        } else {
            $error = 'Could not create user (email may already exist)';
        }
    } else {
        $error = 'Email and password are required';
    }
}

$pdo = get_pdo_connection();
$users = $pdo->query('SELECT id, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();

// API Keys actions
if ($tab === 'keys') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create_key'])) {
            $name = trim($_POST['name'] ?? '');
            $api_key = trim($_POST['api_key'] ?? '');
            $is_active = isset($_POST['is_active']) ? 'TRUE' : 'FALSE';
            if ($name && $api_key) {
                if ($is_active === 'TRUE') { $pdo->exec('UPDATE api_keys SET is_active = FALSE'); }
                $stmt = $pdo->prepare('INSERT INTO api_keys (name, api_key, is_active) VALUES (?, ?, ?)');
                $stmt->execute([$name, $api_key, $is_active]);
                $message = 'API key added';
            } else { $error = 'Name and key are required'; }
        } elseif (isset($_POST['activate_key'])) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $pdo->exec('UPDATE api_keys SET is_active = FALSE');
                $stmt = $pdo->prepare('UPDATE api_keys SET is_active = TRUE WHERE id = ?');
                $stmt->execute([$id]);
                $message = 'API key activated';
            }
        } elseif (isset($_POST['delete_key'])) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $stmt = $pdo->prepare('DELETE FROM api_keys WHERE id = ?');
                $stmt->execute([$id]);
                $message = 'API key removed';
            }
        }
    }
}

$keys = $pdo->query('SELECT id, name, is_active, created_at FROM api_keys ORDER BY id DESC')->fetchAll();

$scans = $pdo->query('SELECT s.id, u.email, s.original_filename, s.verdict, s.malicious_count, s.suspicious_count, s.undetected_count, s.created_at FROM file_scans s JOIN users u ON u.id = s.user_id ORDER BY s.id DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7fb; margin: 0; }
        header { background:#111827; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; }
        a { color: #2f6fed; text-decoration: none; }
        .container { max-width: 960px; margin: 24px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        h1 { margin: 0 0 16px; font-size: 22px; }
        label { display:block; margin: 12px 0 6px; font-weight: 600; }
        input, select { width:100%; padding:10px 12px; border:1px solid #d8dbe0; border-radius:6px; }
        button { margin-top:16px; padding:10px 12px; background:#111827; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
        .row { display:grid; grid-template-columns: 1fr 1fr; gap: 24px; }
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
        <div>Admin Panel</div>
        <nav>
            <a href="/">Home</a> | <a href="/logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div style="margin-bottom:16px;">
            <a href="/admin.php?tab=users" style="margin-right:12px;">Users</a>
            <a href="/admin.php?tab=keys" style="margin-right:12px;">API Keys</a>
            <a href="/admin.php?tab=scans">Scans</a>
        </div>

        <?php if ($tab === 'users'): ?>
        <h1>Add New User</h1>
        <?php if ($message): ?><div class="alert success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
            </div>
            <div class="row">
                <div>
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div style="display:flex; align-items:flex-end;">
                    <button type="submit">Create User</button>
                </div>
            </div>
        </form>

        <h1 style="margin-top:32px;">Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo (int)$u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if ($tab === 'keys'): ?>
        <h1>VirusTotal API Keys</h1>
        <?php if ($message): ?><div class="alert success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div>
                    <label for="api_key">API Key</label>
                    <input type="text" id="api_key" name="api_key" required>
                </div>
            </div>
            <label><input type="checkbox" name="is_active"> Set active</label>
            <div>
                <button type="submit" name="create_key" value="1">Add Key</button>
            </div>
        </form>

        <h1 style="margin-top:32px;">Existing Keys</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Active</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keys as $k): ?>
                    <tr>
                        <td><?php echo (int)$k['id']; ?></td>
                        <td><?php echo htmlspecialchars($k['name']); ?></td>
                        <td><?php echo $k['is_active'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo htmlspecialchars($k['created_at']); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$k['id']; ?>">
                                <button type="submit" name="activate_key" value="1">Activate</button>
                            </form>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this key?');">
                                <input type="hidden" name="id" value="<?php echo (int)$k['id']; ?>">
                                <button type="submit" name="delete_key" value="1">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if ($tab === 'scans'): ?>
        <h1>All User Scans</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
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
                    <tr>
                        <td><?php echo (int)$s['id']; ?></td>
                        <td><?php echo htmlspecialchars($s['email']); ?></td>
                        <td><?php echo htmlspecialchars($s['original_filename']); ?></td>
                        <td><?php echo htmlspecialchars($s['verdict'] ?: 'clean'); ?></td>
                        <td><?php echo (int)$s['malicious_count']; ?></td>
                        <td><?php echo (int)$s['suspicious_count']; ?></td>
                        <td><?php echo (int)$s['undetected_count']; ?></td>
                        <td><?php echo htmlspecialchars($s['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>


