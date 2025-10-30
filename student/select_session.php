<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
redirectIfNotStudent();

$student_id = $_SESSION['student_id'];

// Fetch available sessions and terms where the student has results
$sessions = $pdo->query("
    SELECT DISTINCT s.id, s.name 
    FROM academic_sessions s
    JOIN results r ON s.id = r.session_id
    WHERE r.student_id = $student_id
    ORDER BY s.name DESC
")->fetchAll();

$terms = $pdo->query("
    SELECT DISTINCT t.id, t.name 
    FROM terms t
    JOIN results r ON t.id = r.term_id
    WHERE r.student_id = $student_id
    ORDER BY t.id
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Session & Term</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>View Your Results</h1>
        <form action="dashboard.php" method="GET">
            <?php if (empty($sessions)): ?>
                <p class="alert">No results found for your account.</p>
            <?php else: ?>
                <div class="form-group">
                    <label>Academic Session:</label>
                    <select name="session_id" required>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= $session['id'] ?>"><?= $session['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Term:</label>
                    <select name="term_id" required>
                        <?php foreach ($terms as $term): ?>
                            <option value="<?= $term['id'] ?>"><?= $term['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">View Results</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>