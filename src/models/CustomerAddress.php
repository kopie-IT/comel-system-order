<?php

class CustomerAddress {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByCustomer(int $customerId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    // Returns existing record if same name+address combination already exists
    public function findMatch(int $customerId, string $name, string $address): array|false {
        $normAddr = strtoupper(preg_replace('/\s+/', ' ', trim($address)));
        $normName = strtoupper(preg_replace('/\s+/', ' ', trim($name)));
        $stmt = $this->db->prepare('SELECT * FROM customer_addresses WHERE customer_id = ?');
        $stmt->execute([$customerId]);
        foreach ($stmt->fetchAll() as $row) {
            $existAddr = strtoupper(preg_replace('/\s+/', ' ', trim($row['address'])));
            $existName = strtoupper(preg_replace('/\s+/', ' ', trim($row['name'] ?? '')));
            if ($existAddr === $normAddr && $existName === $normName) return $row;
        }
        return false;
    }

    public function create(int $customerId, string $name, string $address, string $state = ''): int {
        $stmt = $this->db->prepare(
            'INSERT INTO customer_addresses (customer_id, name, address, state) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$customerId, $name, $address, $state]);
        return (int)$this->db->lastInsertId();
    }

    public function countByCustomer(int $customerId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM customer_addresses WHERE customer_id = ?');
        $stmt->execute([$customerId]);
        return (int)$stmt->fetchColumn();
    }
}
