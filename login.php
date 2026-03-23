<?php
session_start();
include 'db.php'; // your database connection

$error = '';

// =======================
// LOGOUT
// =======================
if(isset($_GET['action']) && $_GET['action'] === 'logout'){
    session_destroy();
    header("Location: login.php");
    exit();
}

// =======================
// LOGIN
// =======================
if(isset($_POST['login'])){
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if($role !== 'instructor' && $role !== 'admin'){
        $error = "Invalid role!";
    } else {
        $table = ($role === 'instructor') ? "instructors" : "admins";

        // Check credentials (plain-text password)
        $stmt = $conn->prepare("SELECT * FROM $table WHERE full_name=? AND password=?");
        $stmt->bind_param("ss", $full_name, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $user = $result->fetch_assoc();

            // Set session
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $role;

            // Redirect to the proper dashboard
            if($role === 'instructor'){
                header("Location: Instructor/instructor_dashboard.php");
            } else {
                header("Location: admin_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid username or password!";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { 
            font-family: Arial; 
            height:100vh;
            margin:0; 
            display:flex; 
            justify-content:center; 
            align-items:center;
            background-image: url('assets/image/jessie.webp'); /* your image path */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .container { 
            background: rgba(255,255,255,0.9); /* semi-transparent for readability */
            padding:30px; 
            border-radius:10px; 
            width:400px; 
            border: 3px solid #007BFF; /* blue border */
            box-shadow:0 0 15px rgba(0,0,0,0.3);
        }
        input, select, button { 
            width:100%; 
            padding:10px; 
            margin:10px 0; 
            border-radius:4px; 
            border:1px solid #ccc; 
        }
        button { 
            background:#007BFF; 
            color:white; 
            border:none; 
            cursor:pointer; 
        }
        button:hover { 
            background:#0056b3; 
        }
        h2{text-align:center;}
        .error{color:red;text-align:center;}
    </style>
</head>
<body>
<div class="container">
    <?php
    if(!empty($error)) echo "<p class='error'>$error</p>";
    ?>

    <h2>Login</h2>
    <form method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="text" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="instructor">Instructor</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>