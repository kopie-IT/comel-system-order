<?php

class Admin {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function all(): array {
        $stmt = $this->db->query('SELECT id, username, role, created_at FROM admins ORDER BY role ASC, username ASC');
        return $stmt->fetchAll();
    }

    public function create(string $username, string $password, string $role): int {
        $stmt = $this->db->prepare('INSERT INTO admins (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT), $role]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $username, string $role, string $password = ''): void {
        if ($password !== '') {
            $stmt = $this->db->prepare('UPDATE admins SET username = ?, role = ?, password = ? WHERE id = ?');
            $stmt->execute([$username, $role, password_hash($password, PASSWORD_BCRYPT), $id]);
        } else {
            $stmt = $this->db->prepare('UPDATE admins SET username = ?, role = ? WHERE id = ?');
            $stmt->execute([$username, $role, $id]);
        }
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM admins WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function usernameExists(string $username, int $excludeId = 0): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM admins WHERE username = ? AND id != ?');
        $stmt->execute([$username, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}

