<?php
session_start();
require 'database.php'; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $short_desc = $_POST['short_desc'];
    $long_desc = $_POST['long_desc'];
    $priority = $_POST['priority'];
    $project = $_POST['project'];
    $per_id = $_SESSION['user_id']; // Get user ID from session

    $query = "INSERT INTO iss_issues (short_desc, long_desc, open_date, priority, project, per_id, status) 
              VALUES (?, ?, NOW(), ?, ?, ?, 'Open')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $short_desc, $long_desc, $priority, $project, $per_id);
    
    if ($stmt->execute()) {
        echo "Issue created successfully! <a href='issues_list.php'>View Issues</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Issue</title>
</head>
<body>
    <h2>Create a New Issue</h2>
    <form method="post">
        <label>Short Description:</label><br>
        <input type="text" name="short_desc" required><br><br>

        <label>Long Description:</label><br>
        <textarea name="long_desc" required></textarea><br><br>

        <label>Priority:</label><br>
        <select name="priority">
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
        </select><br><br>

        <label>Project:</label><br>
        <input type="text" name="project" required><br><br>

        <button type="submit">Submit Issue</button>
    </form>
</body>
</html>
