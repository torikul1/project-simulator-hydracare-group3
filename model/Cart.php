<?php
// model/Cart.php

class Cart {
    private $db;

    // Expects a PDO database instance now
    public function __construct($db) {
        $this->db = $db;
    }

    // 1. Get a specific item to check if it's already in the user's cart
    public function getCartItem($userId, $medicineId) {
        $query = "SELECT * FROM cart WHERE user_id = ? AND medicine_id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([intval($userId), intval($medicineId)]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Returns row array or false
    }

    // 2. Add a new product row into the user's cart
    public function addToCart($userId, $medicineId, $quantity) {
        $query = "INSERT INTO cart (user_id, medicine_id, quantity) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([intval($userId), intval($medicineId), intval($quantity)]);
    }

    // 3. Update the item count quantity for an existing cart item id
    public function updateQuantity($cartId, $quantity) {
        $query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([intval($quantity), intval($cartId)]);
    }

    // 4. Remove an entry entirely from the cart list table
    public function removeFromCart($cartId) {
        $query = "DELETE FROM cart WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([intval($cartId)]);
    }

    // 5. Fetch all cart entries for a specific customer user id
    public function getUserCart($userId) {
        $query = "SELECT c.id AS cart_id, c.medicine_id, c.quantity, m.name, m.price, m.vendor_name, m.availability 
                  FROM cart c 
                  JOIN medicines m ON c.medicine_id = m.id 
                  WHERE c.user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([intval($userId)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}