<?php
class Cart {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getCartItem($userId, $medicineId) {
        $stmt = $this->db->prepare("SELECT * FROM cart WHERE user_id = ? AND medicine_id = ?");
        $stmt->execute([$userId, $medicineId]);
        return $stmt->fetch();
    }

    public function addToCart($userId, $medicineId, $quantity) {
        $stmt = $this->db->prepare("INSERT INTO cart (user_id, medicine_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $medicineId, $quantity]);
    }

    public function updateQuantity($cartId, $quantity) {
        $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        return $stmt->execute([$quantity, $cartId]);
    }

    public function getUserCart($userId) {
        $sql = "SELECT c.id AS cart_id, c.quantity, m.id AS medicine_id, m.name, m.vendor_name, m.price, m.availability 
                FROM cart c 
                JOIN medicines m ON c.medicine_id = m.id 
                WHERE c.user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function removeFromCart($cartId) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE id = ?");
        return $stmt->execute([$cartId]);
    }
}