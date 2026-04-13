<?php
require(__DIR__ . '/../db.php');

header('Content-Type: application/json');

if (!isset($_POST['student_id'], $_POST['class_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing data'
    ]);
    exit();
}

$student_code = trim($_POST['student_id']);
$class_id = intval($_POST['class_id']);

// FIND STUDENT
$stmt = $conn->prepare("
    SELECT id, student_name 
    FROM students 
    WHERE student_code = ? AND class_id = ?
");
$stmt->bind_param("si", $student_code, $class_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Student not found'
    ]);
    exit();
}

$student = $result->fetch_assoc();
$student_id = $student['id'];

// CHECK DUPLICATE TODAY
$today = date('Y-m-d');

$stmt = $conn->prepare("
    SELECT id FROM attendance 
    WHERE student_id = ? 
    AND class_id = ? 
    AND DATE(date_time) = ?
");
$stmt->bind_param("iis", $student_id, $class_id, $today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Already recorded today',
        'name' => $student['student_name']
    ]);
    exit();
}

// INSERT ATTENDANCE
$status = "Present";
$date_time = date('Y-m-d H:i:s');

$stmt = $conn->prepare("
    INSERT INTO attendance (student_id, class_id, status, date_time)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiss", $student_id, $class_id, $status, $date_time);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Attendance recorded',
        'name' => $student['student_name']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
?>