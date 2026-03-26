<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}

// GET CLASS ID
if (!isset($_GET['class_id'])) {
    die("Class not specified.");
}
$class_id = intval($_GET['class_id']);

// FETCH CLASS NAME
$stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class_result = $stmt->get_result();
if ($class_result->num_rows === 0) {
    die("Class not found.");
}
$class = $class_result->fetch_assoc();
$class_name = $class['class_name'];

$message = '';
$barcode_image = '';
$student_registered = false; // flag to control form visibility

// REGISTER STUDENT
if (isset($_POST['register_student'])) {
    $student_name = trim($_POST['student_name']);
    $id_number = trim($_POST['id_number']);

    if (!empty($student_name) && !empty($id_number)) {

        $student_code = strtoupper(substr($student_name,0,3)) . '-' . substr($id_number, -4);

        $stmt = $conn->prepare("INSERT INTO students (student_name, id_number, student_code, class_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $student_name, $id_number, $student_code, $class_id);

        if ($stmt->execute()) {
            // Generate barcode PNG in memory using GD
            $barcode_image = generateBarcode($id_number);
            $message = "Student registered successfully!";
            $student_registered = true; // hide the form
        } else {
            $message = "Error adding student. Maybe this ID already exists.";
        }

        $stmt->close();
    } else {
        $message = "All fields are required!";
    }
}

// -------------------------
// Function to generate barcode
// -------------------------
function generateBarcode($code) {
    $barWidth = 2;
    $height = 60;

    $patterns = [
        '0'=>"101001101101",'1'=>"110100101011",'2'=>"101100101011",
        '3'=>"110110010101",'4'=>"101001101011",'5'=>"110100110101",
        '6'=>"101100110101",'7'=>"101001011011",'8'=>"110100101101",
        '9'=>"101100101101",'A'=>"110101001011",'B'=>"101101001011",
        'C'=>"110110100101",'D'=>"101011001011",'E'=>"110101100101",
        'F'=>"101101100101",'G'=>"101010011011",'H'=>"110101001101",
        'I'=>"101101001101",'J'=>"101011001101",'K'=>"110101010011",
        'L'=>"101101010011",'M'=>"110110101001",'N'=>"101011010011",
        'O'=>"110101101001",'P'=>"101101101001",'Q'=>"101010110011",
        'R'=>"110101011001",'S'=>"101101011001",'T'=>"101011011001",
        'U'=>"110010101011",'V'=>"100110101011",'W'=>"110011010101",
        'X'=>"100101101011",'Y'=>"110010110101",'Z'=>"100110110101",
        '-'=>"100101011011",'.'=>"110010101101",' '=>"100110101101",
        '*'=>"100101101101" // start/stop
    ];

    $text = "*" . strtoupper($code) . "*";
    $width = strlen($text) * 13 * $barWidth;
    $image = imagecreate($width, $height);

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    $x = 0;

    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];

        if (!isset($patterns[$char])) continue;

        $pattern = $patterns[$char];

        for ($j = 0; $j < strlen($pattern); $j++) {
            $color = ($pattern[$j] == '1') ? $black : $white;
            imagefilledrectangle($image, $x, 0, $x + $barWidth, $height, $color);
            $x += $barWidth;
        }

        $x += $barWidth; // space between characters
    }

    // Capture the PNG output as base64 to display inline
    ob_start();
    imagepng($image);
    $png_data = ob_get_clean();
    imagedestroy($image);

    return 'data:image/png;base64,' . base64_encode($png_data);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student - <?php echo htmlspecialchars($class_name); ?></title>
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
            margin-bottom:10px;
        }
        .class-name{
            color:#007BFF;
            margin-bottom:20px;
            font-weight:bold;
        }
        input{
            width:100%;
            padding:10px;
            margin:10px 0;
            border-radius:5px;
            border:1px solid #ccc;
        }
        button{
            width:100%;
            padding:10px;
            background:#007BFF;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-size:16px;
        }
        button:hover{
            background:#0056b3;
        }
        .message{
            margin-bottom:10px;
            color:green;
            font-weight:bold;
        }
        .back{
            display:block;
            margin-top:20px;
            text-decoration:none;
            color:#007BFF;
            font-weight:bold;
        }
        .back:hover{
            text-decoration:underline;
        }
        .barcode{
            margin-top:20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="class-name"><?php echo htmlspecialchars($class_name); ?></div>

    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (!$student_registered): ?>
        <form method="POST">
            <input type="text" name="student_name" placeholder="Full Name" required>
            <input type="text" name="id_number" placeholder="ID Number" required>
            <button type="submit" name="register_student">Add Student</button>
        </form>
    <?php endif; ?>

    <?php if ($student_registered && $barcode_image): ?>
        <div class="barcode">
            <p>Barcode for: <?php echo htmlspecialchars($student_name); ?></p>
            <img src="<?php echo $barcode_image; ?>" alt="Barcode">
        </div>
    <?php endif; ?>

    <a class="back" href="instructor_dashboard.php?class_id=<?php echo $class_id; ?>">← Back to Dashboard</a>
</div>

</body>
</html>