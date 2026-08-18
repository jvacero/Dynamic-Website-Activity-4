<?php
// auth.php - authentication/authorization helpers.
// email -> SHA-256 (deterministic lookup), password -> bcrypt, username -> plain text.

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/mysqli_connect.php';

function hash_email(string $email): string {
    return hash('sha256', strtolower(trim($email)));
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

// Fetches the full users+profiles row for whoever is logged in.
function get_current_user_row(mysqli $conn): ?array {
    if (!is_logged_in()) {
        return null;
    }
    $stmt = $conn->prepare(
        "SELECT u.user_id, u.username, u.role, p.display_name, p.bio, p.cover_photo, p.profile_photo
         FROM users u JOIN profiles p ON p.user_id = u.user_id
         WHERE u.user_id = ?"
    );
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ?: null;
}
