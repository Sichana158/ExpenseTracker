<?php include "../includes/header.php"; ?>
<!-- <link rel="stylesheet" href="../assets/css/manage_users.css"> Optional CSS file -->
<link rel="stylesheet" href="manage_users.css">
<div class="container">
    <h2>Manage Users</h2>

    <input type="text" id="searchInput" placeholder="Search by name, email, or phone..." style="margin-bottom: 10px; padding: 8px; width: 100%;">

    <div id="userList"></div>
</div>

<script>
    async function fetchUsers(search = '') {
        const res = await fetch(`fetch_users.php?search=${encodeURIComponent(search)}`);
        const data = await res.json();

        if (data.status === "success") {
            renderUserList(data.users);
        }
    }

    function renderUserList(users) {
        const container = document.getElementById("userList");
        container.innerHTML = "";

        users.forEach(user => {
            const card = document.createElement("div");
            card.className = "user-card";
            card.innerHTML = `
                <strong>${user.name}</strong><br>
                Email: ${user.email}<br>
                Phone: ${user.phone}<br>
                <div class="menu-container">
                    <button onclick="toggleMenu(${user.id})">⋮</button>
                    <div id="menu-${user.id}" class="menu" style="display:none;">
                        <button onclick="viewUser(${user.id})">View</button>
                        <button onclick="deleteUser(${user.id})">Delete</button>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function toggleMenu(id) {
        document.querySelectorAll('.menu').forEach(menu => {
            menu.style.display = 'none';
        });
        const selectedMenu = document.getElementById(`menu-${id}`);
        selectedMenu.style.display = selectedMenu.style.display === "block" ? "none" : "block";
    }

    function viewUser(id) {
        window.location.href = `../user/expense_report.php?user_id=${id}`;
    }

    async function deleteUser(id) {
        if (!confirm("Are you sure you want to delete this user?")) return;

        const formData = new FormData();
        formData.append('id', id);

        const res = await fetch(`delete_user.php`, {
            method: "POST",
            body: formData
        });

        const data = await res.json();
        alert(data.message);
        if (data.status === "success") {
            fetchUsers(document.getElementById("searchInput").value);
        }
    }

    // Initial fetch
    fetchUsers();

    // Live search
    document.getElementById("searchInput").addEventListener("input", e => {
        fetchUsers(e.target.value);
    });

    // Close all menus when clicking outside
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".menu-container")) {
            document.querySelectorAll(".menu").forEach(menu => {
                menu.style.display = "none";
            });
        }
    });
</script>
