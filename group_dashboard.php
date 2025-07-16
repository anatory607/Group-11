<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Chukua group_id kutoka URL
if (!isset($_GET['group_id']) || !is_numeric($_GET['group_id'])) {
    die("Invalid Group ID.");
}
$group_id = intval($_GET['group_id']);

// Angalia kama user ni member wa group hii
$stmtCheck = $pdo->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
$stmtCheck->execute([$group_id, $user_id]);
$member = $stmtCheck->fetch(PDO::FETCH_ASSOC);
if (!$member) {
    die("You are not a member of this group.");
}

// POST: ku add transaction kwenye group
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type'], $_POST['amount'], $_POST['description'], $_POST['category'], $_POST['transaction_date'])) {
    $type = $_POST['type']; // 'income' or 'expense'
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $transaction_date = $_POST['transaction_date'];

    if ($amount > 0 && ($type === 'income' || $type === 'expense') && $description && $category && $transaction_date) {
        // Insert transaction linked to group (assume transactions table has group_id column)
        $stmtInsert = $pdo->prepare("INSERT INTO transactions (user_id, group_id, type, amount, description, category, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $success = $stmtInsert->execute([$user_id, $group_id, $type, $amount, $description, $category, $transaction_date]);
        if ($success) {
            $message = "Transaction added successfully.";
        } else {
            $message = "Failed to add transaction.";
        }
    } else {
        $message = "Please fill all fields correctly.";
    }
}

// Hapa tunataka kuonyesha data ya group kwa sasa (mwezi huu na mwaka huu)
$currentMonth = date('m');
$currentYear = date('Y');

// Total income for group this month
$stmtIncome = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE group_id = ? AND type = 'income' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?");
$stmtIncome->execute([$group_id, $currentMonth, $currentYear]);
$totalIncome = $stmtIncome->fetchColumn() ?? 0;

// Total expense for group this month
$stmtExpense = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE group_id = ? AND type = 'expense' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?");
$stmtExpense->execute([$group_id, $currentMonth, $currentYear]);
$totalExpense = $stmtExpense->fetchColumn() ?? 0;

$remainingBalance = $totalIncome - $totalExpense;

// Expenses per category for group this month
$stmtCategory = $pdo->prepare("SELECT category, SUM(amount) as total FROM transactions WHERE group_id = ? AND type = 'expense' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ? GROUP BY category");
$stmtCategory->execute([$group_id, $currentMonth, $currentYear]);
$categoryData = $stmtCategory->fetchAll(PDO::FETCH_ASSOC);

// Monthly trend (Bar chart) for group
$stmtTrend = $pdo->prepare("SELECT MONTH(transaction_date) as month, SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense FROM transactions WHERE group_id = ? AND YEAR(transaction_date) = ? GROUP BY MONTH(transaction_date)");
$stmtTrend->execute([$group_id, $currentYear]);
$monthlyData = $stmtTrend->fetchAll(PDO::FETCH_ASSOC);

// Get group name for header
$stmtGroup = $pdo->prepare("SELECT name FROM groups WHERE id = ?");
$stmtGroup->execute([$group_id]);
$groupName = $stmtGroup->fetchColumn() ?? "Group";

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title><?= htmlspecialchars($groupName) ?> - Group Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body { font-family: Arial; background: #f4f6f9; margin: 0; }
  .sidebar { width: 250px; background: #002147; height: 100vh; float: left; color: white; padding-top: 30px; position: fixed; }
  .sidebar h2 { text-align: center; margin-bottom: 40px; }
  .sidebar a { display: block; padding: 15px 30px; text-decoration: none; color: white; }
  .sidebar a:hover, .sidebar a.active { background: #003366; }
  .main { margin-left: 250px; padding: 30px; }
  .card-box { display: flex; gap: 20px; flex-wrap: wrap; }
  .card { flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
  .charts { margin-top: 40px; display: flex; gap: 30px; flex-wrap: wrap; }
  canvas { background: white; border-radius: 8px; padding: 10px; box-shadow: 0 0 10px #ccc; }
  form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; margin-top: 40px; max-width: 500px; }
  form input, form select, form button { width: 100%; padding: 10px; margin: 10px 0; font-size: 1rem; }
  form button { background: #002147; color: white; border: none; cursor: pointer; border-radius: 6px; }
  form button:hover { background: #003366; }
  .message { margin-top: 20px; padding: 10px; background: #dff0d8; color: #3c763d; border-radius: 6px; }
  .header { display: flex; justify-content: space-between; align-items: center; }
  .btn-back { background: #007bff; border: none; color: white; padding: 8px 16px; border-radius: 5px; cursor: pointer; }
  .btn-back:hover { background: #0056b3; }
</style>
</head>
<body>

<div class="sidebar">
  <h2>💰 Budget Tracker</h2>
  <a href="dashboard.php">🏠 Dashboard</a>
  <a href="add_transaction.php">➕ Add Transaction</a>
  <a href="transactions.php">📜 Transactions</a>
  <a href="budget.php">🎯 Budget Goals</a>
  <a href="category.php">📂 Categories</a>
  <?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="users.php">👥 User Management</a>
  <?php endif; ?>
  <a href="create_group.php">👥 Create Group</a>
  <a href="my_groups.php">📂 My Groups</a>
  <a href="group_dashboard.php?group_id=<?= $group_id ?>" class="active">📊 Group Dashboard</a>
  <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
  <div class="header">
    <h1><?= htmlspecialchars($groupName) ?> - Group Dashboard</h1>
    <button class="btn-back" onclick="history.back()">← Kurudi nyuma</button>
  </div>

  <div class="card-box">
    <div class="card">
      <h3>Total Income (<?= date('F') ?>)</h3>
      <p><strong style="color: green;">Tsh <?= number_format($totalIncome) ?></strong></p>
    </div>
    <div class="card">
      <h3>Total Expenses</h3>
      <p><strong style="color: red;">Tsh <?= number_format($totalExpense) ?></strong></p>
    </div>
    <div class="card">
      <h3>Remaining Balance</h3>
      <p><strong>Tsh <?= number_format($remainingBalance) ?></strong></p>
    </div>
  </div>

  <div class="charts">
    <div style="flex:1; min-width:300px">
      <h3>Expenses by Category</h3>
      <canvas id="pieChart" height="300"></canvas>
    </div>
    <div style="flex:2; min-width:300px">
      <h3>Monthly Income vs Expense</h3>
      <canvas id="barChart" height="300"></canvas>
    </div>
  </div>

  <form method="POST" action="">
    <h3>Add Transaction to Group</h3>
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <label for="type">Type:</label>
    <select name="type" id="type" required>
      <option value="">--Select Type--</option>
      <option value="income">Income</option>
      <option value="expense">Expense</option>
    </select>

    <label for="amount">Amount (Tsh):</label>
    <input type="number" step="0.01" name="amount" id="amount" required />

    <label for="category">Category:</label>
    <input type="text" name="category" id="category" placeholder="E.g. Food, Salary" required />

    <label for="description">Description:</label>
    <input type="text" name="description" id="description" required />

    <label for="transaction_date">Date:</label>
    <input type="date" name="transaction_date" id="transaction_date" value="<?= date('Y-m-d') ?>" required />

    <button type="submit">Add Transaction</button>
  </form>
</div>

<script>
const pieCtx = document.getElementById('pieChart').getContext('2d');
const barCtx = document.getElementById('barChart').getContext('2d');

new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($categoryData, 'category')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($categoryData, 'total')) ?>,
            backgroundColor: ['#ff6384','#36a2eb','#ffce56','#8e44ad','#2ecc71','#f39c12','#d35400','#7f8c8d','#27ae60']
        }]
    }
});

new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($row) => date("F", mktime(0, 0, 0, $row['month'], 1)), $monthlyData)) ?>,
        datasets: [
            {
                label: 'Income',
                backgroundColor: '#2ecc71',
                data: <?= json_encode(array_column($monthlyData, 'income')) ?>
            },
            {
                label: 'Expense',
                backgroundColor: '#e74c3c',
                data: <?= json_encode(array_column($monthlyData, 'expense')) ?>
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>
