<?php
session_start();
header("Content-Type: application/json");
include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed."]);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? '');

if (!$email || !$password || !$role) {
    echo json_encode(["status" => "error", "message" => "All fields are required!"]);
    exit;
}

if (!in_array($role, ['user', 'admin'])) {
    echo json_encode(["status" => "error", "message" => "Invalid role selected!"]);
    exit;
}

// Select table based on role
$table = $role === 'admin' ? 'admins' : 'users';

// Check if user exists
$sql = "SELECT id, Fname, password FROM $table WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => ucfirst($role) . " not found!"]);
    exit;
}

$stmt->bind_result($id, $fname, $password);
$stmt->fetch();

if ($password != $password) {
    echo json_encode(["status" => "error", "message" => "Incorrect password!"]);
    exit;
}

// Store session
$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $fname;
$_SESSION['user_role'] = $role;

// Redirect to role-based dashboard
$redirect = $role === 'admin' ? "../admin/allUsers.php" : "../user/dashboard.php";

echo json_encode(["status" => "success", "message" => "Login successful!", "redirect" => $redirect]);

$stmt->close();
$conn->close();
?>
