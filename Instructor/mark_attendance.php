<?php
session_start();
require(__DIR__ . '/../db.php'); // adjust path if needed

// Only allow access if class_id and barcode are provided
if(!isset($_POST['class_id'], $_POST['barcode'])){
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Missing class or barcode']);
    exit();
}

$class_id = intval($_POST['class_id']);
$barcode = trim($_POST['barcode']);

// Check if student exists for this class
$stmt = $conn->prepare("SELECT id, student_name FROM students WHERE student_code = ? AND class_id = ?");
$stmt->bind_param("si", $barcode, $class_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(['status'=>'error','message'=>'Student not found in this class']);
    $stmt->close();
    exit();
}

$student = $result->fetch_assoc();
$student_id = $student['id'];

// Check if attendance already marked for today
$today = date('Y-m-d');
$stmt->close();
$stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND class_id = ? AND DATE(date_time) = ?");
$stmt->bind_param("iis", $student_id, $class_id, $today);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    // Already marked today
    echo json_encode(['status'=>'error','message'=>'Attendance already marked for today']);
    $stmt->close();
    exit();
}
$stmt->close();

// Determine attendance status automatically
// For simplicity, mark all scanned students as "Present"
// You can modify this to calculate Late/Absent based on class start time
$status = "Present";
$date_time = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO attendance (student_id, class_id, status, date_time) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $student_id, $class_id, $status, $date_time);

if($stmt->execute()){
    echo json_encode([
        'status'=>'success',
        'message'=>"Attendance marked: $status",
        'student_name'=>$student['student_name'],
        'status_marked'=>$status
    ]);
}else{
    echo json_encode(['status'=>'error','message'=>'Failed to mark attendance']);
}

$stmt->close();
$conn->close();
?>