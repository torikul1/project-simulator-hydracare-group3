<?php
// views/cart.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?msg=auth_required");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$pdo = $database->getConnection();

$userId = intval($_SESSION['user_id']);
$allMedicines = [];
$cartItems = [];

// Fetch Master Catalog Inventory
$medQuery = $pdo->query("SELECT m.*, c.name AS category_name FROM medicines m LEFT JOIN categories c ON m.category_id = c.id ORDER BY m.name ASC");
while ($row = $medQuery->fetch(PDO::FETCH_ASSOC)) {
    $allMedicines[$row['id']] = $row;
}

// Fetch Active Cart Selections linked to active database configuration context map paths
$cartStmt = $pdo->prepare("SELECT c.id AS cart_id, c.medicine_id, c.quantity, m.name, m.price, m.vendor_name 
                           FROM cart c JOIN medicines m ON c.medicine_id = m.id WHERE c.user_id = ?");
$cartStmt->execute([$userId]);
while ($row = $cartStmt->fetch(PDO::FETCH_ASSOC)) {
    $cartItems[$row['medicine_id']] = $row;
}

// Calculate values
$grandTotal = 0;
foreach ($cartItems as $item) {
    $grandTotal += floatval($item['price']) * intval($item['quantity']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>HydraCare | Shopping Suite Desk</title>
    <link rel="stylesheet" href="css/indexstyle.css">
    <link rel="stylesheet" href="css/cart.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .workspace-layout.row-layout {
            display: flex;
            gap: 24px;
            align-items: flex-start;
            margin-top: 20px;
        }

        .main-inventory-area {
            flex: 1;
        }

        .cart-sidebar {
            width: 360px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            position: sticky;
            top: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .queue-item {
            border-bottom: 1px solid #f1f5f9;
            padding: 12px 0;
        }

        .queue-item-top {
            font-weight: 600;
            font-size: 0.95rem;
            color: #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .queue-item-bottom {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .qty-submit-btn {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 4px;
            padding: 2px 10px;
            font-weight: bold;
            cursor: pointer;
        }

        .qty-submit-btn:hover {
            background: #e2e8f0;
        }

        .inline-form {
            display: inline;
            margin: 0;
            padding: 0;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .cart-empty-msg {
            text-align: center;
            color: #94a3b8;
            padding: 40px 0;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="navbar-brand"><span style="color:white;font-weight:700;">HydraCare Suite</span></div>
        <div class="nav-links">

            <a href="../controllers/logout_controller.php"><i class="ri-logout-box-line"></i> Logout</a>
        </div>
    </nav>

    <div class="catalog-container">
        <div class="workspace-layout row-layout">

            <div class="main-inventory-area">
                <h3 class="section-heading"><i class="ri-apps-2-line"></i> Store Inventory Catalog</h3>
                <div class="custom-med-grid">
                    <?php foreach ($allMedicines as $med): ?>
                        <div class="med-item-card">
                            <div class="med-card-img" style="height:140px; background:#f8fafc; display:flex; justify-content:center; align-items:center;">
                                <?php
                                $imgFile = basename($med['image_path'] ?? '');
                                if (!empty($imgFile) && file_exists('../public/uploads/' . $imgFile)) {
                                    $src = '../public/uploads/' . $imgFile;
                                } else if (strpos(strtolower($med['name']), 'ace') !== false && file_exists('../public/uploads/med_1779135857.jpeg')) {
                                    $src = '../public/uploads/med_1779135857.jpeg';
                                } else if (strpos(strtolower($med['name']), 'adryl') !== false && file_exists('../public/uploads/med_1779137392.jpeg')) {
                                    $src = '../public/uploads/med_1779137392.jpeg';
                                } else if (strpos(strtolower($med['name']), 'azithro') !== false && file_exists('../public/uploads/med_1779137400.jpeg')) {
                                    $src = '../public/uploads/med_1779137400.jpeg';
                                } else {
                                    $src = 'https://placehold.co/150?text=' . urlencode($med['name']);
                                }
                                ?>
                                <img src="<?= htmlspecialchars($src) ?>" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='https://placehold.co/150?text=Medicine'">
                            </div>
                            <div class="med-card-details">
                                <h4><?= htmlspecialchars($med['name']) ?></h4>
                                <p class="med-vendor">By: <span><?= htmlspecialchars($med['vendor_name']) ?></span></p>
                                <div class="med-card-footer">
                                    <span class="price-lbl">৳<?= number_format($med['price'], 2) ?></span>

                                    <form action="../controllers/CartController.php" method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="medicine_id" value="<?= intval($med['id']) ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="add-to-queue-btn"><i class="ri-add-fill"></i> Add to Queue</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="cart-sidebar">
                <h3 class="cart-title"><i class="ri-shopping-bag-3-line" style="color:#2ecc71;"></i> Your Active Queue</h3>

                <div class="cart-items-wrapper">
                    <?php if (empty($cartItems)): ?>
                        <p class="cart-empty-msg">Your queue is currently empty.</p>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): $sub = floatval($item['price']) * intval($item['quantity']); ?>
                            <div class="queue-item">
                                <div class="queue-item-top">
                                    <span><?= htmlspecialchars($item['name']) ?></span>

                                    <form action="../controllers/CartController.php" method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="medicine_id" value="<?= intval($item['medicine_id']) ?>">
                                        <input type="hidden" name="cart_id" value="<?= intval($item['cart_id']) ?>">
                                        <button type="submit" class="remove-btn"><i class="ri-delete-bin-6-line"></i></button>
                                    </form>
                                </div>
                                <div class="queue-item-bottom">
                                    <div class="qty-controls">
                                        <form action="../controllers/CartController.php" method="POST" class="inline-form">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="medicine_id" value="<?= intval($item['medicine_id']) ?>">
                                            <input type="hidden" name="cart_id" value="<?= intval($item['cart_id']) ?>">
                                            <input type="hidden" name="quantity" value="<?= intval($item['quantity']) - 1 ?>">
                                            <button type="submit" class="qty-submit-btn">−</button>
                                        </form>

                                        <span style="padding: 0 8px; font-weight:700;"><?= $item['quantity'] ?></span>

                                        <form action="../controllers/CartController.php" method="POST" class="inline-form">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="medicine_id" value="<?= intval($item['medicine_id']) ?>">
                                            <input type="hidden" name="cart_id" value="<?= intval($item['cart_id']) ?>">
                                            <input type="hidden" name="quantity" value="<?= intval($item['quantity']) + 1 ?>">
                                            <button type="submit" class="qty-submit-btn">+</button>
                                        </form>
                                    </div>
                                    <span style="font-weight:700; color:#0f172a;">৳<?= number_format($sub, 2) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div style="display:flex; justify-content:space-between; margin-top:20px; border-top:1px dashed #cbd5e1; padding-top:15px;">
                    <span style="font-weight:600; color:#64748b;">Total Bill:</span>
                    <span style="color:#2563eb; font-weight:800; font-size:1.1rem;">৳<?= number_format($grandTotal, 2) ?></span>
                </div>

                <button class="checkout-submit-btn" style="width:100%; margin-top:15px;" <?= ($grandTotal > 0) ? '' : 'disabled' ?> onclick="window.location.href='checkout.php'">
                    Confirm Order Selection
                </button>
            </aside>

        </div>
    </div>

</body>

</html>