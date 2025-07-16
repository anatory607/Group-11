<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$editMode = false;
$editData = null;

// Fetch categories
$stmtCat = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ?");
$stmtCat->execute([$user_id]);
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Handle delete
if (isset($_GET['delete'])) {
    $budget_id = $_GET['delete'];
    $stmtDelete = $pdo->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
    if ($stmtDelete->execute([$budget_id, $user_id])) {
        $message = "Budget goal deleted successfully.";
    } else {
        $message = "Failed to delete budget goal.";
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $editMode = true;
    $editId = $_GET['edit'];
    $stmtEdit = $pdo->prepare("SELECT * FROM budgets WHERE id = ? AND user_id = ?");
    $stmtEdit->execute([$editId, $user_id]);
    $editData = $stmtEdit->fetch(PDO::FETCH_ASSOC);

    if (!$editData) {
        $message = "Invalid budget goal selected.";
        $editMode = false;
    }
}

// Handle form submission (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $budget_month = $_POST['budget_month'] ?? '';
    $budget_id = $_POST['budget_id'] ?? null;

    if (!$category_id || !$amount || !$budget_month) {
        $message = "Please fill all required fields.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $message = "Budget amount must be a positive number.";
    } else {
        if ($budget_id) {
            // Update
            $stmt = $pdo->prepare("UPDATE budgets SET category_id = ?, amount = ?, budget_month = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$category_id, $amount, $budget_month, $budget_id, $user_id])) {
                $message = "Budget goal updated successfully.";
                $editMode = false;
            } else {
                $message = "Failed to update budget.";
            }
        } else {
            // Add new
            $stmtCheck = $pdo->prepare("SELECT id FROM budgets WHERE user_id = ? AND category_id = ? AND budget_month = ?");
            $stmtCheck->execute([$user_id, $category_id, $budget_month]);
            if ($stmtCheck->fetch()) {
                $message = "Budget already set for this category and month.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO budgets (user_id, category_id, amount, budget_month) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $category_id, $amount, $budget_month])) {
                    $message = "Budget goal added successfully.";
                } else {
                    $message = "Failed to add budget goal.";
                }
            }
        }
    }
}

// Fetch budgets
$stmtBudgets = $pdo->prepare("SELECT b.*, c.name as category_name FROM budgets b JOIN categories c ON b.category_id = c.id WHERE b.user_id = ? ORDER BY b.budget_month DESC");
$stmtBudgets->execute([$user_id]);
$budgets = $stmtBudgets->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Budget Goals - Budget Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
        .sidebar { width: 250px; background: #002147; height: 100vh; float: left; color: white; padding-top: 30px; position: fixed; }
        .sidebar h2 { text-align: center; margin-bottom: 40px; }
        .sidebar a { display: block; padding: 15px 30px; text-decoration: none; color: white; }
        .sidebar a:hover { background: #003366; }
        .main { margin-left: 250px; padding: 30px; }
        form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; max-width: 500px; margin-bottom: 30px; }
        input, select { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #002147; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #003366; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 10px #ccc; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #002147; color: white; }
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
    <h1><?= $editMode ? 'Edit Budget Goal' : 'Budget Goals' ?></h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="budget.php<?= $editMode ? '?edit=' . $editData['id'] : '' ?>">
        <input type="hidden" name="budget_id" value="<?= $editMode ? $editData['id'] : '' ?>">

        <label for="category_id">Category *</label>
        <select name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($editMode && $cat['id'] == $editData['category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="amount">Budget Amount (Tsh) *</label>
        <input type="number" name="amount" step="0.01" required value="<?= $editMode ? $editData['amount'] : '' ?>">

        <label for="budget_month">Budget Month *</label>
        <input type="month" name="budget_month" required value="<?= $editMode ? $editData['budget_month'] : '' ?>">

        <button type="submit"><?= $editMode ? 'Update Budget Goal' : 'Add Budget Goal' ?></button>
    </form>

    <h2>My Budget Goals</h2>
    <?php if (count($budgets) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Category</th>
                    <th>Amount (Tsh)</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($budgets as $b): ?>
                <tr>
                    <td><?= htmlspecialchars(date('F Y', strtotime($b['budget_month']))) ?></td>
                    <td><?= htmlspecialchars($b['category_name']) ?></td>
                    <td><?= number_format($b['amount']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($b['created_at']))) ?></td>
                    <td>
                        <a href="budget.php?edit=<?= $b['id'] ?>">✏️ Edit</a> |
                        <a href="budget.php?delete=<?= $b['id'] ?>" onclick="return confirm('Are you sure you want to delete this budget goal?')">🗑 Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You haven't added any budget goals yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
