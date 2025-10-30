<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
redirectIfNotAdmin();

// Fetch sessions, terms, and classes for dropdowns
$sessions = $pdo->query("SELECT * FROM academic_sessions")->fetchAll();
$terms = $pdo->query("SELECT * FROM terms")->fetchAll();
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $session_id = $_POST["session_id"];
    $term_id = $_POST["term_id"];
    $class_id = $_POST["class_id"];
    
    // Fetch all students in the selected class
    // Using prepared statement for safety, though variables are numeric here
    $stmt = $pdo->prepare("
        SELECT id, name, reg_number 
        FROM students 
        WHERE class_id = ?
    ");
    $stmt->execute([$class_id]);
    $students = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Class Results - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .result-sheet { page-break-after: always; margin-bottom: 30px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bulk Print Class Results</h1>
        <a href="dashboard.php" class="btn no-print">Back to Dashboard</a>

        <!-- Selection Form -->
        <form method="POST" class="no-print">
            <select name="session_id" required>
                <option value="">Select Session</option>
                <?php foreach ($sessions as $session): ?>
                    <option value="<?= $session['id'] ?>"><?= $session['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="term_id" required>
                <option value="">Select Term</option>
                <?php foreach ($terms as $term): ?>
                    <option value="<?= $term['id'] ?>"><?= $term['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= $class['id'] ?>"><?= $class['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Generate Results</button>
        </form>

        <!-- Display Results -->
        <?php if (isset($students)): ?>
            <h2>Results for <?= isset($classes[$class_id-1]) ? $classes[$class_id-1]['name'] : '' ?> (<?= isset($sessions[$session_id-1]) ? $sessions[$session_id-1]['name'] : '' ?>, <?= isset($terms[$term_id-1]) ? $terms[$term_id-1]['name'] : '' ?>)</h2>
            <button onclick="window.print()" class="btn no-print">Print All</button>

            <?php foreach ($students as $student): ?>
                <div class="result-sheet">
                    <!-- Reuse the printable result template from print_result.php -->
                    <?php
                    $student_id = $student['id'];
                    // Ensure print_result.php also uses the PDO connection from db.php
                    // Assuming print_result.php is in ../student/ and includes ../includes/db.php
                    include "../student/print_result.php"; // Reuse the student's printable view
                    ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
