<?php
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
    <nav>
        <a href="?action=dashboard">Dashboard</a>
        <a href="?action=categories">Manage Categories</a>
        <a href="?action=medicines">Manage Medicines</a>
        <a href="?action=customers">Customers</a>
        <a href="?action=orders">Orders & History</a>
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