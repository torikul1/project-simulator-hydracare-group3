<?php
class AdminModel {
    private $db;

    public function __construct($dbConn) {
        $this->db = $dbConn;
    }

    public function getDashboardStats() {
        $stats = [];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM medicines");
        $stats['medicines'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM categories");
        $stats['categories'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
        $stats['customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        return $stats;
    }

    public function getAllCategories() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCategory($name, $type) {
        $stmt = $this->db->prepare("INSERT INTO categories (name, category_type) VALUES (?, ?)");
        return $stmt->execute([$name, $type]);
    }

    public function updateCategory($id, $name, $type) {
        $stmt = $this->db->prepare("UPDATE categories SET name = ?, category_type = ? WHERE id = ?");
        return $stmt->execute([$name, $type, $id]);
    }

    public function deleteCategory($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function checkMedicinesInCategory($cat_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM medicines WHERE category_id = ?");
        $stmt->execute([$cat_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }

    public function getAllMedicines() {
        $stmt = $this->db->query("SELECT m.*, c.name as category_name FROM medicines m LEFT JOIN categories c ON m.category_id = c.id ORDER BY m.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMedicineById($id) {
        $stmt = $this->db->prepare("SELECT * FROM medicines WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createMedicine($name, $cat_id, $vendor, $price, $stock, $desc, $img) {
        $stmt = $this->db->prepare("INSERT INTO medicines (name, category_id, vendor_name, price, availability, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $cat_id, $vendor, $price, $stock, $desc, $img]);
    }

    public function updateMedicine($id, $name, $cat_id, $vendor, $price, $stock, $desc, $img = null) {
        if ($img) {
            $stmt = $this->db->prepare("UPDATE medicines SET name = ?, category_id = ?, vendor_name = ?, price = ?, availability = ?, description = ?, image_path = ? WHERE id = ?");
            return $stmt->execute([$name, $cat_id, $vendor, $price, $stock, $desc, $img, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE medicines SET name = ?, category_id = ?, vendor_name = ?, price = ?, availability = ?, description = WHERE id = ?");
            return $stmt->execute([$name, $cat_id, $vendor, $price, $stock, $desc, $id]);
        }
    }

    public function deleteMedicine($id) {
        $stmt = $this->db->prepare("DELETE FROM medicines WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function isMedicineInPendingOrder($med_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.medicine_id = ? AND o.status = 'pending'");
        $stmt->execute([$med_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }

    public function getAllCustomers() {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = 'customer' ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCustomerCascade($user_id) {
        try {
            $this->db-
            $stmt1 = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt1->execute([$user_id]);

            // Find orders to clean up items first
            $stmt2 = $this->db->prepare("SELECT id FROM orders WHERE user_id = ?");
            $stmt2->execute([$user_id]);
            $orders = $stmt2->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($orders)) {
                $inQuery = implode(',', array_fill(0, count($orders), '?'));
                $stmt3 = $this->db->prepare("DELETE FROM order_items WHERE order_id IN ($inQuery)");
                $stmt3->execute($orders);

                $stmt4 = $this->db->prepare("DELETE FROM payments WHERE order_id IN ($inQuery)");
                $stmt4->execute($orders);
            }

            $stmt5 = $this->db->prepare("DELETE FROM orders WHERE user_id = ?");
            $stmt5->execute([$user_id]);

            $stmt6 = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
            $stmt6->execute([$user_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    
    public function getAllOrders() {
        $stmt = $this->db->query("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($order_id, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $order_id]);
    }

    public function getCompletedOrdersHistory() {
        $stmt = $this->db->query("SELECT o.id as order_id, o.order_date, o.total_amount, u.name as customer_name, m.name as medicine_name, oi.quantity, oi.unit_price FROM orders o JOIN users u ON o.user_id = u.id JOIN order_items oi ON o.id = oi.order_id JOIN medicines m ON oi.medicine_id = m.id WHERE o.status = 'accepted' ORDER BY o.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>