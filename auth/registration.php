<?php
header("Content-Type: application/json"); 
include "../config/database.php"; 

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to validate username (Only letters & numbers, min 3 chars)
function validateUsername($username) {
    return preg_match("/^[a-zA-Z0-9]{1,}$/", $username);
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number (10 digits)
function validatePhone($phone) {
    return preg_match("/^[0-9]{10}$/", $phone);
}

// Function to validate password (Min 6 chars, at least one letter & one number)
function validatePassword($password) {
    return strlen($password) >= 6 && preg_match("/[A-Za-z]/", $password) && preg_match("/[0-9]/", $password);
}

function validateConfirmPassword($confirmPassword, $password){
    return $confirmPassword == $password;
}
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

// Get input data
$fname = trim($_POST['fname'] ?? '');
$lname = trim($_POST['lname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = trim($_POST['confirmPassword'] ?? '');
$role = "user";  // Default role

// Validation checks
if (!$fname || !$email || !$phone || !$password || !$gender || !$confirmPassword) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

if (!validateUsername($fname)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Name must be at least 3 characters and contain only letters and numbers."]);
    exit;
}

if (!validateEmail($email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid email format."]);
    exit;
}

if (!validatePhone($phone)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Phone number must be 10 digits."]);
    exit;
}

if (!validatePassword($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long and contain at least one letter and one number."]);
    exit;
}
if(!validateConfirmPassword($confirmPassword, $password)){
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Password must match"]);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Check if email already exists
$check_email = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($check_email);

if (!$stmt) {
    error_log("SQL Error: " . $conn->error);
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error."]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(["status" => "error", "message" => "Email already registered."]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert user data
$sql = "INSERT INTO users (Fname, Lname, email, phone, gender, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("SQL Error: " . $conn->error);
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error."]);
    exit;
}

$stmt->bind_param("sssssss", $fname, $lname, $email, $phone, $gender, $password, $role);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(["status" => "success", "message" => "Registration successful!", "redirect" => "../public/login.php"]);
} else {
    error_log("SQL Error: " . $stmt->error);
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Registration failed."]);
}

$stmt->close();
$conn->close();
?>
