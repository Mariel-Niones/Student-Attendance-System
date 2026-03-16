<?php
session_start();
include 'db.php';

if(isset($_POST['login'])){

    $barcode = trim($_POST['barcode']);
    $photo = $_POST['photo'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE barcode=?");
    $stmt->bind_param("s",$barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $student = $result->fetch_assoc();

        // Save captured photo
        if(!empty($photo)){
            $photo = str_replace('data:image/png;base64,','',$photo);
            $photo = str_replace(' ','+',$photo);
            $data = base64_decode($photo);

            $filename = "photos/".$barcode."_".time().".png";
            file_put_contents($filename,$data);
        }

        $_SESSION['student_id'] = $student['id'];
        $_SESSION['full_name'] = $student['full_name'];

        header("Location: student_dashboard.php");
        exit();

    }else{
        $error = "Invalid Barcode!";
    }

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Barcode Login</title>

<style>

body{
font-family:Arial;
background:#f4f4f4;
height:100vh;
margin:0;
display:flex;
justify-content:center;
align-items:center;
}

.login-box{
background:white;
padding:30px;
border-radius:8px;
box-shadow:0 0 10px rgba(0,0,0,0.2);
width:350px;
text-align:center;
}

input{
width:100%;
padding:12px;
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
border-radius:4px;
cursor:pointer;
}

button:hover{
background:#0056b3;
}

video{
width:100%;
border-radius:6px;
margin-top:10px;
}

.error{
color:red;
}

</style>

</head>

<body>

<div class="login-box">

<h2>Student Login</h2>

<?php
if(isset($error)){
echo "<p class='error'>$error</p>";
}
?>

<form method="POST">

<input type="text" name="barcode" placeholder="Scan Barcode Here" autofocus required>

<video id="video" autoplay></video>

<button type="button" onclick="capturePhoto()">Capture Photo</button>

<canvas id="canvas" style="display:none;"></canvas>
<input type="hidden" name="photo" id="photo">

<button type="submit" name="login">Login</button>

</form>

</div>

<script>

// open webcam
navigator.mediaDevices.getUserMedia({video:true})
.then(stream=>{
document.getElementById("video").srcObject = stream;
});

// capture image
function capturePhoto(){

let video=document.getElementById("video");
let canvas=document.getElementById("canvas");

canvas.width=300;
canvas.height=200;

canvas.getContext('2d').drawImage(video,0,0,300,200);

let image=canvas.toDataURL("image/png");

document.getElementById("photo").value=image;

alert("Photo captured successfully");

}

</script>

</body>
</html>