<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
include '../config/database.php';

// Fetch user's budget (if applicable, modify based on your DB structure)
$user_id = $_SESSION['user_id'];
$budget_query = $conn->prepare("SELECT budget FROM users WHERE id = ?");
$budget_query->bind_param("i", $user_id);
$budget_query->execute();
$budget_result = $budget_query->get_result();
$budget_row = $budget_result->fetch_assoc();
$budget = $budget_row['budget'] ?? 0;

// Fetch transactions
$transactions_query = $conn->prepare("SELECT id, category, amount, transaction_date FROM transactions WHERE user_id = ?");
$transactions_query->bind_param("i", $user_id);
$transactions_query->execute();
$transactions_result = $transactions_query->get_result();
?>
<?php include '../includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transaction Management</title>
    <link rel="stylesheet" href="../assets/css/transactions.css">
   
</head>
<body>
<div class="content">
    <h2>Transaction Management</h2>

    <!-- Budget Display -->
    <div class="budget">
        <h3>Monthly Budget: ₹<?php echo number_format($budget, 2); ?></h3>
    </div>

    <!-- Expense Form -->
    <form id="transactionForm" action="../auth/transaction_process.php" method="POST">
        <label>Amount:</label>
        <input type="number" id="amount" name="amount" required>

        <label>Date:</label>
        <input type="date" id="date" name="date" required>

        <label>Category:</label>
        <select id="category" name="category" required>
            <option value="Food">Food</option>
            <option value="Travel">Travel</option>
            <option value="Accessories">Accessories</option>
            <option value="Groceries">Groceries</option>
            <option value="Clothes">Clothes</option>
            <option value="Medicines">Medicines</option>
            <option value="Rent">Rent</option>
            <option value="Bill & Recharge">Bill & Recharge</option>
            <option value="Others">Others</option>
        </select>

        <input type="hidden" id="transaction_id" name="transaction_id">

        <button type="submit">Add Transaction</button>
    </form>

    <!-- Transactions Table -->
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount (₹)</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="transactionList">
    <?php if ($transactions_result->num_rows > 0): ?>
        <?php while ($row = $transactions_result->fetch_assoc()): ?>
            <tr data-id="<?php echo $row['id']; ?>" data-category="<?php echo $row['category']; ?>" data-amount="<?php echo $row['amount']; ?>" data-date="<?php echo $row['transaction_date']; ?>">
                <td><?php echo $row['category']; ?></td>
                <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo $row['transaction_date']; ?></td>
                <td>
                    <span class="menu">⋮</span>
                    <div class="dropdown">
                        <button class="edit-btn">Edit</button>
                        <button class="delete-btn">Delete</button>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align: center;">No transactions found</td>
        </tr>
    <?php endif; ?>
</tbody>

    </table>
</div>

<script src="../assets/js/transactions.js"></script>
</body>
</html>

