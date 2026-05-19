<?php
class Order {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getUserProfile($userId) {
        $stmt = $this->db->prepare("SELECT address FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getOrderDetails($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function getOrderItems($orderId) {
        $sql = "SELECT oi.quantity, oi.unit_price, m.name 
                FROM order_items oi 
                JOIN medicines m ON oi.medicine_id = m.id 
                WHERE oi.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}