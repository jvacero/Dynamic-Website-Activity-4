<?php
// register.php - registration page + handler. Creates a 'user' role account
// (admin accounts only via database/seed.php). Same hashing rules as login_process.php.

require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (empty($errors)) {
        $emailHash = hash_email($email);

        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email_hash = ?");
        $check->bind_param("ss", $username, $emailHash);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'That username or email is already registered.';
        }
        $check->close();
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, email_hash, password_hash, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $emailHash, $passwordHash);
        $stmt->execute();
        $newUserId = $stmt->insert_id;
        $stmt->close();

        $displayName = $username;
        $stmt = $conn->prepare("INSERT INTO profiles (user_id, display_name) VALUES (?, ?)");
        $stmt->bind_param("is", $newUserId, $displayName);
        $stmt->execute();
        $stmt->close();

        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Dynamic Website Act 4</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="post-form" style="max-width:340px; margin:60px auto;">
        <h2 style="font-size:13px; text-align:center; color:var(--stud-blue-dark);">
            <span class="material-icons">person_add</span> Register
        </h2>

        <?php foreach ($errors as $error): ?>
            <p style="color:#c00; font-size:10px;"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <form method="POST" action="register.php">
            <label style="font-size:10px;">Username</label><br>
            <input type="text" name="username" required style="width:100%;padding:6px;margin:4px 0;"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"><br>

            <label style="font-size:10px;">Email</label><br>
            <input type="email" name="email" required style="width:100%;padding:6px;margin:4px 0;"><br>

            <label style="font-size:10px;">Password</label><br>
            <input type="password" name="password" required style="width:100%;padding:6px;margin:4px 0;"><br>

            <label style="font-size:10px;">Confirm Password</label><br>
            <input type="password" name="confirm_password" required style="width:100%;padding:6px;margin:4px 0;"><br>

            <button type="submit" class="pixel-btn" style="width:100%;margin-top:10px;">Create Account</button>
        </form>

        <p style="font-size:9px; text-align:center; margin-top:12px;">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </div>
</body>
</html>
