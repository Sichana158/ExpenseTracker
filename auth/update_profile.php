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

    $stmt = $conn->prepare("SELECT monthly_budget FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($old_budget);
    $stmt->fetch();
    $stmt->close();

// Check if budget was updated
if ($old_budget != $budget) {
    $updateQuery = $conn->prepare("UPDATE users SET Fname=?, Lname=?, phone=?, gender=?, monthly_budget=?, budget=? WHERE id=?");
    $updateQuery->bind_param("ssssssi", $fname, $lname, $phone, $gender, $budget, $budget, $user_id);
} else {
    $updateQuery = $conn->prepare("UPDATE users SET Fname=?, Lname=?, phone=?, gender=?, monthly_budget=? WHERE id=?");
    $updateQuery->bind_param("sssssi", $fname, $lname, $phone, $gender, $budget, $user_id);
}

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
