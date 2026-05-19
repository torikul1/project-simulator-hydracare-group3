<?php
// Top of views/login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Safely pull in the auth helper file path
require_once __DIR__ . '/../controllers/auth_helper.php';

// Call the function safely
checkRememberMe();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HydraCare | Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="register-container">
        <div class="logo-wrapper">
            <img src="public/hydracarelogo.png" onerror="this.src='../public/hydracarelogo.png'" alt="HydraCare Logo" class="login-logo">
        </div>
        
        <h2>Login to HydraCare</h2>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'signup_success'): ?>
            <p class="success-text" style="color: #16a34a; background: #f0fdf4; padding: 10px; border-radius: 6px; border: 1px solid #bbf7d0; font-size: 0.85rem; text-align: center;">Registration successful! Please login.</p>
        <?php endif; ?>

        <?php if(isset($_GET['msg']) && ($_GET['msg'] == 'error' || $_GET['msg'] == 'invalid_credentials')): ?>
            <p class="error-text" style="color: #dc2626; background: #fef2f2; padding: 10px; border-radius: 6px; border: 1px solid #fecaca; font-size: 0.85rem; text-align: center;">Invalid email or password. Please try again.</p>
        <?php endif; ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'empty_fields'): ?>
            <p class="error-text" style="color: #dc2626; background: #fef2f2; padding: 10px; border-radius: 6px; border: 1px solid #fecaca; font-size: 0.85rem; text-align: center;">Please fill in all layout field boxes.</p>
        <?php endif; ?>

        <form action="../controllers/login_controller.php" method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Enter your email address">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>

            <div class="remember-me-group" style="display: flex; align-items: center; gap: 8px; margin: 15px 0; width: 100%;">
                <input type="checkbox" name="remember_me" id="remember_me" style="width: auto !important; margin: 0; cursor: pointer;">
                <label for="remember_me" style="margin: 0; font-weight: 500; font-size: 0.85rem; color: #64748b; cursor: pointer; user-select: none;">
                    Remember me for 1 month
                </label>
            </div>

            <div class="loginbtn" style="margin-top: 30px !important; display: block; width: 100%;">
                <button type="submit" class="btn-submit">Login</button>
            </div>
        </form>
        
        <div class="login-footer">
            Don't have an account? <a href="register.php">Sign Up</a>
        </div>
    </div>
</body>
</html>