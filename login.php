<?php
// Start session only if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

$error = '';

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role"] = $user["role"];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Budget Tracker</title>
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .login-box {
            width: 400px;
            margin: 80px auto;
            background: white;
            box-shadow: 0 0 10px #ccc;
            padding: 30px;
            border-radius: 8px;
        }
        .login-box h2 {
            text-align: center;
            color: #007bff;
        }
        .form-group {
            margin-top: 20px;
        }
        .form-group label {
            font-weight: bold;
            display: block;
        }
        .form-group input {
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
            text-align: center;
            margin-top: 10px;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        .register-link a {
            text-decoration: none;
            color: #007bff;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>B&E Tracker - Login</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email / Username</label>
            <input type="text" name="email" id="email" placeholder="Enter email or username" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter password" required>
        </div>

        <button class="btn" type="submit">Login</button>

        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
    </form>
</div>

</body>
</html>
