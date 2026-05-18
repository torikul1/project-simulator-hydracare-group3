<?php
require_once __DIR__ . '/../models/Cart.php';

class OrderController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function enforceAuth() {
        if (session_status() == PHP_SESSION_NONE) { 
            session_start(); 
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
            header('Location: ../views/login.php');
            exit;
        }
    }

    public function placeOrder() {
        $this->enforceAuth();
        $userId = $_SESSION['user_id'];

        // Server-side Validation
        $shippingAddress = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
        $paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';

        if (empty($shippingAddress) || empty($paymentMethod)) {
            die("Error: Shipping address and payment method choice are mandatory details.");
        }

        try {
            $this->pdo->beginTransaction();

            $cartModel = new Cart($this->pdo);
            $cartItems = $cartModel->getUserCart($userId);

            if (empty($cartItems)) {
                throw new Exception("Your shopping cart layout is currently empty.");
            }

            $totalAmount = 0;
            foreach ($cartItems as $item) {
                if ($item['quantity'] > $item['availability']) {
                    throw new Exception("Product '" . $item['name'] . "' has run out of stock during processing.");
                }
                $totalAmount += $item['price'] * $item['quantity'];
            }

            $orderStmt = $this->pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status, payment_method) VALUES (?, ?, ?, 'pending', ?)");
            $orderStmt->execute([$userId, $totalAmount, $shippingAddress, $paymentMethod]);
            $orderId = $this->pdo->lastInsertId();

            $itemStmt = $this->pdo->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stockStmt = $this->pdo->prepare("UPDATE medicines SET availability = availability - ? WHERE id = ?");

            foreach ($cartItems as $item) {
                $itemStmt->execute([$orderId, $item['medicine_id'], $item['quantity'], $item['price']]);
                $stockStmt->execute([$item['quantity'], $item['medicine_id']]);
            }

            $payStmt = $this->pdo->prepare("INSERT INTO payments (order_id, amount, payment_method, transaction_id) VALUES (?, ?, ?, ?)");
            $mockTxnId = "TXN-RAW-" . strtoupper(uniqid());
            $payStmt->execute([$orderId, $totalAmount, $paymentMethod, $mockTxnId]);

            $this->pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

            $this->pdo->commit();

            header("Location: ../views/order_success.php?order_id=" . $orderId);
            exit;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            die("Checkout sequence terminated: " . $e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/database.php';
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    $orderController = new OrderController($pdo);
    $orderController->placeOrder();
}