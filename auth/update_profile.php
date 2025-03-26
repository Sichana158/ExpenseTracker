<?php
session_start();
include '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);
    $budget = trim($_POST['budget']);

    $updateQuery = $conn->prepare("UPDATE users SET Fname=?, Lname=?, phone=?, gender=?, budget=? WHERE id=?");
    $updateQuery->bind_param("sssssi", $fname, $lname, $phone, $gender, $budget, $user_id);

    if ($updateQuery->execute()) {
        $_SESSION['user_name'] = $fname; // Update session name
        header("Location: ../user/profile.php?success=1");
    } else {
        header("Location: ../user/profile.php?error=1");
    }

    $updateQuery->close();
    $conn->close();
}
?>
