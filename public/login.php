<?php include "../includes/header.php"; ?>
<link rel="stylesheet" href="../assets/css/login.css">
<body>
    <div class="container" >
    <h2>Login</h2>
    <form id="loginForm" onsubmit="loginUser(event)">
        
        <input type="email" name="email" placeholder="Email" required>
        <br>
        
        <input type="password" name="password" placeholder="Password" required>
        <br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
<script>
        async function loginUser(event) {
            event.preventDefault(); // Prevent default form submission

            let formData = new FormData(document.getElementById("loginForm"));

            let response = await fetch("../auth/login.php", {
                method: "POST",
                body: formData
            });

            let result = await response.json();
            alert(result.message);
            
            if (result.status === "success") {
                window.location.href = "../index.php"; // Redirect after login
            }
        }
    </script>
