<?php
// dashboard.php - main post feed: create/edit/delete own posts, list all posts.
// REQUIRES: $conn (mysqli), session started, auth.php loaded.

function handle_new_post(mysqli $conn, string $uploadDir): void {
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        return;
    }

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $imagePath = save_uploaded_image($_FILES['image'], $uploadDir);
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['user_id'], $content, $imagePath);
    $stmt->execute();
    $stmt->close();
}

// Validates and moves an uploaded image into assets/uploads, returning the relative
// path to store in the DB. Renames to a random filename to avoid collisions/attacks.
function save_uploaded_image(array $file, string $uploadDir): ?string {
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    $mime = mime_content_type($file['tmp_name']);

    if (!isset($allowedTypes[$mime]) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $allowedTypes[$mime];
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

    return 'assets/uploads/' . $filename;
}

// Updates a post's content, only if the current user owns it (enforced in the WHERE clause).
function handle_edit_post(mysqli $conn): void {
    $postId  = (int)($_POST['post_id'] ?? 0);
    $content = trim($_POST['edited_content'] ?? '');
    if ($postId <= 0 || $content === '') {
        return;
    }
    $stmt = $conn->prepare("UPDATE posts SET content = ? WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("sii", $content, $postId, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Deletes a post, only if the current user owns it.
function handle_delete_post(mysqli $conn): void {
    $postId = (int)($_POST['post_id'] ?? 0);
    if ($postId <= 0) {
        return;
    }
    $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Routes a "dashboard_action" POST (create/edit/delete) to its handler.
// Must run before any HTML is echoed since it issues a header() redirect.
function dashboard_handle_post(mysqli $conn, string $uploadDir): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['dashboard_action'])) {
        return;
    }
    switch ($_POST['dashboard_action']) {
        case 'create': handle_new_post($conn, $uploadDir); break;
        case 'edit':   handle_edit_post($conn); break;
        case 'delete': handle_delete_post($conn); break;
    }
    header('Location: index.php');
    exit;
}

// Pulls every post joined with its author's info, newest first.
function fetch_all_posts(mysqli $conn): array {
    $result = $conn->query(
        "SELECT p.post_id, p.content, p.image, p.created_at, u.user_id, u.username, u.role
         FROM posts p JOIN users u ON u.user_id = p.user_id
         ORDER BY p.created_at DESC"
    );
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Outputs the post composer + full feed as HTML.
function render_dashboard(mysqli $conn): void {
$allPosts = fetch_all_posts($conn);
?>
<div class="dashboard-wrap">

    <form class="post-form" method="POST" action="index.php" enctype="multipart/form-data">
        <input type="hidden" name="dashboard_action" value="create">
        <textarea name="content" placeholder="what's on your mind?" required></textarea>
        <input type="file" name="image" accept="image/png, image/jpeg, image/gif">
        <button type="submit" class="pixel-btn"><span class="material-icons">send</span> Post</button>
    </form>

    <?php foreach ($allPosts as $post): ?>
        <div class="post-card">
            <div class="post-meta">
                <span>
                    @<?= htmlspecialchars($post['username']) ?>
                    <?php if ($post['role'] === 'admin'): ?>
                        <span class="material-icons" style="font-size:12px;color:#1DA1F2;">verified</span>
                    <?php endif; ?>
                </span>
                <span><?= htmlspecialchars(date('M j, Y g:ia', strtotime($post['created_at']))) ?></span>
            </div>

            <div class="post-content" id="postContent<?= $post['post_id'] ?>">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>

            <?php if (!empty($post['image'])): ?>
                <img class="post-image" src="<?= htmlspecialchars($post['image']) ?>" alt="Post image">
            <?php endif; ?>

            <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                <div class="post-actions">
                    <button type="button" class="pixel-btn" onclick="toggleEdit(<?= $post['post_id'] ?>)">
                        <span class="material-icons">edit</span> Edit
                    </button>

                    <form method="POST" action="index.php" style="display:inline;">
                        <input type="hidden" name="dashboard_action" value="delete">
                        <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                        <button type="submit" class="pixel-btn danger" onclick="return confirm('Delete this post?');">
                            <span class="material-icons">delete</span> Delete
                        </button>
                    </form>
                </div>

                <form method="POST" action="index.php" id="editForm<?= $post['post_id'] ?>" style="display:none; margin-top:8px;">
                    <input type="hidden" name="dashboard_action" value="edit">
                    <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                    <textarea name="edited_content"><?= htmlspecialchars($post['content']) ?></textarea>
                    <button type="submit" class="pixel-btn"><span class="material-icons">save</span> Save</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
function toggleEdit(postId) {
    const form = document.getElementById('editForm' + postId);
    form.style.display = (form.style.display === 'none') ? 'block' : 'none';
}
</script>
<?php
}
