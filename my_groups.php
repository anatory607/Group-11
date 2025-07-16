<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch groups where user is a member
$stmt = $pdo->prepare("
    SELECT g.id, g.name, gm.role, g.created_at 
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = ?
");
$stmt->execute([$user_id]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>My Groups - Budget Tracker</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f9;
    margin: 0;
    color: #333;
  }
  a {
    text-decoration: none;
    color: #002147;
  }
  a:hover {
    text-decoration: underline;
  }
  .sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    width: 250px;
    background-color: #002147;
    padding-top: 30px;
    color: white;
    overflow-y: auto;
    box-shadow: 2px 0 5px rgba(0,0,0,0.2);
  }
  .sidebar h2 {
    text-align: center;
    margin-bottom: 40px;
    font-weight: 700;
    font-size: 24px;
    letter-spacing: 1.2px;
  }
  .sidebar a {
    display: block;
    padding: 15px 30px;
    font-weight: 600;
    color: white;
    transition: background-color 0.3s ease;
  }
  .sidebar a.active, .sidebar a:hover {
    background-color: #003366;
    cursor: pointer;
  }

  .main {
    margin-left: 250px;
    padding: 30px 40px;
    min-height: 100vh;
  }

  .header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30px;
  }
  .header h1 {
    margin: 0;
    font-weight: 700;
    font-size: 28px;
  }
  .btn-back {
    background: #007bff;
    border: none;
    color: white;
    padding: 8px 16px;
    border-radius: 5px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .btn-back:hover {
    background: #0056b3;
  }

  .group-box {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgb(0 0 0 / 0.1);
  }
  .group-box h3 {
    margin: 0 0 10px 0;
  }
  .group-box p {
    margin: 5px 0;
    font-weight: 600;
  }
  .group-box a {
    display: inline-block;
    margin-top: 10px;
    background: #002147;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    font-weight: 600;
    transition: background-color 0.3s ease;
  }
  .group-box a:hover {
    background: #003366;
  }

  @media (max-width: 768px) {
    .sidebar {
      width: 60px;
      padding-top: 10px;
      overflow-x: hidden;
    }
    .sidebar h2,
    .sidebar a {
      font-size: 0;
      padding: 10px 0;
    }
    .sidebar a {
      position: relative;
    }
    .sidebar a::before {
      content: attr(title);
      position: absolute;
      left: 60px;
      white-space: nowrap;
      background: #002147;
      padding: 5px 10px;
      border-radius: 5px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
      color: white;
      font-weight: 600;
      font-size: 14px;
    }
    .sidebar a:hover::before {
      opacity: 1;
    }
    .main {
      margin-left: 60px;
      padding: 20px;
    }
  }
</style>
</head>
<body>

<div class="sidebar">
  <h2>💰 Budget Tracker</h2>
  <a href="dashboard.php" title="Dashboard">🏠 Dashboard</a>
  <a href="add_transaction.php" title="Add Transaction">➕ Add Transaction</a>
  <a href="transactions.php" title="Transactions">📜 Transactions</a>
  <a href="budget.php" title="Budget Goals">🎯 Budget Goals</a>
  <a href="category.php" title="Categories">📂 Categories</a>
  <?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="users.php" title="User Management">👥 User Management</a>
  <?php endif; ?>
  <a href="create_group.php" title="Create Group">👥 Create Group</a>
  <a href="my_groups.php" class="active" title="My Groups">📂 My Groups</a>
  <a href="logout.php" title="Logout">🚪 Logout</a>
</div>

<div class="main">
  <div class="header">
    <h1>My Groups</h1>
    <button class="btn-back" onclick="history.back()">← Kurudi nyuma</button>
  </div>

  <?php if (count($groups) > 0): ?>
      <?php foreach ($groups as $group): ?>
        <div class="group-box">
          <h3><?= htmlspecialchars($group['name']) ?></h3>
          <p><strong>Role:</strong> <?= htmlspecialchars($group['role']) ?></p>
          <p><strong>Created:</strong> <?= date('Y-m-d', strtotime($group['created_at'])) ?></p>
          <a href="group_dashboard.php?group_id=<?= $group['id'] ?>">🔍 View Group</a>
        </div>
      <?php endforeach; ?>
  <?php else: ?>
      <p>You are not a member of any group.</p>
  <?php endif; ?>
</div>

</body>
</html>
