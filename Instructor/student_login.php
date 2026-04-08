<?php
session_start();
include '../db.php'; // DB connection

// Only allow instructors
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}

// Check class_id
if (!isset($_GET['class_id'])) die("Class not specified.");
$class_id = intval($_GET['class_id']);

// Handle AJAX POST for barcode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['barcode'])) {
    $barcode = $conn->real_escape_string($_POST['barcode']);

    // Find student in class
    $stmt = $conn->prepare("SELECT * FROM students WHERE barcode=? AND class_id=?");
    $stmt->bind_param("si", $barcode, $class_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($student) {
        $student_id = $student['id'];
        $date = date("Y-m-d");

        // Check duplicate attendance
        $check = $conn->prepare("SELECT * FROM attendance WHERE student_id=? AND date=?");
        $check->bind_param("is", $student_id, $date);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO attendance (student_id, class_id, date, time) VALUES (?, ?, ?, NOW())");
            $insert->bind_param("iis", $student_id, $class_id, $date);
            $insert->execute();
            $message = "✅ Attendance marked for " . htmlspecialchars($student['student_name']);
            $insert->close();
        } else {
            $message = "⚠️ Attendance already marked today for " . htmlspecialchars($student['student_name']);
        }
        $check->close();
    } else {
        $message = "❌ Invalid barcode!";
    }

    echo json_encode(['message' => $message]);
    exit;
}

// Get class name
$stmt = $conn->prepare("SELECT class_name FROM classes WHERE id=?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$stmt->bind_result($class_name);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Attendance Scanner</title>
<script src="js/html5-qrcode.min.js"></script>
<style>
body { font-family: Arial; text-align: center; padding: 30px; background: lightskyblue; }
#scanner { width: 400px; height: 300px; margin: auto; border: 2px solid #ccc; border-radius: 8px; }
#message { font-size: 18px; margin-top: 20px; min-height: 30px; color: green; }
h1, h2 { margin-bottom: 15px; }
.back-btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007BFF; color: white; text-decoration: none; border-radius: 5px; }
.back-btn:hover { background: #0056b3; }
</style>
</head>
<body>

<h1>Attendance Scanner</h1>
<h2><?php echo htmlspecialchars($class_name); ?></h2>

<div id="scanner">Loading camera...</div>
<div id="message"></div>
<a class="back-btn" href="instructor_dashboard.php">← Back to Dashboard</a>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const scannerEl = document.getElementById("scanner");
    const messageEl = document.getElementById("message");

    function markAttendance(barcode) {
        fetch('student_login.php?class_id=<?php echo $class_id; ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'barcode=' + encodeURIComponent(barcode)
        })
        .then(r => r.json())
        .then(data => { messageEl.innerText = data.message; });
    }

    const html5QrCode = new Html5Qrcode("scanner");

    // Explicitly get camera list and start the first one
    Html5Qrcode.getCameras().then(cameras => {
        if (cameras && cameras.length) {
            const cameraId = cameras[0].id;
            html5QrCode.start(
                { deviceId: { exact: cameraId } },
                { fps: 10, qrbox: 250 },
                decodedText => {
                    markAttendance(decodedText);
                    html5QrCode.pause(true);
                    setTimeout(() => html5QrCode.resume(), 2000);
                },
                errorMessage => {
                    // ignore scan errors
                }
            ).catch(err => {
                scannerEl.innerText = "❌ Cannot start camera: " + err;
                console.error(err);
            });
        } else {
            scannerEl.innerText = "❌ No camera found";
        }
    }).catch(err => {
        scannerEl.innerText = "❌ Camera access error: " + err;
        console.error(err);
    });
});
</script>

</body>
</html>