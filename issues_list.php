<?php
session_start();
require 'database.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// If "Resolve" button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resolve_id'])) {
    $issue_id = $_POST['resolve_id'];
    $query = "UPDATE iss_issues SET status = 'Resolved', close_date = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $stmt->close();
}


// Fetch issues from the database
$query = "SELECT id, short_desc, long_desc, open_date, close_date, priority, org, project, per_id FROM iss_issues ORDER BY project";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issues List - DSR</title>
</head>
<body>
    <h2>Department Status Report - Issues List</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! <a href="login.php">Logout</a></p>
    <a href="create_issues.php">Create New Issue</a><br><br>
    <table border="1">
        <tr>
            <th>Project Name</th>
            <th>Issue Description</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['project']); ?></td>
                <td><?php echo htmlspecialchars($row['short_desc']); ?></td>
                <td><?php echo htmlspecialchars($row['priority']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>

                    <?php if ($row['status'] != 'Resolved') { ?>
                        <form method="post">
                            <input type="hidden" name="resolve_id" value="<?php echo $row['id']; ?>">
                            <button type="submit">Resolve</button>
                        </form>
                    <?php } else {
                        echo "Resolved on " . $row['close_date'];
                    } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
