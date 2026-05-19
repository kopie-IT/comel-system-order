<?php

class OrderItem {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function forOrder(int $orderId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function create(int $orderId, array $item): void {
        $stmt = $this->db->prepare(
            'INSERT INTO order_items
             (order_id, product_id, size_id, product_name, size_label, unit_price, quantity, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['size_id'] ?? null,
            $item['product_name'],
            $item['size_label'] ?? null,
            $item['unit_price'],
            $item['quantity'],
            $item['unit_price'] * $item['quantity'],
        ]);
    }
}
