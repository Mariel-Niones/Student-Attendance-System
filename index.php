<?php
session_start();
include 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'register';

// =======================
// REGISTRATION
// =======================
if(isset($_POST['register'])){

    $full_name = trim($_POST['full_name']);
    $password = $_POST['password']; // No hashing
    $role = $_POST['role'];

    if($role === 'instructor' || $role === 'admin'){

        $table = ($role === 'instructor') ? "instructors" : "admins";

        $stmt = $conn->prepare("INSERT INTO $table (full_name,password) VALUES (?,?)");
        $stmt->bind_param("ss",$full_name,$password);

        if($stmt->execute()){
            $_SESSION['success_message'] = "Registration successful! Please login.";
            header("Location: index.php?action=login");
            exit();
        }else{
            $error = "Registration failed!";
        }

        $stmt->close();
    }
}

// =======================
// LOGIN
// =======================
if(isset($_POST['login'])){

    $role = $_POST['role'];
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];

    $table = ($role === 'instructor') ? "instructors" : "admins";

    $stmt = $conn->prepare("SELECT * FROM $table WHERE full_name=?");
    $stmt->bind_param("s",$full_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $user = $result->fetch_assoc();

        // Compare plain text password
        if($password === $user['password']){

            $_SESSION['full_name']=$user['full_name'];
            $_SESSION['role']=$role;

            if($role === 'instructor'){
                // Check if instructor has classes
                $stmt2 = $conn->prepare("SELECT * FROM classes WHERE instructor_name=?");
                $stmt2->bind_param("s",$full_name);
                $stmt2->execute();
                $res2 = $stmt2->get_result();

                if($res2->num_rows == 0){
                    // No classes → redirect to create_class.php
                    header("Location: Instructor/create_class.php");
                    exit();
                } else {
                    // Has classes → redirect to dashboard
                    header("Location: Instructor/instructor_dashboard.php");
                    exit();
                }

            } else {
                // Admin login → redirect to admin dashboard (you can create later)
                header("Location: admin_dashboard.php");
                exit();
            }

        }else{
            $error="Invalid password!";
        }

    }else{
        $error="User not found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Attendance System</title>
<style>
body{
    font-family:Arial;
    margin:0;
    padding:0;
    height:100vh;
    background-image:url('assets/image/jessie.webp');
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
    display:flex;
    justify-content:center;
    align-items:center;
}
.container{
    width:400px;
    background:rgba(255,255,255,0.9);
    padding:30px;
    border-radius:10px;
    box-shadow:0 0 20px rgba(0,0,0,0.3);
}
.form-box{
    border:3px solid #007BFF;
    padding:20px;
    border-radius:10px;
}
input,select,button{
    width:100%;
    padding:10px;
    margin:10px 0;
    border-radius:4px;
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
h2{text-align:center;}
.error{color:red;text-align:center;}
.success{color:green;text-align:center;}
.text-center{ text-align:center; margin-top:10px; }
.text-center a{ color:#007BFF; text-decoration:none; font-weight:bold; }
.text-center a:hover{ text-decoration:underline; }
</style>
</head>
<body>

<div class="container">

<?php
if(isset($error)) echo "<p class='error'>$error</p>";
if(isset($_SESSION['success_message'])){
    echo "<p class='success'>".$_SESSION['success_message']."</p>";
    unset($_SESSION['success_message']);
}
?>

<?php if($action=="register"){ ?>
<div class="form-box">
<h2>Student Attendance System</h2>
<h2>Register</h2>
<form method="POST">
<input type="text" name="full_name" placeholder="Full Name" required>
<input type="password" name="password" placeholder="Password" required>
<select name="role" required>
<option value="">Select Role</option>
<option value="instructor">Instructor</option>
<option value="admin">Administrator</option>
</select>
<button type="submit" name="register">Register</button>
</form>
<p class="text-center">Already registered? <a href="index.php?action=login">Login here</a></p>
</div>
<?php } ?>

<?php if($action=="login"){ ?>
<div class="form-box">
<h2>Login</h2>
<form method="POST">
<select name="role" required>
<option value="">Select Role</option>
<option value="instructor">Instructor</option>
<option value="admin">Administrator</option>
</select>
<input type="text" name="full_name" placeholder="Full Name" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit" name="login">Login</button>
</form>
</div>
<?php } ?>

</div>
</body>
</html>