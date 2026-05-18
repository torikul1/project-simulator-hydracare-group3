<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Order.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: /login.php');
    exit;
}

$database = new Database();
$pdo = $database->getConnection();

$cartModel = new Cart($pdo);
$orderModel = new Order($pdo);

$cartItems = $cartModel->getUserCart($_SESSION['user_id']);
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$profile = $orderModel->getUserProfile($_SESSION['user_id']);
$grandTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout Process</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Secure Checkout Sequence</h2>
        
        <div class="invoice-box">
            <h3>Invoice Statement Preview</h3>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Qty Ordered</th>
                        <th>Unit Valuation</th>
                        <th>Sub-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): 
                        $sub = $item['price'] * $item['quantity'];
                        $grandTotal += $sub;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>$<?= number_format($sub, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h4>Invoice Grand Total: $<?= number_format($grandTotal, 2) ?></h4>
        </div>

        <form action="../controllers/OrderController.php" method="POST" id="checkoutForm" onsubmit="return validateCheckoutForm(event)">
            <div class="form-group">
                <label for="shipping_address">Shipping Destination Address:</label>
                <textarea name="shipping_address" id="shipping_address" rows="3"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Select Payment Method:</label>
                <div class="radio-group">
                    <label><input type="radio" name="payment_method" value="Credit Card"> Credit Card</label>
                    <label><input type="radio" name="payment_method" value="bKash"> bKash</label>
                    <label><input type="radio" name="payment_method" value="Nagad"> Nagad</label>
                    <label><input type="radio" name="payment_method" value="Bank Transfer"> Bank Transfer</label>
                    <label><input type="radio" name="payment_method" value="Cash on Delivery"> Cash on Delivery</label>
                </div>
            </div>

            <div class="action-buttons">
                <a href="cart.php" class="btn-secondary">Cancel and Return to Cart</a>
                <button type="submit" class="btn-success">Confirm Purchase</button>
            </div>
        </form>
    </div>

    <script src="js/validation.js"></script>
</body>
</html>