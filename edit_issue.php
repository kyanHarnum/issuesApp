<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $issue_id = $_POST['id'];
    $short_desc = $_POST['short_desc'];
    $long_desc = $_POST['long_desc'];
    $priority = $_POST['priority'];
    $project = $_POST['project'];
    $status = $_POST['status'];
    
    $query = "UPDATE iss_issues 
              SET short_desc = ?, long_desc = ?, priority = ?, project = ?, status = ?
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $short_desc, $long_desc, $priority, $project, $status, $issue_id);
    
    if ($stmt->execute()) {
        header("Location: issues_list.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    
    $stmt->close();
}

// Get issue data
if (!isset($_GET['id'])) {
    header("Location: issues_list.php");
    exit();
}

$issue_id = $_GET['id'];
$query = "SELECT * FROM iss_issues WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $issue_id);
$stmt->execute();
$result = $stmt->get_result();
$issue = $result->fetch_assoc();
$stmt->close();

if (!$issue) {
    header("Location: issues_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Issue - DSR</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea { height: 150px; }
        .button-group { margin-top: 20px; }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Issue</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo $issue['id']; ?>">
            
            <div class="form-group">
                <label for="short_desc">Short Description:</label>
                <input type="text" id="short_desc" name="short_desc" 
                       value="<?php echo htmlspecialchars($issue['short_desc']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="long_desc">Long Description:</label>
                <textarea id="long_desc" name="long_desc" required><?php 
                    echo htmlspecialchars($issue['long_desc']); 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="priority">Priority:</label>
                <select id="priority" name="priority">
                    <option value="Low" <?php echo ($issue['priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                    <option value="Medium" <?php echo ($issue['priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="High" <?php echo ($issue['priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="project">Project:</label>
                <input type="text" id="project" name="project" 
                       value="<?php echo htmlspecialchars($issue['project']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="Open" <?php echo ($issue['status'] == 'Open') ? 'selected' : ''; ?>>Open</option>
                    <option value="In Progress" <?php echo ($issue['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Resolved" <?php echo ($issue['status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn btn-primary">Update Issue</button>
                <a href="issues_list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>