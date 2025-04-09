<?php
require_once 'database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $fname = trim($_POST['fname']?? '');
    $lname = trim($_POST['lname']?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile']?? '');
    $password = trim($_POST['password'] ?? '');


    if($fname && $lname && $email && $mobile && $password){
        $checkStmt = $conn ->prepare("SELECT id FROM iss_persons WHERE email = ?");
        $checkStmt ->bind_param("s",$email);
        $checkStmt->execute();
        $checkStmt->store_result();


        if($checkStmt->num_rows > 0){
            $error = "An account with that email already exists";
        } else {
            $salt = bin2hex(random_bytes(8));
            $hashedPassword = md5($password . $salt);
            $insertStmt = $conn->prepare("INSERT INTO iss_persons (fname, lname, email, mobile, pwd_salt, pwd_hash) VALUES (?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("ssssss", $fname,$lname,$email,$mobile, $salt, $hashedPassword);

            if($insertStmt->execute()){
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $insertStmt->error;
            }

            $insertStmt->close();
        }

        $checkStmt->close();
    } else {
        $error = "Please fill in all fields";
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
<div class="box">
    <h2>Register</h2>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>First Name</label>
        <input type="text" name="fname" required><br><br>

        <label>Last Name</label>
        <input type="text" name="lname" required><br><br>

        <label>Email</label>
        <input type="email" name="email" required><br><br>

        <label>Phone Number</label>
        <input type="text" name="mobile" required><br><br>

        <label>Password</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>


