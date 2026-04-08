<?php
session_start();
require(__DIR__ . '/../db.php');

// Only allow logged-in instructors
if(!isset($_SESSION['full_name']) || $_SESSION['role'] !== 'instructor'){
    header("Location: ../index.php?action=login");
    exit();
}

$instructor_name = $_SESSION['full_name'];
$error = '';
$success = '';

// Check if class_id is provided
if(!isset($_GET['class_id'])){
    die("Class not specified.");
}

$class_id = intval($_GET['class_id']);

// Verify class belongs to instructor
$stmt = $conn->prepare("SELECT * FROM classes WHERE id=? AND instructor_name=?");
$stmt->bind_param("is", $class_id, $instructor_name);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Unauthorized access.");
}

$class = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $new_name = trim($_POST['class_name']);

    if(empty($new_name)){
        $error = "Class name cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE classes SET class_name=? WHERE id=? AND instructor_name=?");
        $stmt->bind_param("sis", $new_name, $class_id, $instructor_name);

        if($stmt->execute()){
            $success = "Class updated successfully!";
            $class['class_name'] = $new_name;
        } else {
            $error = "Failed to update class.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Class</title>
    <style>
        body{
            font-family: Arial;
            background-color: lightskyblue;
            padding: 20px;
        }
        .container{
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        h2{text-align:center;}
        input[type="text"]{
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn{
            padding: 10px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        .btn:hover{
            background: #0056b3;
        }
        .back-container{
            text-align: center;
            margin-top: 20px;
        }
        .back{
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            background: #007BFF;
            color: white;
            border-radius: 5px;
        }
        .back:hover{
            background: #0056b3;
        }
        .error{color:red; text-align:center;}
        .success{color:green; text-align:center;}
    </style>
</head>
<body>
<div class="container">

    <h2>Edit Class</h2>

    <?php
        if(!empty($error)) echo "<p class='error'>$error</p>";
        if(!empty($success)) echo "<p class='success'>$success</p>";
    ?>

    <form method="POST">
        <label>Class Name</label>
        <input type="text" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>

        <button type="submit" class="btn">Update Class</button>
    </form>

    <!-- Centered Back Button at Bottom -->
    <div class="back-container">
        <a class="back" href="instructor_dashboard.php?class_id=<?php echo $class_id; ?>">
            ← Back to Dashboard
        </a>
    </div>

</div>
</body>
</html>