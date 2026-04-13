<?php
// student_login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Attendance Scanner</title>

<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

<style>
body {
    margin: 0;
    background: black;
    color: white;
    font-family: Arial, sans-serif;
    text-align: center;
}

#scanner-container {
    width: 100%;
    height: 100vh;
    position: relative;
    overflow: hidden;
}

.overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 80%;
    height: 200px;
    transform: translate(-50%, -50%);
    border: 3px solid lime;
    z-index: 10;
}

#status {
    position: absolute;
    bottom: 80px;
    width: 100%;
    font-size: 22px;
    z-index: 20;
}

#student-name {
    position: absolute;
    bottom: 40px;
    width: 100%;
    font-size: 20px;
    color: #00ffcc;
    z-index: 20;
}

video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
}
</style>
</head>

<body>

<div id="scanner-container">
    <div class="overlay"></div>
    <div id="status">Initializing camera...</div>
    <div id="student-name"></div>
</div>

<script>

let lastScan = null;
let scanCooldown = false;

// Get class_id from URL
const urlParams = new URLSearchParams(window.location.search);
const classId = urlParams.get("class_id");

// ============================
// CHECK CAMERA DEVICES (DEBUG HELP)
// ============================
navigator.mediaDevices.enumerateDevices()
.then(devices => {
    console.log("Devices:", devices);
});

// ============================
// START QUAGGA SCANNER
// ============================
Quagga.init({
    inputStream: {
        name: "Live",
        type: "LiveStream",
        target: document.querySelector("#scanner-container"),
        constraints: {
            width: 1280,
            height: 720,
            facingMode: "environment"
        }
    },

    decoder: {
        readers: [
            "code_128_reader"   // ✅ ONLY THIS (important for your barcode)
        ]
    },

    locate: true,
    frequency: 5
}, function(err) {

    if (err) {
        console.error(err);
        document.getElementById("status").innerText =
            "❌ Camera error or permission denied";
        return;
    }

    Quagga.start();
    document.getElementById("status").innerText =
        "📷 Scan student barcode...";
});

// ============================
// ON BARCODE DETECTED
// ============================
Quagga.onDetected(function(result) {

    let code = result.codeResult.code;

    if (scanCooldown || code === lastScan) return;

    lastScan = code;
    scanCooldown = true;

    document.getElementById("status").innerText =
        "⏳ Recording attendance...";
    document.getElementById("student-name").innerText = "";

    fetch("mark_attendance.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "student_id=" + encodeURIComponent(code) +
              "&class_id=" + encodeURIComponent(classId)
    })
    .then(res => res.json())
    .then(data => {

        if (data.success === true) {
            document.getElementById("status").innerText =
                "✅ Attendance Recorded";

            document.getElementById("student-name").innerText =
                "👤 " + (data.name || "Student Found");
        } else {
            document.getElementById("status").innerText =
                "❌ " + data.message;
        }
    })
    .catch(() => {
        document.getElementById("status").innerText =
            "❌ Server error";
    });

    setTimeout(() => {
        scanCooldown = false;
    }, 3000);
});

</script>

</body>
</html>