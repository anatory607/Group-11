<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = trim($_POST['group_name']);
    $user_id = $_SESSION['user_id'];

    if ($group_name) {
        $stmt = $pdo->prepare("INSERT INTO groups (name, owner_id) VALUES (?, ?)");
        if ($stmt->execute([$group_name, $user_id])) {
            $group_id = $pdo->lastInsertId();

            // Add creator as group admin
            $stmt2 = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')");
            $stmt2->execute([$group_id, $user_id]);

            $message = "Group created successfully.";
        } else {
            $message = "Failed to create group.";
        }
    } else {
        $message = "Group name is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Create Group - Budget Tracker</title>
<style>
  /* Sidebar style same as dashboard */
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f9;
    margin: 0;
    color: #333;
  }
  a {
    text-decoration: none;
    color: white;
  }
  a:hover {
    background: #003366;
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

  /* Form styles */
  form {
    background: white;
    padding: 25px 30px;
    border-radius: 10px;
    max-width: 400px;
    box-shadow: 0 3px 10px rgb(0 0 0 / 0.1);
  }
  form input[type="text"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
  }
  form button {
    background: #002147;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  form button:hover {
    background: #003366;
  }

  /* Message style */
  .message {
    margin-bottom: 20px;
    font-weight: 600;
    padding: 10px 15px;
    border-radius: 6px;
  }
  .message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  .message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  /* Responsive */
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
  <a href="create_group.php" class="active" title="Create Group">👥 Create Group</a>
  <a href="my_groups.php" title="My Groups">📂 My Groups</a>
  
  <a href="logout.php" title="Logout">🚪 Logout</a>
</div>

<div class="main">
  <div class="header">
    <h1>Create New Group</h1>
    <button class="btn-back" onclick="history.back()">← Kurudi nyuma</button>
  </div>

  <?php if ($message): ?>
    <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="text" name="group_name" placeholder="Group Name" required autofocus />
    <button type="submit">Create Group</button>
  </form>
</div>

</body>
</html>
