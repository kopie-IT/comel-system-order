<?php

class AdminAiController {

    // ── Fetch available models from provider ─────────────────────────────
    public function models(): void {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'Method not allowed']); return; }

        $baseUrl = rtrim(trim($_POST['base_url'] ?? ''), '/');
        $apiKey  = trim($_POST['api_key'] ?? '');
        if ($baseUrl === '' || $apiKey === '') { echo json_encode(['error' => 'Base URL dan API key diperlukan.']); return; }

        $ch = curl_init($baseUrl . '/models');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'], CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => true]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) { echo json_encode(['error' => 'Ralat sambungan: ' . $curlErr]); return; }
        $data = json_decode($response, true);
        if ($httpCode !== 200) { echo json_encode(['error' => $data['error']['message'] ?? 'Ralat API (' . $httpCode . ')']); return; }

        $models  = [];
        foreach ($data['data'] ?? $data['models'] ?? [] as $m) { $id = $m['id'] ?? $m['name'] ?? null; if ($id) $models[] = $id; }
        $exclude = ['embedding', 'tts', 'whisper', 'dall-e', 'davinci', 'babbage', 'curie', 'ada', 'moderation'];
        $models  = array_values(array_filter($models, fn($id) => !array_filter($exclude, fn($ex) => str_contains(strtolower($id), $ex))));
        sort($models);
        echo json_encode(['models' => $models, 'count' => count($models)]);
    }

    // ── Main chat endpoint ────────────────────────────────────────────────
    public function chat(): void {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'Method not allowed']); return; }

        // Parse input (JSON or multipart)
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($ct, 'application/json')) {
            $in      = json_decode(file_get_contents('php://input'), true);
            $message = trim($in['message'] ?? '');
            $orderId = (int)($in['order_id'] ?? 0);
        } else {
            $message = trim($_POST['message'] ?? '');
            $orderId = (int)($_POST['order_id'] ?? 0);
        }

        // Handle file attachment
        $hasFile = !empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;
        $isImage = false; $fileBase64 = null; $fileMime = null; $fileName = null; $fileText = null;

        if ($hasFile) {
            $file    = $_FILES['file'];
            $allowed = ['image/jpeg','image/png','image/webp','image/gif','application/pdf','text/plain','text/csv'];
            $finfo   = new finfo(FILEINFO_MIME_TYPE);
            $fileMime = $finfo->file($file['tmp_name']);
            $fileName = basename($file['name']);

            if (!in_array($fileMime, $allowed)) { echo json_encode(['reply' => 'Format fail tidak disokong. Gunakan imej, PDF, atau teks.']); return; }
            if ($file['size'] > 10 * 1024 * 1024) { echo json_encode(['reply' => 'Fail terlalu besar. Maksimum 10MB.']); return; }

            if (str_starts_with($fileMime, 'image/')) {
                $isImage    = true;
                $fileBase64 = base64_encode(file_get_contents($file['tmp_name']));
            } else {
                $raw      = file_get_contents($file['tmp_name']);
                if ($fileMime === 'application/pdf') { $raw = preg_replace('/[^\x20-\x7E\n\r\t]/', ' ', $raw); $raw = preg_replace('/\s{3,}/', "\n", $raw); }
                $fileText = mb_substr($raw, 0, 6000) . (strlen($raw) > 6000 ? "\n...[dipotong]" : '');
            }
        }

        if ($message === '' && !$hasFile) { echo json_encode(['error' => 'Tiada mesej atau fail.']); return; }

        $settings = (new Setting())->all();
        $apiKey   = $settings['ai_api_key'] ?? '';
        $baseUrl  = rtrim($settings['ai_base_url'] ?? 'https://api.openai.com/v1', '/');
        $model    = $settings['ai_model'] ?? 'gpt-4o';

        if ($apiKey === '') { echo json_encode(['reply' => 'API key belum dikonfigurasi. Pergi ke Tetapan > AI Assistant.']); return; }

        // Build rich DB context from message + order_id
        $dbContext = $this->buildDbContext($message, $orderId);

        $systemPrompt = "Anda adalah pembantu AI untuk admin kedai " . app_name() . ". "
            . "Anda mempunyai akses kepada DATA SEBENAR kedai yang disediakan di bawah. "
            . "SENTIASA gunakan data yang disediakan untuk menjawab soalan dengan tepat dan terkini. "
            . "Jangan reka atau andaikan data — hanya gunakan data yang ada. "
            . "Jawab dalam Bahasa Malaysia. Ringkas dan tepat.\n\n"
            . "=== KEMAMPUAN TINDAKAN ===\n"
            . "Anda boleh melakukan tindakan dalam sistem apabila admin meminta. Sertakan blok tindakan dalam respons.\n"
            . "Format: <action>{\"type\":\"...\", ...}</action>\n\n"
            . "Tindakan tersedia:\n"
            . "1. Kemaskini status: <action>{\"type\":\"update_order_status\",\"order_id\":X,\"status\":\"pending|confirmed|completed|cancelled\"}</action>\n"
            . "2. Kemaskini tracking: <action>{\"type\":\"update_order_tracking\",\"order_id\":X,\"tracking_number\":\"...\"}</action>\n"
            . "3. Kemaskini alamat: <action>{\"type\":\"update_order_address\",\"order_id\":X,\"address\":\"...\"}</action>\n"
            . "4. Cipta produk: <action>{\"type\":\"create_product\",\"name\":\"...\",\"description\":\"...\",\"category_id\":X,\"price\":0.00,\"stock\":0}</action>\n"
            . "5. Kemaskini stok: <action>{\"type\":\"update_product_stock\",\"product_id\":X,\"stock\":N}</action>\n"
            . "6. Kemaskini harga: <action>{\"type\":\"update_product_price\",\"product_id\":X,\"price\":N.NN}</action>\n\n"
            . "PENTING: Hanya lakukan tindakan apabila admin meminta secara eksplisit. Nyatakan tindakan sebelum melaksanakannya."
            . $dbContext;

        // Build user content
        if ($isImage && $fileBase64) {
            $userContent = [
                ['type' => 'text',      'text'      => $message ?: 'Analisa imej ini dan berikan maklumat berguna.'],
                ['type' => 'image_url', 'image_url' => ['url' => 'data:' . $fileMime . ';base64,' . $fileBase64, 'detail' => 'high']],
            ];
        } elseif ($fileText !== null) {
            $userContent = ($message ?: 'Analisa kandungan fail ini:') . "\n\n[Fail: {$fileName}]\n```\n{$fileText}\n```";
        } else {
            $userContent = $message;
        }

        // Conversation history — last 10 exchanges (20 messages) per order context
        $historyKey = 'ai_history_' . $orderId;
        $history    = $_SESSION[$historyKey] ?? [];

        // Build messages: system + history + current
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $msg) { $messages[] = $msg; }
        $messages[] = ['role' => 'user', 'content' => $userContent];

        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => 500,
            'temperature' => 0.3,
            'stream'      => false,
        ];

        $result = $this->callApi($baseUrl, $apiKey, $payload);
        if (isset($result['error'])) { echo json_encode(['reply' => $result['error']]); return; }

        $rawReply = $this->parseReply($result['data']);
        $parsed   = $this->parseAndExecuteAction($rawReply);

        // Save exchange to history
        $historyUser = $message;
        if ($isImage && $fileName)          $historyUser = "[Imej: $fileName] " . $message;
        elseif ($fileText !== null && $fileName) $historyUser = "[Fail: $fileName] " . $message;

        $history[] = ['role' => 'user',      'content' => $historyUser ?: '[Fail dilampirkan]'];
        $history[] = ['role' => 'assistant', 'content' => $rawReply];
        if (count($history) > 20) $history = array_slice($history, -20);
        $_SESSION[$historyKey] = $history;

        echo json_encode(['reply' => $parsed['reply'], 'action' => $parsed['action']]);
    }

    // ── Parse action block from AI reply and execute it ──────────────────
    private function parseAndExecuteAction(string $reply): array {
        $actionResult = null;
        $cleanReply   = $reply;

        if (preg_match('/<action>(.*?)<\/action>/s', $reply, $match)) {
            $action = json_decode(trim($match[1]), true);
            if ($action && isset($action['type'])) {
                $actionResult = $this->executeAction($action);
                $cleanReply   = trim(preg_replace('/<action>.*?<\/action>/s', '', $reply));
            }
        }

        return ['reply' => $cleanReply, 'action' => $actionResult];
    }

    // ── Execute a system action ───────────────────────────────────────────
    private function executeAction(array $action): array {
        $type = $action['type'] ?? '';
        try {
            switch ($type) {

                case 'update_order_status':
                    $orderId = (int)($action['order_id'] ?? 0);
                    $status  = $action['status'] ?? '';
                    $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
                    if (!$orderId || !in_array($status, $allowed))
                        return ['success' => false, 'type' => $type, 'message' => 'Parameter tidak sah untuk kemaskini status.'];
                    $order = (new Order())->find($orderId);
                    if (!$order) return ['success' => false, 'type' => $type, 'message' => 'Pesanan tidak dijumpai.'];
                    (new Order())->updateStatus($orderId, $status);
                    $lbl = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
                    return ['success' => true, 'type' => $type, 'message' => "✅ Status {$order['order_number']} dikemaskini ke \"{$lbl[$status]}\"."];

                case 'update_order_tracking':
                    $orderId  = (int)($action['order_id'] ?? 0);
                    $tracking = strtoupper(trim($action['tracking_number'] ?? ''));
                    if (!$orderId || !$tracking)
                        return ['success' => false, 'type' => $type, 'message' => 'Order ID dan tracking number diperlukan.'];
                    $order = (new Order())->find($orderId);
                    if (!$order) return ['success' => false, 'type' => $type, 'message' => 'Pesanan tidak dijumpai.'];
                    (new Order())->updateCourierSlip($orderId, $tracking, $order['courier_slip'] ?? '');
                    return ['success' => true, 'type' => $type, 'message' => "✅ Tracking {$order['order_number']} dikemaskini: $tracking"];

                case 'update_order_address':
                    $orderId = (int)($action['order_id'] ?? 0);
                    $address = strtoupper(trim($action['address'] ?? ''));
                    if (!$orderId || !$address)
                        return ['success' => false, 'type' => $type, 'message' => 'Order ID dan alamat diperlukan.'];
                    $order = (new Order())->find($orderId);
                    if (!$order) return ['success' => false, 'type' => $type, 'message' => 'Pesanan tidak dijumpai.'];
                    $updated = (new Order())->updateAddress($orderId, $address);
                    if (!$updated) return ['success' => false, 'type' => $type, 'message' => 'Gagal kemaskini alamat. Status mungkin bukan pending.'];
                    return ['success' => true, 'type' => $type, 'message' => "✅ Alamat {$order['order_number']} dikemaskini."];

                case 'create_product':
                    $name       = trim($action['name'] ?? '');
                    $desc       = trim($action['description'] ?? '');
                    $categoryId = (int)($action['category_id'] ?? 0);
                    $price      = (float)($action['price'] ?? 0);
                    $stock      = (int)($action['stock'] ?? 0);
                    if (!$name || !$categoryId)
                        return ['success' => false, 'type' => $type, 'message' => 'Nama produk dan kategori diperlukan.'];
                    $db   = getDB();
                    $stmt = $db->prepare('INSERT INTO products (name, description, category_id, price, stock) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$name, $desc, $categoryId, $price, $stock]);
                    $id = (int)$db->lastInsertId();
                    return ['success' => true, 'type' => $type, 'message' => "✅ Produk \"$name\" berjaya dicipta (ID: $id)."];

                case 'update_product_stock':
                    $productId = (int)($action['product_id'] ?? 0);
                    $stock     = (int)($action['stock'] ?? 0);
                    if (!$productId) return ['success' => false, 'type' => $type, 'message' => 'Product ID tidak sah.'];
                    getDB()->prepare('UPDATE products SET stock = ? WHERE id = ?')->execute([$stock, $productId]);
                    $p = (new Product())->find($productId);
                    return ['success' => true, 'type' => $type, 'message' => "✅ Stok \"{$p['name']}\" dikemaskini ke $stock unit."];

                case 'update_product_price':
                    $productId = (int)($action['product_id'] ?? 0);
                    $price     = (float)($action['price'] ?? 0);
                    if (!$productId) return ['success' => false, 'type' => $type, 'message' => 'Product ID tidak sah.'];
                    getDB()->prepare('UPDATE products SET price = ? WHERE id = ?')->execute([$price, $productId]);
                    $p = (new Product())->find($productId);
                    return ['success' => true, 'type' => $type, 'message' => "✅ Harga \"{$p['name']}\" dikemaskini ke RM" . number_format($price, 2) . "."];

                default:
                    return ['success' => false, 'type' => $type, 'message' => "Tindakan \"$type\" tidak dikenali."];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'type' => $type, 'message' => 'Ralat sistem: ' . $e->getMessage()];
        }
    }

    // ── Build comprehensive live DB context ──────────────────────────────
    private function buildDbContext(string $message, int $orderId): string {
        $db      = getDB();
        $context = "\n\n=== DATA SEBENAR KEDAI ===\n";

        // 1. Store statistics — always included
        $total     = $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $pending   = $db->query('SELECT COUNT(*) FROM orders WHERE status = "pending"')->fetchColumn();
        $confirmed = $db->query('SELECT COUNT(*) FROM orders WHERE status = "confirmed"')->fetchColumn();
        $completed = $db->query('SELECT COUNT(*) FROM orders WHERE status = "completed"')->fetchColumn();
        $cancelled = $db->query('SELECT COUNT(*) FROM orders WHERE status = "cancelled"')->fetchColumn();
        $revenue   = $db->query('SELECT COALESCE(SUM(total),0) FROM orders WHERE status = "completed"')->fetchColumn();
        $today     = $db->query('SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()')->fetchColumn();

        $context .= "[STATISTIK KEDAI]\n"
            . "Jumlah Pesanan: {$total} | Hari Ini: {$today}\n"
            . "Pending: {$pending} | Confirmed: {$confirmed} | Completed: {$completed} | Cancelled: {$cancelled}\n"
            . "Jumlah Hasil (Selesai): RM" . number_format($revenue, 2) . "\n\n";

        // 2. Last 10 orders — always included
        $stmt = $db->query(
            'SELECT o.order_number, o.status, o.total, o.created_at, c.name AS cn, c.phone
             FROM orders o JOIN customers c ON c.id = o.customer_id
             ORDER BY o.created_at DESC LIMIT 10'
        );
        $rows = $stmt->fetchAll();
        if ($rows) {
            $context .= "[10 PESANAN TERKINI]\n";
            foreach ($rows as $r) {
                $context .= "- {$r['order_number']} | {$r['cn']} ({$r['phone']}) | {$r['status']} | RM"
                    . number_format($r['total'], 2) . " | " . date('d/m/Y', strtotime($r['created_at'])) . "\n";
            }
            $context .= "\n";
        }

        // 3. All products with stock — always included
        $stmt = $db->query(
            'SELECT p.id, p.name, p.stock, p.price, c.name AS cat, c.type
             FROM products p JOIN categories c ON c.id = p.category_id
             ORDER BY c.type, p.name'
        );
        $products = $stmt->fetchAll();
        if ($products) {
            $context .= "[SENARAI PRODUK & STOK]\n";
            foreach ($products as $p) {
                if ($p['type'] === 'pakaian') {
                    $s2   = $db->prepare('SELECT size_label, stock FROM product_sizes WHERE product_id = ? ORDER BY id');
                    $s2->execute([$p['id']]);
                    $sizes = $s2->fetchAll();
                    $sz    = implode(', ', array_map(fn($x) => "{$x['size_label']}:{$x['stock']}", $sizes));
                    $context .= "- {$p['name']} ({$p['cat']}): [{$sz}]\n";
                } else {
                    $context .= "- {$p['name']} ({$p['cat']}): Stok {$p['stock']}, RM{$p['price']}\n";
                }
            }
            $context .= "\n";
        }

        // 4. Specific order context if order_id provided
        if ($orderId > 0) {
            $context .= $this->getOrderContext($orderId);
        }

        // 5. Detect CML order numbers in message
        if (preg_match_all('/CML-\d{6}/i', $message, $m)) {
            foreach (array_unique($m[0]) as $num) {
                $s = $db->prepare('SELECT id FROM orders WHERE order_number = ?');
                $s->execute([strtoupper($num)]);
                $id = (int)$s->fetchColumn();
                if ($id && $id !== $orderId) $context .= $this->getOrderContext($id);
            }
        }

        // 6. Detect phone numbers
        if (preg_match('/\b(01[0-9]{8,9}|60[0-9]{9,10})\b/', $message, $m)) {
            $s = $db->prepare(
                'SELECT o.order_number, o.status, o.total, o.created_at
                 FROM orders o JOIN customers c ON c.id = o.customer_id
                 WHERE c.phone = ? ORDER BY o.created_at DESC LIMIT 5'
            );
            $s->execute([$m[0]]);
            $rows = $s->fetchAll();
            if ($rows) {
                $context .= "[PESANAN UNTUK TELEFON: {$m[0]}]\n";
                foreach ($rows as $r) {
                    $context .= "- {$r['order_number']} | {$r['status']} | RM"
                        . number_format($r['total'], 2) . " | " . date('d/m/Y', strtotime($r['created_at'])) . "\n";
                }
                $context .= "\n";
            }
        }

        $context .= "=== TAMAT DATA ===";
        return $context;
    }

    // ── Get full order context ────────────────────────────────────────────
    private function getOrderContext(int $orderId): string {
        $order = (new Order())->find($orderId);
        if (!$order) return '';
        $items = (new OrderItem())->forOrder($orderId);
        $lines = '';
        foreach ($items as $i) {
            $lines .= "  - {$i['product_name']}";
            if ($i['size_label']) $lines .= " ({$i['size_label']})";
            $lines .= " x{$i['quantity']} = RM" . number_format($i['subtotal'], 2) . "\n";
        }
        return "\n\n[PESANAN: {$order['order_number']}]\n"
            . "Nama: {$order['customer_name']} | Tel: {$order['customer_phone']}\n"
            . "Alamat: {$order['address']}\n"
            . "Status: {$order['status']} | Jumlah: RM" . number_format($order['total'], 2) . "\n"
            . "Tracking: " . ($order['tracking_number'] ?? 'Belum ada') . " | Slip: " . ($order['courier_slip'] ? 'Ada' : 'Belum ada') . "\n"
            . "Tarikh: " . date('d/m/Y H:i', strtotime($order['created_at'])) . "\n"
            . "Item:\n{$lines}";
    }

    // ── Parse reply — handles standard JSON and SSE streaming wrapped in 'raw' ──
    private function parseReply(array $data): string {
        // Standard OpenAI non-streaming format
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }

        // Provider wraps SSE stream in a 'raw' field (e.g. core.fiqstr.com)
        if (isset($data['raw'])) {
            $content = '';
            foreach (explode("\n", $data['raw']) as $line) {
                $line = trim($line);
                if (!str_starts_with($line, 'data: ') || $line === 'data: [DONE]') continue;
                $chunk = json_decode(substr($line, 6), true);
                if ($chunk && isset($chunk['choices'][0]['delta']['content'])) {
                    $content .= $chunk['choices'][0]['delta']['content'];
                }
            }
            return trim($content) ?: 'Tiada respons dari AI.';
        }

        // Raw SSE text response (provider streams directly without wrapping)
        if (isset($data[0]) || (is_string($data) && str_contains($data, 'data:'))) {
            return 'Tiada respons dari AI.';
        }

        return 'Tiada respons dari AI.';
    }

    // ── Call AI API with 429 retry ────────────────────────────────────────
    private function callApi(string $baseUrl, string $apiKey, array $payload): array {
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        $ch       = curl_init($baseUrl . '/chat/completions');
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) return ['error' => 'Ralat sambungan: ' . $curlErr];

        $data = json_decode($response, true);

        // Retry once on 429
        if ($httpCode === 429) {
            sleep(2);
            $ch2 = curl_init($baseUrl . '/chat/completions');
            curl_setopt_array($ch2, $opts);
            $response = curl_exec($ch2);
            $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            curl_close($ch2);
            $data = json_decode($response, true);
        }

        if ($httpCode === 429) return ['error' => "⏳ Had permintaan API (429). Cuba lagi sebentar.\n• Tukar ke model lebih ringan: gemini-2.0-flash-lite\n• Gunakan OpenRouter model percuma (:free)"];
        if ($httpCode !== 200) return ['error' => $data['error']['message'] ?? 'Ralat API (' . $httpCode . ')'];

        return ['data' => $data];
    }
}
