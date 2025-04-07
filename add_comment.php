<?php
header('Content-Type: application/json');
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['issue_id']) || !isset($_POST['comment_text'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$issue_id = (int)$_POST['issue_id'];
$comment_text = trim($_POST['comment_text']);
$user_id = $_SESSION['user_id'];

if (empty($comment_text)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit();
}

// Use the comment text for both short and long comment (since we removed the summary)
$query = "INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) 
          VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiss", $user_id, $issue_id, $comment_text, $comment_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>