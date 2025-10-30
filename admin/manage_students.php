<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
redirectIfNotAdmin();

// Fetch sessions and classes for dropdowns
$sessions = $pdo->query("SELECT * FROM academic_sessions")->fetchAll();
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();

// Handle filters
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;
$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;

// Fetch students based on filters
$students = [];
if ($session_id && $class_id) {
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.reg_number 
        FROM students s
        JOIN results r ON s.id = r.student_id
        WHERE r.session_id = ? AND s.class_id = ?
        GROUP BY s.id
    ");
    $stmt->execute([$session_id, $class_id]);
    $students = $stmt->fetchAll();
}

// Handle student deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt_delete = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt_delete->execute([$delete_id]);
    header("Location: manage_students.php?session_id=$session_id&class_id=$class_id");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Students</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Filter Form -->
        <form method="GET" class="filter-form">
            <select name="session_id" required>
                <option value="">Select Session</option>
                <?php foreach ($sessions as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $session_id == $s['id'] ? 'selected' : '' ?>>
                        <?= $s['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $class_id == $c['id'] ? 'selected' : '' ?>>
                        <?= $c['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn">Filter</button>
        </form>

        <!-- Students List -->
        <?php if ($session_id && $class_id): ?>
            <div class="student-list">
                <h2>
    Students in <?= isset($classes[$class_id-1]['name']) ? $classes[$class_id-1]['name'] : 'Unknown' ?> 
    (<?= isset($sessions[$session_id-1]['name']) ? $sessions[$session_id-1]['name'] : 'Unknown Session' ?>)
</h2>

                
                <?php if (empty($students)): ?>
                    <p>No students found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Reg Number</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['reg_number']) ?></td>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td>
                                        <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn">Edit</a>
                                        <a href="manage_students.php?delete_id=<?= $student['id'] ?>&session_id=<?= $session_id ?>&class_id=<?= $class_id ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Delete this student?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
