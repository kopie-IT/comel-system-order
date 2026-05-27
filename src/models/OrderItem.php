<?php

class OrderItem {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function forOrder(int $orderId): array {
        $stmt = $this->db->prepare(
            'SELECT oi.*, p.image AS product_image
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?
             ORDER BY oi.id ASC'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function create(int $orderId, array $item): void {
        $stmt = $this->db->prepare(
            'INSERT INTO order_items
             (order_id, product_id, size_id, variant_id, product_name, size_label, variant_label, unit_price, quantity, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['size_id']      ?? null,
            $item['variant_id']   ?? null,
            $item['product_name'],
            $item['size_label']   ?? null,
            $item['variant_label'] ?? null,
            $item['unit_price'],
            $item['quantity'],
            $item['unit_price'] * $item['quantity'],
        ]);
    }
}
