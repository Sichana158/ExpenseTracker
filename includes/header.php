<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="../assets/css/header.css">
<header>
    <h3>Expense Tracker</h3>
    <?php if (isset($_SESSION['user_id'])): ?>
        <nav>
            <a href="../admin/allUsers.php">All Users</a>
            <a href="../admin/profile.php">Profile</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>
    <?php endif; ?>
</header>
