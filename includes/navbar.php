<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expense Tracker</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #333;
            color: white;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #575757;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 style="text-align:center;">Expense Tracker</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="transactions.php">💰 Transaction Management</a>
    <a href="expense_report.php">📊 Expense Report</a>
    <a href="profile.php">👤 Profile</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

</body>
</html>
