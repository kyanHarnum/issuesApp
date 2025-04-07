<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $issue_id = $_POST['delete_id'];
    $query = "DELETE FROM iss_issues WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $stmt->close();
    
    // Refresh the page to show updated list
    header("Location: issues_list.php");
    exit();
}

// Fetch issues with creator names
$query = "SELECT i.*, p.fname, p.lname 
          FROM iss_issues i 
          JOIN iss_persons p ON i.per_id = p.id 
          ORDER BY i.project";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issues List - DSR</title>
    <link rel="stylesheet" href="issues_list.css">
</head>
<body>
    <h2>Department Status Report - Issues List</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! <a href="login.php">Logout</a></p>
    <a href="create_issues.php">Create New Issue</a><br><br>
    
    <table>
        <tr>
            <th>Project</th>
            <th>Short Description</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['project']); ?></td>
                <td><?php echo htmlspecialchars($row['short_desc']); ?></td>
                <td><?php echo htmlspecialchars($row['priority']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <button class="action-btn read-btn" onclick="showIssueDetails(
                        '<?php echo htmlspecialchars($row['short_desc']); ?>',
                        '<?php echo htmlspecialchars($row['long_desc']); ?>',
                        '<?php echo htmlspecialchars($row['project']); ?>',
                        '<?php echo htmlspecialchars($row['priority']); ?>',
                        '<?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?>',
                        '<?php echo htmlspecialchars($row['open_date']); ?>',
                        '<?php echo htmlspecialchars($row['status']); ?>',
                        '<?php echo $row['close_date'] ? htmlspecialchars($row['close_date']) : 'Not resolved yet'; ?>'
                    )">Read</button>
                    <a href="edit_issue.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn">Edit</a>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this issue?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- The Modal Popup -->
    <div id="issueModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalShortDesc"></h3>
            
            <div class="issue-detail">
                <span class="detail-label">Long Description:</span>
                <p id="modalLongDesc"></p>
            </div>
            
            <div class="issue-detail">
                <span class="detail-label">Project:</span>
                <span id="modalProject"></span>
            </div>
            
            <div class="issue-detail">
                <span class="detail-label">Priority:</span>
                <span id="modalPriority"></span>
            </div>
            
            <div class="issue-detail">
                <span class="detail-label">Created By:</span>
                <span id="modalCreator"></span>
            </div>
            
            <div class="issue-detail">
                <span class="detail-label">Open Date:</span>
                <span id="modalOpenDate"></span>
            </div>
            
            <div class="issue-detail">
                <span class="detail-label">Status:</span>
                <span id="modalStatus"></span>
            </div>
            
            <div class="issue-detail">
                <span class="detail-label">Close Date:</span>
                <span id="modalCloseDate"></span>
            </div>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("issueModal");

        // Function to show issue details in modal
        function showIssueDetails(shortDesc, longDesc, project, priority, creator, openDate, status, closeDate) {
            document.getElementById("modalShortDesc").innerText = shortDesc;
            document.getElementById("modalLongDesc").innerText = longDesc;
            document.getElementById("modalProject").innerText = project;
            document.getElementById("modalPriority").innerText = priority;
            document.getElementById("modalCreator").innerText = creator;
            document.getElementById("modalOpenDate").innerText = openDate;
            document.getElementById("modalStatus").innerText = status;
            document.getElementById("modalCloseDate").innerText = closeDate;
            
            modal.style.display = "block";
        }

        // Function to close the modal
        function closeModal() {
            modal.style.display = "none";
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>