<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch total income
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'income' AND MONTH(transaction_date) = MONTH(CURRENT_DATE())");
$stmt->execute([$user_id]);
$total_income = $stmt->fetchColumn() ?: 0;

// Fetch total expenses
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'expense' AND MONTH(transaction_date) = MONTH(CURRENT_DATE())");
$stmt->execute([$user_id]);
$total_expense = $stmt->fetchColumn() ?: 0;

$balance = $total_income - $total_expense;

// Fetch category totals for pie chart
$stmt = $pdo->prepare("SELECT category, SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'expense' GROUP BY category");
$stmt->execute([$user_id]);
$category_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch monthly data for bar chart
$stmt = $pdo->prepare("SELECT MONTH(transaction_date) as month, SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense FROM transactions WHERE user_id = ? GROUP BY MONTH(transaction_date)");
$stmt->execute([$user_id]);
$monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Budget Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            background: #f4f6f9;
        }
        .sidebar {
            width: 220px;
            background: #002147;
            color: white;
            height: 100vh;
            float: left;
            padding: 20px;
        }
        .sidebar h2 {
            color: #fff;
            margin-bottom: 30px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background: #0056b3;
        }
        .main {
            margin-left: 240px;
            padding: 30px;
        }
        .cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            text-align: center;
        }
        .card h3 {
            margin: 0;
            color: #333;
        }
        .charts {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .chart-box {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>💰 Budget Tracker</h2>

    <?php if ($role === 'member'): ?>
         <a href="dashboard.php">🏠 Dashboard</a>
    <a href="add_transaction.php">➕ Add Transaction</a>
    <a href="transactions.php">📜 Transactions</a>
    <a href="budget.php">🎯 Budget Goals</a>
    <a href="reports.php">📊 Reports</a>
    <a href="category.php">📂 Categories</a> <!-- Hii ni line mpya ya Category -->
    <?php elseif ($role === 'admin'): ?>
        <!--a href="admin.php">🛠 Admin Dashboard</a> -->
        <a href="users.php">👥 Manage Users</a>
        <a href="reports.php">📊 System Reports</a>
    <?php endif; ?>

    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h2>

    <div class="cards">
        <div class="card">
            <h3>Total Income</h3>
            <p><?= number_format($total_income, 2) ?> TZS</p>
        </div>
        <div class="card">
            <h3>Total Expenses</h3>
            <p><?= number_format($total_expense, 2) ?> TZS</p>
        </div>
        <div class="card">
            <h3>Remaining Balance</h3>
            <p><?= number_format($balance, 2) ?> TZS</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart-box">
            <h3>Expenses by Category</h3>
            <canvas id="pieChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Monthly Trend</h3>
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<script>
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const barCtx = document.getElementById('barChart').getContext('2d');

    const pieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($category_data, 'category')) ?>,
            datasets: [{
                label: 'Expenses',
                data: <?= json_encode(array_column($category_data, 'total')) ?>,
                backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff']
            }]
        }
    });

    const barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($m) => date('M', mktime(0, 0, 0, $m['month'], 1)), $monthly_data)) ?>,
            datasets: [
                {
                    label: 'Income',
                    backgroundColor: '#36a2eb',
                    data: <?= json_encode(array_column($monthly_data, 'income')) ?>
                },
                {
                    label: 'Expenses',
                    backgroundColor: '#ff6384',
                    data: <?= json_encode(array_column($monthly_data, 'expense')) ?>
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
