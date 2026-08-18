<?php
// index.php - app shell: gates on login, handles POST actions, renders the page.

require_once __DIR__ . '/config/auth.php'; // also loads session.php + mysqli_connect.php

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/dashboard.php';
require_once __DIR__ . '/includes/admin_dashboard.php';

$uploadDir = __DIR__ . '/assets/uploads/';
$isAdminView = (($_GET['view'] ?? '') === 'admin') && is_admin();

// Process any form submission before any HTML is echoed.
if ($isAdminView) {
    admin_handle_post($conn);
} else {
    dashboard_handle_post($conn, $uploadDir);
}

require __DIR__ . '/includes/header.php';
?>

<main class="page-body" style="display:flex; max-width:1000px; margin:0 auto; gap:16px; padding:16px; flex-wrap:wrap;">

    <aside style="flex:1; min-width:260px;">
        <?php require __DIR__ . '/includes/profile.php'; ?>
        <div id="musicWidget"><?php require __DIR__ . '/includes/music_list.php'; ?></div>
        <div id="friendsWidget"><?php require __DIR__ . '/includes/friend_lists.php'; ?></div>
    </aside>

    <section style="flex:2; min-width:320px;">
        <?php if ($isAdminView): ?>
            <?php render_admin_dashboard($conn); ?>
        <?php else: ?>
            <?php render_dashboard($conn); ?>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
