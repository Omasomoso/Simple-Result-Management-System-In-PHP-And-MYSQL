<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
redirectIfNotAdmin();

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;
$term_id = isset($_GET['term_id']) ? $_GET['term_id'] : null;


if (!$student_id || !$session_id || !$term_id) {
    die("Invalid parameters");
}

// Fetch student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();


// Fetch all results for this student
$stmt = $pdo->prepare("
    SELECT r.*, sub.name as subject_name 
    FROM results r
    JOIN subjects sub ON r.subject_id = sub.id
    WHERE r.student_id = ? 
    AND r.session_id = ? 
    AND r.term_id = ?
");
$stmt->execute([$student_id, $session_id, $term_id]);
$results = $stmt->fetchAll();
?>
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Results updated successfully!</div>
<?php endif; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Results - <?= htmlspecialchars($student['name']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Editing Results for <?= htmlspecialchars($student['name']) ?></h1>
        <p>Registration: <?= htmlspecialchars($student['reg_number']) ?></p>
        <a href="view_results.php" class="btn">Back to List</a>

        <form method="POST" action="update_results.php">
            <input type="hidden" name="student_id" value="<?= $student_id ?>">
            <input type="hidden" name="session_id" value="<?= $session_id ?>">
            <input type="hidden" name="term_id" value="<?= $term_id ?>">

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
                            <td><?= htmlspecialchars($result['subject_name']) ?></td>
                            <td>
                                <input type="number" name="scores[<?= $result['id'] ?>][test1]" 
                                       value="<?= $result['test1'] ?>" min="0" max="100">
                            </td>
                            <td>
                                <input type="number" name="scores[<?= $result['id'] ?>][test2]" 
                                       value="<?= $result['test2'] ?>" min="0" max="100">
                            </td>
                            <td>
                                <input type="number" name="scores[<?= $result['id'] ?>][exam]" 
                                       value="<?= $result['exam'] ?>" min="0" max="100">
                            </td>
                            <td><?= $result['total'] ?></td>
                            <td><?= $result['grade'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn">Save Changes</button>
        </form>
    </div>
</body>
</html>