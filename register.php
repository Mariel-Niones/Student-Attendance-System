<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "attendance_system";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Register process
if (isset($_POST['register'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";

    if ($conn->query($sql) === TRUE) {
        $success = "Registration successful! You can now login.";
    } else {
        $error = "Error: " . $conn->error;
    }
}

$barcode = "STU" . time() . rand(100,999);
?>

<!DOCTYPE html>
<html>
<head>
<title>Register - Student Attendance System</title>

<style>
body{
    font-family: Arial, sans-serif;
    background-image: url('assets/image/jessie.webp');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    height:100vh;
    margin:0;
    display:flex;
    justify-content:center;
    align-items:center;
}

.register-box{
    width:350px;
    padding:30px;
    background:rgba(255,255,255,0.9);
    border-radius:8px;
    border:3px solid #007BFF;
    box-shadow:0 0 15px rgba(0,0,0,0.3);
}

input{
    width:100%;
    padding:10px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:4px;
}

button{
    width:100%;
    padding:10px;
    background:#007BFF;
    border:none;
    color:white;
    font-size:16px;
    cursor:pointer;
    border-radius:4px;
}

button:hover{
    background:#0056b3;
}

h2{
    text-align:center;
}

.message{
    text-align:center;
    margin-bottom:10px;
}

.success{
    color:green;
}

.error{
    color:red;
}

a{
    display:block;
    text-align:center;
    margin-top:10px;
}
</style>

</head>

<body>

<div class="register-box">

<h2>Register</h2>

<?php
if(isset($success)){
    echo "<p class='message success'>$success</p>";
}

if(isset($error)){
    echo "<p class='message error'>$error</p>";
}
?>

<form method="POST">
<input type="text" name="username" placeholder="Enter Username" required>
<input type="password" name="password" placeholder="Enter Password" required>

<button type="submit" name="register">Register</button>
</form>

<a href="index.php">Back to Login</a>

</div>

</body>
</html>

