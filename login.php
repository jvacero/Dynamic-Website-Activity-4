<?php
// login.php - public login page; posts to config/login_process.php.

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/cookies.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$rememberedUsername = get_remembered_username() ?? '';

$errorMessages = [
    'missing_fields'      => 'Please fill in all fields.',
    'invalid_credentials' => 'Username, email, or password is incorrect.',
];
$errorKey = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Dynamic Website Act 4</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="post-form" style="max-width:340px; margin:60px auto;">
        <h2 style="font-size:13px; text-align:center; color:var(--stud-blue-dark);">
            <span class="material-icons">login</span> Log In
        </h2>

        <?php if ($errorKey && isset($errorMessages[$errorKey])): ?>
            <p style="color:#c00; font-size:10px;"><?= htmlspecialchars($errorMessages[$errorKey]) ?></p>
        <?php endif; ?>

        <form method="POST" action="config/login_process.php">
            <label style="font-size:10px;">Username</label><br>
            <input type="text" name="username" required style="width:100%;padding:6px;margin:4px 0;"
                   value="<?= htmlspecialchars($rememberedUsername) ?>"><br>

            <label style="font-size:10px;">Email</label><br>
            <input type="email" name="email" required style="width:100%;padding:6px;margin:4px 0;"><br>

            <label style="font-size:10px;">Password</label><br>
            <input type="password" name="password" required style="width:100%;padding:6px;margin:4px 0;"><br>

            <label style="font-size:9px;">
                <input type="checkbox" name="remember" <?= $rememberedUsername ? 'checked' : '' ?>>
                Remember my username
            </label><br>

            <button type="submit" class="pixel-btn" style="width:100%;margin-top:10px;">Log In</button>
        </form>

        <p style="font-size:9px; text-align:center; margin-top:12px;">
            No account? <a href="register.php">Register here</a>
        </p>
    </div>
</body>
</html>
