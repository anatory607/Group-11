<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// Kuzuia kuingia bila login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch categories za user ili kuonyesha dropdown
$stmtCat = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ?");
$stmtCat->execute([$user_id]);
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? '';
    $type = $_POST['type'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $transaction_date = $_POST['transaction_date'] ?? '';
    $description = $_POST['description'] ?? '';

    // Simple validation
    if (!$category_id || !$type || !$amount || !$transaction_date) {
        $message = "Please fill all required fields.";
    } elseif (!in_array($type, ['income', 'expense'])) {
        $message = "Invalid transaction type.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $message = "Amount must be a positive number.";
    } else {
        // Check category belongs to user
        $stmtCheck = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
        $stmtCheck->execute([$category_id, $user_id]);
        if (!$stmtCheck->fetch()) {
            $message = "Invalid category selected.";
        } else {
            // Insert transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, type, amount, transaction_date, description) VALUES (?, ?, ?, ?, ?, ?)");
            $inserted = $stmt->execute([$user_id, $category_id, $type, $amount, $transaction_date, $description]);
            if ($inserted) {
                $message = "Transaction added successfully.";
            } else {
                $message = "Failed to add transaction.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Transaction - Budget Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
        .sidebar { width: 250px; background: #002147; height: 100vh; float: left; color: white; padding-top: 30px; position: fixed; }
        .sidebar h2 { text-align: center; margin-bottom: 40px; }
        .sidebar a { display: block; padding: 15px 30px; text-decoration: none; color: white; }
        .sidebar a:hover { background: #003366; }
        .main { margin-left: 250px; padding: 30px; }
        form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; max-width: 500px; }
        label { display: block; margin-top: 15px; }
        input[type="text"], input[type="number"], input[type="date"], select, textarea {
            width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;
        }
        button {
            margin-top: 20px; background: #002147; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;
        }
        button:hover { background: #003366; }
        .message { margin-bottom: 20px; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
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

    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h1>Add Transaction</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="add_transaction.php">
        <label for="category_id">Category *</label>
        <select name="category_id" id="category_id" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>" <?= (isset($category_id) && $category_id == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="type">Type *</label>
        <select name="type" id="type" required>
            <option value="">-- Select Type --</option>
            <option value="income" <?= (isset($type) && $type == 'income') ? 'selected' : '' ?>>Income</option>
            <option value="expense" <?= (isset($type) && $type == 'expense') ? 'selected' : '' ?>>Expense</option>
        </select>

        <label for="amount">Amount (Tsh) *</label>
        <input type="number" step="0.01" name="amount" id="amount" value="<?= htmlspecialchars($amount ?? '') ?>" required>

        <label for="transaction_date">Date *</label>
        <input type="date" name="transaction_date" id="transaction_date" value="<?= htmlspecialchars($transaction_date ?? date('Y-m-d')) ?>" required>

        <label for="description">Description (optional)</label>
        <textarea name="description" id="description" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>

        <button type="submit">Add Transaction</button>
    </form>
</div>

</body>
</html>
