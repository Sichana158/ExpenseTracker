<?php
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

include '../config/database.php';

$user_id = $_SESSION['user_id'];
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
}

$filter = $_GET['filter'] ?? 'monthly'; // Default: Monthly

function fetchExpenses($conn, $user_id, $filter) {
    if ($filter === 'weekly') {
        $query = "SELECT category, SUM(amount) as total FROM transactions 
                  WHERE user_id = ? 
                    AND YEARWEEK(transaction_date, 1) = YEARWEEK(CURDATE(), 1)
                  GROUP BY category";
    } elseif ($filter === 'monthly') {
        $query = "SELECT category, SUM(amount) as total FROM transactions 
                  WHERE user_id = ? 
                    AND YEAR(transaction_date) = YEAR(CURDATE()) 
                    AND MONTH(transaction_date) = MONTH(CURDATE())
                  GROUP BY category";
    } else { // yearly
        $query = "SELECT category, SUM(amount) as total FROM transactions 
                  WHERE user_id = ? 
                    AND YEAR(transaction_date) = YEAR(CURDATE())
                  GROUP BY category";
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Query preparation failed", "error" => $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Query execution failed", "error" => $stmt->error]);
        exit;
    }

    $result = $stmt->get_result();
    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
    return $expenses;
}

$expenses = fetchExpenses($conn, $user_id, $filter);
$conn->close();
echo json_encode($expenses);
exit;
