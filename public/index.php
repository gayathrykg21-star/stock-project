<?php
/**
 * MTI_SMS - Login Page
 * Compatible with PHP 5.5+
 */

require_once 'config/database.php';
require_once 'config/session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    
    if (empty($username) || empty($password) || empty($role)) {
        $error = 'Please fill in all fields.';
    } else {
        // Query user
        $sql = "SELECT * FROM users WHERE username = ? AND password = ? AND role = ? AND status = 'active'";
        $user = dbFetchOne($sql, array($username, $password, $role));
        
        if ($user) {
            loginUser($user);
            setMessage('Welcome, ' . $user['full_name'] . '!', 'success');
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTI_SMS - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="logo-section">
            <div class="logo-icon"><i class="fas fa-boxes-stacked"></i></div>
            <h1>MTI_SMS</h1>
            <p class="subtitle">Total Stock Management System</p>
        </div>
        
        <?php if (!empty($error)): ?>
        <div class="login-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Username</label>
                <input type="text" name="username" placeholder="Enter your username" value="admin" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Enter your password" value="admin123" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-shield-halved"></i> Login As</label>
                <select name="role" required>
                    <option value="STOCK_ADMIN">STOCK_ADMIN</option>
                    <option value="HOD">HOD</option>
                    <option value="DEPT_IN_CHARGE">DEPT_IN_CHARGE</option>
                    <option value="STAFF">STAFF</option>
                </select>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-right-to-bracket"></i> Sign In
            </button>
        </form>
        
        <div class="demo-credentials">
            <h4><i class="fas fa-info-circle"></i> Demo Credentials</h4>
            <p>
                <code>admin</code> / <code>admin123</code> — STOCK_ADMIN<br>
                <code>hod_cs</code> / <code>hod123</code> — HOD<br>
                <code>dept_cs</code> / <code>dept123</code> — DEPT_IN_CHARGE<br>
                <code>staff1</code> / <code>staff123</code> — STAFF
            </p>
        </div>
    </div>
</div>
</body>
</html>
