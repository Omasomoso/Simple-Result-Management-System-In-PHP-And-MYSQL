<?php
// Database connection
require_once __DIR__ . '/../includes/db.php'; // Include the PDO connection

$message = "";

// Handle Academic Session Submission
if (isset($_POST['add_session'])) {
    $name = trim($_POST['session']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO academic_sessions (name, start_date, end_date) VALUES (?, ?, ?)");
            $stmt->execute([$name, $start_date, $end_date]);
            $message .= "<p style='color: green;'>Academic session '$name' added successfully.</p>";
        } catch (PDOException $e) {
            $message .= "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}

// Handle Class Submission
if (isset($_POST['add_class'])) {
    $name = strtoupper(trim($_POST['class']));
    if (!empty($name)) {
        try {
            // Check if class already exists
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE name = ?");
            $stmt_check->execute([$name]);
            if ($stmt_check->fetchColumn() == 0) {
                // Insert new class
                $stmt_insert = $pdo->prepare("INSERT INTO classes (name) VALUES (?)");
                $stmt_insert->execute([$name]);
                $message .= "<p style='color: green;'>Class '$name' added successfully.</p>";
            } else {
                $message .= "<p style='color: orange;'>Class '$name' already exists.</p>";
            }
        } catch (PDOException $e) {
            $message .= "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}

// mysqli_close($conn); // No longer needed as PDO manages connection automatically
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Add Session & Class</title>
	    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Session And Class</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    <!-- Display feedback messages -->
    <?php if (!empty($message)) echo "<div class='card mb-20'>$message</div>"; ?>

    <!-- Add Academic Session -->
    <div class="section card">
        <h2>Add Academic Session</h2>
        <form method="post">
            <input type="text" name="session" placeholder="e.g. 2024/2025" required>
            <input type="date" name="start_date" required>
            <input type="date" name="end_date" required>
            <button type="submit" name="add_session">Add Session</button>
        </form>
    </div>

    <!-- Add New Class -->
    <div class="section card">
        <h2>Add New Class</h2>
        <form method="post">
            <input type="text" name="class" placeholder="e.g. JSS3 or SS3" required>
            <button type="submit" name="add_class">Add Class</button>
        </form>
    </div>
</div>
</body>
</html>
