<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        h2 { text-align: center; color: #002147; }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        li a {
            text-decoration: none;
            color: #002147;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Admin Dashboard</h2>
    <ul>
        <li><a href="users.php">👥 Manage Users</a></li>
        <li><a href="reports.php">📊 View Financial Reports</a></li>
        <li><a href="settings.php">⚙️ System Settings</a></li>
        <li><a href="dashboard.php">🏠 Back to User Dashboard</a></li>
    </ul>
</div>

</body>
</html>
