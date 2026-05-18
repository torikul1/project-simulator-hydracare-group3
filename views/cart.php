<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cart.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$database = new Database();
$pdo = $database->getConnection();
$cartModel = new Cart($pdo);
$cartItems = $cartModel->getUserCart($_SESSION['user_id']);
$grandTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart Summary</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Your Shopping Cart</h2>
        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty. <a href="../index.php">Go browse medicines</a>.</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Vendor</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $grandTotal += $subtotal;
                    ?>
                        <tr id="cart-row-<?= $item['cart_id'] ?>">
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['vendor_name']) ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input type="number" 
                                       class="qty-input" 
                                       value="<?= $item['quantity'] ?>" 
                                       min="1" 
                                       max="<?= $item['availability'] ?>" 
                                       onchange="alterQuantity(<?= $item['cart_id'] ?>, this.value, <?= $item['price'] ?>)">
                            </td>
                            <td id="subtotal-<?= $item['cart_id'] ?>">$<?= number_format($subtotal, 2) ?></td>
                            <td>
                                <button class="btn-danger" onclick="removeItem(<?= $item['cart_id'] ?>)">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <h3>Total Amount: <span id="cart-grand-total">$<?= number_format($grandTotal, 2) ?></span></h3>
                <a href="checkout.php"><button class="btn-primary">Proceed to Checkout</button></a>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/cart.js"></script>
</body>
</html>