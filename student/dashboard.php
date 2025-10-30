<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
redirectIfNotStudent();

$student_id = $_SESSION['student_id'];
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;
$term_id = isset($_GET['term_id']) ? $_GET['term_id'] : null;

// Redirect if no selection made
if (!$session_id || !$term_id) {
    header("Location: select_session.php");
    exit();
}

// Fetch student and result data
$student = $pdo->query("SELECT * FROM students WHERE id = $student_id")->fetch();
$results = $pdo->query("
    SELECT r.*, s.name AS subject_name 
    FROM results r 
    JOIN subjects s ON r.subject_id = s.id 
    WHERE r.student_id = $student_id
    AND r.session_id = $session_id
    AND r.term_id = $term_id
    AND (r.test1 IS NOT NULL OR r.test2 IS NOT NULL OR r.exam IS NOT NULL)
    ORDER BY s.name
")->fetchAll();

// Fetch only subjects with valid scores (ignore NULL, "", or "-")
$results = $pdo->query("
    SELECT r.*, s.name AS subject_name 
    FROM results r
    JOIN subjects s ON r.subject_id = s.id
    WHERE r.student_id = $student_id
    AND r.session_id = $session_id
    AND r.term_id = $term_id
    AND (
        (r.test1 IS NOT NULL AND r.test1 != '' AND r.test1 != '-') OR
        (r.test2 IS NOT NULL AND r.test2 != '' AND r.test2 != '-') OR
        (r.exam IS NOT NULL AND r.exam != '' AND r.exam != '-')
    )
")->fetchAll();

// Fetch session/term names
$session = $pdo->query("SELECT name FROM academic_sessions WHERE id = $session_id")->fetchColumn();
$term = $pdo->query("SELECT name FROM terms WHERE id = $term_id")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Results</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Your Results</h1>
        <a href="select_session.php" class="btn">Change Session/Term</a>
        
        <div class="result-header">
            <h2><?php echo htmlspecialchars($session); ?> - <?php echo htmlspecialchars($term); ?></h2>
            <p>Name: <?php echo htmlspecialchars($student['name']); ?></p>
            <p>Reg Number: <?php echo htmlspecialchars($student['reg_number']); ?></p>
        </div>

        <?php if (empty($results)): ?>
            <p class="alert">No results found for the selected session and term.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Test 1</th>
                        <th>Test 2</th>
                        <th>Exam</th>
                        <th>Total</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                            <td><?php echo isset($result['test1']) ? $result['test1'] : '-'; ?></td>
                            <td><?php echo isset($result['test2']) ? $result['test2'] : '-'; ?></td>
                            <td><?php echo isset($result['exam']) ? $result['exam'] : '-'; ?></td>
                            <td><?php echo isset($result['total']) ? $result['total'] : '-'; ?></td>
                            <td><?php echo isset($result['grade']) ? $result['grade'] : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
	<!-- In student/dashboard.php -->
<a href="print_result.php?session_id=<?= $session_id ?>&term_id=<?= $term_id ?>" class="btn no-print">
    Print Result
</a>
<!-- logout session -->
<a href="logout.php?session_id=<?= $session_id ?>&term_id=<?= $term_id ?>" class="btn">
   Log Out
</a>
</body>
</html>
