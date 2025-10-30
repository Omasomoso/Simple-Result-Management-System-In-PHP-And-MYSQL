<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
redirectIfNotAdmin();

// Define functions outside the main logic
function calculateGrade($total) {
    if ($total >= 75) return "A";
    if ($total >= 65) return "B";
    if ($total >= 50) return "C";
    if ($total >= 40) return "D";
    return "F";
}

function calculateGPA($student_id, $term_id, $session_id) {
    global $pdo;
    // Use prepared statement for safety
    $stmt = $pdo->prepare("SELECT grade FROM results WHERE student_id = ? AND term_id = ? AND session_id = ?");
    $stmt->execute([$student_id, $term_id, $session_id]);
    $grades = $stmt->fetchAll();

    $total_points = 0;
    $count = 0;
    foreach ($grades as $grade) {
        switch ($grade["grade"]) {
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

function validateScore($value) {
    if ($value === null || $value === '' || $value === '-') {
        return 0;
    }
    return (float)$value;
}

// Main processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $session_id = $_POST["session_id"];
    $term_id = $_POST["term_id"];
    $class_id = $_POST["class_id"];
    $file = $_FILES["result_file"]["tmp_name"];

// Validate CSV
if (!is_uploaded_file($file)) {
    die("Error: File upload failed.");
}

$file_type = $_FILES["result_file"]["type"];
if (!in_array($file_type, ["text/csv", "application/vnd.ms-excel"])) {
    die("Error: Only CSV files are allowed.");
}

    // Open CSV
    if (($handle = fopen($file, "r")) === FALSE) {
        die("Error: Could not open CSV file.");
    }

    // Skip header
    fgetcsv($handle);

    // Start transaction
    $pdo->beginTransaction();

    try {
        $row_count = 0;
        while (($row = fgetcsv($handle)) !== FALSE) {
            $row_count++;

            // Validate row length
            if (count($row) < 93) {
                throw new Exception("Row $row_count: Expected 93 columns, got " . count($row));
            }

            // Process student
            $student_name = $row[0];
            $reg_number = $row[1];

            // Check if student already exists to avoid duplicate inserts and get existing ID
            $stmt_check_student = $pdo->prepare("SELECT id FROM students WHERE reg_number = ?");
            $stmt_check_student->execute([$reg_number]);
            $existing_student = $stmt_check_student->fetch(PDO::FETCH_ASSOC);

            if ($existing_student) {
                $student_id = $existing_student['id'];
                // Optionally update student name or class if needed, but for now, just use existing ID
                $stmt_update_student = $pdo->prepare("UPDATE students SET name = ?, class_id = ? WHERE id = ?");
                $stmt_update_student->execute([$student_name, $class_id, $student_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO students (name, reg_number, class_id, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $student_name,
                    $reg_number,
                    $class_id,
                    password_hash($reg_number, PASSWORD_DEFAULT) // Hash password
                ]);
                $student_id = $pdo->lastInsertId();
            }

            // Process subjects
            $subject_index = 2;
            for ($i = 0; $i < 27; $i++) {
                $test1 = validateScore($row[$subject_index++]);
                $test2 = validateScore($row[$subject_index++]);
                $exam = validateScore($row[$subject_index++]);
                $total = $test1 + $test2 + $exam;

                // Check if result already exists for this student, subject, session, term
                $stmt_check_result = $pdo->prepare("SELECT id FROM results WHERE student_id = ? AND subject_id = ? AND session_id = ? AND term_id = ?");
                $stmt_check_result->execute([$student_id, $i + 1, $session_id, $term_id]);
                $existing_result = $stmt_check_result->fetch(PDO::FETCH_ASSOC);

                if ($existing_result) {
                    // Update existing result
                    $stmt_update_result = $pdo->prepare("
                        UPDATE results 
                        SET test1 = ?, test2 = ?, exam = ?, total = ?, grade = ?, remark = ?
                        WHERE id = ?
                    ");
                    $stmt_update_result->execute([
                        $test1, $test2, $exam, $total, calculateGrade($total), ($total >= 50) ? "PASS" : "FAIL",
                        $existing_result['id']
                    ]);
                } else {
                    // Insert new result
                    $stmt_insert_result = $pdo->prepare("INSERT INTO results (student_id, subject_id, session_id, term_id, test1, test2, exam, total, grade, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_insert_result->execute([
                        $student_id,
                        $i + 1,
                        $session_id,
                        $term_id,
                        $test1,
                        $test2,
                        $exam,
                        $total,
                        calculateGrade($total),
                        ($total >= 50) ? "PASS" : "FAIL"
                    ]);
                }
            }

            // Psychomotor skills
            $psycho = array_map('intval', array_slice($row, $subject_index, 5));
            $subject_index += 5;

            // Check and update/insert psychomotor skills
            $stmt_check_psycho = $pdo->prepare("SELECT id FROM psychomotor_skills WHERE student_id = ? AND session_id = ? AND term_id = ?");
            $stmt_check_psycho->execute([$student_id, $session_id, $term_id]);
            $existing_psycho = $stmt_check_psycho->fetch(PDO::FETCH_ASSOC);

            if ($existing_psycho) {
                $stmt_update_psycho = $pdo->prepare("
                    UPDATE psychomotor_skills 
                    SET skill1 = ?, skill2 = ?, skill3 = ?, skill4 = ?, skill5 = ?
                    WHERE id = ?
                ");
                $stmt_update_psycho->execute([
                    $psycho[0], $psycho[1], $psycho[2], $psycho[3], $psycho[4],
                    $existing_psycho['id']
                ]);
            } else {
                $stmt_insert_psycho = $pdo->prepare("INSERT INTO psychomotor_skills (student_id, session_id, term_id, skill1, skill2, skill3, skill4, skill5) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert_psycho->execute([
                    $student_id,
                    $session_id,
                    $term_id,
                    $psycho[0],
                    $psycho[1],
                    $psycho[2],
                    $psycho[3],
                    $psycho[4]
                ]);
            }

            // Affective skills
            $affective = array_map('intval', array_slice($row, $subject_index, 5));

            // Check and update/insert affective skills
            $stmt_check_affective = $pdo->prepare("SELECT id FROM affective_skills WHERE student_id = ? AND session_id = ? AND term_id = ?");
            $stmt_check_affective->execute([$student_id, $session_id, $term_id]);
            $existing_affective = $stmt_check_affective->fetch(PDO::FETCH_ASSOC);

            if ($existing_affective) {
                $stmt_update_affective = $pdo->prepare("
                    UPDATE affective_skills 
                    SET trait1 = ?, trait2 = ?, trait3 = ?, trait4 = ?, trait5 = ?
                    WHERE id = ?
                ");
                $stmt_update_affective->execute([
                    $affective[0], $affective[1], $affective[2], $affective[3], $affective[4],
                    $existing_affective['id']
                ]);
            } else {
                $stmt_insert_affective = $pdo->prepare("INSERT INTO affective_skills (student_id, session_id, term_id, trait1, trait2, trait3, trait4, trait5) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert_affective->execute([
                    $student_id,
                    $session_id,
                    $term_id,
                    $affective[0],
                    $affective[1],
                    $affective[2],
                    $affective[3],
                    $affective[4]
                ]);
            }

            // Calculate and insert/update GPA
            $gpa = calculateGPA($student_id, $term_id, $session_id);
            $stmt_check_gpa = $pdo->prepare("SELECT id FROM gpa_records WHERE student_id = ? AND session_id = ? AND term_id = ?");
            $stmt_check_gpa->execute([$student_id, $session_id, $term_id]);
            $existing_gpa = $stmt_check_gpa->fetch(PDO::FETCH_ASSOC);

            if ($existing_gpa) {
                $stmt_update_gpa = $pdo->prepare("
                    UPDATE gpa_records 
                    SET gpa = ?, cgpa = ?
                    WHERE id = ?
                ");
                $stmt_update_gpa->execute([$gpa, $gpa, $existing_gpa['id']]);
            } else {
                $stmt_insert_gpa = $pdo->prepare("INSERT INTO gpa_records (student_id, session_id, term_id, gpa, cgpa) VALUES (?, ?, ?, ?, ?)");
                $stmt_insert_gpa->execute([$student_id, $session_id, $term_id, $gpa, $gpa]);
            }
        }

        $pdo->commit();
        fclose($handle);
        header("Location: dashboard.php?success=" . urlencode("$row_count students imported!"));
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error at row $row_count: " . $e->getMessage());
    }
}
?>
