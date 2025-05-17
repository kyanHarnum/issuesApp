<?php
require 'admin_check.php';
require 'database.php';

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$id = (int) $_GET['id'];

// On submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $admin = $_POST['admin'];
    $new_password = trim($_POST['new_password']);

    if ($new_password !== "") {
        $salt = bin2hex(random_bytes(8));
        $pwd_hash = md5($new_password . $salt);
        $stmt = $conn->prepare("UPDATE iss_persons SET fname=?, lname=?, mobile=?, email=?, admin=?, pwd_salt=?, pwd_hash=? WHERE id=?");
        $stmt->bind_param("sssssssi", $fname, $lname, $mobile, $email, $admin, $salt, $pwd_hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE iss_persons SET fname=?, lname=?, mobile=?, email=?, admin=? WHERE id=?");
        $stmt->bind_param("sssssi", $fname, $lname, $mobile, $email, $admin, $id);
    }

    if ($stmt->execute()) {
        header("Location: manage_users.php");
        exit();
    } else {
        $error = "Error updating user.";
    }
}

// Load user
$stmt = $conn->prepare("SELECT * FROM iss_persons WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
<h2>Edit User</h2>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <label>First Name:</label><input type="text" name="fname" value="<?= htmlspecialchars($user['fname']) ?>"><br><br>
    <label>Last Name:</label><input type="text" name="lname" value="<?= htmlspecialchars($user['lname']) ?>"><br><br>
    <label>Email:</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><br><br>
    <label>Phone:</label><input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>"><br><br>
    <label>Admin:</label>
    <select name="admin">
        <option value="Yes" <?= $user['admin'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
        <option value="No" <?= $user['admin'] === 'No' ? 'selected' : '' ?>>No</option>
    </select><br><br>
    <label>New Password:</label><input type="password" name="new_password" placeholder="Leave blank to keep current"><br><br>
    <button type="submit">Update</button>
</form>
</body>
</html>
