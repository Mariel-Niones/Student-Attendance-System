<?php
session_start();
require(__DIR__ . '/../db.php');

// Restrict to instructors
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}

$instructor_name = $_SESSION['full_name'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name']);

    if (empty($class_name)) {
        $error = "Class name cannot be empty.";
    } else {
        // Insert class
        $stmt = $conn->prepare("INSERT INTO classes (class_name, instructor_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $class_name, $instructor_name);

        if ($stmt->execute()) {
            $success = "Class created successfully!";
        } else {
            $error = "Failed to create class.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Class</title>
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
            margin-bottom:20px;
        }
        input{
            width:100%;
            padding:10px;
            margin-bottom:15px;
            border-radius:5px;
            border:1px solid #ccc;
        }
        .btn{
            width:100%;
            padding:10px;
            background:#007BFF;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
        }
        .btn:hover{
            background:#0056b3;
        }
        .message{
            margin-bottom:10px;
            font-weight:bold;
            color:green;
        }
        .error{
            margin-bottom:10px;
            color:red;
            font-weight:bold;
        }
        .back-container{
            margin-top:20px;
        }
        .back-btn{
            width:100%;
            padding:10px;
            background:#28a745;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
        }
        .back-btn:hover{
            background:#1e7e34;
        }
    </style>

    <!-- Auto redirect after success -->
    <?php if ($success): ?>
        <script>
            setTimeout(function(){
                window.location.href = "instructor_dashboard.php";
            }, 1500);
        </script>
    <?php endif; ?>
</head>
<body>

<div class="container">
    <h2>Create New Class</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="class_name" placeholder="Enter class name" required>
        <button type="submit" class="btn">Create Class</button>
    </form>

    <!-- Back Button -->
    <div class="back-container">
        <button class="back-btn" onclick="window.location.href='instructor_dashboard.php'">
            ← Back to Dashboard
        </button>
    </div>
</div>

</body>
</html>