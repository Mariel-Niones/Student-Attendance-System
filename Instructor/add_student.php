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
$student_registered = false;

// ============================
// BARCODE FUNCTION (FIXED)
// ============================
function generateBarcode($code) {
    return "https://barcode.tec-it.com/barcode.ashx?data="
        . urlencode($code)
        . "&code=Code128&translate-esc=false";
}

// ============================
// REGISTER STUDENT
// ============================
if (isset($_POST['register_student'])) {

    $student_name = trim($_POST['student_name']);
    $id_number = trim($_POST['id_number']);

    if (!empty($student_name) && !empty($id_number)) {

        // ✅ UNIQUE STUDENT CODE
        $student_code = "STU-" . strtoupper(substr(md5($id_number . time()), 0, 8));

        $stmt = $conn->prepare("
            INSERT INTO students (student_name, id_number, student_code, class_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("sssi", $student_name, $id_number, $student_code, $class_id);

        if ($stmt->execute()) {

            // ✅ FIX: barcode must use student_code
            $barcode_image = generateBarcode($student_code);

            $message = "Student registered successfully!";
            $student_registered = true;

        } else {
            $message = "Error adding student. Maybe ID already exists.";
        }

        $stmt->close();

    } else {
        $message = "All fields are required!";
    }
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
        }

        button:hover{
            background:#0056b3;
        }

        .message{
            margin-bottom:10px;
            color:green;
            font-weight:bold;
        }

        .barcode{
            margin-top:20px;
        }

        img{
            margin-top:10px;
            width:250px;
        }

        .back-container{
            margin-top:30px;
        }

        .back{
            display:inline-block;
            padding:10px 15px;
            background:#007BFF;
            color:white;
            text-decoration:none;
            border-radius:5px;
        }

        .back:hover{
            background:#0056b3;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="class-name">
        <?php echo htmlspecialchars($class_name); ?>
    </div>

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
            <p>Barcode for student</p>
            <img src="<?php echo $barcode_image; ?>" alt="Barcode">
        </div>
    <?php endif; ?>

    <div class="back-container">
        <a class="back" href="instructor_dashboard.php?class_id=<?php echo $class_id; ?>">
            ← Back to Dashboard
        </a>
    </div>

</div>

</body>
</html>