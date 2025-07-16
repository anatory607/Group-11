<?php
session_start();
require 'db.php';

// Haki ya kuingia - only admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Load existing settings from database (unaweza ku store katika table 'settings' au config file)
// Kwa mfano hapa tunadhani kuna table settings key-value
// Format: key VARCHAR, value VARCHAR

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save posted settings
    $app_name = trim($_POST['app_name']);
    $contact_email = trim($_POST['contact_email']);

    // Validate basic
    if (filter_var($contact_email, FILTER_VALIDATE_EMAIL) === false) {
        $message = "Invalid email format.";
    } elseif (empty($app_name)) {
        $message = "App name is required.";
    } else {
        // Save to DB
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        $stmt->execute(['app_name', $app_name]);
        $stmt->execute(['contact_email', $contact_email]);
        $message = "Settings saved successfully.";
    }
}

// Load settings
function getSetting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn() ?: '';
}

$app_name = getSetting($pdo, 'app_name');
$contact_email = getSetting($pdo, 'contact_email');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
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
            margin: 12px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background: #0056b3;
        }
        .main {
            margin-left: 240px;
            padding: 30px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            max-width: 400px;
        }
        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
        }
        input[type=text], input[type=email] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            margin-top: 20px;
            padding: 12px 20px;
            border: none;
            background: #002147;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
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
    <h1>Admin Settings</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'success') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="app_name">App Name</label>
        <input type="text" id="app_name" name="app_name" value="<?= htmlspecialchars($app_name) ?>" required>

        <label for="contact_email">Contact Email</label>
        <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($contact_email) ?>" required>

        <button type="submit">Save Settings</button>
    </form>
</div>

</body>
</html>
