<?php

session_start();
if (!isset($_SESSION['user_id']) && !isset($_GET['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

include '../config/database.php';

// Only include navbar if role is not admin
if ($_SESSION['user_role'] !== 'admin') {
    include '../includes/navbar.php';
}

$user_id = $_SESSION['user_id'];

// If admin, allow viewing any user's report by passing ?user_id=ID
if ($_SESSION['user_role'] === 'admin' && isset($_GET['user_id'])) {
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->bind_param("i", $_GET['user_id']);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $user_id = $_GET['user_id'];
    }
    $checkStmt->close();
}

// Fetch totals for Weekly, Monthly, and Yearly
function fetchTotal($conn, $user_id, $filter) {
    if ($filter === 'weekly') {
        $query = "SELECT SUM(amount) as total FROM transactions 
                  WHERE user_id = ? 
                  AND YEARWEEK(transaction_date, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($filter === 'monthly') {
        $query = "SELECT SUM(amount) as total FROM transactions 
                  WHERE user_id = ? 
                  AND YEAR(transaction_date) = YEAR(CURDATE()) 
                  AND MONTH(transaction_date) = MONTH(CURDATE())";
    } elseif ($filter === 'yearly') {
        $query = "SELECT SUM(amount) as total FROM transactions 
                  WHERE user_id = ? 
                  AND YEAR(transaction_date) = YEAR(CURDATE())";
    } else {
        return 0;
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

$weeklyTotal = fetchTotal($conn, $user_id, 'weekly');  
$monthlyTotal = fetchTotal($conn, $user_id, 'monthly');   
$yearlyTotal = fetchTotal($conn, $user_id, 'yearly');   

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

    <?php if ($_SESSION['user_role'] === 'admin' && isset($_GET['user_id'])): ?>
        <p><strong>Viewing report for user ID:</strong> <?php echo htmlspecialchars($_GET['user_id']); ?></p>
    <?php endif; ?>

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
<script>
    const userId = <?php echo json_encode($user_id); ?>;
    const username = <?php echo json_encode($_SESSION['username'] ?? 'User'); ?>;
</script>
<script defer src="../assets/js/expense_report.js"></script>

</body>
</html>
