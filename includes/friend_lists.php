<?php
// friend_lists.php - lets the logged-in user search other registered users by
// username. Plain GET form submitted to index.php. REQUIRES: $conn (mysqli), session started.

$searchTerm = trim($_GET['friend_search'] ?? '');
$searchResults = [];

if ($searchTerm !== '') {
    $stmt = $conn->prepare(
        "SELECT u.user_id, u.username, p.display_name, p.profile_photo
         FROM users u JOIN profiles p ON p.user_id = u.user_id
         WHERE u.username LIKE CONCAT('%', ?, '%') AND u.user_id != ?
         LIMIT 10"
    );
    $stmt->bind_param("si", $searchTerm, $_SESSION['user_id']);
    $stmt->execute();
    $searchResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<div class="friends-widget">
    <h3><span class="material-icons">group</span> Find Users</h3>

    <form method="GET" action="index.php">
        <input type="text" name="friend_search" placeholder="Search username..."
               value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit" class="pixel-btn"><span class="material-icons">search</span></button>
    </form>

    <?php if ($searchTerm !== ''): ?>
        <?php if (empty($searchResults)): ?>
            <p style="font-size:9px;">No users found for "<?= htmlspecialchars($searchTerm) ?>".</p>
        <?php else: ?>
            <?php foreach ($searchResults as $result): ?>
                <div class="friend-result">
                    <img class="avatar-tiny" src="<?= htmlspecialchars($result['profile_photo']) ?>" alt="">
                    <span><?= htmlspecialchars($result['display_name']) ?> (@<?= htmlspecialchars($result['username']) ?>)</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
