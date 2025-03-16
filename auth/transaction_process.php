<?php
session_start();
header("Content-Type: application/json");
include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed."]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not authenticated!"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
$date = isset($_POST['date']) ? $_POST['date'] : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';
$transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';

$today = date('Y-m-d'); // Get today's date

if (!$date || $date > $today) {
    echo json_encode(["status" => "error", "message" => "Invalid date! Future dates are not allowed."]);
    exit;
}
// Handle delete request
if (isset($_POST['delete']) && !empty($transaction_id)) {
    $stmt = $conn->prepare("SELECT amount FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $transaction_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $deleted_amount = $row['amount'];

        // Delete transaction
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $transaction_id, $user_id);

        if ($stmt->execute()) {
            $update_budget = $conn->prepare("UPDATE users SET budget = budget + ? WHERE id = ?");
            $update_budget->bind_param("di", $deleted_amount, $user_id);
            $update_budget->execute();

            echo json_encode(["status" => "success", "message" => "Transaction deleted and amount restored!"]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete transaction!"]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Transaction not found!"]);
        exit;
    }
}

// Validation
if (empty($amount) || empty($date) || empty($category)) {
    echo json_encode(["status" => "error", "message" => "All fields are required!"]);
    exit;
}

// Handle insert or update
if (!empty($transaction_id)) {
    $stmt = $conn->prepare("UPDATE transactions SET amount = ?, transaction_date = ?, category = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("dssii", $amount, $date, $category, $transaction_id, $user_id);
} else {
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_date, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $amount, $date, $category);
    
    if ($stmt->execute()) {
        $update_budget = $conn->prepare("UPDATE users SET budget = budget - ? WHERE id = ?");
        $update_budget->bind_param("di", $amount, $user_id);
        $update_budget->execute();

        echo json_encode(["status" => "success", "message" => "Transaction added and budget updated!"]);
        header("Location: ../user/transactions.php");
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add transaction!"]);
        header("Location: ../user/transactions.php");
        exit;
    }
}

$stmt->close();
$conn->close();
