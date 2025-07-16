<?php
session_start();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already exists.";
    }

    // Register user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $hashedPassword, $role]);

        // Auto-login
        $user_id = $pdo->lastInsertId();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['role'] = $role;

        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Budget Tracker</title>
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .register-box {
            width: 400px;
            margin: 50px auto;
            background: white;
            box-shadow: 0 0 10px #ccc;
            padding: 30px;
            border-radius: 8px;
        }
        .register-box h2 {
            text-align: center;
            color: #007bff;
        }
        .form-group {
            margin-top: 15px;
        }
        .form-group label {
            font-weight: bold;
            display: block;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            margin-top: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            text-decoration: none;
            color: #007bff;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2>Register to Budget Tracker</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password (min 6 characters)</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <div class="form-group">
            <label for="role">Select Role</label>
            <select name="role" id="role" required>
                <option value="">-- Select Role --</option>
                <option value="member">Member</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button class="btn" type="submit">Create Account</button>

        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </form>
</div>

</body>
</html>

