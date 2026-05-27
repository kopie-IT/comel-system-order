<?php

class AdminWhatsappController {

    // GET /admin/whatsapp
    public function index(): void {
        $templates = (new WhatsappTemplate())->all();
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $search    = trim($_GET['search'] ?? '');
        $logData   = (new WhatsappLog())->paginate($page, 30, $search);
        $stats     = (new WhatsappLog())->countByStatus();

        view('admin/whatsapp/index', [
            'pageTitle' => 'Template & Log WhatsApp',
            'templates' => $templates,
            'logData'   => $logData,
            'stats'     => $stats,
            'search'    => $search,
        ], 'admin');
    }

    // GET /admin/whatsapp/edit/{id}
    public function edit(int $id): void {
        $template = (new WhatsappTemplate())->find($id);
        if (!$template) {
            flash('error', 'Template tidak dijumpai.');
            redirect('/admin/whatsapp');
            return;
        }

        // Build placeholder reference for this template's slug
        $placeholders = self::placeholdersForSlug($template['slug']);

        view('admin/whatsapp/edit', [
            'pageTitle'    => 'Edit Template: ' . $template['name'],
            'tpl'          => $template,
            'placeholders' => $placeholders,
        ], 'admin');
    }

    // POST /admin/whatsapp/edit/{id}
    public function update(int $id): void {
        csrf_verify();

        $tplModel = new WhatsappTemplate();
        $template = $tplModel->find($id);
        if (!$template) {
            flash('error', 'Template tidak dijumpai.');
            redirect('/admin/whatsapp');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $body = trim($_POST['body'] ?? '');

        if ($name === '' || $body === '') {
            flash('error', 'Nama dan kandungan template tidak boleh kosong.');
            redirect('/admin/whatsapp/edit/' . $id);
            return;
        }

        $tplModel->update($id, $name, $body);
        flash('success', 'Template "' . $name . '" berjaya dikemaskini.');
        redirect('/admin/whatsapp');
    }

    // POST /admin/whatsapp/reset/{id}  — restore factory default body
    public function reset(int $id): void {
        csrf_verify();

        $tplModel = new WhatsappTemplate();
        $template = $tplModel->find($id);
        if (!$template) {
            flash('error', 'Template tidak dijumpai.');
            redirect('/admin/whatsapp');
            return;
        }

        $defaults = self::defaultBodies();
        $slug     = $template['slug'];

        if (!isset($defaults[$slug])) {
            flash('error', 'Tiada teks asal untuk template ini.');
            redirect('/admin/whatsapp/edit/' . $id);
            return;
        }

        $tplModel->update($id, $defaults[$slug]['name'], $defaults[$slug]['body']);
        flash('success', 'Template telah ditetapkan semula kepada teks asal.');
        redirect('/admin/whatsapp/edit/' . $id);
    }

    // POST /admin/whatsapp/blast  — send blast using blast template
    public function blast(): void {
        csrf_verify();

        $settings = (new Setting())->all();
        $endpoint = trim($settings['wawp_api_endpoint'] ?? '');
        $apiKey   = trim($settings['wawp_api_key'] ?? '');
        $senderId = trim($settings['wawp_sender_id'] ?? '');

        if ($endpoint === '' || $apiKey === '') {
            flash('error', 'WAWP API Endpoint dan API Key belum dikonfigurasi dalam Tetapan.');
            redirect('/admin/whatsapp');
            return;
        }

        $customMessage = trim($_POST['blast_message'] ?? '');
        $useTemplate   = (int)($_POST['use_template'] ?? 0);

        if ($useTemplate) {
            $tpl  = (new WhatsappTemplate())->findBySlug('blast');
            $body = $tpl ? $tpl['body'] : '';
        } else {
            $body = $customMessage;
        }

        if ($body === '') {
            flash('error', 'Sila masukkan mesej atau pilih template untuk blast.');
            redirect('/admin/whatsapp');
            return;
        }

        $customers = (new Order())->getCustomersWithOrders();
        if (empty($customers)) {
            flash('error', 'Tiada pelanggan ditemui untuk blast.');
            redirect('/admin/whatsapp');
            return;
        }

        $wawp      = new WawpService($endpoint, $apiKey, $senderId);
        $logModel  = new WhatsappLog();
        $sentCount = 0;
        $failCount = 0;

        foreach ($customers as $customer) {
            $result = $wawp->sendTextMessage($customer['phone'], $body);
            $success = $result['success'];
            $logModel->log('blast', $customer['phone'], $body, $success, $result['error'] ?? '');
            if ($success) {
                $sentCount++;
            } else {
                $failCount++;
            }
        }

        // Notify admin
        $adminNumber = trim($settings['whatsapp_number'] ?? '');
        if ($adminNumber !== '') {
            $summary = "WhatsApp blast dihantar ke {$sentCount} pelanggan.";
            if ($failCount > 0) {
                $summary .= " Kegagalan: {$failCount}.";
            }
            $wawp->sendTextMessage($adminNumber, $summary);
        }

        if ($failCount > 0) {
            flash('error', "Blast dihantar ke {$sentCount} pelanggan tetapi terdapat {$failCount} kegagalan.");
        } else {
            flash('success', "Blast berjaya dihantar ke {$sentCount} pelanggan.");
        }
        redirect('/admin/whatsapp');
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    public static function placeholdersForSlug(string $slug): array {
        $map = [
            'order_confirmation' => [
                '{{app_name}}'      => 'Nama kedai (dari Tetapan)',
                '{{order_number}}'  => 'Nombor pesanan (cth: ORD-2026-0001)',
                '{{customer_name}}' => 'Nama pelanggan',
                '{{address}}'       => 'Alamat penghantaran',
                '{{item_lines}}'    => 'Senarai item pesanan',
                '{{total}}'         => 'Jumlah keseluruhan (cth: 45.00)',
            ],
            'qr_payment' => [
                '{{total}}' => 'Jumlah keseluruhan (cth: 45.00)',
            ],
            'courier_notify' => [
                '{{order_number}}'  => 'Nombor pesanan',
                '{{item_lines}}'    => 'Senarai item pesanan',
                '{{tracking_line}}' => 'Baris nombor tracking (kosong jika tiada)',
                '{{ref_url}}'       => 'URL rujukan pesanan pelanggan',
            ],
            'blast' => [],
        ];
        return $map[$slug] ?? [];
    }

    public static function defaultBodies(): array {
        return [
            'order_confirmation' => [
                'name' => 'Pengesahan Pesanan (Auto)',
                'body' => "Hi! Salam 😊\n\nTerima kasih kerana membuat pesanan di {{app_name}}! Kami dah terima order anda.\n\n📦 *No. Pesanan: {{order_number}}*\n👤 Nama: {{customer_name}}\n📍 Alamat: {{address}}\n\n*Senarai Pesanan:*\n{{item_lines}}\n💰 *Jumlah Keseluruhan: RM{{total}}*\n\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\n\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, kami hanya boleh hold barang sekejap je. Kalau payment lebih dari 2 jam belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏",
            ],
            'qr_payment' => [
                'name' => 'Caption QR Pembayaran',
                'body' => "Sila buat bayaran sebanyak *RM{{total}}* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊",
            ],
            'courier_notify' => [
                'name' => 'Notifikasi Penghantaran (Slip Kurier)',
                'body' => "Hi! Salam 😊\n\nPesanan anda *{{order_number}}* dah kami hantar ya!\n\n*Item Pesanan:*\n{{item_lines}}{{tracking_line}}\nNi slip penghantaran untuk pesanan anda 📦\n\nRujukan pesanan: {{ref_url}}\n\nTerima kasih kerana membeli dengan kami! Kalau ada apa-apa pertanyaan, jangan segan tanya ya 🙏",
            ],
            'blast' => [
                'name' => 'Blast Promosi',
                'body' => "Hi! Salam 😊\n\nAda tawaran menarik untuk anda hari ini!\n\nJangan lepaskan peluang ini. Hubungi kami untuk maklumat lanjut. TQ! 🙏",
            ],
        ];
    }
}
