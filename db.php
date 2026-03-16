<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "attendance_system";

/* Connect to MySQL server first (without database) */
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Create database if it does not exist */
$sql = "CREATE DATABASE IF NOT EXISTS $database";
$conn->query($sql);

/* Select the database */
$conn->select_db($database);

/* ==========================
   CREATE TABLES IF NOT EXIST
   ========================== */

/* Students table */
$conn->query("
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    barcode VARCHAR(100) UNIQUE NOT NULL
)");

/* Instructors table */
$conn->query("
CREATE TABLE IF NOT EXISTS instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
)");

/* Admins table */
$conn->query("
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
)");

/* Attendance table */
$conn->query("
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date DATE,
    time_in TIME,
    FOREIGN KEY (student_id) REFERENCES students(id)
)");

?>