<?php

class WhatsappTemplate {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(): array {
        return $this->db->query("SELECT * FROM `whatsapp_templates` ORDER BY `id` ASC")
            ->fetchAll();
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM `whatsapp_templates` WHERE `id` = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findBySlug(string $slug): array|false {
        $stmt = $this->db->prepare("SELECT * FROM `whatsapp_templates` WHERE `slug` = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function update(int $id, string $name, string $body): void {
        $stmt = $this->db->prepare(
            "UPDATE `whatsapp_templates` SET `name` = ?, `body` = ? WHERE `id` = ?"
        );
        $stmt->execute([$name, $body, $id]);
    }

    /**
     * Replace template placeholders with actual values.
     * $vars is an associative array: ['order_number' => 'ORD-001', ...]
     */
    public function render(string $body, array $vars): string {
        foreach ($vars as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }
        return $body;
    }

    /**
     * Get rendered body by slug. Returns raw body if slug not found.
     */
    public function renderBySlug(string $slug, array $vars): string {
        $tpl = $this->findBySlug($slug);
        if (!$tpl) {
            return '';
        }
        return $this->render($tpl['body'], $vars);
    }
}
