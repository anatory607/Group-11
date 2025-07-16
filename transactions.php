<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch categories for this user
$cat_stmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ?");
$cat_stmt->execute([$user_id]);
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// ADD TRANSACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $category_id = $_POST['category_id'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    // Get category name
    $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ? AND user_id = ?");
    $cat_stmt->execute([$category_id, $user_id]);
    $cat = $cat_stmt->fetch();
    $category_name = $cat ? $cat['name'] : '';

    if (!empty($type) && !empty($amount) && !empty($category_id) && !empty($date)) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, category, type, amount, transaction_date, description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $category_id, $category_name, $type, $amount, $date, $description]);
        // Optional: redirect after POST to avoid form resubmission
        header("Location: transactions.php");
        exit();
    }
}

// DELETE TRANSACTION
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $user_id]);
    // Optional: redirect after delete
    header("Location: transactions.php");
    exit();
}

// FILTERS
$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? '';
$date = $_GET['date'] ?? '';

$query = "SELECT id, type, amount, category, transaction_date, description FROM transactions WHERE user_id = ?";
$params = [$user_id];

if (!empty($type)) {
    $query .= " AND type = ?";
    $params[] = $type;
}
if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}
if (!empty($date)) {
    $query .= " AND DATE(transaction_date) = ?";
    $params[] = $date;
}

$query .= " ORDER BY transaction_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transactions</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 960px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; color: #007bff; }
        form { margin-bottom: 30px; }
        .form-group { margin-bottom: 10px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }

        .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters input, .filters select { flex: 1; }

        .btn { background: #007bff; color: white; padding: 10px 16px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #007bff; color: white; }
        .actions a { margin-right: 10px; color: #007bff; text-decoration: none; }
        .actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>➕ Add Transaction</h2>
    <form method="POST">
        <input type="hidden" name="add_transaction" value="1">

        <div class="form-group">
            <label>Type</label>
            <select name="type" required>
                <option value="">--Select--</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
            </select>
        </div>

        <div class="form-group">
            <label>Amount (TZS)</label>
            <input type="number" step="0.01" name="amount" required>
        </div>

        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="2"></textarea>
        </div>

        <button type="submit" class="btn">Save Transaction</button>
    </form>

    <h2>📜 Transactions List</h2>

    <form method="GET" class="filters">
        <select name="type">
            <option value="">-- Type --</option>
            <option value="income" <?= $type == 'income' ? 'selected' : '' ?>>Income</option>
            <option value="expense" <?= $type == 'expense' ? 'selected' : '' ?>>Expense</option>
        </select>

        <input type="text" name="category" placeholder="Category" value="<?= htmlspecialchars($category) ?>">
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
        <button class="btn" type="submit">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($transactions)): ?>
            <tr><td colspan="6">No transactions found.</td></tr>
        <?php else: ?>
            <?php foreach ($transactions as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['transaction_date']) ?></td>
                    <td><?= ucfirst($row['type']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td class="actions">
                        <a href="transactions.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this transaction?')">🗑 Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
