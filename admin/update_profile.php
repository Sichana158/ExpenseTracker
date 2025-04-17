<?php
session_start();
include '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);
    



// Check if budget was updated

    $updateQuery = $conn->prepare("UPDATE admins SET Fname=?, Lname=?, phone=?, gender=? WHERE id=?");
    $updateQuery->bind_param("ssssi", $fname, $lname, $phone, $gender, $user_id);


    if ($updateQuery->execute()) {
        $_SESSION['user_name'] = $fname; // Update session name
        header("Location: profile.php?success=1");
    } else {
        header("Location: profile.php?error=1");
    }

    $updateQuery->close();
    $conn->close();
}
?>
