<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment_id'])) {
    $comment_id = (int) $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];
    $is_admin = $_SESSION['user_admin'] === 'Yes';

    // Check who owns the comment
    $stmt = $conn->prepare("SELECT per_id FROM iss_comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $stmt->bind_result($per_id);
    $stmt->fetch();
    $stmt->close();

    // Allow delete if admin or owner
    if ($is_admin || $user_id == $per_id) {
        $del = $conn->prepare("DELETE FROM iss_comments WHERE id = ?");
        $del->bind_param("i", $comment_id);
        $del->execute();
        echo "success";
    } else {
        http_response_code(403);
        echo "Permission denied.";
    }
}
?>
