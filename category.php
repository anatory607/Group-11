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

// ✅ Ongeza default categories kama hazipo
$stmtCheckCat = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE user_id = ?");
$stmtCheckCat->execute([$user_id]);
$catCount = $stmtCheckCat->fetchColumn();

if ($catCount == 0) {
    $defaultCategories = [
        ['Food', 'Matumizi ya chakula na vinywaji'],
        ['Rent', 'Kodi ya nyumba'],
        ['Transport', 'Nauli na mafuta'],
        ['Utilities', 'Umeme, maji, na bili'],
        ['Education', 'Ada na vifaa vya shule'],
        ['Healthcare', 'Huduma za afya'],
        ['Entertainment', 'Burudani kama sinema na outing'],
        ['Shopping', 'Mavazi na bidhaa'],
        ['Salary', 'Mshahara wa kazi'],
        ['Business', 'Mapato ya biashara'],
        ['Investment', 'Mapato ya uwekezaji'],
        ['Gifts', 'Zawadi au misaada'],
        ['Bonus', 'Malipo ya ziada kazini']
    ];

    $stmtInsert = $pdo->prepare("INSERT INTO categories (name, description, user_id) VALUES (?, ?, ?)");
    foreach ($defaultCategories as $cat) {
        $stmtInsert->execute([$cat[0], $cat[1], $user_id]);
    }

    $message = "Default categories added successfully.";
}

// ✅ Handle category addition manually via form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $message = "Category name is required.";
    } else {
        $stmtCheck = $pdo->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ?");
        $stmtCheck->execute([$user_id, $name]);
        if ($stmtCheck->fetch()) {
            $message = "Category already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description, user_id) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $description, $user_id])) {
                $message = "Category added successfully.";
            } else {
                $message = "Error adding category.";
            }
        }
    }
}

// ✅ Fetch categories for this user
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY name");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories - Budget Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
        .sidebar { width: 250px; background: #002147; height: 100vh; float: left; color: white; padding-top: 30px; position: fixed; }
        .sidebar h2 { text-align: center; margin-bottom: 40px; }
        .sidebar a { display: block; padding: 15px 30px; text-decoration: none; color: white; }
        .sidebar a:hover { background: #003366; }
        .main { margin-left: 250px; padding: 30px; }
        form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; margin-bottom: 30px; max-width: 400px; }
        input[type="text"], textarea { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; }
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
    <h1>Manage Categories</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'success') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="category.php">
        <label for="name">Category Name *</label>
        <input type="text" name="name" id="name" required>

        <label for="description">Description (optional)</label>
        <textarea name="description" id="description" rows="3"></textarea>

        <button type="submit">Add Category</button>
    </form>

    <h2>Your Categories</h2>
    <?php if (count($categories) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td><?= htmlspecialchars($cat['description']) ?></td>
                    <td><?= date('Y-m-d', strtotime($cat['created_at'] ?? '')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No categories found. Add some to start tracking your expenses.</p>
    <?php endif; ?>
</div>

</body>
</html>
