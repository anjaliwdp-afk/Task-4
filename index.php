<?php
session_start();
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination settings
$posts_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $posts_per_page;

// Search functionality
$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
$search_params = [];

if (!empty($search_query)) {
    $search_condition = "WHERE title LIKE :search OR content LIKE :search";
    $search_params[':search'] = '%' . $search_query . '%';
}

// Get total posts count
$count_query = "SELECT COUNT(*) as total FROM posts $search_condition";
$count_stmt = $db->prepare($count_query);
if (!empty($search_params)) {
    $count_stmt->execute($search_params);
} else {
    $count_stmt->execute();
}
$total_posts = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Get posts for current page
$query = "SELECT * FROM posts $search_condition ORDER BY created_at DESC LIMIT :offset, :posts_per_page";
$stmt = $db->prepare($query);

if (!empty($search_params)) {
    $stmt->bindValue(':search', $search_params[':search'], PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':posts_per_page', $posts_per_page, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog App - Share Your Thoughts</title>
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
                <a href="index.php" class="active"><i class="fas fa-home"></i> Home</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="create_post.php"><i class="fas fa-plus-circle"></i> Create Post</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Welcome to BlogApp</h1>
                <p>Share your thoughts with the world!</p>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn-hero">Get Started <i class="fas fa-arrow-right"></i></a>
                <?php else: ?>
                    <a href="create_post.php" class="btn-hero">Write a Post <i class="fas fa-pen-fancy"></i></a>
                <?php endif; ?>
            </div>
        </section>

        <div class="container">
            <div class="main-content">
                <h2 class="section-title"><i class="fas fa-newspaper"></i> Latest Posts</h2>

                <!-- Search Section -->
                <div class="search-section">
                    <form action="index.php" method="GET" class="search-form">
                        <input type="text" name="search" placeholder="Search posts by title or content..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>

                <?php if(!empty($search_query)): ?>
                    <div class="search-info">
                        <span><i class="fas fa-info-circle"></i> Found <?php echo $total_posts; ?> result(s) for "<?php echo htmlspecialchars($search_query); ?>"</span>
                        <a href="index.php" class="clear-search"><i class="fas fa-times"></i> Clear Search</a>
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
                                    <i class="fas fa-user"></i> <?php echo isset($post['author']) ? htmlspecialchars($post['author']) : 'Anonymous'; ?> |
                                    <i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                </div>
                                <div class="post-content">
                                    <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 150))); ?>...</p>
                                </div>
                                <div class="post-footer">
                                    <a href="posts.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Read More <i class="fas fa-arrow-right"></i></a>
                                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if($total_pages > 1): ?>
                        <div class="pagination">
                            <?php echo getPaginationLinks($current_page, $total_pages, "index.php", $search_query); ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <?php echo !empty($search_query) ? 'No posts found matching your search.' : 'No posts yet. Be the first to create one!'; ?>
                    </div>
                    <?php if(empty($search_query) && isset($_SESSION['user_id'])): ?>
                        <div class="text-center">
                            <a href="create_post.php" class="btn btn-primary">Create Your First Post</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> BlogApp. All rights reserved. | Share your thoughts with the world!</p>
    </footer>
</body>
</html>