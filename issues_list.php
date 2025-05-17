<?php
session_start();
$currentUserId = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_admin'] === 'Yes');
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

    <?php if ($_SESSION['user_admin'] === 'Yes'): ?>
    <a href="manage_users.php" class="btn-manage-users">Manage Users</a>
<?php endif; ?>


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
                        '<?php echo $row['close_date'] ? htmlspecialchars($row['close_date']) : 'Not resolved yet'; ?>',
                        '<?php echo $row['id']; ?>'
                    )">Read</button>

                    <?php if ($is_admin || $row['per_id'] == $_SESSION['user_id']): ?>
                        <a href="edit_issue.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn">Edit</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this issue?')">Delete</button>
                        </form>

                    <?php endif; ?>

                 <!--   <button class="action-btn comment-btn" onclick="showCommentForm(<?php echo $row['id']; ?>)">Comment</button> -->

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

            <!-- Comments Section -->
        <div class="comments-section">
            <h4>Comments</h4>
            <div id="commentsContainer"></div>

            <form id="commentForm" onsubmit="return submitComment(event)">
                <input type="hidden" id="commentIssueId" name="issue_id">
                <div class="form-group">
                    <textarea id="commentText" name="comment_text" placeholder="Add a comment..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
            </div>
        </div>
    </div>

<script>
    
let currentIssueId = null;
const modal = document.getElementById("issueModal");
const currentUserId = <?= $_SESSION['user_id']; ?>;
const isAdmin = <?= ($_SESSION['user_admin'] === 'Yes') ? 'true' : 'false'; ?>;

// Show issue details in modal
function showIssueDetails(shortDesc, longDesc, project, priority, creator, openDate, status, closeDate, issueId) {
    document.getElementById("modalShortDesc").innerText = shortDesc;
    document.getElementById("modalLongDesc").innerText = longDesc;
    document.getElementById("modalProject").innerText = project;
    document.getElementById("modalPriority").innerText = priority;
    document.getElementById("modalCreator").innerText = creator;
    document.getElementById("modalOpenDate").innerText = openDate;
    document.getElementById("modalStatus").innerText = status;
    document.getElementById("modalCloseDate").innerText = closeDate;
    document.getElementById("commentIssueId").value = issueId;

    currentIssueId = issueId;
    loadComments(issueId);

    modal.style.display = "block";
}

// Load comments for an issue
function loadComments(issueId) {
    fetch('get_comments.php?issue_id=' + issueId)
        .then(response => response.json())
        .then(comments => {
            const container = document.getElementById('commentsContainer');
            container.innerHTML = '';

            if (comments.length === 0) {
                container.innerHTML = '<p>No comments yet. Be the first to comment!</p>';
                return;
            }

            comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';

                let deleteBtn = '';
                if (isAdmin || comment.per_id == currentUserId) {
                    deleteBtn = `<button class="delete-btn" onclick="deleteComment(${comment.id})">Delete</button>`;
                }

                let editBtn = '';
                if (comment.per_id == currentUserId) {
                editBtn = `<button class="edit-btn" onclick="editComment(${comment.id}, \`${comment.comment_text.replace(/`/g, '\\`')}\`)">Edit</button>`;
                }

                commentDiv.innerHTML = `
                    <div class="comment-header">
                        <strong>${comment.creator}</strong> 
                        <span class="comment-date">${comment.posted_date}</span>
                    </div>
                    <div class="comment-text">${comment.comment_text}</div>
                    ${deleteBtn} ${editBtn}
                `;

                container.appendChild(commentDiv);
            });
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            document.getElementById('commentsContainer').innerHTML = '<p style="color:red;">Failed to load comments.</p>';
        });
}

// Submit a new comment
function submitComment(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    fetch('add_comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            form.reset();
            loadComments(currentIssueId);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while posting the comment.');
    });

    return false;
}

// Delete a comment
function deleteComment(commentId) {
    if (!confirm("Are you sure you want to delete this comment?")) return;

    const formData = new FormData();
    formData.append('comment_id', commentId);

    fetch('delete_comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        if (result === 'success') {
            loadComments(currentIssueId);
        } else {
            alert("Failed to delete comment: " + result);
        }
    });
}

function editComment(commentId, currentText) {
    const newText = prompt("Edit your comment:", currentText);
    if (newText === null || newText.trim() === "") return;

    const formData = new FormData();
    formData.append('comment_id', commentId);
    formData.append('comment_text', newText);

    fetch('edit_comment.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(result => {
        if (result === 'success') {
            loadComments(currentIssueId);
        } else {
            alert("Failed to edit comment: " + result);
        }
    });
}



function closeModal() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target === modal) {
        closeModal();
    }
}
</script>
</body>
</html>