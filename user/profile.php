<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
include '../config/database.php';

$user_id = $_SESSION['user_id'];

// Fetch user details
$query = $conn->prepare("SELECT Fname, Lname, email, phone, gender,budget FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

$query->close();
$conn->close();
?>

<?php include '../includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
<div class="profile-container">
    <h2>User Profile</h2>
    <form action="../auth/update_profile.php" method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="fname" value="<?php echo htmlspecialchars($user['Fname']); ?>" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lname" value="<?php echo htmlspecialchars($user['Lname']); ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
        </div>
        <div class="form-group">
            <label>Gender</label>
            <input type="text" name="gender" value="<?php echo htmlspecialchars($user['gender']); ?>">
        </div>
        <div class="form-group">
            <label>Budget</label>
            <input type="text" name="budget" value="<?php echo htmlspecialchars($user['budget']); ?>">
        </div>
        <button type="submit" class="update-btn">Update</button>
    </form>
</div>
</body>
</html>
