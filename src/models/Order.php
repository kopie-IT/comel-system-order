<?php

class Order {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(string $status = ''): array {
        $sql    = 'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
                   FROM orders o
                   JOIN customers c ON c.id = o.customer_id
                   WHERE 1=1';
        $params = [];
        if ($status !== '') {
            $sql     .= ' AND o.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY o.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
             FROM orders o
             JOIN customers c ON c.id = o.customer_id
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByOrderNumber(string $orderNumber): array|false {
        $stmt = $this->db->prepare(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
             FROM orders o
             JOIN customers c ON c.id = o.customer_id
             WHERE o.order_number = ?'
        );
        $stmt->execute([$orderNumber]);
        return $stmt->fetch();
    }

    public function create(int $customerId, string $address, float $total): array {
        $orderNumber = 'ORD-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
        $stmt = $this->db->prepare(
            'INSERT INTO orders (order_number, customer_id, address, total, status)
             VALUES (?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$orderNumber, $customerId, $address, $total]);
        return [
            'id'           => (int)$this->db->lastInsertId(),
            'order_number' => $orderNumber,
        ];
    }

    public function updateStatus(int $id, string $status): void {
        $stmt = $this->db->prepare(
            'UPDATE orders SET status = ? WHERE id = ?'
        );
        $stmt->execute([$status, $id]);
    }

    public function stats(): array {
        $total   = $this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $revenue = $this->db->query(
            'SELECT COALESCE(SUM(total),0) FROM orders WHERE status = "completed"'
        )->fetchColumn();
        $pending = $this->db->query(
            'SELECT COUNT(*) FROM orders WHERE status = "pending"'
        )->fetchColumn();
        $recent  = $this->db->query(
            'SELECT o.*, c.name AS customer_name
             FROM orders o
             JOIN customers c ON c.id = o.customer_id
             ORDER BY o.created_at DESC LIMIT 10'
        )->fetchAll();

        return [
            'total_orders'   => (int)$total,
            'total_revenue'  => (float)$revenue,
            'pending_orders' => (int)$pending,
            'recent_orders'  => $recent,
        ];
    }
}
