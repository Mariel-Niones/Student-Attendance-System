<?php
session_start();
include '../db.php';

// Only allow logged-in instructors
if(!isset($_SESSION['full_name']) || $_SESSION['role'] !== 'instructor'){
    header("Location: ../index.php?action=login");
    exit();
}

// Get class_id and student_id from URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if($class_id <= 0 || $student_id <= 0){
    die("Invalid class or student specified.");
}

// Fetch class name
$stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class_result = $stmt->get_result();
if($class_result->num_rows === 0){
    die("Class not found.");
}
$class = $class_result->fetch_assoc();
$class_name = $class['class_name'];
$stmt->close();

// Fetch student info
$stmt = $conn->prepare("SELECT student_name, id_number FROM students WHERE id = ? AND class_id = ?");
$stmt->bind_param("ii", $student_id, $class_id);
$stmt->execute();
$student_result = $stmt->get_result();
if($student_result->num_rows === 0){
    die("Student not found in this class.");
}
$student = $student_result->fetch_assoc();
$stmt->close();

// Handle update
$message = '';
if(isset($_POST['update_student'])){
    $student_name = trim($_POST['student_name']);
    $id_number = trim($_POST['id_number']);

    if(!empty($student_name) && !empty($id_number)){
        $student_code = strtoupper(substr($student_name,0,3)) . '-' . substr($id_number, -4);

        $stmt = $conn->prepare("UPDATE students SET student_name = ?, id_number = ?, student_code = ? WHERE id = ? AND class_id = ?");
        $stmt->bind_param("sssii", $student_name, $id_number, $student_code, $student_id, $class_id);

        if($stmt->execute()){
            header("Location: view_students.php?class_id=$class_id");
            exit();
        } else {
            $message = "Error updating student. Maybe the ID already exists.";
        }
        $stmt->close();
    } else {
        $message = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student - <?php echo htmlspecialchars($class_name); ?></title>
    <style>
        body {
            font-family: Arial;
            background: lightskyblue;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 400px;
            text-align: center;
        }
        h2 { margin-bottom: 10px; }
        .class-name { color: #007BFF; font-weight: bold; margin-bottom: 20px; }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #0056b3; }
        .message { margin-bottom: 10px; color: red; font-weight: bold; }
        .back { display: block; margin-top: 20px; color: #007BFF; text-decoration: none; font-weight: bold; }
        .back:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Student</h2>
    <div class="class-name"><?php echo htmlspecialchars($class_name); ?></div>

    <?php if($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="student_name" placeholder="Full Name" value="<?php echo htmlspecialchars($student['student_name']); ?>" required>
        <input type="text" name="id_number" placeholder="ID Number" value="<?php echo htmlspecialchars($student['id_number']); ?>" required>
        <button type="submit" name="update_student">Update Student</button>
    </form>

    <a class="back" href="view_students.php?class_id=<?php echo $class_id; ?>">← Back to Students</a>
</div>

</body>
</html>