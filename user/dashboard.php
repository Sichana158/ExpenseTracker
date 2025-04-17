<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
include '../config/database.php';
include '../includes/navbar.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

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

// Fetch budget left from users table
$budgetStmt = $conn->prepare("SELECT budget FROM users WHERE id = ?");
$budgetStmt->bind_param("i", $user_id);
$budgetStmt->execute();
$budgetResult = $budgetStmt->get_result()->fetch_assoc();
$budgetLeft = $budgetResult['budget'] ?? 0;
$budgetStmt->close();

// Fetch recent 5 transactions
$recentQuery = "SELECT category, amount, transaction_date FROM transactions 
                WHERE user_id = ? ORDER BY transaction_date DESC LIMIT 5";
$stmt = $conn->prepare($recentQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent = $stmt->get_result();

$conn->close();
?>

<style>
    .content {
        padding: 2rem;
    }

    .budget-alert {
        background-color: #ffe0e0;
        color: red;
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid red;
        border-radius: 8px;
        font-weight: bold;
        animation: blink 1s linear infinite;
    }

    @keyframes blink {
        50% { opacity: 0; }
    }

    .recent-transactions table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .recent-transactions th, .recent-transactions td {
        border: 1px solid #ccc;
        padding: 8px;
    }

    .recent-transactions th {
        background: #f0f0f0;
    }

    .summary {
        display: flex;
        justify-content: space-around;
        margin: 20px 10px;
    }

    .card {
        width: 20%;
        padding: 20px;
        background: #f4f4f4;
        border-radius: 8px;
        box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    .budgetLeft{
        margin-left:60px;
        padding: 8px;
    }
</style>

<div class="content">
    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>

    <!-- Budget Left -->
    <div class="card" class="budgetLeft">
        <h3>Budget Left</h3>
        <p>₹<span id="budgetLeft"><?php echo number_format($budgetLeft, 2); ?></span></p>
    </div>

    <!-- Budget Alert -->
    <?php if ($budgetLeft < 2000): ?>
        <div class="budget-alert">
            ⚠ Budget left is below ₹2000 (₹<?php echo number_format($budgetLeft, 2); ?>)
        </div>
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

    <!-- Recent Transactions -->
    <div class="recent-transactions">
        <h3>Recent Transactions</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($txn = $recent->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($txn['category']); ?></td>
                        <td>₹<?php echo number_format($txn['amount'], 2); ?></td>
                        <td><?php echo date("d M Y", strtotime($txn['transaction_date'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
