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

// **Handle Edit (Update)**
if (!empty($transaction_id)) {
    // Fetch the old amount before updating
    $stmt = $conn->prepare("SELECT amount FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $transaction_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $old_amount = $row['amount'];
        $amount_diff = $amount - $old_amount; // **Calculate the difference**

        // Update transaction
        $stmt = $conn->prepare("UPDATE transactions SET amount = ?, transaction_date = ?, category = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("dssii", $amount, $date, $category, $transaction_id, $user_id);

        if ($stmt->execute()) {
            // **Update the budget based on the difference**
            $update_budget = $conn->prepare("UPDATE users SET budget = budget - ? WHERE id = ?");
            $update_budget->bind_param("di", $amount_diff, $user_id);
            $update_budget->execute();

            echo json_encode(["status" => "success", "message" => "Transaction updated and budget adjusted!"]);
            header("Location: ../user/transactions.php");
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update transaction!"]);
            header("Location: ../user/transactions.php");
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Transaction not found!"]);
        header("Location: ../user/transactions.php");
        exit;
    }
}

// **Handle Insert (New Transaction)**
$stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_date, category) VALUES (?, ?, ?, ?)");
$stmt->bind_param("idss", $user_id, $amount, $date, $category);

if ($stmt->execute()) {
    // **Subtract the new amount from the budget**
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

$stmt->close();
$conn->close();
