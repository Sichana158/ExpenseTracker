<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "expense_tracker";

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
