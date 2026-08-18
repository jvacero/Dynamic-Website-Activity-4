<?php
// login_process.php - verifies the POSTed login form against the database
// and either starts a session + redirects to index.php, or bounces back with an error.

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cookies.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if ($username === '' || $email === '' || $password === '') {
    header('Location: login.php?error=missing_fields');
    exit;
}

$emailHash = hash_email($email);

$stmt = $conn->prepare("SELECT user_id, username, role, password_hash FROM users WHERE username = ? AND email_hash = ? LIMIT 1");
$stmt->bind_param("ss", $username, $emailHash);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password_hash'])) {
    session_regenerate_id(true); // prevent session fixation

    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    $logStmt = $conn->prepare("INSERT INTO sessions_log (user_id) VALUES (?)");
    $logStmt->bind_param("i", $user['user_id']);
    $logStmt->execute();
    $logStmt->close();

    if ($remember) {
        set_remember_cookie($user['username']);
    }

    header('Location: index.php');
    exit;
}

header('Location: login.php?error=invalid_credentials');
exit;
