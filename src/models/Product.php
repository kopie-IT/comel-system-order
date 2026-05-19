<?php

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(int $categoryId = 0, string $search = ''): array {
        $sql    = 'SELECT p.*, c.name AS category_name, c.type AS category_type
                   FROM products p
                   JOIN categories c ON c.id = p.category_id
                   WHERE 1=1';
        $params = [];

        if ($categoryId > 0) {
            $sql     .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($search !== '') {
            $sql     .= ' AND p.name LIKE ?';
            $params[] = '%' . $search . '%';
        }
        $sql .= ' ORDER BY p.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name, c.type AS category_type
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO products (category_id, name, description, image, price, stock)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'] ?? '',
            $data['image'] ?? null,
            $data['price'] ?? 0,
            $data['stock'] ?? 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            'UPDATE products
             SET category_id = ?, name = ?, description = ?, price = ?, stock = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'] ?? '',
            $data['price'] ?? 0,
            $data['stock'] ?? 0,
            $id,
        ]);
    }

    public function updateImage(int $id, string $image): void {
        $stmt = $this->db->prepare('UPDATE products SET image = ? WHERE id = ?');
        $stmt->execute([$image, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function decrementStock(int $id, int $qty): void {
        $stmt = $this->db->prepare(
            'UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?'
        );
        $stmt->execute([$qty, $id]);
    }

    public function isInOrders(int $id): bool {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM order_items WHERE product_id = ?'
        );
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
