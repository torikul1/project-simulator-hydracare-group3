<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HydraCare | Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="register-container">
        <form id="registerForm" action="../controllers/register_controller.php" method="POST">
            <h2>HydraCare Register</h2>
            
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Password (Min 8 chars)</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Phone Number</label>
                <input type="text" id="phone" name="phone" required>
            </div>

            <div class="input-group">
                <label>Address</label>
                <input type="text" id="address" name="address" required>
            </div>

            <div class="input-group">
                <label>Register As</label>
                <select name="role" id="role">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                    <option value="vendor">Vendor</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Sign Up</button>
            <div id="error-message" class="error-text"></div>
        </form>
    </div>
    <script src="js/script.js"></script>
</body>
</html>