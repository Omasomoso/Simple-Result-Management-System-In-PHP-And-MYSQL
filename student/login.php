<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_number = $_POST["reg_number"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE reg_number = ?");
    $stmt->execute([$reg_number]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student["password"])) {
        $_SESSION["student_id"] = $student["id"];
        header("Location: select_session.php");
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
	    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial; padding: 20px; }
        form { max-width: 400px; margin: 0 auto; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Student Login</h1>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="reg_number" placeholder="Registration Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>