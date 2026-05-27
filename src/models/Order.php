<?php

class Order {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(string $status = '', array $exclude = []): array {
        $sql    = 'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
                   FROM orders o
                   JOIN customers c ON c.id = o.customer_id
                   WHERE 1=1';
        $params = [];
        if ($status !== '') {
            $sql     .= ' AND o.status = ?';
            $params[] = $status;
        } elseif (!empty($exclude)) {
            $placeholders = implode(',', array_fill(0, count($exclude), '?'));
            $sql    .= ' AND o.status NOT IN (' . $placeholders . ')';
            $params  = array_merge($params, $exclude);
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

    private function generateOrderNumber(): string {
        // Get the highest existing CML order number and increment
        $stmt = $this->db->query(
            "SELECT order_number FROM orders
             WHERE order_number LIKE 'CML-%'
             ORDER BY id DESC LIMIT 1"
        );
        $last = $stmt->fetchColumn();
        if ($last && preg_match('/CML-(\d+)/', $last, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        return 'CML-' . str_pad((string)$next, 6, '0', STR_PAD_LEFT);
    }

    public function create(int $customerId, string $address, float $total, string $state = '', float $postage = 0.00): array {
        // Generate unique order number with retry on collision
        $attempts = 0;
        do {
            $orderNumber = $this->generateOrderNumber();
            $attempts++;
            if ($attempts > 10) {
                throw new \RuntimeException('Unable to generate unique order number.');
            }
            $check = $this->db->prepare('SELECT id FROM orders WHERE order_number = ?');
            $check->execute([$orderNumber]);
        } while ($check->fetchColumn());

        $stmt = $this->db->prepare(
            'INSERT INTO orders (order_number, customer_id, address, state, postage, total, status)
             VALUES (?, ?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$orderNumber, $customerId, $address, $state, $postage, $total]);
        return [
            'id'           => (int)$this->db->lastInsertId(),
            'order_number' => $orderNumber,
        ];
    }

    public function dashboardOrders(string $search = ''): array {
        $sql    = 'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
                   FROM orders o
                   JOIN customers c ON c.id = o.customer_id
                   WHERE o.status != "completed"';
        $params = [];

        if ($search !== '') {
            $sql    .= ' AND (c.name LIKE ? OR c.phone LIKE ? OR o.address LIKE ?)';
            $params  = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];
        }

        $sql .= ' ORDER BY o.created_at DESC LIMIT 30';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCustomerId(int $customerId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    public function findByPhone(string $phone): array {
        $stmt = $this->db->prepare(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
             FROM orders o
             JOIN customers c ON c.id = o.customer_id
             WHERE c.phone = ?
             ORDER BY o.created_at DESC'
        );
        $stmt->execute([$phone]);
        return $stmt->fetchAll();
    }

    public function cancelExpired(int $hours): int {
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = 'cancelled'
             WHERE status = 'pending'
             AND created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)"
        );
        $stmt->execute([$hours]);
        return $stmt->rowCount();
    }

    public function getCustomersWithOrders(): array {
        $stmt = $this->db->query(
            'SELECT c.name, c.phone
             FROM customers c
             JOIN orders o ON o.customer_id = c.id
             WHERE TRIM(c.phone) != ""
             GROUP BY c.id, c.name, c.phone
             ORDER BY MAX(o.created_at) DESC'
        );
        return $stmt->fetchAll();
    }

    public function updateCourierSlip(int $id, string $trackingNumber, string $slipPath): void {
        $stmt = $this->db->prepare(
            'UPDATE orders SET tracking_number = ?, courier_slip = ?, courier_notified_at = NOW(), status = "confirmed"
             WHERE id = ?'
        );
        $stmt->execute([$trackingNumber, $slipPath, $id]);
    }

    public function updateAddress(int $id, string $address, string $state = ''): bool {
        // Only allow update if order is still pending
        if ($state !== '') {
            $stmt = $this->db->prepare(
                'UPDATE orders SET address = ?, state = ? WHERE id = ? AND status = "pending"'
            );
            $stmt->execute([$address, $state, $id]);
        } else {
            $stmt = $this->db->prepare(
                'UPDATE orders SET address = ? WHERE id = ? AND status = "pending"'
            );
            $stmt->execute([$address, $id]);
        }
        return $stmt->rowCount() > 0;
    }

    public function updateStatus(int $id, string $status): void {
        $stmt = $this->db->prepare(
            'UPDATE orders SET status = ? WHERE id = ?'
        );
        $stmt->execute([$status, $id]);
    }

    public function delete(int $id): bool {
        // order_items will cascade-delete via FK
        $stmt = $this->db->prepare('DELETE FROM orders WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
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
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
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

    /**
     * Aggregate every order_item belonging to a pending order, grouped by
     * product + size + variant. Used by the admin "picking list" view so the
     * shop knows exactly which products (and how many of each) need to be
     * prepared for the pending queue.
     *
     * Returns rows shaped like:
     *   product_name, size_label, variant_label,
     *   total_quantity, order_count, product_image
     */
    public function pendingItemsAggregated(): array {
        $stmt = $this->db->query(
            "SELECT
                oi.product_id,
                oi.product_name,
                COALESCE(oi.size_label, '')    AS size_label,
                COALESCE(oi.variant_label, '') AS variant_label,
                SUM(oi.quantity)               AS total_quantity,
                COUNT(DISTINCT oi.order_id)    AS order_count,
                MAX(p.image)                   AS product_image
             FROM order_items oi
             JOIN orders o      ON o.id = oi.order_id
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE o.status = 'pending'
             GROUP BY oi.product_id, oi.product_name, size_label, variant_label
             ORDER BY oi.product_name ASC, size_label ASC, variant_label ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Plain list of all order_items for pending orders — used to render a
     * detailed breakdown alongside the aggregated totals.
     */
    public function pendingItemsDetailed(): array {
        $stmt = $this->db->query(
            "SELECT
                oi.*,
                o.order_number,
                o.created_at AS order_created_at,
                c.name       AS customer_name,
                c.phone      AS customer_phone,
                p.image      AS product_image
             FROM order_items oi
             JOIN orders o      ON o.id = oi.order_id
             JOIN customers c   ON c.id = o.customer_id
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE o.status = 'pending'
             ORDER BY oi.product_name ASC, o.created_at ASC"
        );
        return $stmt->fetchAll();
    }
}
