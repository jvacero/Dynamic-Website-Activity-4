<?php
// header.php - <head> stylesheet links + top navigation bar shown on every page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Website Act 4</title>

    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <link rel="stylesheet" href="css/music_list.css">
    <link rel="stylesheet" href="css/friend_lists.css">
</head>
<body>

<header class="site-header">
    <div class="logo">Dynamic_Website_Act4</div>

    <nav>
        <ul class="site-nav">
            <li><a href="index.php"><span class="material-icons">home</span> Home</a></li>
            <li><a href="index.php#musicWidget"><span class="material-icons">music_note</span> Music</a></li>
            <li><a href="index.php#friendsWidget"><span class="material-icons">group</span> Friends</a></li>
            <?php if (is_admin()): ?>
                <li><a href="index.php?view=admin"><span class="material-icons">admin_panel_settings</span> Admin</a></li>
            <?php endif; ?>
            <li><a href="config/logout.php"><span class="material-icons">logout</span> Logout</a></li>
        </ul>
    </nav>
</header>
