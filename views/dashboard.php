<?php
// views/admin_dashboard.php (or integrated within your AdminController layout render context)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================================
// ⚠️ FIXED HERE: ADMIN SECURITY ACCESS GATEWAY
// ==========================================================================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // If the account isn't an admin, bounce them back to the login page immediately
    header("Location: ../views/login.php?msg=access_denied");
    exit();
}

if (!isset($stats) || !$stats) {
    $stats = ['medicines' => 0, 'categories' => 0, 'customers' => 0, 'pending_orders' => 0];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../views/css/admin.css">
</head>
<body>
    <h2>Admin Management System</h2>
    
    <p style="color: #475569;">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong> (Admin)</p>
    
    <nav>
        <a href="?action=dashboard">Dashboard</a>
        <a href="?action=categories">Manage Categories</a>
        <a href="?action=medicines">Manage Medicines</a>
        <a href="?action=customers">Customers</a>
        <a href="?action=orders">Orders & History</a>
        <a href="../views/profile.php">Profile</a>
    
        <a href="../controllers/logout_controller.php" style="color: red; margin-left: 20px;">Logout</a>
    </nav>
    <hr>
    <h3>System Overview Metrics</h3>
    <div class="card-container">
        <div class="card"><h3>Medicines</h3><p><?php echo $stats['medicines']; ?></p></div>
        <div class="card"><h3>Categories</h3><p><?php echo $stats['categories']; ?></p></div>
        <div class="card"><h3>Customers</h3><p><?php echo $stats['customers']; ?></p></div>
        <div class="card"><h3>Pending Orders</h3><p><?php echo $stats['pending_orders']; ?></p></div>
    </div>
</body>
</html>