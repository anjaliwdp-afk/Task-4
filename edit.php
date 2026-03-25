<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$post_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

// Fetch post
$stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['error'] = "Post not found.";
    header('Location: dashboard.php');
    exit();
}

// Permission check
$can_edit = false;
if ($role === 'admin') {
    $can_edit = true;
} elseif ($role === 'editor') {
    $can_edit = true; // Editors can edit any post
} elseif ($post['user_id'] == $user_id) {
    $can_edit = true; // Owner can edit
}

if (!$can_edit) {
    $_SESSION['error'] = "You do not have permission to edit this post.";
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    // Validation
    if (empty($title)) {
        $error = "Title is required!";
    } elseif (strlen($title) > 200) {
        $error = "Title must not exceed 200 characters!";
    } elseif (empty($content)) {
        $error = "Content is required!";
    } elseif (strlen($content) < 10) {
        $error = "Content must be at least 10 characters!";
    } else {
        $update = $db->prepare("UPDATE posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ?");
        if ($update->execute([$title, $content, $post_id])) {
            $success = "Post updated successfully!";
            $post['title'] = $title;
            $post['content'] = $content;
        } else {
            $error = "Failed to update post.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Blog CRUD</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="index.php"><i class="fas fa-blog"></i> BlogCRUD</a>
            </div>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="create_post.php"><i class="fas fa-plus-circle"></i> Create Post</a>
                <?php if($role == 'admin'): ?>
                    <a href="admin.php"><i class="fas fa-crown"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="main-content" style="max-width: 800px; margin: 0 auto;">
            <h1 class="page-title"><i class="fas fa-edit"></i> Edit Post</h1>

            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Title</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($post['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="content"><i class="fas fa-file-alt"></i> Content</label>
                    <textarea id="content" name="content" class="form-control" rows="12" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Post</button>
                    <a href="dashboard.php" class="btn" style="background: #6c757d; color: white;"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 BlogCRUD. All rights reserved. | Developed for ApexPlanet</p>
    </footer>
</body>
</html>