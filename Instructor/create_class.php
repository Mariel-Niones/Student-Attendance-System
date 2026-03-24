<?php
session_start();
require(__DIR__ . '/../db.php'); // go up to root for db.php

// Check if user is logged in and is an instructor
if(!isset($_SESSION['full_name']) || $_SESSION['role'] !== 'instructor'){
    header("Location: ../index.php?action=login");
    exit();
}

$error = '';
$success = '';
$instructor_name = $_SESSION['full_name'];

if(isset($_POST['create_class'])){
    $class_name = trim($_POST['class_name']);

    if(!empty($class_name)){
        // Check if class already exists for this instructor
        $check_stmt = $conn->prepare("SELECT * FROM classes WHERE class_name=? AND instructor_name=?");
        $check_stmt->bind_param("ss", $class_name, $instructor_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result->num_rows > 0){
            $error = "You already have a class with this name.";
        } else {
            // Insert new class
            $stmt = $conn->prepare("INSERT INTO classes (class_name, instructor_name) VALUES (?, ?)");
            $stmt->bind_param("ss", $class_name, $instructor_name);

            if($stmt->execute()){
                // Success → redirect to dashboard
                header("Location: instructor_dashboard.php");
                exit();
            } else {
                $error = "Failed to create class.";
            }

            $stmt->close();
        }

        $check_stmt->close();
    } else {
        $error = "Please enter a class name.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Class - Instructor Dashboard</title>
    <style>
        body{ font-family: Arial; background-color: lightblue; display:flex; justify-content:center; align-items:center; height:100vh; }
        .container{ background:white; padding:30px; border-radius:10px; width:400px; box-shadow:0 0 15px rgba(0,0,0,0.2); }
        input, button{ width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; font-size:16px; }
        button{ background:#007BFF; color:white; border:none; cursor:pointer; }
        button:hover{ background:#0056b3; }
        .error{color:red; text-align:center;}
        .success{color:green; text-align:center;}
        h2{text-align:center;}
    </style>
</head>
<body>
    <div class="container">
        <h2>Create New Class</h2>

        <?php
            if(!empty($error)) echo "<p class='error'>$error</p>";
            if(!empty($success)) echo "<p class='success'>$success</p>";
        ?>

        <form method="POST">
            <input type="text" name="class_name" placeholder="Class Name" required>
            <button type="submit" name="create_class">Create Class</button>
        </form>

        <p style="text-align:center;"><a href="instructor_dashboard.php">Go to Dashboard</a></p>
    </div>
</body>
</html>