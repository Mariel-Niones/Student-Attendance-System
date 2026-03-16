<?php
session_start();
include 'db.php';

if(isset($_POST['login'])){
    $barcode = trim($_POST['barcode']);

    $stmt = $conn->prepare("SELECT * FROM students WHERE barcode=?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $student = $result->fetch_assoc();
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['full_name'] = $student['full_name'];
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = "Invalid Barcode!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <style>
    body{
        font-family:Arial;
        background:#f2f2f2;
        display:flex;
        justify-content:center;
        align-items:center;
        height:100vh;
    }
    .container{
        width:400px;
        background:white;
        padding:30px;
        border-radius:10px;
        border:3px solid #007BFF;
        box-shadow:0 0 20px rgba(0,0,0,0.3);
        text-align:center;
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
    button:hover{
        background:#0056b3;
    }
    .error{color:red;}
    </style>
</head>
<body>

<div class="container">
<h2>Student Login</h2>
<form method="POST">
    <input type="text" name="barcode" placeholder="Scan Barcode Here" autofocus required>
    <button type="submit" name="login">Login</button>
</form>
<?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
</div>

</body>
</html>