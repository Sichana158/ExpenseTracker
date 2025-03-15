document.querySelectorAll('.menu').forEach(menu => {
    menu.addEventListener('click', function () {
        this.nextElementSibling.style.display = 'block';
    });
});

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const row = this.closest('tr');
        document.getElementById('transaction_id').value = row.dataset.id;
        document.getElementById('amount').value = row.dataset.amount;
        document.getElementById('date').value = row.dataset.date;
        document.getElementById('category').value = row.dataset.category;
    });
});

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        if (confirm("Are you sure you want to delete this transaction?")) {
            const id = this.closest('tr').dataset.id;
            fetch('../auth/transaction_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ transaction_id: id, delete: true })
            })
            .then(response => response.text()) // Read as text first
            .then(text => {
                console.log("Raw Response:", text); // Debugging
                return JSON.parse(text); // Convert to JSON
            })
            .then(data => {
                console.log("Parsed JSON:", data);
                if (data.status === "success") {
                    console.log("Transaction deleted successfully!");
                    location.reload();
                } else {
                    console.error("Error deleting transaction:", data.message);
                }
            })
            .catch(error => console.error("Fetch error:", error));
        }
    });
});
