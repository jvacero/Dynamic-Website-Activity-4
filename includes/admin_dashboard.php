<?php
// admin_dashboard.php - admin-only panel: moderate all posts, view users,
// posts-per-user chart. Guarded by require_admin() throughout.

// Deletes any post by post_id, no ownership restriction (admin-only action).
function handle_admin_remove_post(mysqli $conn): void {
    $postId = (int)($_POST['post_id'] ?? 0);
    if ($postId <= 0) {
        return;
    }
    $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();
}

// Routes an "admin_action" POST. Must run before any HTML is echoed since it redirects.
function admin_handle_post(mysqli $conn): void {
    require_admin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['admin_action'] ?? '') === 'remove_post') {
        handle_admin_remove_post($conn);
        header('Location: index.php?view=admin');
        exit;
    }
}

// Pulls every user's public info (never password/email hashes) for the admin table.
function fetch_all_users(mysqli $conn): array {
    $result = $conn->query(
        "SELECT u.user_id, u.username, u.role, u.created_at, p.display_name
         FROM users u JOIN profiles p ON p.user_id = u.user_id
         ORDER BY u.created_at ASC"
    );
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Aggregates post counts per user for the Google Charts bar graph.
function fetch_post_counts_per_user(mysqli $conn): array {
    $result = $conn->query(
        "SELECT u.username, COUNT(p.post_id) AS post_count
         FROM users u LEFT JOIN posts p ON p.user_id = u.user_id
         GROUP BY u.user_id
         ORDER BY post_count DESC"
    );
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Pulls every post joined with author info, newest first (kept separate from
// dashboard.php's version so this file has no dependency on it).
function fetch_all_posts_for_admin(mysqli $conn): array {
    $result = $conn->query(
        "SELECT p.post_id, p.content, p.image, p.created_at, u.user_id, u.username, u.role
         FROM posts p JOIN users u ON u.user_id = p.user_id
         ORDER BY p.created_at DESC"
    );
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Outputs the chart, user table, and moderation feed as HTML.
function render_admin_dashboard(mysqli $conn): void {
    require_admin();
    $allUsers = fetch_all_users($conn);
    $postCounts = fetch_post_counts_per_user($conn);
    $allPostsAdmin = fetch_all_posts_for_admin($conn);
?>
<div class="admin-wrap">

    <div class="admin-section">
        <h2><span class="material-icons">bar_chart</span> Posts per User</h2>
        <div id="postsChart"></div>
    </div>

    <div class="admin-section">
        <h2><span class="material-icons">group</span> User List</h2>
        <table class="user-table">
            <tr><th>ID</th><th>Username</th><th>Display Name</th><th>Role</th><th>Joined</th></tr>
            <?php foreach ($allUsers as $u): ?>
                <tr>
                    <td><?= (int)$u['user_id'] ?></td>
                    <td>@<?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['display_name']) ?></td>
                    <td>
                        <span class="role-pill <?= $u['role'] ?>">
                            <?= htmlspecialchars(ucfirst($u['role'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars(date('M j, Y', strtotime($u['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="admin-section">
        <h2><span class="material-icons">forum</span> Moderate Posts</h2>
        <?php foreach ($allPostsAdmin as $post): ?>
            <div class="post-card">
                <div class="post-meta">
                    <span>@<?= htmlspecialchars($post['username']) ?></span>
                    <span><?= htmlspecialchars(date('M j, Y g:ia', strtotime($post['created_at']))) ?></span>
                </div>
                <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
                <?php if (!empty($post['image'])): ?>
                    <img class="post-image" src="<?= htmlspecialchars($post['image']) ?>" alt="Post image">
                <?php endif; ?>

                <form method="POST" action="index.php?view=admin" class="post-actions">
                    <input type="hidden" name="admin_action" value="remove_post">
                    <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                    <button type="submit" class="pixel-btn danger" onclick="return confirm('Remove this post as admin?');">
                        <span class="material-icons">delete_forever</span> Remove
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', { packages: ['corechart', 'bar'] });
google.charts.setOnLoadCallback(drawPostsChart);

function drawPostsChart() {
    const rawData = <?= json_encode($postCounts) ?>;

    const dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'User');
    dataTable.addColumn('number', 'Posts');
    rawData.forEach(row => dataTable.addRow([row.username, parseInt(row.post_count, 10)]));

    const options = {
        colors: ['#0075D3'],
        backgroundColor: '#FFFFFF',
        legend: { position: 'none' },
        chartArea: { width: '80%', height: '70%' }
    };

    const chart = new google.visualization.ColumnChart(document.getElementById('postsChart'));
    chart.draw(dataTable, options);
}
</script>
<?php
}
