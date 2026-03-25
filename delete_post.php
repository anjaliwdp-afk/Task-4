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

// Fetch post to check ownership
$stmt = $db->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['error'] = "Post not found.";
    header('Location: dashboard.php');
    exit();
}

// Permission check
$can_delete = false;
if ($role === 'admin') {
    $can_delete = true;
} elseif ($post['user_id'] == $user_id) {
    $can_delete = true;
}

if (!$can_delete) {
    $_SESSION['error'] = "You do not have permission to delete this post.";
    header('Location: dashboard.php');
    exit();
}

// Proceed to delete
$delete = $db->prepare("DELETE FROM posts WHERE id = ?");
$delete->execute([$post_id]);

$_SESSION['success'] = "Post deleted successfully.";
header('Location: dashboard.php');
exit();
?>