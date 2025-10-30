<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
redirectIfNotAdmin();

// Fetch classes for dropdown
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
	    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial; padding: 20px; }
        .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <a href="logout.php">Logout</a>

    <div class="section">
        <h2>Upload Results</h2>
        <form action="upload_results.php" method="POST" enctype="multipart/form-data">
            <select name="session_id" required>
                <?php
                $sessions = $pdo->query("SELECT * FROM academic_sessions")->fetchAll();
                foreach ($sessions as $session) {
                    echo "<option value='{$session["id"]}'>{$session["name"]}</option>";
                }
                ?>
            </select>
            <select name="term_id" required>
                <?php
                $terms = $pdo->query("SELECT * FROM terms")->fetchAll();
                foreach ($terms as $term) {
                    echo "<option value='{$term["id"]}'>{$term["name"]}</option>";
                }
                ?>
            </select>
            <select name="class_id" required>
                <?php foreach ($classes as $class): ?>
                <option value="<?= $class["id"] ?>"><?= $class["name"] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="result_file" accept=".xlsx,.xls" required>
            <button type="submit">Upload</button>
        </form>
    </div>

    <div class="section">
        <h2>View Class Results</h2>
        <form action="view_results.php" method="GET">
            <select name="session_id" required>
                <?php foreach ($sessions as $session): ?>
                <option value="<?= $session["id"] ?>"><?= $session["name"] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="term_id" required>
                <?php foreach ($terms as $term): ?>
                <option value="<?= $term["id"] ?>"><?= $term["name"] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="class_id" required>
                <?php foreach ($classes as $class): ?>
                <option value="<?= $class["id"] ?>"><?= $class["name"] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View Results</button>
        </form>
    </div>

    <div style="display: flex; gap: 20px;">
    <div class="section" style="flex: 1;">
        <h2><a href="manage_students.php">Manage Students</a></h2>
    </div>

    <div class="section" style="flex: 1;">
        <h2><a href="add_session.php">Manage Session And Class</a></h2>
    </div>
</div>

</body>
</html>
