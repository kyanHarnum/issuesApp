<?php
require 'admin_check.php';
require 'database.php';

// Setup default values
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'lname';
$order = ($_GET['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Count total for pagination
$countQuery = $conn->prepare("SELECT COUNT(*) FROM iss_persons WHERE fname LIKE CONCAT('%', ?, '%') OR lname LIKE CONCAT('%', ?, '%')");
$countQuery->bind_param("ss", $search, $search);
$countQuery->execute();
$countQuery->bind_result($total);
$countQuery->fetch();
$countQuery->close();

$totalPages = ceil($total / $limit);

// Fetch filtered + sorted users
$query = "
    SELECT id, fname, lname, email, mobile, admin 
    FROM iss_persons 
    WHERE fname LIKE CONCAT('%', ?, '%') OR lname LIKE CONCAT('%', ?, '%') 
    ORDER BY $sort $order 
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $search, $search, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="issues_list.css">
    <style>
        .search-bar { margin-bottom: 15px; }
        .pagination { margin-top: 15px; }
        .pagination a {
            padding: 5px 10px;
            margin: 2px;
            border: 1px solid #ccc;
            text-decoration: none;
        }
        .pagination .current {
            font-weight: bold;
            background-color: #ddd;
        }
        th a { color: inherit; text-decoration: none; }
    </style>
</head>
<body>
    <h2>Manage Users</h2>
    <a href="issues_list.php">← Back to Issues</a>

    <!-- Search form -->
    <form method="get" class="search-bar">
        <input type="text" name="search" placeholder="Filter by name..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <!-- User table -->
    <table>
        <tr>
            <?php
            $headers = ['fname' => 'First Name', 'lname' => 'Last Name', 'email' => 'Email', 'mobile' => 'Mobile', 'admin' => 'Admin'];
            foreach ($headers as $col => $label) {
                $newOrder = ($sort === $col && $order === 'asc') ? 'desc' : 'asc';
                echo "<th><a href='?sort=$col&order=$newOrder&search=" . urlencode($search) . "'>$label</a></th>";
            }
            ?>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['fname']) ?></td>
            <td><?= htmlspecialchars($row['lname']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['mobile']) ?></td>
            <td><?= htmlspecialchars($row['admin']) ?></td>
            <td>
                <a class="edit-btn" href="edit_user.php?id=<?= $row['id'] ?>">Edit</a>
                <form method="post" action="delete_user.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button class="delete-btn" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
</body>
</html>
