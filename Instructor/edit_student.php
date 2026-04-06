<?php
session_start();
include '../db.php';

// Security check: only instructors
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../index.php?action=login");
    exit();
}

// Get class ID
if (!isset($_GET['class_id'])) {
    die("Class not specified.");
}
$class_id = intval($_GET['class_id']);

// Fetch class name
$stmt = $conn->prepare("SELECT class_name FROM classes WHERE id=?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class_result = $stmt->get_result();
if ($class_result->num_rows === 0) {
    die("Class not found.");
}
$class = $class_result->fetch_assoc();
$class_name = $class['class_name'];

$message = '';
$edit_student = null;

// Handle student update
if (isset($_POST['update_student'])) {
    $student_id = intval($_POST['student_id']);
    $student_name = trim($_POST['student_name']);
    $id_number = trim($_POST['id_number']);
    $student_code = strtoupper(substr($student_name, 0, 3)) . '-' . substr($id_number, -4);

    if (!empty($student_name) && !empty($id_number)) {
        $stmt = $conn->prepare("UPDATE students SET student_name=?, id_number=?, student_code=? WHERE id=? AND class_id=?");
        $stmt->bind_param("sssii", $student_name, $id_number, $student_code, $student_id, $class_id);
        if ($stmt->execute()) {
            $message = "Student updated successfully!";
        } else {
            $message = "Error updating student. Maybe ID already exists.";
        }
        $stmt->close();
    } else {
        $message = "All fields are required!";
    }
}

// Load student data for editing if edit_student_id is set
if (isset($_GET['edit_student_id'])) {
    $edit_student_id = intval($_GET['edit_student_id']);
    $stmt = $conn->prepare("SELECT * FROM students WHERE id=? AND class_id=?");
    $stmt->bind_param("ii", $edit_student_id, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_student = $result->fetch_assoc();
    } else {
        $message = "Student not found.";
    }
    $stmt->close();
}

// Fetch all students in the class
$stmt = $conn->prepare("SELECT * FROM students WHERE class_id=? ORDER BY student_name");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$students = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Students - <?php echo htmlspecialchars($class_name); ?></title>
    <style>
        body { font-family: Arial; background: lightskyblue; padding:30px; }
        .container { background:white; padding:30px; border-radius:10px; max-width:900px; margin:auto; }
        h2 { text-align:center; }
        table { width:100%; border-collapse: collapse; margin-top:20px; }
        th, td { border:1px solid #ddd; padding:8px; text-align:center; }
        th { background:#007BFF; color: lightskyblue; }
        a.button { text-decoration:none; padding:5px 10px; background:#007BFF; color:white; border-radius:5px; }
        a.button:hover { background:#0056b3; }
        .message { color: green; font-weight:bold; margin-top:10px; text-align:center; }
        .edit-form { margin-top:20px; background:#f1f1f1; padding:20px; border-radius:10px; max-width:500px; margin:auto; }
        .edit-form input { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
        .edit-form button { width:100%; padding:10px; background:#007BFF; color:white; border:none; border-radius:5px; cursor:pointer; font-size:16px; }
        .edit-form button:hover { background:#0056b3; }
        .back { display:block; margin-top:20px; text-align:center; text-decoration:none; font-weight:bold; color:#007BFF; }
        .back:hover { text-decoration:underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Students - <?php echo htmlspecialchars($class_name); ?></h2>

    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($edit_student): ?>
        <!-- Edit Student Form -->
        <div class="edit-form">
            <h3>Edit Student</h3>
            <form method="POST">
                <input type="hidden" name="student_id" value="<?php echo $edit_student['id']; ?>">
                <input type="text" name="student_name" value="<?php echo htmlspecialchars($edit_student['student_name']); ?>" placeholder="Full Name" required>
                <input type="text" name="id_number" value="<?php echo htmlspecialchars($edit_student['id_number']); ?>" placeholder="ID Number" required>
                <button type="submit" name="update_student">Update Student</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Students List -->
    <table>
        <tr>
            <th>Full Name</th>
            <th>ID Number</th>
            <th>Student Code</th>
            <th>Action</th>
        </tr>
        <?php if (!empty($students)): ?>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['id_number']); ?></td>
                    <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                    <td>
                        <a class="button" href="edit_student.php?class_id=<?php echo $class_id; ?>&edit_student_id=<?php echo $student['id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No students found.</td></tr>
        <?php endif; ?>
    </table>

    <a class="back" href="instructor_dashboard.php?class_id=<?php echo $class_id; ?>">← Back to Dashboard</a>
</div>

</body>
</html>