<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to create a post!";
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $author = trim($_POST['author']) ?: $_SESSION['username'];
    $user_id = $_SESSION['user_id'];
    
    // Server-side validation
    if (empty($title)) {
        $error = "Title is required!";
    } elseif (strlen($title) > 200) {
        $error = "Title must not exceed 200 characters!";
    } elseif (empty($content)) {
        $error = "Content is required!";
    } elseif (strlen($content) < 10) {
        $error = "Content must be at least 10 characters!";
    } else {
        try {
            $query = "INSERT INTO posts (user_id, title, content, author, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, NOW(), NOW())";
            $stmt = $db->prepare($query);
            if ($stmt->execute([$user_id, $title, $content, $author])) {
                $_SESSION['success'] = "Post created successfully!";
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Failed to create post.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Blog CRUD</title>
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
                <a href="create_post.php" class="active"><i class="fas fa-plus-circle"></i> Create Post</a>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="admin.php"><i class="fas fa-crown"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="main-content" style="max-width: 800px; margin: 0 auto;">
            <h1 class="page-title"><i class="fas fa-plus-circle"></i> Create New Post</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="create-post-form">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Title</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                           placeholder="Enter post title (max 200 chars)" required>
                </div>

                <div class="form-group">
                    <label for="author"><i class="fas fa-user"></i> Author (optional)</label>
                    <input type="text" id="author" name="author" class="form-control" 
                           value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : $_SESSION['username']; ?>" 
                           placeholder="Enter author name">
                </div>

                <div class="form-group">
                    <label for="content"><i class="fas fa-file-alt"></i> Content (min. 10 characters)</label>
                    <textarea id="content" name="content" class="form-control" 
                              placeholder="Write your post content here..." required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Publish Post
                    </button>
                    <a href="dashboard.php" class="btn" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 BlogCRUD. All rights reserved. | Developed for ApexPlanet</p>
    </footer>
</body>
</html>