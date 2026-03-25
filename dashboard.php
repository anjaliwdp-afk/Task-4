<?php
session_start();
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$role = $_SESSION['role'] ?? 'user';
$user_id = $_SESSION['user_id'];

// Pagination settings
$posts_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $posts_per_page;

// Search functionality
$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
$search_params = [];

if (!empty($search_query)) {
    $search_condition = "AND (title LIKE :search OR content LIKE :search)";
    $search_params[':search'] = '%' . $search_query . '%';
}

// If admin, show all posts; otherwise only user's own
if ($role === 'admin') {
    $count_query = "SELECT COUNT(*) as total FROM posts WHERE 1=1 $search_condition";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute($search_params);
    $total_posts = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $query = "SELECT * FROM posts WHERE 1=1 $search_condition ORDER BY created_at DESC LIMIT :offset, :posts_per_page";
    $stmt = $db->prepare($query);
} else {
    $count_query = "SELECT COUNT(*) as total FROM posts WHERE user_id = :user_id $search_condition";
    $count_stmt = $db->prepare($count_query);
    $count_params = array_merge([':user_id' => $user_id], $search_params);
    $count_stmt->execute($count_params);
    $total_posts = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $query = "SELECT * FROM posts WHERE user_id = :user_id $search_condition ORDER BY created_at DESC LIMIT :offset, :posts_per_page";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}

if (!empty($search_query)) {
    $stmt->bindValue(':search', $search_params[':search'], PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':posts_per_page', $posts_per_page, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_pages = ceil($total_posts / $posts_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Blog CRUD</title>
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
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="create_post.php"><i class="fas fa-plus-circle"></i> Create Post</a>
                <?php if($role == 'admin'): ?>
                    <a href="admin.php"><i class="fas fa-crown"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="main-content">
            <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> My Dashboard 
                <?php if($role == 'admin'): ?>
                    <span style="font-size: 0.8rem; background: gold; padding: 0.2rem 0.5rem; border-radius: 20px;">Admin</span>
                <?php endif; ?>
            </h1>
            
            <div class="search-section">
                <form action="dashboard.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search your posts..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>

            <?php if(!empty($search_query)): ?>
                <div class="search-info">
                    <span><i class="fas fa-info-circle"></i> Found <?php echo $total_posts; ?> result(s) in your posts for "<?php echo htmlspecialchars($search_query); ?>"</span>
                    <a href="dashboard.php" class="clear-search"><i class="fas fa-times"></i> Clear Search</a>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if(count($posts) > 0): ?>
                <div class="posts-grid">
                    <?php foreach($posts as $post): ?>
                        <article class="post-card">
                            <div class="post-header">
                                <h3><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($post['title']); ?></h3>
                            </div>
                            <div class="post-meta">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author'] ?? 'Anonymous'); ?><br>
                                <i class="fas fa-calendar"></i> Created: <?php echo date('F j, Y', strtotime($post['created_at'])); ?><br>
                                <i class="fas fa-clock"></i> Last updated: <?php echo date('F j, Y', strtotime($post['updated_at'])); ?>
                            </div>
                            <div class="post-content">
                                <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 150))); ?>...</p>
                            </div>
                            <div class="post-footer">
                                <a href="posts.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_post.php?id=<?php echo $post['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this post?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if($total_pages > 1): ?>
                    <div class="pagination">
                        <?php echo getPaginationLinks($current_page, $total_pages, "dashboard.php", $search_query); ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <?php echo !empty($search_query) ? 'No posts found matching your search.' : 'You haven\'t created any posts yet.'; ?>
                </div>
                <?php if(empty($search_query)): ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="create_post.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Your First Post
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 BlogCRUD. All rights reserved. | Developed for ApexPlanet</p>
    </footer>
</body>
</html>