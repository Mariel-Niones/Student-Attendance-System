<?php
session_start();
include '../db.php';

// Only allow logged-in instructors
if(!isset($_SESSION['full_name']) || $_SESSION['role'] !== 'instructor'){
    header("Location: ../index.php?action=login");
    exit();
}

// Get class_id from URL
if(!isset($_GET['class_id'])){
    die("Class not specified.");
}
$class_id = intval($_GET['class_id']);

// Fetch class info
$stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class_result = $stmt->get_result();
if($class_result->num_rows === 0){
    die("Class not found.");
}
$class = $class_result->fetch_assoc();
$class_name = $class['class_name'];

// Handle student registration
$message = '';
if(isset($_POST['register_student'])){
    $student_name = trim($_POST['student_name']);
    $id_number = trim($_POST['id_number']);

    if(!empty($student_name) && !empty($id_number)){
        $student_code = strtoupper(substr($student_name,0,3)) . '-' . substr($id_number,-4);

        $stmt = $conn->prepare("INSERT INTO students (student_name, id_number, student_code, class_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $student_name, $id_number, $student_code, $class_id);

        if($stmt->execute()){
            // Redirect to view_students for this class
            header("Location: view_students.php?class_id=$class_id");
            exit();
        } else {
            $message = "Error adding student. Maybe this ID already exists.";
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
    <title>Add Student - <?php echo htmlspecialchars($class_name); ?></title>
    <style>
        body{
            font-family: Arial;
            background: lightskyblue;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }
        .container{
            background:white;
            padding:30px;
            border-radius:10px;
            box-shadow:0 5px 15px rgba(0,0,0,0.2);
            width:400px;
            text-align:center;
        }
        h2{
            margin-bottom:10px;
        }
        .class-name{
            color:#007BFF;
            margin-bottom:20px;
            font-weight:bold;
        }
        input{
            width:100%;
            padding:10px;
            margin:10px 0;
            border-radius:5px;
            border:1px solid #ccc;
        }
        button{
            width:100%;
            padding:10px;
            background:#007BFF;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-size:16px;
        }
        button:hover{
            background:#0056b3;
        }
        .message{
            margin-bottom:10px;
            color:red;
            font-weight:bold;
        }
        .back{
            display:block;
            margin-top:20px;
            color:#007BFF;
            text-decoration:none;
            font-weight:bold;
        }
        .back:hover{
            text-decoration:underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Student</h2>
    <div class="class-name"><?php echo htmlspecialchars($class_name); ?></div>

    <?php if($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="student_name" placeholder="Full Name" required>
        <input type="text" name="id_number" placeholder="ID Number" required>
        <button type="submit" name="register_student">Add Student</button>
    </form>

    <a class="back" href="view_students.php?class_id=<?php echo $class_id; ?>">← Back to Students</a>
</div>

</body>
</html>