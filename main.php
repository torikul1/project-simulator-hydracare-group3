<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HydraCare | Modern Medical & Wellness Inventory Suite</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="views/css/main.css">
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="views/public/hydracarelogo.png" alt="HydraCare Logo" onerror="this.style.display='none'">
            <span>HydraCare</span>
        </a>

        <div class="nav-links">
            <a href="main.php"><i class="ri-home-4-line"></i> Home</a>
            

            <div class="auth-buttons-group">
                
              
                    <a href="views/login.php" class="btn-login"><i class="ri-login-box-line"></i> Login</a>
                    <a href="views/register.php" class="btn-signup"><i class="ri-user-add-line"></i> Sign Up</a>
                
            </div>
        </div>
    </nav>

    <header class="hero-banner">
        <div class="hero-content">
            <h1>Your Trusted Smart Pharmaceutical Network</h1>
            <p>Access your targeted medical compounds, review active inventory logs, and arrange secure clinical storage updates with our high-speed interactive shopping catalog system.</p>
            
        </div>
    </header>

    <main class="main-container">
        
        <div class="features-row">
            <div class="feature-card">
                <div class="feature-icon"><i class="ri-shield-cross-line"></i></div>
                <h3>Authenticated Vaults</h3>
                <p>All listings go through absolute multi-tier cryptographic routing chains assuring precise batch management parameters.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="ri-truck-line"></i></div>
                <h3>Accelerated Dispatch</h3>
                <p>Direct system pipeline connectivity optimizes medical supply dispatch tracking straight to warehouse endpoints.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="ri-pulse-line"></i></div>
                <h3>Real-time Inventories</h3>
                <p>Continuous dynamic mapping linking current physical quantities inside database records seamlessly down to your shopping views.</p>
            </div>
        </div>

       

    </main>

    <footer class="footer">
        <p>&copy; 2026 HydraCare Suite Inc. All operations secured under standardized local system compliance parameters.</p>
    </footer>

</body>
</html>