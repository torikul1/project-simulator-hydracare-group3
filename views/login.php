<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HydraCare | Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="register-container">
        <h2>Login to HydraCare</h2>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'signup_success'): ?>
            <p style="color: green; text-align: center;">Registration successful! Please login.</p>
        <?php endif; ?>

        <form action="../controllers/login_controller.php" method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-submit">Login</button>
        </form>
    </div>
</body>
</html>