<?php
session_start();
require 'db.php';

// Kuzuia tu admin waweze kuona
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$currentMonth = date('m');
$currentYear = date('Y');

// Jumla ya mapato ya mwezi huu (system-wide)
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE type = 'income' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?");
$stmt->execute([$currentMonth, $currentYear]);
$totalIncome = $stmt->fetchColumn() ?: 0;

// Jumla ya matumizi ya mwezi huu
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE type = 'expense' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?");
$stmt->execute([$currentMonth, $currentYear]);
$totalExpense = $stmt->fetchColumn() ?: 0;

$balance = $totalIncome - $totalExpense;

// Ripoti ya mapato na matumizi kwa kila mtumiaji
$stmt = $pdo->query("
    SELECT u.id, u.full_name,
    COALESCE(SUM(CASE WHEN t.type='income' THEN t.amount ELSE 0 END), 0) as income,
    COALESCE(SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END), 0) as expense
    FROM users u
    LEFT JOIN transactions t ON u.id = t.user_id AND MONTH(t.transaction_date) = $currentMonth AND YEAR(t.transaction_date) = $currentYear
    GROUP BY u.id, u.full_name
    ORDER BY u.full_name ASC
");
$userReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kwa chart ya mapato/matumizi kwa mwezi huu system-wide kwa siku (line chart)
$stmt = $pdo->prepare("
    SELECT DAY(transaction_date) as day,
    SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income,
    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense
    FROM transactions
    WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
    GROUP BY DAY(transaction_date)
    ORDER BY day ASC
");
$stmt->execute([$currentMonth, $currentYear]);
$dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Reports</title>
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
        .summary-cards {
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
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px #ccc;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 40px;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #002147;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>🛠 Admin Panel</h2>
    <!--a href="admin.php">🏠 Dashboard</a>-->
    <a href="users.php">👥 Manage Users</a>
    <a href="reports.php">📊 Reports</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h1>Monthly Reports (<?= date('F Y') ?>)</h1>

    <div class="summary-cards">
        <div class="card">
            <h3>Total Income</h3>
            <p><?= number_format($totalIncome, 2) ?> TZS</p>
        </div>
        <div class="card">
            <h3>Total Expenses</h3>
            <p><?= number_format($totalExpense, 2) ?> TZS</p>
        </div>
        <div class="card">
            <h3>Remaining Balance</h3>
            <p><?= number_format($balance, 2) ?> TZS</p>
        </div>
    </div>

    <h2>User Reports</h2>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Income (TZS)</th>
                <th>Expenses (TZS)</th>
                <th>Balance (TZS)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($userReports as $user): 
                $userBalance = $user['income'] - $user['expense'];
            ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= number_format($user['income'], 2) ?></td>
                    <td><?= number_format($user['expense'], 2) ?></td>
                    <td><?= number_format($userBalance, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="chart-container">
        <h2>Daily Income vs Expense (<?= date('F Y') ?>)</h2>
        <canvas id="dailyChart" height="150"></canvas>
    </div>
</div>

<script>
    const ctx = document.getElementById('dailyChart').getContext('2d');

    const dailyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(fn($d) => $d['day'], $dailyData)) ?>,
            datasets: [
                {
                    label: 'Income',
                    data: <?= json_encode(array_map(fn($d) => floatval($d['income']), $dailyData)) ?>,
                    borderColor: '#36a2eb',
                    fill: false,
                    tension: 0.1
                },
                {
                    label: 'Expenses',
                    data: <?= json_encode(array_map(fn($d) => floatval($d['expense']), $dailyData)) ?>,
                    borderColor: '#ff6384',
                    fill: false,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    title: {
                        display: true,
                        text: 'Day of Month'
                    }
                }
            }
        }
    });
</script>

</body>
</html>
