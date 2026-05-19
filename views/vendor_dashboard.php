<?php
// views/vendor_dashboard.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Initialize session safely at the absolute top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================================
// 🛠️ DATABASE COUPLING LAYER
// ==========================================================================
if (file_exists('../config/db_config.php')) {
    require_once '../config/db_config.php'; 
} elseif (file_exists('../Model/db_config.php')) {
    require_once '../Model/db_config.php';
} elseif (file_exists('../config/database.php')) {
    require_once '../config/database.php';
} else {
    die("Core Database Configuration file could not be localized. Check your folder tree.");
}

if (file_exists('../model/cart.php')) {
    require_once '../model/cart.php';
}

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=auth_required");
    exit();
}

$current_vendor_id   = intval($_SESSION['user_id']);
$current_vendor_name = 'Admin'; 

if (isset($_SESSION['name']) && !empty($_SESSION['name'])) {
    $current_vendor_name = strval($_SESSION['name']);
} elseif (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
    $current_vendor_name = strval($_SESSION['user_name']);
}

$total_medicines = 0;
$total_stock     = 0;

if (isset($conn) && $conn) {
    
    // 📊 MASTER ACCOUNT DETECTOR RULE: If user is 'ara', view ALL records across companies
    if ($current_vendor_name === 'ara') {
        
        // Count All Global Inventory
        $q1 = $conn->query("SELECT COUNT(*) AS med_count FROM medicines");
        if ($q1) {
            $res1 = $q1->fetch_assoc();
            $total_medicines = intval($res1['med_count']);
        }

        // Sum All Global Quantities
        $q2 = $conn->query("SELECT SUM(availability) AS stock_sum FROM medicines");
        if ($q2) {
            $res2 = $q2->fetch_assoc();
            if ($res2 && $res2['stock_sum'] !== null) {
                $total_stock = intval($res2['stock_sum']);
            }
        }
        
    } else {
        // Standard Filtering for normal sub-vendors
        $q1 = $conn->prepare("SELECT COUNT(*) AS med_count FROM medicines WHERE vendor_name = ? OR vendor_name = ''");
        if ($q1) {
            $q1->bind_param("s", $current_vendor_name);
            $q1->execute();
            $res1 = $q1->get_result()->fetch_assoc();
            if ($res1) { $total_medicines = intval($res1['med_count']); }
            $q1->close();
        }

        $q2 = $conn->prepare("SELECT SUM(availability) AS stock_sum FROM medicines WHERE vendor_name = ? OR vendor_name = ''");
        if ($q2) {
            $q2->bind_param("s", $current_vendor_name);
            $q2->execute();
            $res2 = $q2->get_result()->fetch_assoc();
            if ($res2 && $res2['stock_sum'] !== null) { $total_stock = intval($res2['stock_sum']); }
            $q2->close();
        }
    }

} else {
    $total_medicines = 0;
    $total_stock     = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard</title>
    <link rel="stylesheet" href="../views/css/vendordas.css">
</head>
<body>

<div class="container">
    <div class="card">
        <h2>Vendor Dashboard</h2>
        <div class="text-muted">
            Welcome, <strong><?php echo htmlspecialchars($current_vendor_name); ?></strong> 
            <span class="badge">Vendor ID: <?php echo $current_vendor_id; ?></span>
        </div>
        
        <hr>

        <h4>Operational Summary Metrics</h4>
        <table class="metrics-table">
            <tbody>
                <tr>
                    <th>Total Unique Medicines</th>
                    <td><span class="fw-bold"><?php echo $total_medicines; ?></span> items</td>
                </tr>
                <tr>
                    <th>Aggregate Warehouse Stock</th>
                    <td><span class="text-success"><?php echo $total_stock; ?></span> units</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h4>Management Controls</h4>
        <div class="controls-grid">
            <a href="medicines.php" class="btn btn-primary">
                🗂️ View & Manage Medicines Inventory
            </a>
            <a href="cart.php" class="btn btn-secondary">
                🛒 View Customer Shopping Carts
            </a>
            <a href="../controllers/statistics.php" class="btn btn-warning">
                📊 Refresh Dashboard Analytics
            </a>
            <a href="../views/profile.php" class="btn btn-secondary">🙈 View Profile</a>
            <a href="login.php?logout=1" class="btn btn-danger">
                🔒 Secure Logout System
            </a>
        </div>
    </div>
</div>

</body>
</html>