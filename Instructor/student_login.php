<?php
// student_login.php

// Database connection (update with your credentials)
$host = "localhost";
$user = "root";
$password = "";
$dbname = "attendance_system";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle AJAX request from scanner
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['barcode'])) {
    $barcode = $conn->real_escape_string($_POST['barcode']);

    // Check if student exists
    $sql = "SELECT * FROM students WHERE barcode = '$barcode'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $student_id = $student['id'];
        $date = date("Y-m-d");

        // Prevent duplicate attendance
        $check = "SELECT * FROM attendance WHERE student_id='$student_id' AND date='$date'";
        $checkResult = $conn->query($check);

        if ($checkResult->num_rows == 0) {
            $insert = "INSERT INTO attendance (student_id, date, time) VALUES ('$student_id', '$date', NOW())";
            $conn->query($insert);
            $message = "✅ Attendance marked for " . htmlspecialchars($student['name']);
        } else {
            $message = "⚠️ Attendance already marked today for " . htmlspecialchars($student['name']);
        }
    } else {
        $message = "❌ Invalid barcode!";
    }

    // Return JSON response
    echo json_encode(['message' => $message]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Attendance Scanner</title>
    <!-- Include Html5Qrcode library from CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 30px; }
        #scanner { width: 500px; margin: auto; }
        .message { font-size: 20px; margin-top: 20px; color: green; }
    </style>
</head>
<body>
    <h1>Student Attendance Scanner</h1>
    <div id="scanner"></div>
    <div class="message" id="message"></div>

    <script>
        const messageEl = document.getElementById('message');

        // Function to send scanned barcode to PHP via AJAX
        function markAttendance(barcode) {
            fetch('student_login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'barcode=' + encodeURIComponent(barcode)
            })
            .then(response => response.json())
            .then(data => {
                messageEl.innerText = data.message;
            });
        }

        // Called when scanner successfully reads a barcode
        function onScanSuccess(decodedText, decodedResult) {
            markAttendance(decodedText);

            // Optional: pause scanning for 3 seconds to prevent duplicate scans
            html5QrcodeScanner.clear().then(() => {
                setTimeout(() => {
                    startScanner();
                }, 3000);
            });
        }

        let html5QrcodeScanner;

        function startScanner() {
            html5QrcodeScanner = new Html5Qrcode("scanner");
            html5QrcodeScanner.start(
                { facingMode: "environment" },  // Use back camera
                { fps: 10, qrbox: 250 },       // scanning settings
                onScanSuccess
            ).catch(err => {
                console.error("Camera start failed: ", err);
                messageEl.innerText = "❌ Cannot access camera. Please allow camera access.";
            });
        }

        startScanner();
    </script>
</body>
</html>