<?php

class Setting {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function get(string $key, string $default = ''): string {
        $stmt = $this->db->prepare(
            'SELECT value FROM settings WHERE `key` = ? LIMIT 1'
        );
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? (string)$row['value'] : $default;
    }

    public function all(): array {
        $stmt = $this->db->query('SELECT `key`, value FROM settings');
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }

    public function set(string $key, string $value): void {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (`key`, value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        $stmt->execute([$key, $value]);
    }
}
