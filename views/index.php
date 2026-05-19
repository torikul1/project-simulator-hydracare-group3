<?php
// views/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Gate: Redirect back to login if user isn't authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HydraCare | Home Catalog</title>
    <link rel="stylesheet" href="../views/css/indexstyle.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <img src="public/hydracarelogo.png" alt="HydraCare Logo">
        </div>
        
        <div class="nav-links">
            <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></span>
            <a href="profile.php"><i class="ri-user-settings-line"></i> My Profile</a>
            <a href="../controllers/logout_controller.php" class="logout-btn"><i class="ri-logout-box-line"></i> Logout</a>
        </div>
    </nav>

    <div class="catalog-container">
        <div class="welcome-card">
            <h2><i class="ri-capsule-line" style="color: #2ecc71;"></i> Medicine Catalog Dashboard</h2>
            <p>You have successfully logged in! Use the live filtering field below to navigate medical stock options instantly.</p>
        </div>
        
        <div class="search-wrapper">
            <input type="text" id="searchBox" class="search-input" placeholder="Search by medication name, manufacturing brand, or symptoms (e.g., Napa, Antacid)...">
        </div>

        <div class="workspace-layout">
            
            <div class="catalog-grid" id="catalogContainer">
                </div>

            <aside class="cart-sidebar">
                <h3 class="cart-title">
                    <i class="ri-shopping-bag-3-line" style="color: #2ecc71;"></i> Order Queue
                </h3>
                
                <div class="cart-items-wrapper" id="cartItemsContainer">
                    <p style="color: #94a3b8; font-size: 0.9rem; text-align: center; margin: 20px 0;">Your queue is currently empty.</p>
                </div>
                
                <div class="cart-summary-row">
                    <span>Total Bill:</span>
                    <span id="cartOrderTotal">৳0.00</span>
                </div>
                
                <button id="checkoutBtn" class="checkout-submit-btn" disabled>
                    <i class="ri-check-double-line"></i> Confirm Order
                </button>
            </aside>

        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>