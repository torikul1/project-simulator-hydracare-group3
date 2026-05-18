<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($orderId <= 0) {
    die("Error: Missing invalid system parameter records.");
}

$database = new Database();
$pdo = $database->getConnection();
$orderModel = new Order($pdo);

$orderData = $orderModel->getOrderDetails($orderId);
$orderItems = $orderModel->getOrderItems($orderId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Confirmed Successfully</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container success-container">
        <div class="success-card">
            <h2>✔️ Order Placed Successfully!</h2>
            <p>Thank you for shopping with us. Your invoice records are stored under reference ID tracking numbers below.</p>
            
            <div class="summary-details">
                <p><strong>System Order ID Reference:</strong> #<?= $orderData['id'] ?></p>
                <p><strong>Total Processing Cost:</strong> $<?= number_format($orderData['total_amount'], 2) ?></p>
                <p><strong>Payment Strategy Selected:</strong> <?= htmlspecialchars($orderData['payment_method']) ?></p>
                <p><strong>Current Order Tracking Status:</strong> <span class="badge-pending"><?= htmlspecialchars($orderData['status']) ?> admin approval</span></p>
            </div>

            <h3>Items Bundled</h3>
            <ul>
                <?php foreach ($orderItems as $item): ?>
                    <li><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>) - $<?= number_format($item['unit_price'], 2) ?> each</li>
                <?php endforeach; ?>
            </ul>

            <a href="../index.php"><button class="btn-primary">Return to Marketplace Home Page</button></a>
        </div>
    </div>
</body>
</html>