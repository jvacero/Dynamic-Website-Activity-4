<?php
// session.php - centralizes secure PHP session startup.

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,        // expires when the browser closes
            'path'     => '/',
            'httponly' => true,     // blocks JS access to the cookie (XSS mitigation)
            'samesite' => 'Lax',    // blocks the cookie being sent on cross-site requests
        ]);
        session_start();
    }
}

start_secure_session();
