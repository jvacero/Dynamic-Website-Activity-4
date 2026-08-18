<?php
// profile.php - renders the logged-in user's profile card (cover, avatar, bio,
// and an admin verified badge). REQUIRES: $conn (mysqli) + auth.php already loaded.

$profileUser = get_current_user_row($conn);

if (!$profileUser) {
    echo '<p>Profile unavailable.</p>';
    return;
}
?>
<div class="profile-card">
    <div class="profile-cover" style="background-image:url('<?= htmlspecialchars($profileUser['cover_photo']) ?>');">
        <img class="profile-avatar" src="<?= htmlspecialchars($profileUser['profile_photo']) ?>" alt="Profile photo">
    </div>

    <div class="profile-info">
        <h2>
            <?= htmlspecialchars($profileUser['display_name']) ?>

            <?php if ($profileUser['role'] === 'admin'): ?>
                <span class="material-icons" title="Verified Administrator" style="color:#1DA1F2;">
                    verified
                </span>
            <?php endif; ?>
        </h2>

        <p class="profile-username">@<?= htmlspecialchars($profileUser['username']) ?></p>
        <p class="profile-bio"><?= htmlspecialchars($profileUser['bio']) ?></p>
    </div>
</div>
