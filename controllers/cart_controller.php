<?php

header('Content-Type: application/json; charset=utf-8');

// Enable internal error visibility for troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Validation Gate
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User session expired. Please log in again.']);
    exit();
}

// 2. Safe Database Configuration inclusion link
$config_path = __DIR__ . '/../config/db_config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    echo json_encode(['success' => false, 'message' => 'Database configuration file missing. Check folder mapping.']);
    exit();
}

// Ensure database variable exists
if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Database connection resource variable not defined.']);
    exit();
}

// 3. Initialize active session structure elements if missing
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 4. Capture and parse inbound fetch requests safely
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);
$action = isset($input['action']) ? $input['action'] : '';

switch ($action) {
    case 'add':
        $medicine_id = isset($input['medicine_id']) ? intval($input['medicine_id']) : 0;

        if ($medicine_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product identifier code provided.']);
            exit();
        }

        // Query database mapping matching your actual medicine table columns
        $stmt = $conn->prepare("SELECT id, name, price, availability FROM medicines WHERE id = ? LIMIT 1");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'SQL prepare compilation failure: ' . $conn->error]);
            exit();
        }

        $stmt->bind_param("i", $medicine_id);
        $stmt->execute();
        $med = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$med) {
            echo json_encode(['success' => false, 'message' => 'Medication item could not be tracked in warehouse inventory rows.']);
            exit();
        }

        // Validate customer allocations against matching database availability column
        $current_in_cart = isset($_SESSION['cart'][$medicine_id]) ? $_SESSION['cart'][$medicine_id]['quantity'] : 0;
        $next_quantity = $current_in_cart + 1;

        if ($next_quantity > $med['availability']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient warehouse items available. Maximum limit reached.']);
            exit();
        }

        // Apply array parameters safely
        $_SESSION['cart'][$medicine_id] = [
            'id' => intval($med['id']),
            'name' => $med['name'],
            'price' => floatval($med['price']),
            'quantity' => $next_quantity
        ];

        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        break;

    case 'fetch':
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown application controller routing task requested.']);
        break;
}
exit();
