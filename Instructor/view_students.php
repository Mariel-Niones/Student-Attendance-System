<?php
session_start();
include '../db.php';

// Security
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}

// Get class_id from URL
if (!isset($_GET['class_id'])) {
    die("Class not specified.");
}

$class_id = intval($_GET['class_id']);

// Fetch students of specific class
$stmt = $conn->prepare("
    SELECT c.class_name, s.student_name, s.id_number, s.student_code
    FROM students s
    JOIN classes c ON s.class_id = c.id
    WHERE c.id = ?
    ORDER BY s.student_name
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
$class_name = "";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
        $class_name = $row['class_name'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Students</title>
    <style>
        body{
            font-family: Arial;
            background: lightskyblue;
            padding:30px;
        }
        .container{
            background:white;
            padding:30px;
            border-radius:10px;
            box-shadow:0 5px 15px rgba(0,0,0,0.2);
            max-width:900px;
            margin:auto;
        }
        h2{
            text-align:center;
        }
        h3{
            margin-top:30px;
            background:#007BFF;
            color:white;
            padding:10px;
            border-radius:5px;
        }
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
        }
        th, td{
            border:1px solid #ddd;
            padding:8px;
            text-align:center;
        }
        th{
            background:#007BFF;
            color:white;
        }
        .back{
            display:block;
            margin-top:20px;
            text-align:center;
            text-decoration:none;
            font-weight:bold;
            color:#007BFF;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Students per Class</h2>

    <h3><?php echo htmlspecialchars($class_name); ?></h3>

    <?php if (!empty($students)): ?>
        <table>
            <tr>
                <th>Full Name</th>
                <th>ID Number</th>
                <th>Student Code</th>
            </tr>

            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['id_number']); ?></td>
                    <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No students found.</p>
    <?php endif; ?>

    <a href="instructor_dashboard.php" class="back">← Back to Dashboard</a>
</div>

</body>
</html>