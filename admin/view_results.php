<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
redirectIfNotAdmin();

// Get filter parameters
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;
$term_id = isset($_GET['term_id']) ? $_GET['term_id'] : null;
$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;

// Fetch all classes, sessions, terms for dropdowns
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$sessions = $pdo->query("SELECT * FROM academic_sessions")->fetchAll();
$terms = $pdo->query("SELECT * FROM terms")->fetchAll();

// Fetch students when filters are selected
$students = [];
if ($session_id && $term_id && $class_id) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.name, s.reg_number
        FROM students s
        JOIN results r ON s.id = r.student_id
        WHERE r.session_id = ? 
        AND r.term_id = ?
        AND s.class_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$session_id, $term_id, $class_id]);
    $students = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Students - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .student-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .student-card {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .student-card:hover {
            background: #f5f5f5;
            transform: translateY(-3px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Students</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Filter Form -->
        <form method="GET" class="filter-form">
            <select name="session_id" required>
                <option value="">Select Session</option>
                <?php foreach ($sessions as $session): ?>
                    <option value="<?= $session['id'] ?>" <?= $session_id == $session['id'] ? 'selected' : '' ?>>
                        <?= $session['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="term_id" required>
                <option value="">Select Term</option>
                <?php foreach ($terms as $term): ?>
                    <option value="<?= $term['id'] ?>" <?= $term_id == $term['id'] ? 'selected' : '' ?>>
                        <?= $term['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= $class['id'] ?>" <?= $class_id == $class['id'] ? 'selected' : '' ?>>
                        <?= $class['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn">View Students</button>
        </form>

        <!-- Students List -->
        <?php if (!empty($students)): ?>
            <div class="student-list">
                <?php foreach ($students as $student): ?>
                    <a href="edit_student_results.php?student_id=<?= $student['id'] ?>&session_id=<?= $session_id ?>&term_id=<?= $term_id ?>" class="student-card">
                        <h3><?= htmlspecialchars($student['name']) ?></h3>
                        <p><?= htmlspecialchars($student['reg_number']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php elseif ($session_id && $term_id && $class_id): ?>
            <p>No students found with results for the selected filters.</p>
        <?php endif; ?>
    </div>
	<!--In admin/view_results.php-->
<a href="print_class_results.php?session_id=<?= $session_id ?>&term_id=<?= $term_id ?>&class_id=<?= $class_id ?>" 
   class="btn" target="_blank">
   Print All Class Results
</a>
</body>
</html>
