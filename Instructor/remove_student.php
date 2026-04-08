<?php
session_start();
require(__DIR__ . '/../db.php');

// Only allow logged-in instructors
if(!isset($_SESSION['full_name']) || $_SESSION['role'] !== 'instructor'){
    header("Location: ../index.php?action=login");
    exit();
}

$instructor_name = $_SESSION['full_name'];
$error = '';
$success = '';

// Get class ID
if(!isset($_GET['class_id'])){
    die("Class not specified.");
}

$class_id = intval($_GET['class_id']);

// Verify that this class belongs to the instructor
$stmt = $conn->prepare("SELECT * FROM classes WHERE id=? AND instructor_name=?");
$stmt->bind_param("is", $class_id, $instructor_name);
$stmt->execute();
$class_result = $stmt->get_result();

if($class_result->num_rows === 0){
    die("Unauthorized access.");
}

$class = $class_result->fetch_assoc();
$stmt->close();

// Handle student removal
if(isset($_GET['remove_student'])){
    $student_id = intval($_GET['remove_student']);

    $stmt = $conn->prepare("DELETE FROM students WHERE id=? AND class_id=?");
    $stmt->bind_param("ii", $student_id, $class_id);

    if($stmt->execute()){
        $success = "Student removed successfully!";
    } else {
        $error = "Failed to remove student.";
    }

    $stmt->close();
}

// Fetch students in this class
$stmt = $conn->prepare("SELECT * FROM students WHERE class_id=?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remove Student</title>
    <style>
        body{
            font-family: Arial;
            background-color: lightskyblue;
            padding: 20px;
        }
        .container{
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        h2{text-align:center;}
        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td{
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        .btn{
            padding: 5px 10px;
            background: red;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover{
            background: darkred;
        }
        .back-container{
            text-align: center;
            margin-top: 20px;
        }
        .back{
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            color: white;
            background: #007BFF;
            border-radius: 5px;
        }
        .back:hover{
            background: #0056b3;
        }
        .error{color:red; text-align:center;}
        .success{color:green; text-align:center;}
    </style>
</head>
<body>
<div class="container">

    <h2>Remove Students from "<?php echo htmlspecialchars($class['class_name']); ?>"</h2>

    <?php
        if(!empty($error)) echo "<p class='error'>$error</p>";
        if(!empty($success)) echo "<p class='success'>$success</p>";
    ?>

    <table>
        <tr>
            <th>Student Name</th>
            <th>Action</th>
        </tr>

        <?php if(count($students) === 0): ?>
            <tr><td colspan="2">No students in this class.</td></tr>
        <?php else: ?>
            <?php foreach($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                    <td>
                        <a class="btn"
                           href="?class_id=<?php echo $class_id; ?>&remove_student=<?php echo $student['id']; ?>"
                           onclick="return confirm('Are you sure you want to remove this student?')">
                           Remove
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <!-- Centered Back Button -->
    <div class="back-container">
        <a class="back" href="instructor_dashboard.php?class_id=<?php echo $class_id; ?>">
            ← Back to Dashboard
        </a>
    </div>

</div>
</body>
</html>