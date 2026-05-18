<?php
// Safe check to make sure variables exist even if database counts return zero or empty rows
if (!isset($stats) || !$stats) {
    $stats = ['medicines' => 0, 'categories' => 0, 'customers' => 0, 'pending_orders' => 0];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 20px; color: #333; }
        nav { background: #333; padding: 15px; border-radius: 5px; }
        nav a { margin-right: 15px; text-decoration: none; font-weight: bold; color: #fff; }
        nav a:hover { color: #ddd; }
        .card-container { margin-top: 20px; }
        .card { background: #fff; padding: 20px; display: inline-block; margin: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); width: 180px; text-align: center; }
        .card h3 { margin: 0 0 10px 0; color: #555; }
        .card p { font-size: 24px; font-weight: bold; margin: 0; color: #007BFF; }
    </style>
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