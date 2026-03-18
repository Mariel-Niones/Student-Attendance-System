<?php
session_start();
include '../db.php'; // adjust path if db.php is in parent folder

// =======================
// SECURITY: Only instructors can access
// =======================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Dashboard</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            margin:0;
            padding:0;
            background:#f0f2f5;
        }

        .header{
            background:#007BFF;
            color:white;
            padding:20px;
            text-align:center;
        }

        .container{
            width:90%;
            max-width:900px;
            margin:30px auto;
        }

        .card{
            background:white;
            padding:25px;
            border-radius:10px;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
        }

        h2{text-align:center;}

        .welcome{
            text-align:center;
            margin-bottom:20px;
            font-size:18px;
        }

        .grid{
            display:grid;
            grid-template-columns: repeat(2, 1fr);
            gap:15px;
        }

        .btn{
            display:block;
            padding:15px;
            text-align:center;
            background:#007BFF;
            color:white;
            text-decoration:none;
            border-radius:8px;
            font-size:16px;
            transition:0.3s;
        }

        .btn:hover{
            background:#0056b3;
        }

        .btn-danger{
            background:#dc3545;
        }

        .btn-danger:hover{
            background:#a71d2a;
        }

        .logout{
            margin-top:20px;
            display:block;
            text-align:center;
            padding:12px;
            background:black;
            color:white;
            border-radius:8px;
            text-decoration:none;
        }

        .logout:hover{
            background:#333;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Instructor Dashboard</h1>
</div>

<div class="container">
    <div class="card">

        <div class="welcome">
            Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong>
        </div>

        <h2>Manage Classes & Students</h2>

        <div class="grid">
            <!-- Buttons linking to functionality -->
            <a href="../create_class.php" class="btn">➕ Create Class</a>
            <a href="../register_student.php" class="btn">👨‍🎓 Register Student</a>
            <a href="../edit_class.php" class="btn">✏️ Edit Class Name</a>
            <a href="../edit_student.php" class="btn">✏️ Edit Student</a>
            <a href="../delete_class.php" class="btn btn-danger">🗑 Delete Class</a>
            <a href="../delete_student.php" class="btn btn-danger">🗑 Delete Student</a>
        </div>

        <a href="../logout.php" class="logout">Logout</a>

    </div>
</div>

</body>
</html>