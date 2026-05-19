<?php

class Admin {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM admins WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
