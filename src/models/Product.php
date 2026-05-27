<?php

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(int $categoryId = 0, string $search = '', bool $inStockOnly = false): array {
        $sql    = 'SELECT p.*, c.name AS category_name, c.type AS category_type,
                       CASE WHEN c.type = \'pakaian\' THEN
                           COALESCE((SELECT MIN(ps.price) FROM product_sizes ps WHERE ps.product_id = p.id AND ps.stock > 0), p.price)
                       ELSE p.price END AS display_price
                   FROM products p
                   JOIN categories c ON c.id = p.category_id
                   WHERE p.deleted_at IS NULL';
        $params = [];

        if ($categoryId > 0) {
            $sql     .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($search !== '') {
            $sql     .= ' AND p.name LIKE ?';
            $params[] = '%' . $search . '%';
        }
        if ($inStockOnly) {
            $sql .= ' AND (
                (c.type = "produk" AND p.stock > 0)
                OR (c.type = "pakaian" AND EXISTS (
                    SELECT 1 FROM product_sizes ps
                    WHERE ps.product_id = p.id AND ps.stock > 0
                ))
            )';
        }
        $sql .= ' ORDER BY p.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function softDelete(int $id): void {
        $this->db->prepare('UPDATE products SET deleted_at = NOW() WHERE id = ?')->execute([$id]);
    }

    public function restore(int $id): void {
        $this->db->prepare('UPDATE products SET deleted_at = NULL WHERE id = ?')->execute([$id]);
    }

    public function forceDelete(int $id): void {
        // Unlink from order_items (product_name is already stored there)
        $this->db->prepare('UPDATE order_items SET product_id = NULL WHERE product_id = ?')->execute([$id]);
        // Delete sizes
        $this->db->prepare('DELETE FROM product_sizes WHERE product_id = ?')->execute([$id]);
        // Delete product
        $this->db->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    }

    public function trashed(): array {
        $stmt = $this->db->query(
            'SELECT p.*, c.name AS category_name, c.type AS category_type
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.deleted_at IS NOT NULL
             ORDER BY p.deleted_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function trashedCount(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NOT NULL')->fetchColumn();
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

    public function getPreviewImagesForCategory(int $categoryId, int $limit = 8): array {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT image FROM (
                 SELECT p.image AS image
                 FROM products p
                 WHERE p.category_id = ? AND p.deleted_at IS NULL AND p.image IS NOT NULL
                 UNION ALL
                 SELECT pi.image AS image
                 FROM product_images pi
                 JOIN products p ON p.id = pi.product_id
                 WHERE p.category_id = ? AND p.deleted_at IS NULL
             ) AS preview_images
             ORDER BY RAND()
             LIMIT ?'
        );
        $stmt->execute([$categoryId, $categoryId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateImage(int $id, ?string $image): void {
        $stmt = $this->db->prepare('UPDATE products SET image = ? WHERE id = ?');
        $stmt->execute([$image, $id]);
    }

    public function getImages(int $id): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function addImage(int $productId, string $image, int $sortOrder = 0): void {
        $this->db->prepare(
            'INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)'
        )->execute([$productId, $image, $sortOrder]);
    }

    public function deleteImage(int $imageId): ?string {
        $stmt = $this->db->prepare('SELECT image FROM product_images WHERE id = ?');
        $stmt->execute([$imageId]);
        $path = $stmt->fetchColumn();
        if ($path) {
            $this->db->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imageId]);
            return $path;
        }
        return null;
    }

    public function deleteImages(int $productId): void {
        $this->db->prepare('DELETE FROM product_images WHERE product_id = ?')->execute([$productId]);
    }

    public function getImagesForProducts(array $ids): array {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT product_id, image FROM product_images
             WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute($ids);
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[$row['product_id']][] = $row['image'];
        }
        return $grouped;
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

    public function incrementStock(int $id, int $qty): void {
        $stmt = $this->db->prepare(
            'UPDATE products SET stock = stock + ? WHERE id = ?'
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
