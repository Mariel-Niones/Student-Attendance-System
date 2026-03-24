<?php
session_start();
require(__DIR__ . '/../db.php');

// Check login
if(!isset($_SESSION['full_name']) || $_SESSION['role'] !== 'instructor'){
    header("Location: ../index.php?action=login");
    exit();
}

if(!isset($_GET['class_id'])){
    header("Location: instructor_dashboard.php");
    exit();
}

$class_id = intval($_GET['class_id']);
$instructor_name = $_SESSION['full_name'];

// Verify class ownership
$stmt = $conn->prepare("SELECT * FROM classes WHERE id=? AND instructor_name=?");
$stmt->bind_param("is", $class_id, $instructor_name);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    header("Location: instructor_dashboard.php");
    exit();
}

$class = $result->fetch_assoc();
$stmt->close();

// Get students
$stmt = $conn->prepare("SELECT * FROM students WHERE class_id=?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Students List</title>
    <style>
        body{
            font-family: Arial;
            background:#f2f2f2;
            padding:20px;
        }
        .container{
            max-width:800px;
            margin:auto;
            background:white;
            padding:20px;
            border-radius:10px;
            box-shadow:0 0 15px rgba(0,0,0,0.2);
        }
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }
        th, td{
            padding:10px;
            border:1px solid #ccc;
            text-align:center;
        }
        h2{text-align:center;}
        a.button{
            padding:8px 12px;
            background:#007BFF;
            color:white;
            text-decoration:none;
            border-radius:5px;
        }
        a.button:hover{
            background:#0056b3;
        }
    </style>
</head>
<body>

<div class="container">

<h2>Students in "<?php echo htmlspecialchars($class['class_name']); ?>"</h2>

<table>
<tr>
    <th>Name</th>
    <th>Email</th>
</tr>

<?php if(count($students) === 0): ?>
    <tr><td colspan="2">No students found.</td></tr>
<?php else: ?>
    <?php foreach($students as $student): ?>
        <tr>
            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
            <td><?php echo htmlspecialchars($student['student_email']); ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

</table>

<br>
<p style="text-align:center;">
    <a class="button" href="instructor_dashboard.php">Back to Dashboard</a>
</p>

</div>

</body>
</html>