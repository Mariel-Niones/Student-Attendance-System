<?php
session_start();
include '../db.php'; // Adjust if db.php is in a different folder

// =======================
// SECURITY: Only instructors can access
// =======================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}

$message = '';

// =======================
// CREATE CLASS
// =======================
if(isset($_POST['create_class'])){
    $class_name = trim($_POST['class_name']);

    if(!empty($class_name)){
        // Insert class into classes table
        $stmt = $conn->prepare("INSERT INTO classes (class_name, instructor_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $class_name, $_SESSION['full_name']);

        if($stmt->execute()){
            $message = "Class '$class_name' created successfully!";
        } else {
            $message = "Failed to create class. Maybe it already exists.";
        }

        $stmt->close();
    } else {
        $message = "Class name cannot be empty!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Class</title>
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
    <h2>Create Class</h2>

    <?php if($message): ?>
        <p class="message <?php echo strpos($message,'successfully') !== false ? 'success':'error'; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="class_name" placeholder="Enter class name" required>
        <button type="submit" name="create_class">Create Class</button>
    </form>

    <a href="instructor_dashboard.php" class="back">← Back to Dashboard</a>
</div>

</body>
</html>