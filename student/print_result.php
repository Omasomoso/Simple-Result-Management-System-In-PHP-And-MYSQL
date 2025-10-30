<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
redirectIfNotStudent();

$student_id = $_SESSION["student_id"];
$session_id = isset($_GET["session_id"]) ? $_GET["session_id"] : null;
$term_id = isset($_GET["term_id"]) ? $_GET["term_id"] : null;

// Redirect if session/term not selected
if (!$session_id || !$term_id) {
    header("Location: select_session.php");
    exit();
}

// Fetch data - MODIFIED TO GET CLASS NAME
$student = $pdo->query("SELECT students.*, classes.name AS class_name 
                       FROM students 
                       JOIN classes ON students.class_id = classes.id
                       WHERE students.id = $student_id")->fetch();

// MODIFIED QUERY TO ONLY SHOW RESULTS WITH VALID SCORES
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
    ORDER BY s.name
")->fetchAll();

$psychomotor = $pdo->query("SELECT * FROM psychomotor_skills WHERE student_id = $student_id AND session_id = $session_id AND term_id = $term_id")->fetch();
$affective = $pdo->query("SELECT * FROM affective_skills WHERE student_id = $student_id AND session_id = $session_id AND term_id = $term_id")->fetch();
$gpa = $pdo->query("SELECT gpa, cgpa FROM gpa_records WHERE student_id = $student_id AND session_id = $session_id AND term_id = $term_id")->fetch();
$session = $pdo->query("SELECT name FROM academic_sessions WHERE id = $session_id")->fetchColumn();
$term = $pdo->query("SELECT name FROM terms WHERE id = $term_id")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Official Result - <?= htmlspecialchars($student["name"]) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Base styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.3;
        }
        
        .container {
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            margin: 0 auto;
            padding: 5mm;
            box-sizing: border-box;
        }
        
        /* Header styling */
        .result-header {
            text-align: center;
            margin-bottom: 5mm;
        }
        .result-header img {
            height: 20mm;
        }
        .result-header h1 {
            font-size: 14px;
            margin: 2mm 0;
        }
        .result-header p {
            font-size: 10px;
            margin: 1mm 0;
        }
        .result-header h2 {
            font-size: 12px;
            margin: 3mm 0;
        }
        
        /* Student info */
        .student-info {
            text-align: center;
            margin-bottom: 5mm;
        }
        .student-info h3 {
            font-size: 12px;
            margin: 2mm 0;
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 10px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        /* Skills sections */
        .skills-table {
            margin-bottom: 3mm;
        }
        .skills-table h3 {
            font-size: 11px;
            margin: 2mm 0 1mm 0;
        }
        .skills-table .table td {
            padding: 1px 2px;
        }
        
        /* Grading key */
        .grading-key {
            margin: 3mm 0;
            border: 1px dashed #000;
            padding: 2mm;
            font-size: 10px;
            text-align: center;
        }
        
        /* Signature section */
        .signature-line {
            display: flex;
            justify-content: space-between;
            margin-top: 5mm;
            font-size: 10px;
        }
        .signature-img {
            height: 15mm;
        }
        
        /* Print-specific styles */
        @media print {
            body {
                font-family: 'Times New Roman', serif;
                font-size: 11px;
            }
            .no-print {
                display: none;
            }
            .container {
                padding: 5mm;
                margin: 0;
            }
            /* Ensure borders print */
            .table, .table th, .table td {
                border: 1px solid #000 !important;
            }
            /* Prevent page breaks inside elements */
            .student-result, .table, .skills-table, .signature-line {
                page-break-inside: avoid;
            }
        }
        
        /* Watermark */
        body::after {
            content: "";
            background: url('../assets/images/school-logo.png') no-repeat center center;
            opacity: 0.1;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            z-index: -1;
            background-size: 50%;
        }
        
        /* No results message */
        .no-results {
            text-align: center;
            font-style: italic;
            padding: 10mm 0;
            border: 1px dashed #ccc;
            margin-bottom: 5mm;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- School Header -->
        <div class="result-header text-center">
            <img src="../assets/images/school-logo.png" alt="School Logo">
            <h1>AVALON GROUP OF SCHOOL</h1>
            <p>Address: 134 Order Of D-Roses Road, Area 10, FCT, Abuja | Email: info@avalonschool.edu | Phone: +234 8066 456 7890</p>
            <h2>TERMINAL RESULT SHEET</h2>
        </div>

        <!-- Student Info -->
        <div class="student-info text-center">
            <h3>Name: <?= htmlspecialchars($student["name"]) ?> | Class: <?= htmlspecialchars($student["class_name"]) ?> | Session: <?= htmlspecialchars($session) ?> | Term: <?= htmlspecialchars($term) ?></h3>
        </div>

        <!-- Academic Results -->
        <?php if (empty($results)): ?>
            <div class="no-results">
                No results with valid scores found for this term.
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 30%">Subject</th>
                        <th style="width: 14%">Test 1</th>
                        <th style="width: 14%">Test 2</th>
                        <th style="width: 14%">Exam</th>
                        <th style="width: 14%">Total</th>
                        <th style="width: 14%">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result["subject_name"]) ?></td>
                        <td><?= isset($result["test1"]) && $result["test1"] !== null ? $result["test1"] : '-' ?></td>
                        <td><?= isset($result["test2"]) && $result["test2"] !== null ? $result["test2"] : '-' ?></td>
                        <td><?= isset($result["exam"]) && $result["exam"] !== null ? $result["exam"] : '-' ?></td>
                        <td><?= isset($result["total"]) && $result["total"] !== null ? $result["total"] : '-' ?></td>
                        <td><?= isset($result["grade"]) && $result["grade"] !== null ? $result["grade"] : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Psychomotor Skills - Compact version -->
        <div class="skills-table">
            <h3>Psychomotor Skills</h3>
            <table class="table">
                <tr>
                    <td style="width: 20%"><strong>Handwriting:</strong> <?= isset($psychomotor["skill1"]) ? $psychomotor["skill1"] : '-' ?></td>
                    <td style="width: 20%"><strong>Sports:</strong> <?= isset($psychomotor["skill2"]) ? $psychomotor["skill2"] : '-' ?></td>
                    <td style="width: 20%"><strong>Creativity:</strong> <?= isset($psychomotor["skill3"]) ? $psychomotor["skill3"] : '-' ?></td>
                    <td style="width: 20%"><strong>Attentiveness:</strong> <?= isset($psychomotor["skill4"]) ? $psychomotor["skill4"] : '-' ?></td>
                    <td style="width: 20%"><strong>Class Contribution:</strong> <?= isset($psychomotor["skill5"]) ? $psychomotor["skill5"] : '-' ?></td>
                </tr>
            </table>
        </div>

        <!-- Affective Skills - Compact version -->
        <div class="skills-table">
            <h3>Affective Skills</h3>
            <table class="table">
                <tr>
                    <td style="width: 20%"><strong>Punctuality:</strong> <?= isset($affective["trait1"]) ? $affective["trait1"] : '-' ?></td>
                    <td style="width: 20%"><strong>Teamwork:</strong> <?= isset($affective["trait2"]) ? $affective["trait2"] : '-' ?></td>
                    <td style="width: 20%"><strong>Leadership:</strong> <?= isset($affective["trait3"]) ? $affective["trait3"] : '-' ?></td>
                    <td style="width: 20%"><strong>Selflessness:</strong> <?= isset($affective["trait4"]) ? $affective["trait4"] : '-' ?></td>
                    <td style="width: 20%"><strong>Kindness:</strong> <?= isset($affective["trait5"]) ? $affective["trait5"] : '-' ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Grading Key -->
        <div class="grading-key">
            <h4>Grading System: A (70-100) | B (60-69) | C (50-59) | D (45-49) | F (0-44)</h4>
        </div>
        
        <!-- Footer -->
        <div class="signature-line">
            <div>
                <p><strong>GPA:</strong> <?= isset($gpa["gpa"]) ? $gpa["gpa"] : 'N/A' ?></p>
                <p><strong>CGPA:</strong> <?= isset($gpa["cgpa"]) ? $gpa["cgpa"] : 'N/A' ?></p>
            </div>
            <div style="text-align: center;">
                <img src="../assets/images/principal-signature.png" alt="Principal's Signature" class="signature-img">
                <p>_________________________</p>
                <p>Leonado Davinci</p>
            </div>
        </div>

        <button onclick="window.print()" class="no-print">Print Result</button>
    </div>
</body>
</html>