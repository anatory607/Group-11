<?php
session_start();
require 'db.php';

// Hakikisha ni admin tu
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle delete action
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Avoid deleting yourself
    if ($delete_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
        $message = "User deleted successfully.";
    } else {
        $message = "You cannot delete yourself.";
    }
}

// Handle add user POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!$full_name || !$email || !$role || !$password || !$confirm_password) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $message = "Email already registered.";
        } else {
            // Insert new user with hashed password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, role, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $email, $role, $hash])) {
                $message = "User added successfully.";
            } else {
                $message = "Failed to add user.";
            }
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, full_name, email, role FROM users ORDER BY full_name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Management - Admin Panel</title>
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
        h1 {
            margin-bottom: 20px;
        }
        .message {
            background: #e7f3fe;
            color: #31708f;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bce8f1;
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
        a.delete-btn {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }
        a.delete-btn:hover {
            text-decoration: underline;
        }
        form.add-user-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            max-width: 500px;
        }
        form.add-user-form input, form.add-user-form select {
            width: 100%;
            padding: 8px;
            margin: 8px 0 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        form.add-user-form button {
            background: #002147;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        form.add-user-form button:hover {
            background: #0056b3;
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
    <h1>User Management</h1>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                <td>
                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                        <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Are you sure to delete this user?')" class="delete-btn">Delete</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Add New User</h2>
    <form class="add-user-form" method="POST" action="">
        <input type="hidden" name="add_user" value="1">
        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Role</label>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="admin">Admin</option>
        </select>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Add User</button>
    </form>
</div>

</body>
</html>
