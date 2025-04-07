<?php
session_start();
require 'database.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, fname, lname, pwd_hash, pwd_salt FROM iss_persons WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $fname, $lname, $pwd_hash, $pwd_salt);
            $stmt->fetch();
            
            if ($pwd_hash === md5($password . $pwd_salt)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $fname . ' ' . $lname;
                header("Location: issues_list.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - DSR</title>
    <link rel="stylesheet" href="project.css">
</head>
<body>
<div class="box">
    <h2>Login to DSR</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" action="login.php">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Login</button>
</div>

    </form>
</body>
</html>
