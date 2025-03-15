<?php
session_start();
header("Content-Type: application/json"); // Ensure JSON response
include "../config/database.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed."]);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Email and password are required!"]);
    exit;
}

// Check if user exists
$sql = "SELECT id, Fname, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found!"]);
    exit;
}

$stmt->bind_result($id, $fname, $hashed_password, $role);
$stmt->fetch();

if (!password_verify($password, $hashed_password)) {
    echo json_encode(["status" => "error", "message" => "Incorrect password!"]);
    exit;
}

// Store user session
$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $fname;
$_SESSION['user_role'] = $role;

echo json_encode(["status" => "success", "message" => "Login successful!", "redirect" => "../index.php"]);

$stmt->close();
$conn->close();
?>
