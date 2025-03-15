<?php include "../includes/header.php"; ?>
<link rel="stylesheet" href="../assets/css/register.css">
<div class="container">
    <h2>Register</h2>
    <form id="registerForm" action="http://localhost/ExpenseTracker/auth/registration.php" method="POST">
        <input type="text" name="fname" placeholder="First Name" required>
        <input type="text" name="lname" placeholder="Last Name" >
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="confirmPassword" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="../public/login.php">Login here</a></p>
    <div id="message"></div>
</div>
<script>
document.getElementById("registerForm").addEventListener("submit", async function (event) {
    event.preventDefault(); // Prevent default form submission

    let formData = new FormData(this);

    try {
        let response = await fetch("http://localhost/ExpenseTracker/auth/registration.php", {
            method: "POST", // Make sure it's a POST request
            body: formData
        });

        let result = await response.json();
        if (result.status === "success") {
            alert(result.message);
            window.location.href = result.redirect; // Redirect to login page
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error("Error:", error);
    }
});

</script>
<!-- <?php include "../includes/footer.php"; ?> -->
