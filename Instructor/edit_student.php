<?php
session_start();
include '../db.php';

// Security
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}

// Validate student_id
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    die("Invalid student specified.");
}

$student_id = intval($_GET['student_id']);

if ($student_id <= 0) {
    die("Invalid student ID.");
}

// Fetch student
$stmt = $conn->prepare("
    SELECT s.*, c.class_name 
    FROM students s
    JOIN classes c ON s.class_id = c.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student not found.");
}

$student = $result->fetch_assoc();

// Update student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = $_POST['student_name'];
    $id_number = $_POST['id_number'];
    $student_code = $_POST['student_code'];

    $update = $conn->prepare("
        UPDATE students 
        SET student_name = ?, id_number = ?, student_code = ?
        WHERE id = ?
    ");
    $update->bind_param("sssi", $student_name, $id_number, $student_code, $student_id);

    if ($update->execute()) {
        header("Location: view_students.php?class_id=" . $student['class_id']);
        exit();
    } else {
        echo "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
</head>
<body>

<h2>Edit Student</h2>

<p><strong>Class:</strong> <?php echo htmlspecialchars($student['class_name']); ?></p>

<form method="POST">
    <label>Full Name</label><br>
    <input type="text" name="student_name" value="<?php echo htmlspecialchars($student['student_name']); ?>" required><br><br>

    <label>ID Number</label><br>
    <input type="text" name="id_number" value="<?php echo htmlspecialchars($student['id_number']); ?>" required><br><br>

    <label>Student Code</label><br>
    <input type="text" name="student_code" value="<?php echo htmlspecialchars($student['student_code']); ?>" required><br><br>

    <button type="submit">Update Student</button>
</form>

<br>
<a href="view_students.php?class_id=<?php echo $student['class_id']; ?>">← Back</a>

</body>
</html>