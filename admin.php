<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

// Get all posts with author info
$stmt = $db->query("
    SELECT p.*, u.username as author_name 
    FROM posts p 
    LEFT JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");
$all_posts = $stmt->fetchAll();

// Statistics
$total_posts = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$user_stats = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - BlogApp</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="index.php"><i class="fas fa-blog"></i> BlogApp</a>
            </div>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="create_post.php"><i class="fas fa-plus-circle"></i> Create Post</a>
                <a href="admin.php" class="active"><i class="fas fa-crown"></i> Admin</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="main-content">
            <h1 class="page-title"><i class="fas fa-crown"></i> Admin Panel</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Posts</h3>
                    <p><?php echo $total_posts; ?></p>
                </div>
                <?php foreach($user_stats as $stat): ?>
                    <div class="stat-card">
                        <h3><?php echo ucfirst($stat['role']); ?>s</h3>
                        <p><?php echo $stat['count']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 class="section-title">All Posts</h2>
            <div class="posts-grid">
                <?php foreach($all_posts as $post): ?>
                    <article class="post-card">
                        <div class="post-header">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        </div>
                        <div class="post-meta">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author_name'] ?? $post['author'] ?? 'Anonymous'); ?>
                            <br>
                            <i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                        </div>
                        <div class="post-footer">
                            <a href="posts.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">View</a>
                            <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this post?')">Delete</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> BlogApp. All rights reserved.</p>
    </footer>
</body>
</html>