<?php
session_start();
header("Content-Type: application/json");
include "../config/database.php";

ob_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$amount = trim($_POST['amount'] ?? '');
$date = $_POST['date'] ?? '';
$category = $_POST['category'] ?? '';
$transaction_id = $_POST['transaction_id'] ?? '';


// **Handle Delete Request**
if (!$user_id || !$transaction_id) {
    ob_end_clean(); // Clear any unwanted output
    echo json_encode(["status" => "error", "message" => "Invalid request!"]);
    exit;
}

// **Handle Delete Request**
if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $transaction_id, $user_id);

    if ($stmt->execute()) {
        ob_end_clean(); // Ensure only JSON output
        echo json_encode(["status" => "success", "message" => "Transaction deleted successfully!"]);
    } else {
        ob_end_clean();
        echo json_encode(["status" => "error", "message" => "Failed to delete transaction!"]);
    }
    $stmt->close();
    exit;
}

// If it reaches here, return an error
ob_end_clean();
echo json_encode(["status" => "error", "message" => "Invalid request."]);
exit;
// Validation
if (!$amount || !$date || !$category) {
    echo json_encode(["status" => "error", "message" => "All fields are required!"]);
    exit;
}

// Check if it’s an update
if ($transaction_id) {
    $stmt = $conn->prepare("UPDATE transactions SET amount = ?, transaction_date = ?, category = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("dssii", $amount, $date, $category, $transaction_id, $user_id);
} else {
    // Insert new transaction
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_date, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $amount, $date, $category);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => $transaction_id ? "Transaction updated!" : "Transaction added!"]);
    header("Location: ../user/transactions.php");
    exit;
} else {
    echo json_encode(["status" => "error", "message" => "Database error!"]);
    header("Location: ../user/transactions.php");
    exit;
}

$stmt->close();
$conn->close();
