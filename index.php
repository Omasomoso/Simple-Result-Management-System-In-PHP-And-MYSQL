<?php
session_start();
// Include the database connection
require_once "includes/db.php"; // Adjust the path if necessary

// Redirect logged-in users to their dashboards
if (isset($_SESSION["admin_id"])) {
    header("Location: admin/dashboard.php");
    exit();
} elseif (isset($_SESSION["student_id"])) {
    header("Location: student/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avalon School Result Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="text-center">
            <h1>Welcome To Avalon School Result Management System</h1>
            <p>Manage and view academic results with ease.</p>
        </header>

        <main class="flex-container">
            <!-- Admin Login Card -->
            <div class="card">
                <h2>Admin Portal</h2>
                <p>Upload results, manage students, and generate reports.</p>
                <a href="admin/login.php" class="btn">Admin Login</a>
            </div>

            <!-- Student Login Card -->
            <div class="card">
                <h2>Student Portal</h2>
                <p>View your results and print score sheets.</p>
                <a href="student/login.php" class="btn">Student Login</a>
            </div>
        </main>

        <footer class="text-center mt-20">
            <p>&copy; <?= date('Y') ?> Avalon Group of Schools. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
