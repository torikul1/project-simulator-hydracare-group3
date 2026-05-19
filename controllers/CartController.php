<?php

require_once __DIR__ . '/../model/Cart.php'; 
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class CartController {
    private $cartModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->cartModel = new Cart($pdo);
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Customer login required.']);
            exit;
        }
    }

    // Helper method to parse input values coming from either Fetch JSON payloads or Native HTML Form submissions
    private function getRequestData() {
        $raw = json_decode(file_get_contents('php://input'), true);
        if (is_array($raw)) {
            return array_merge($_POST, $_GET, $raw);
        }
        return array_merge($_POST, $_GET);
    }

    public function add() {
        $this->checkAuth();
        $data = $this->getRequestData();

        $medicineId = isset($data['medicine_id']) ? intval($data['medicine_id']) : 0;
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;
        $userId = $_SESSION['user_id'];

        if ($medicineId <= 0 || $quantity <= 0) {
            $this->respond(['success' => false, 'message' => 'Invalid medicine selection or quantity.']);
        }

        $stmt = $this->pdo->prepare("SELECT availability FROM medicines WHERE id = ?");
        $stmt->execute([$medicineId]);
        $med = $stmt->fetch();

        if (!$med) {
            $this->respond(['success' => false, 'message' => 'Medicine does not exist.']);
        }

        $existing = $this->cartModel->getCartItem($userId, $medicineId);
        $totalRequested = $existing ? ($existing['quantity'] + $quantity) : $quantity;

        if ($totalRequested > $med['availability']) {
            $this->respond(['success' => false, 'message' => 'Cannot add item. Exceeds available stock.']);
        }

        if ($existing) {
            $this->cartModel->updateQuantity($existing['id'], $totalRequested);
        } else {
            $this->cartModel->addToCart($userId, $medicineId, $quantity);
        }

        $this->respond(['success' => true, 'message' => 'Item added successfully!']);
    }

    public function update() {
        $this->checkAuth();
        $data = $this->getRequestData();

        $medicineId = isset($data['medicine_id']) ? intval($data['medicine_id']) : 0;
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
        $userId = $_SESSION['user_id'];

        // Fallback resolution path to find item identifiers if cart_id wasn't generated
        $cartItem = $this->cartModel->getCartItem($userId, $medicineId);
        if (!$cartItem && isset($data['cart_id'])) {
            $cartId = intval($data['cart_id']);
        } else {
            $cartId = $cartItem ? intval($cartItem['id']) : 0;
        }

        if ($cartId <= 0 || $quantity <= 0) {
            $this->respond(['success' => false, 'message' => 'Quantity must be at least 1.']);
        }

        $this->cartModel->updateQuantity($cartId, $quantity);
        $this->respond(['success' => true, 'message' => 'Quantity updated successfully!']);
    }

    public function remove() {
        $this->checkAuth();
        $data = $this->getRequestData();

        $medicineId = isset($data['medicine_id']) ? intval($data['medicine_id']) : 0;
        $userId = $_SESSION['user_id'];

        $cartItem = $this->cartModel->getCartItem($userId, $medicineId);
        if ($cartItem) {
            $this->cartModel->removeFromCart(intval($cartItem['id']));
        } else if (isset($data['cart_id'])) {
            $this->cartModel->removeFromCart(intval($data['cart_id']));
        }

        $this->respond(['success' => true, 'message' => 'Item removed successfully!']);
    }

    private function respond($responseArray) {
        // Detect if request came from standard HTML form post or JS fetch
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'empty') {
            header('Content-Type: application/json');
            echo json_encode($responseArray);
        } else {
            // Standard form post simply redirects back to update view natively
            header("Location: ../views/cart.php");
        }
        exit;
    }
}

// Global Request Handler routing block entrypoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $database = new Database();
    $pdo = $database->getConnection();
    $cartController = new CartController($pdo);
    
    // Check $_POST first, then fallback to url query strings
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // Fallback query payload inspector for raw incoming fetch items
    if (empty($action)) {
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        $action = $jsonInput['action'] ?? '';
    }
    
    if ($action === 'add') {
        $cartController->add();
    } elseif ($action === 'update') {
        $cartController->update();
    } elseif ($action === 'remove') {
        $cartController->remove();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Action routing parameters validation failed. Received: ' . htmlspecialchars($action)]);
    }
}