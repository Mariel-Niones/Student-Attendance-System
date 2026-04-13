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
        }

        .overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 80%;
            height: 200px;
            transform: translate(-50%, -50%);
            border: 3px solid lime;
        }

        #status {
            position: absolute;
            bottom: 80px;
            width: 100%;
            font-size: 22px;
        }

        #student-name {
            position: absolute;
            bottom: 40px;
            width: 100%;
            font-size: 20px;
            color: #00ffcc;
        }
    </style>
</head>
<body>

<div id="scanner-container">
    <div class="overlay"></div>
    <div id="status">Scan Student Barcode...</div>
    <div id="student-name"></div>
</div>

<script>
    let lastScan = null;
    let scanCooldown = false;

    Quagga.init({
        inputStream: {
            type: "LiveStream",
            target: document.querySelector('#scanner-container'),
            constraints: {
                facingMode: "environment"
            }
        },
        decoder: {
            readers: ["code_128_reader", "ean_reader"]
        }
    }, function(err) {
        if (err) {
            console.error(err);
            alert("Camera error");
            return;
        }
        Quagga.start();
    });

    Quagga.onDetected(function(result) {
        let code = result.codeResult.code;

        if (scanCooldown || code === lastScan) return;

        lastScan = code;
        scanCooldown = true;

        document.getElementById("status").innerText = "Processing...";
        document.getElementById("student-name").innerText = "";

        fetch("mark_attendance.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "student_id=" + encodeURIComponent(code)
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById("status").innerText = data.message;
            document.getElementById("student-name").innerText = data.name || "";
        })
        .catch(() => {
            document.getElementById("status").innerText = "Error processing scan";
        });

        // Allow next scan after 3 seconds
        setTimeout(() => {
            scanCooldown = false;
        }, 3000);
    });
</script>

</body>
</html>