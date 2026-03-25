<?php
session_start();
require_once 'database.php';

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$post_id = $_GET['id'];

$query = "SELECT p.*, u.username as author_name, u.role as author_role 
          FROM posts p 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $post_id);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$post) {
    $_SESSION['error'] = "Post not found!";
    header("Location: index.php");
    exit();
}

// Permissions for buttons
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'user';
$is_owner = ($user_id && $post['user_id'] == $user_id);
$can_edit = ($role == 'admin' || $role == 'editor' || $is_owner);
$can_delete = ($role == 'admin' || $is_owner);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Blog CRUD</title>
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="create_post.php"><i class="fas fa-plus-circle"></i> Create Post</a>
                    <?php if($role == 'admin'): ?>
                        <a href="admin.php"><i class="fas fa-crown"></i> Admin</a>
                    <?php endif; ?>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="main-content">
            <article style="max-width: 800px; margin: 0 auto;">
                <h1 class="page-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="post-meta" style="margin-bottom: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <p><i class="fas fa-user"></i> Author: <?php echo htmlspecialchars($post['author_name'] ?? $post['author'] ?? 'Anonymous'); ?></p>
                    <p><i class="fas fa-calendar"></i> Created: <?php echo date('F j, Y \a\t g:i a', strtotime($post['created_at'])); ?></p>
                    <?php if($post['created_at'] != $post['updated_at']): ?>
                        <p><i class="fas fa-clock"></i> Last updated: <?php echo date('F j, Y \a\t g:i a', strtotime($post['updated_at'])); ?></p>
                    <?php endif; ?>
                </div>

                <div class="post-content-full" style="font-size: 1.1rem; line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <div style="margin-top: 40px; display: flex; gap: 15px;">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Posts
                    </a>
                    
                    <?php if(isset($_SESSION['user_id']) && $can_edit): ?>
                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Post
                        </a>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['user_id']) && $can_delete): ?>
                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this post?')">
                            <i class="fas fa-trash"></i> Delete Post
                        </a>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 BlogCRUD. All rights reserved. | Developed for ApexPlanet</p>
    </footer>
</body>
</html>