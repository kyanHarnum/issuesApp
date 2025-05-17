<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'], $_POST['comment_text'])) {
    $comment_id = (int) $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];
    $comment_text = trim($_POST['comment_text']);

    // Check if user is the comment creator
    $stmt = $conn->prepare("SELECT per_id FROM iss_comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $stmt->bind_result($per_id);
    $stmt->fetch();
    $stmt->close();

    if ($per_id != $user_id) {
        http_response_code(403);
        echo "Permission denied.";
        exit();
    }

    // Update the comment
    $stmt = $conn->prepare("UPDATE iss_comments SET long_comment = ? WHERE id = ?");
    $stmt->bind_param("si", $comment_text, $comment_id);
    $stmt->execute();

    echo "success";
    $stmt->close();
}
?>
