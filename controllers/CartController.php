<?php
require_once __DIR__ . '/../models/Cart.php';

class CartController {
    private $cartModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->cartModel = new Cart($pdo);
    }

    private function checkAuth() {
        if (session_status() == PHP_SESSION_NONE) { 
            session_start(); 
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
            header('Content-Type: application/json');
            // Inform the frontend of authentication failure clearly
            echo json_encode(['success' => false, 'message' => 'Customer login required.']);
            exit;
        }
    }

    public function add() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $medicineId = isset($data['medicine_id']) ? intval($data['medicine_id']) : 0;
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
        $userId = $_SESSION['user_id'];

        if ($medicineId <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid medicine selection or quantity.']);
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT availability FROM medicines WHERE id = ?");
        $stmt->execute([$medicineId]);
        $med = $stmt->fetch();

        if (!$med) {
            echo json_encode(['success' => false, 'message' => 'Medicine doesn\'t exist.']);
            exit;
        }

        $existing = $this->cartModel->getCartItem($userId, $medicineId);
        $totalRequested = $existing ? ($existing['quantity'] + $quantity) : $quantity;

        if ($totalRequested > $med['availability']) {
            echo json_encode(['success' => false, 'message' => 'Cannot add item. Requested amount exceeds available stock (' . $med['availability'] . ').']);
            exit;
        }

        if ($existing) {
            $this->cartModel->updateQuantity($existing['id'], $totalRequested);
        } else {
            $this->cartModel->addToCart($userId, $medicineId, $quantity);
        }

        $items = $this->cartModel->getUserCart($userId);
        echo json_encode(['success' => true, 'newCartCount' => count($items)]);
    }

    public function update() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $cartId = isset($data['cart_id']) ? intval($data['cart_id']) : 0;
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
        $userId = $_SESSION['user_id'];

        if ($cartId <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1.']);
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT m.availability FROM cart c JOIN medicines m ON c.medicine_id = m.id WHERE c.id = ?");
        $stmt->execute([$cartId]);
        $med = $stmt->fetch();

        if (!$med) {
            echo json_encode(['success' => false, 'message' => 'Cart item matching medicine warehouse links not found.']);
            exit;
        }

        if ($quantity > $med['availability']) {
            echo json_encode(['success' => false, 'message' => 'Exceeds available warehouse stock. Max: ' . $med['availability']]);
            exit;
        }

        $this->cartModel->updateQuantity($cartId, $quantity);

        $items = $this->cartModel->getUserCart($userId);
        $grandTotal = 0;
        foreach ($items as $item) { 
            $grandTotal += $item['price'] * $item['quantity']; 
        }

        echo json_encode(['success' => true, 'newGrandTotal' => $grandTotal]);
    }

    public function remove() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $cartId = isset($data['cart_id']) ? intval($data['cart_id']) : 0;
        $userId = $_SESSION['user_id'];

        $this->cartModel->removeFromCart($cartId);

        $items = $this->cartModel->getUserCart($userId);
        $grandTotal = 0;
        foreach ($items as $item) { 
            $grandTotal += $item['price'] * $item['quantity']; 
        }

        echo json_encode(['success' => true, 'newGrandTotal' => $grandTotal, 'newCartCount' => count($items)]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/database.php';
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    $cartController = new CartController($pdo);
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action === 'add') {
        $cartController->add();
    } elseif ($action === 'update') {
        $cartController->update();
    } elseif ($action === 'remove') {
        $cartController->remove();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: Requested controller action is invalid or missing.']);
    }
}