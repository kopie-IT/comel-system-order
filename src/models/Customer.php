<?php

class Customer {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByPhone(string $phone): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM customers WHERE phone = ? LIMIT 1'
        );
        $stmt->execute([$phone]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createOrUpdate(string $name, string $phone): int {
        $existing = $this->findByPhone($phone);
        if ($existing) {
            $stmt = $this->db->prepare(
                'UPDATE customers SET name = ? WHERE phone = ?'
            );
            $stmt->execute([$name, $phone]);
            return (int)$existing['id'];
        }
        $stmt = $this->db->prepare(
            'INSERT INTO customers (name, phone) VALUES (?, ?)'
        );
        $stmt->execute([$name, $phone]);
        return (int)$this->db->lastInsertId();
    }
}
