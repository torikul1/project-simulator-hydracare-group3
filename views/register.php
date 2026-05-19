<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HydraCare | Advanced Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="register-container advanced-card">
            
            <div class="logo-wrapper">
                <img src="../views/public/hydracarelogo.png" alt="HydraCare Logo" class="login-logo">
            </div>

            <div class="auth-header">
                <h2>Create Your Account</h2>
                <p>Join HydraCare Online Medicine Shop today</p>
            </div>
            
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'email_exists'): ?>
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line"></i> This email is already registered!
                </div>
            <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line"></i> Registration failed. Please try again.
                </div>
            <?php endif; ?>

            <form id="registerForm" action="../controllers/register_controller.php" method="POST">
                
                <div class="form-grid">
                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <div class="input-field-wrapper">
                            <i class="ri-user-line field-icon"></i>
                            <input type="text" id="name" name="name" required placeholder="John Doe">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <div class="input-field-wrapper">
                            <i class="ri-mail-line field-icon"></i>
                            <input type="email" id="email" name="email" required placeholder="name@example.com">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Password (Min 8 chars)</label>
                        <div class="input-field-wrapper">
                            <i class="ri-lock-2-line field-icon"></i>
                            <input type="password" id="password" name="password" required placeholder="••••••••">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="phone">Phone Number</label>
                        <div class="input-field-wrapper">
                            <i class="ri-phone-line field-icon"></i>
                            <input type="text" id="phone" name="phone" required placeholder="+880 1XXX-XXXXXX">
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <label for="address">Delivery Address</label>
                        <div class="input-field-wrapper">
                            <i class="ri-map-pin-line field-icon"></i>
                            <input type="text" id="address" name="address" required placeholder="House, Road, Area, City">
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <label for="role">Register Account Type</label>
                        <div class="input-field-wrapper">
                            <i class="ri-user-settings-line field-icon"></i>
                            <select name="role" id="role">
                                <option value="customer">Customer (Buy Medicines)</option>
                                <option value="vendor">Vendor (Sell Medicines)</option>
                                <option value="admin">System Administrator</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit advanced-btn">
                    <span>Sign Up Now</span> <i class="ri-arrow-right-line"></i>
                </button>
                
                <div id="error-message" class="error-text"></div>
            </form>

            <div class="login-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>