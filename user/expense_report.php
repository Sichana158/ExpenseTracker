<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

include '../config/database.php';
include '../includes/navbar.php';

$user_id = $_SESSION['user_id'];

// Fetch totals for Weekly (1 hour), Monthly (1 day), and Yearly (1 week)
function fetchTotal($conn, $user_id, $interval) {
    $query = "SELECT SUM(amount) as total FROM transactions 
              WHERE user_id = ? AND transaction_date >= NOW() - INTERVAL $interval";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

$weeklyTotal = fetchTotal($conn, $user_id, "1 DAY");  
$monthlyTotal = fetchTotal($conn, $user_id, "3 DAY");   
$yearlyTotal = fetchTotal($conn, $user_id, "1 WEEK");   

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expense Report</title>
    <link rel="stylesheet" href="../assets/css/expense_report.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
    <h2>Expense Report</h2>

    <!-- Summary Cards -->
    <div class="summary">
        <div class="card">
            <h3>Weekly</h3>
            <p>₹<span id="weeklyTotal"><?php echo number_format($weeklyTotal, 2); ?></span></p>
        </div>
        <div class="card">
            <h3>Monthly</h3>
            <p>₹<span id="monthlyTotal"><?php echo number_format($monthlyTotal, 2); ?></span></p>
        </div>
        <div class="card">
            <h3>Yearly</h3>
            <p>₹<span id="yearlyTotal"><?php echo number_format($yearlyTotal, 2); ?></span></p>
        </div>
    </div>

    <!-- Filter Dropdown -->
    <div class="div2">
    <div class="filter">
        <label>View Expenses: </label>
        <select id="filter">
            <option value="weekly">Weekly</option>
            <option value="monthly" selected>Monthly</option>
            <option value="yearly">Yearly</option>
        </select>
    </div>

    <!-- Pie Chart -->
    <div class="chart-container">
        <canvas id="expenseChart"></canvas>
    </div>

    <!-- Category-wise Expenses -->
    <div class="categories" id="categoryList">
        <!-- Categories will be inserted dynamically -->
    </div>
</div>
    <!-- Export Options -->
    <div class="export">
        <button id="printReport">Print</button>
        <button id="exportCSV">Export to CSV</button>
        <button id="exportExcel">Export to Excel</button>
    </div>
</div>

<script src="../assets/js/expense_report.js"></script>
</body>
</html>
