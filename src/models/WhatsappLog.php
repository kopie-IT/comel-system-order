<?php

class WhatsappLog {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function log(string $templateSlug, string $recipient, string $message, bool $success, string $error = ''): void {
        $stmt = $this->db->prepare(
            "INSERT INTO `whatsapp_logs` (`template_slug`, `recipient`, `message`, `status`, `error`)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $templateSlug,
            $recipient,
            $message,
            $success ? 'sent' : 'failed',
            $error,
        ]);
    }

    public function paginate(int $page = 1, int $perPage = 30, string $search = ''): array {
        $offset = ($page - 1) * $perPage;

        if ($search !== '') {
            $like = '%' . $search . '%';

            $cntStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM `whatsapp_logs`
                 WHERE `recipient` LIKE ? OR `template_slug` LIKE ? OR `message` LIKE ?"
            );
            $cntStmt->execute([$like, $like, $like]);
            $total = (int)$cntStmt->fetchColumn();

            $stmt = $this->db->prepare(
                "SELECT * FROM `whatsapp_logs`
                 WHERE `recipient` LIKE ? OR `template_slug` LIKE ? OR `message` LIKE ?
                 ORDER BY `sent_at` DESC LIMIT ? OFFSET ?"
            );
            $stmt->execute([$like, $like, $like, $perPage, $offset]);
        } else {
            $total = (int)$this->db->query("SELECT COUNT(*) FROM `whatsapp_logs`")->fetchColumn();

            $stmt = $this->db->prepare(
                "SELECT * FROM `whatsapp_logs` ORDER BY `sent_at` DESC LIMIT ? OFFSET ?"
            );
            $stmt->execute([$perPage, $offset]);
        }

        return [
            'rows'       => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => max(1, (int)ceil($total / $perPage)),
        ];
    }

    public function countByStatus(): array {
        $rows   = $this->db->query(
            "SELECT `status`, COUNT(*) as `cnt` FROM `whatsapp_logs` GROUP BY `status`"
        )->fetchAll();
        $result = ['sent' => 0, 'failed' => 0];
        foreach ($rows as $row) {
            $result[$row['status']] = (int)$row['cnt'];
        }
        return $result;
    }
}
