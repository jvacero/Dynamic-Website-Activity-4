<?php
// cookies.php - "remember my username" cookie helpers (never stores password/email).

const REMEMBER_COOKIE_NAME = 'dwa4_remember_username';
const REMEMBER_COOKIE_DAYS = 30;

function set_remember_cookie(string $username): void {
    setcookie(
        REMEMBER_COOKIE_NAME,
        $username,
        [
            'expires'  => time() + (REMEMBER_COOKIE_DAYS * 24 * 60 * 60),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

function get_remembered_username(): ?string {
    return $_COOKIE[REMEMBER_COOKIE_NAME] ?? null;
}

function clear_remember_cookie(): void {
    setcookie(REMEMBER_COOKIE_NAME, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
