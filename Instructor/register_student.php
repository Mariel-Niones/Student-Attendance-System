<?php
session_start();
include '../db.php'; // Adjust path to your db.php

// =======================
// SECURITY: Only instructors can access
// =======================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}

$message = '';
$student_code = '';

// =======================
// REGISTER STUDENT
// =======================
if (isset($_POST['register_student'])) {
    $student_name = trim($_POST['student_name']);
    $id_number = trim($_POST['id_number']);

    if (!empty($student_name) && !empty($id_number)) {

        // Generate student code based on ID Number
        $student_code = strtoupper(substr($student_name,0,3)) . '-' . substr($id_number, -4);

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO students (student_name, id_number, student_code) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $student_name, $id_number, $student_code);

        if ($stmt->execute()) {
            $message = "Student '$student_name' registered successfully!";
        } else {
            $message = "Failed to register student. ID might already exist.";
            $student_code = '';
        }

        $stmt->close();
    } else {
        $message = "Both Full Name and ID Number are required!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Student</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f0f2f5;
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
        }
        h2{text-align:center;}
        input, button{
            width:100%;
            padding:10px;
            margin:10px 0;
            border-radius:5px;
            border:1px solid #ccc;
            font-size:16px;
        }
        button{
            background:#007BFF;
            color:white;
            border:none;
            cursor:pointer;
        }
        button:hover{
            background:#0056b3;
        }
        .message{
            text-align:center;
            font-weight:bold;
            margin-bottom:15px;
        }
        .success{ color:green; }
        .error{ color:red; }
        .student-code{
            text-align:center;
            font-size:18px;
            font-weight:bold;
            margin-top:10px;
            color:#333;
        }
        .back{
            display:block;
            margin-top:20px;
            text-align:center;
            text-decoration:none;
            color:#007BFF;
            font-weight:bold;
        }
        .back:hover{
            text-decoration:underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Register Student</h2>

    <?php if($message): ?>
        <p class="message <?php echo strpos($message,'successfully') !== false ? 'success':'error'; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <?php if($student_code): ?>
        <div class="student-code">
            Generated Student Code: <strong><?php echo $student_code; ?></strong>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="student_name" placeholder="Full Name" required>
        <input type="text" name="id_number" placeholder="ID Number" required>
        <button type="submit" name="register_student">Register Student</button>
    </form>

    <a href="instructor_dashboard.php" class="back">← Back to Dashboard</a>
</div>

</body>
</html>