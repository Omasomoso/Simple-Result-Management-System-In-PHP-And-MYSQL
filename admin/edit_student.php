<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
redirectIfNotAdmin();

if (!isset($_GET['id'])) {
    header("Location: manage_students.php");
    exit();
}

$student_id = $_GET['id'];
// Use prepared statement for fetching student data
$stmt_student = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt_student->execute([$student_id]);
$student = $stmt_student->fetch();

$classes = $pdo->query("SELECT * FROM classes")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $reg_number = $_POST['reg_number'];
    $class_id = $_POST['class_id'];
    
    $pdo->prepare("UPDATE students SET name = ?, reg_number = ?, class_id = ? WHERE id = ?")
        ->execute([$name, $reg_number, $class_id, $student_id]);
    header("Location: manage_students.php?success=Student+updated");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Student</h1>
        <a href="manage_students.php" class="btn">Back to List</a>

        <form method="POST" class="card">
            <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
            <input type="text" name="reg_number" value="<?= htmlspecialchars($student['reg_number']) ?>" required>
            <select name="class_id" required>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= $class['id'] ?>" <?= $class['id'] == $student['class_id'] ? 'selected' : '' ?>>
                        <?= $class['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Update Student</button>
        </form>
    </div>
</body>
</html>
