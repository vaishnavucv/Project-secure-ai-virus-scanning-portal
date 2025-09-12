<?php
require_once __DIR__ . '/../auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        if (login($email, $password)) {
            header('Location: /');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    } else {
        $error = 'Email and password are required';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7fb; margin: 0; }
        .container { max-width: 420px; margin: 10vh auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        h1 { margin: 0 0 16px; font-size: 22px; }
        label { display:block; margin: 12px 0 6px; font-weight: 600; }
        input[type="email"], input[type="password"] { width:100%; padding:10px 12px; border:1px solid #d8dbe0; border-radius:6px; }
        button { margin-top:16px; width:100%; padding:10px 12px; background:#2f6fed; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
        .error { color:#b00020; margin-top:10px; }
        .hint { color:#6b7280; margin-top:12px; font-size: 12px; }
        code { background:#f3f4f6; padding:2px 6px; border-radius:4px; }
    </style>
    </head>
<body>
    <div class="container">
        <h1>Sign in</h1>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <div class="hint">
            Demo:
            Admin <code>admin@secure.com</code>, User <code>user1@secure.com</code>, password <code>password</code>
        </div>
    </div>
</body>
</html>


