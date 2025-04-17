<?php
session_start();
require '../config/database.php'; 

$user_id = $_SESSION['user_id'];
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
}


$sql = "SELECT category, amount, DATE_FORMAT(transaction_date, '%Y-%m-%d') AS date FROM transactions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = [];

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

echo json_encode($transactions);
?>
