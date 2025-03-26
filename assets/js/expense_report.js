document.addEventListener("DOMContentLoaded", function () {
    const filterDropdown = document.getElementById("filter");
    const expenseChartCtx = document.getElementById("expenseChart").getContext("2d");
    let expenseChart;
    let expenses = []; 
    let username = "User";

    function fetchAllTransactions(callback) {
        fetch("../functions/fetch_all_transactions.php") // PHP file to fetch all transactions
            .then(response => response.json())
            .then(data => {
                callback(data);
            })
            .catch(error => console.error("Error fetching transactions:", error));
    }

    function updateExpenses(filter) {
        fetch(`fetch_expenses.php?filter=${filter}`) 
            .then(response => response.json())
            .then(data => {
                expenses = data; 
                renderChart(expenses);
                updateCategoryList(expenses);
            })
            .catch(error => console.error("Error fetching expenses:", error));
    }

    function renderChart(expenses) {
        const categories = expenses.map(exp => exp.category);
        const amounts = expenses.map(exp => parseFloat(exp.total)); 

        if (expenseChart) expenseChart.destroy();

        expenseChart = new Chart(expenseChartCtx, {
            type: 'pie',
            data: {
                labels: categories,
                datasets: [{
                    data: amounts,
                    backgroundColor: ["#FF5733", "#33FF57", "#3357FF", "#FF33A1", "#A133FF", "#33FFF5", "#FFD700", "#8A2BE2", "#DC143C"
]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    function updateCategoryList(expenses) {
        const categoryList = document.getElementById("categoryList");
        categoryList.innerHTML = "";

        expenses.forEach(exp => {
            const card = document.createElement("div");
            card.classList.add("category-card");
            card.innerHTML = `<h4>${exp.category}</h4><p>₹${exp.total}</p>`;
            categoryList.appendChild(card);
        });
    }

    filterDropdown.addEventListener("change", function () {
        updateExpenses(this.value);
    });

    document.getElementById("printReport").addEventListener("click", function () {
        window.print();
    });

    // Export CSV Button
    document.getElementById("exportCSV").addEventListener("click", function () {
        fetchAllTransactions(function (transactions) {
            if (transactions.length === 0) {
                alert("No transactions available for export.");
                return;
            }

            let csvContent = "data:text/csv;charset=utf-8,Category,Amount,Date\n";
            transactions.forEach(txn => {
                csvContent += `${txn.category},${txn.amount},${txn.date}\n`;
            });

            downloadFile(csvContent, `${username}_all_transactions.csv`);
        });
    });

    // Export Excel Button
    document.getElementById("exportExcel").addEventListener("click", function () {
        fetchAllTransactions(function (transactions) {
            if (transactions.length === 0) {
                alert("No transactions available for export.");
                return;
            }

            let excelContent = `<table><tr><th>Category</th><th>Amount</th><th>Date</th></tr>`;
            transactions.forEach(txn => {
                excelContent += `<tr><td>${txn.category}</td><td>${txn.amount}</td><td>${txn.date}</td></tr>`;
            });
            excelContent += `</table>`;

            const blob = new Blob([excelContent], { type: "application/vnd.ms-excel" });
            downloadFile(URL.createObjectURL(blob), `${username}_all_transactions.xls`);
        });
    });

    // Function to download the file
    function downloadFile(content, filename) {
        const link = document.createElement("a");
        link.href = content;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    
    updateExpenses(filterDropdown.value);
});
