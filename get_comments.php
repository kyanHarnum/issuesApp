<?php
header('Content-Type: application/json');
require 'database.php';

if (!isset($_GET['issue_id'])) {
    echo json_encode(['error' => 'Issue ID not provided']);
    exit();
}

$issue_id = (int)$_GET['issue_id'];
$query = "SELECT c.*, CONCAT(p.fname, ' ', p.lname) as creator 
          FROM iss_comments c 
          JOIN iss_persons p ON c.per_id = p.id 
          WHERE c.iss_id = ? 
          ORDER BY c.posted_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $issue_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'creator' => $row['creator'],
        'posted_date' => date('M j, Y g:i a', strtotime($row['posted_date'])),
        'comment_text' => htmlspecialchars($row['long_comment'])
    ];
}

echo json_encode($comments);
$stmt->close();
$conn->close();
?>