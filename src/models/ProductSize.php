<?php

class ProductSize {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function forProduct(int $productId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM product_sizes WHERE product_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM product_sizes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(int $productId, string $label, float $price, int $stock): int {
        $stmt = $this->db->prepare(
            'INSERT INTO product_sizes (product_id, size_label, price, stock)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$productId, $label, $price, $stock]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $label, float $price, int $stock): void {
        $stmt = $this->db->prepare(
            'UPDATE product_sizes SET size_label = ?, price = ?, stock = ? WHERE id = ?'
        );
        $stmt->execute([$label, $price, $stock, $id]);
    }

    public function deleteForProduct(int $productId): void {
        $stmt = $this->db->prepare('DELETE FROM product_sizes WHERE product_id = ?');
        $stmt->execute([$productId]);
    }

    public function decrementStock(int $id, int $qty): void {
        $stmt = $this->db->prepare(
            'UPDATE product_sizes SET stock = GREATEST(stock - ?, 0) WHERE id = ?'
        );
        $stmt->execute([$qty, $id]);
    }

    public function incrementStock(int $id, int $qty): void {
        $stmt = $this->db->prepare(
            'UPDATE product_sizes SET stock = stock + ? WHERE id = ?'
        );
        $stmt->execute([$qty, $id]);
    }
}
