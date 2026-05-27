<?php

class Category {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(): array {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function withProductCount(): array {
        $stmt = $this->db->query(
            'SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name ASC'
        );
        return $stmt->fetchAll();
    }

    public function create(string $name, string $type): int {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, type) VALUES (?, ?)'
        );
        $stmt->execute([$name, $type]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $type): void {
        $stmt = $this->db->prepare(
            'UPDATE categories SET name = ?, type = ? WHERE id = ?'
        );
        $stmt->execute([$name, $type, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function hasProducts(int $id): bool {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM products WHERE category_id = ?'
        );
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function nameExists(string $name, int $excludeId = 0): bool {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?'
        );
        $stmt->execute([$name, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
