<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../config/database.php';

require_once '../model/adminmodel.php';

class admincontroller
{
    private $model;

    public function __construct()
    {
        // Strict Admin Gate Check
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo "Access Denied. Please run test_login.php first.";
            exit();
        }
        $database = new database();
        $dbConn = $database->getConnection();
        if (!$dbConn) {
            echo "Database connection failed! Check your XAMPP MySQL server.";
            exit();
        }
        $this->model = new AdminModel($dbConn);
    }

    public function invoke()
    {
        $page = $_GET['action'] ?? 'dashboard';

        switch ($page) {
            case 'dashboard':
                $stats = $this->model->getDashboardStats();

                include __DIR__ . '/../views/dashboard.php';
                break;

            case 'categories':
                $errors = [];
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $name = trim($_POST['name'] ?? '');
                    $type = $_POST['category_type'] ?? '';

                    if (empty($name) || empty($type)) {
                        $errors[] = "All fields are required.";
                    } else {
                        if (isset($_POST['id']) && !empty($_POST['id'])) {
                            $this->model->updateCategory($_POST['id'], $name, $type);
                        } else {
                            $this->model->createCategory($name, $type);
                        }
                    }
                }
                if (isset($_GET['delete'])) {
                    if ($this->model->checkMedicinesInCategory($_GET['delete'])) {
                        $errors[] = "Cannot delete category! Medicines exist under it.";
                    } else {
                        $this->model->deleteCategory($_GET['delete']);
                    }
                }
                $categories = $this->model->getAllCategories();
                include __DIR__ . '/../views/categories.php';
                break;

            case 'medicines':
                $errors = [];
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $name = trim($_POST['name'] ?? '');
                    $cat_id = $_POST['category_id'] ?? '';
                    $vendor = trim($_POST['vendor_name'] ?? '');
                    $price = floatval($_POST['price'] ?? 0);
                    $stock = intval($_POST['availability'] ?? 0);
                    $desc = trim($_POST['description'] ?? '');

                    if (empty($name) || empty($cat_id) || empty($vendor) || $price <= 0 || $stock < 0) {
                        $errors[] = "Invalid inputs. Ensure price > 0 and stock >= 0.";
                    } else {
                        $img_path = null;
                        if (isset($_FILES['medicine_image']) && $_FILES['medicine_image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['medicine_image'];
                            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                            if (!in_array($file['type'], $allowed_types) || $file['size'] > 2 * 1024 * 1024) {
                                $errors[] = "Image must be JPEG/PNG and less than 2MB.";
                            } else {
                                $target_dir = __DIR__ . '/../public/uploads/medicines/';
                                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                                $img_path = 'public/uploads/medicines/' . time() . '_' . basename($file['name']);
                                // FIXED: Resolved double word typo here
                                move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $img_path);
                            }
                        }

                        if (empty($errors)) {
                            if (isset($_POST['id']) && !empty($_POST['id'])) {
                                if ($img_path && isset($_POST['old_image']) && file_exists(__DIR__ . '/../' . $_POST['old_image'])) {
                                    unlink(__DIR__ . '/../' . $_POST['old_image']);
                                }
                                $this->model->updateMedicine($_POST['id'], $name, $cat_id, $vendor, $price, $stock, $desc, $img_path);
                            } else {
                                $this->model->createMedicine($name, $cat_id, $vendor, $price, $stock, $desc, $img_path);
                            }
                        }
                    }
                }
                if (isset($_GET['delete'])) {
                    if ($this->model->isMedicineInPendingOrder($_GET['delete'])) {
                        $errors[] = "Cannot delete item. It is linked to an active pending user order.";
                    } else {
                        $med = $this->model->getMedicineById($_GET['delete']);
                        if ($med && $med['image_path'] && file_exists(__DIR__ . '/../' . $med['image_path'])) {
                            unlink(__DIR__ . '/../' . $med['image_path']);
                        }
                        $this->model->deleteMedicine($_GET['delete']);
                    }
                }
                $medicines = $this->model->getAllMedicines();
                $categories = $this->model->getAllCategories();
                include __DIR__ . '/../views/medicines.php';
                break;

            case 'customers':
                if (isset($_GET['delete'])) {
                    $this->model->deleteCustomerCascade($_GET['delete']);
                }
                $customers = $this->model->getAllCustomers();
                include __DIR__ . '/../views/customers.php';
                break;

            case 'orders':
                $orders = $this->model->getAllOrders();
                $history = $this->model->getCompletedOrdersHistory();
                include __DIR__ . '/../views/purchase_requests.php';
                break;

            case 'update_order_ajax':
                header('Content-Type: application/json');
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $order_id = $_POST['order_id'] ?? '';
                    $status = $_POST['status'] ?? '';
                    if ($order_id && in_array($status, ['accepted', 'rejected'])) {
                        $this->model->updateOrderStatus($order_id, $status);
                        echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
                    }
                }
                exit();
        }
    }
}

$controller = new admincontroller();
$controller->invoke();
