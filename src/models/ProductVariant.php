<?php

class ProductVariant {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function forProduct(int $productId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM product_variants WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(int $productId, string $label, float $price, int $stock, ?string $image = null): int {
        $stmt = $this->db->prepare(
            'INSERT INTO product_variants (product_id, variant_label, price, stock, image) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$productId, $label, $price, $stock, $image]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteForProduct(int $productId): void {
        $this->db->prepare('DELETE FROM product_variants WHERE product_id = ?')->execute([$productId]);
    }

    public function decrementStock(int $id, int $qty): void {
        $this->db->prepare(
            'UPDATE product_variants SET stock = GREATEST(stock - ?, 0) WHERE id = ?'
        )->execute([$qty, $id]);
    }

    public function incrementStock(int $id, int $qty): void {
        $this->db->prepare(
            'UPDATE product_variants SET stock = stock + ? WHERE id = ?'
        )->execute([$qty, $id]);
    }
}
