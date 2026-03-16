<?php
session_start();
include 'db.php';

// Only allow logged-in instructors
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'instructor'){
    header("Location: index.php?action=login");
    exit();
}

$error = "";
$success = "";
$generated_barcode = "";

// =======================
// REGISTER STUDENT
// =======================
if(isset($_POST['register_student'])){
    $full_name = trim($_POST['full_name']);
    $id_number = trim($_POST['id_number']);

    if(!empty($full_name) && !empty($id_number)){
        // Generate unique barcode: STU + ID number + timestamp
        $barcode = "STU".$id_number.time();

        $stmt = $conn->prepare("INSERT INTO students (full_name, id_number, barcode) VALUES (?,?,?)");
        $stmt->bind_param("sss", $full_name, $id_number, $barcode);

        if($stmt->execute()){
            $success = "Student registered successfully!";
            $generated_barcode = $barcode;
        } else {
            $error = "Failed to register student. ID Number might already exist.";
        }

        $stmt->close();
    } else {
        $error = "Please enter both Full Name and ID Number.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Dashboard</title>
    <style>
    body{
        font-family: Arial;
        margin:0;
        padding:0;
        background:#f2f2f2;
        display:flex;
        justify-content:center;
        align-items:center;
        height:100vh;
    }
    .container{
        width:500px;
        background: rgba(255,255,255,0.95);
        padding:30px;
        border-radius:10px;
        box-shadow:0 0 20px rgba(0,0,0,0.3);
        text-align:center;
    }
    .form-box{
        border:3px solid #007BFF;
        padding:20px;
        border-radius:10px;
        margin-bottom:20px;
    }
    input,button{
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
    button:hover{background:#0056b3;}
    .success{color:green; text-align:center;}
    .error{color:red; text-align:center;}
    .barcode-box{
        text-align:center;
        margin-top:20px;
        padding:20px;
        border:2px dashed #007BFF;
        font-size:18px;
        font-weight:bold;
        border-radius:10px;
    }
    a{color:#007BFF; text-decoration:none;}
    a:hover{color:#0056b3;}
    </style>
</head>
<body>

<div class="container">

    <h2>Instructor Dashboard - Register Students</h2>

    <?php if($error) echo "<p class='error'>$error</p>"; ?>
    <?php if($success) echo "<p class='success'>$success</p>"; ?>

    <?php if($generated_barcode): ?>
        <div class="barcode-box">
            Generated Barcode:<br><b><?php echo $generated_barcode; ?></b>
        </div>
        <p style="margin-top:20px;"><a href="student_login.php">Go to Student Login</a></p>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST">
            <input type="text" name="full_name" placeholder="Student Full Name" required>
            <input type="text" name="id_number" placeholder="Student ID Number" required>
            <button type="submit" name="register_student">Register Student</button>
        </form>
    </div>

    <p><a href="index.php?action=login">Logout</a></p>

</div>

</body>
</html>