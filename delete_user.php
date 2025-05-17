<?php
require 'admin_check.php';
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    // Prevent deleting self (optional safety)
    if ($id === $_SESSION['user_id']) {
        die("You can't delete your own account.");
    }

    $stmt = $conn->prepare("DELETE FROM iss_persons WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: manage_users.php");
exit();
