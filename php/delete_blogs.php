<?php
// delete_post.php
include '../config.php';
session_start();

// Only allow logged-in admin or blogger
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'blogger'])) {
    http_response_code(403);
    echo "Unauthorized access.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $post_id = intval($_POST['post_id']);

    // Optional: Only allow deletion of own posts unless admin
    if ($_SESSION['role'] !== 'admin') {
        $user_id = $_SESSION['user_id'];
        $check_stmt = $conn->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $post_id, $user_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows === 0) {
            echo "You don't have permission to delete this post.";
            $check_stmt->close();
            $conn->close();
            exit();
        }
        $check_stmt->close();
    }

    // Delete post
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        echo "Post deleted successfully.";
    } else {
        echo "Error deleting post: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
