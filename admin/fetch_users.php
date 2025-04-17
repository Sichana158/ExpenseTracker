<?php
include "../config/database.php";
header("Content-Type: application/json");

$search = $_GET['search'] ?? '';

$sql = "SELECT id, CONCAT(Fname, ' ', Lname) AS name, email, phone FROM users";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE Fname LIKE ? OR Lname LIKE ? OR email LIKE ? OR phone LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param("ssss", ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(["status" => "success", "users" => $users]);
?>
