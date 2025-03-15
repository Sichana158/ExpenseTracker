<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: public/login.php");
    exit;
}
else{
    header("Location: user/dashboard.php");
    exit;
}
?>

