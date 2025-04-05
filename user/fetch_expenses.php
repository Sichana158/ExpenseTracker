<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
include '../config/database.php';

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'monthly'; // Default: Monthly

// Function to fetch expenses based on filter
function fetchExpenses($conn, $user_id, $filter) {
    $query = "";
    if ($filter === 'weekly') { // Last 1 hour
        $query = "SELECT category, SUM(amount) as total FROM transactions 
                  WHERE user_id = ? AND transaction_date >= NOW() - INTERVAL 7 DAY 
                  GROUP BY category";
    } elseif ($filter === 'monthly') { // Last 1 week
        $query = "SELECT category, SUM(amount) as total FROM transactions 
                  WHERE user_id = ? AND transaction_date >= NOW() - INTERVAL 30 DAY 
                  GROUP BY category";
    } else { // Monthly (Last 1 day)
        $query = "SELECT category, SUM(amount) as total FROM transactions 
                  WHERE user_id = ? AND transaction_date >= NOW() - INTERVAL 365 DAY 
                  GROUP BY category";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
    return $expenses;
}


$expenses = fetchExpenses($conn, $user_id, $filter);
$conn->close();
header("Content-Type: application/json");
echo json_encode($expenses);
exit;
?>