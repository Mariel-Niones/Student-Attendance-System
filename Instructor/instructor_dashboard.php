<?php
session_start();
require(__DIR__ . '/../db.php'); // go up to root for db.php

// Only allow logged-in instructors
if(!isset($_SESSION['full_name']) || $_SESSION['role'] !== 'instructor'){
    header("Location: ../index.php?action=login");
    exit();
}

$instructor_name = $_SESSION['full_name'];
$error = '';
$success = '';

// Handle class deletion
if(isset($_GET['delete_class'])){
    $class_id = intval($_GET['delete_class']);
    $stmt = $conn->prepare("DELETE FROM classes WHERE id=? AND instructor_name=?");
    $stmt->bind_param("is", $class_id, $instructor_name);
    if($stmt->execute()){
        $success = "Class deleted successfully!";
    } else {
        $error = "Failed to delete class.";
    }
    $stmt->close();
}

// Fetch all classes for this instructor
$stmt = $conn->prepare("SELECT * FROM classes WHERE instructor_name=? ORDER BY created_at DESC");
$stmt->bind_param("s", $instructor_name);
$stmt->execute();
$result = $stmt->get_result();
$classes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Dashboard</title>
    <style>
        body{
            font-family: Arial;
            background-color: lightskyblue;
            padding: 20px;
        }
        .container{
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        h2{text-align: center;}
        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td{
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        a.button{
            padding: 5px 10px;
            margin: 2px;
            background: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        a.button:hover{
            background: #0056b3;
        }
        .error{color:red; text-align:center;}
        .success{color:green; text-align:center;}
        .top-bar{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .view-btn{
    padding: 5px 10px;
    margin: 2px;
    background: #28a745; /* light green */
    color: white;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
}

.view-btn:hover{
    background: #1e7e34; /* darker green on hover */
}
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <h2>Welcome, <?php echo htmlspecialchars($instructor_name); ?></h2>
            <a class="button" href="create_class.php">Add New Class</a>
        </div>

        <?php
            if(!empty($error)) echo "<p class='error'>$error</p>";
            if(!empty($success)) echo "<p class='success'>$success</p>";
        ?>

        <h3>Your Classes</h3>
        <table>
            <tr>
                <th>Class Name</th>
                <th>Actions</th>
            </tr>
            <?php if(count($classes) === 0): ?>
                <tr><td colspan="2">No classes created yet.</td></tr>
            <?php else: ?>
                <?php foreach($classes as $class): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                        <td>
                            <a class="button" href="add_student.php?class_id=<?php echo $class['id']; ?>">Add Student</a>
                            <a class="button" href="edit_student.php?class_id=<?php echo $class['id']; ?>">Edit Student</a>
                            <a class="button" href="remove_student.php?class_id=<?php echo $class['id']; ?>">Remove Student</a>
                            <a class="button" href="edit_class.php?class_id=<?php echo $class['id']; ?>">Edit Class</a>
                            <a class="button" href="?delete_class=<?php echo $class['id']; ?>" onclick="return confirm('Are you sure you want to delete this class?')">Delete Class</a>
                            <a class="view-btn" href="view_students.php?class_id=<?php echo $class['id']; ?>">View Students</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>