<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
redirectIfNotAdmin();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: view_results.php");
    exit();
}

// Get form data
$student_id = $_POST['student_id'];
$session_id = $_POST['session_id'];
$term_id = $_POST['term_id'];
$scores = isset($_POST['scores']) ? $_POST['scores'] : [];


// ✅ Fixed Syntax Error (added closing parenthesis)
if (empty($scores)) {
    die("No scores submitted");
}

// Start transaction
$pdo->beginTransaction();

try {
    foreach ($scores as $result_id => $score_data) {
        // Validate inputs
        $test1 = filter_var($score_data['test1'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
        $test2 = filter_var($score_data['test2'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
        $exam = filter_var($score_data['exam'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
        
        if ($test1 === false || $test2 === false || $exam === false) {
            throw new Exception("Invalid score values (must be between 0-100)");
        }

        // Calculate total and grade
        $total = $test1 + $test2 + $exam;
        $grade = calculateGrade($total);
        $remark = ($total >= 50) ? "PASS" : "FAIL";

        // Update record
        $stmt = $pdo->prepare("
            UPDATE results 
            SET test1 = ?, test2 = ?, exam = ?, total = ?, grade = ?, remark = ?
            WHERE id = ? AND student_id = ? AND session_id = ? AND term_id = ?
        ");
        $stmt->execute([
            $test1, $test2, $exam, $total, $grade, $remark,
            $result_id, $student_id, $session_id, $term_id
        ]);
    }

    // ✅ Fixed calculateGPA to use prepared statements
    $gpa = calculateGPA($student_id, $term_id, $session_id);
    $stmt = $pdo->prepare("
        UPDATE gpa_records 
        SET gpa = ?, cgpa = ?
        WHERE student_id = ? AND session_id = ? AND term_id = ?
    ");
    $stmt->execute([$gpa, $gpa, $student_id, $session_id, $term_id]);

    $pdo->commit();
    
    // Redirect back with success message
    header("Location: edit_student_results.php?student_id=$student_id&session_id=$session_id&term_id=$term_id&success=1");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error updating results: " . $e->getMessage());
}

// ✅ Fixed: Reuse grading functions
function calculateGrade($total) {
    if ($total >= 75) return "A";
    if ($total >= 65) return "B";
    if ($total >= 50) return "C";
    if ($total >= 40) return "D";
    return "F";
}

function calculateGPA($student_id, $term_id, $session_id) {
    global $pdo;

    // ✅ Used prepared statement to prevent SQL errors
    $stmt = $pdo->prepare("
        SELECT grade FROM results 
        WHERE student_id = ? AND term_id = ? AND session_id = ?
    ");
    $stmt->execute([$student_id, $term_id, $session_id]);
    $grades = $stmt->fetchAll();

    $total_points = 0;
    $count = 0;

    foreach ($grades as $grade) {
        switch ($grade['grade']) {
            case 'A': $total_points += 5; break;
            case 'B': $total_points += 4; break;
            case 'C': $total_points += 3; break;
            case 'D': $total_points += 2; break;
            default: $total_points += 0;
        }
        $count++;
    }

    return $count > 0 ? round($total_points / $count, 2) : 0;
}
?>
