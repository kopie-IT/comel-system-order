<?php

class AdminSettingsController {
    public function index(): void {
        $settings = (new Setting())->all();
        view('admin/settings/index', ['settings' => $settings], 'admin');
    }

    public function update(): void {
        csrf_verify();

        try {
            $model           = new Setting();
            $appName         = trim($_POST['app_name'] ?? '') ?: 'Comel Baby Store';
            $baseUrl         = rtrim(trim($_POST['base_url'] ?? ''), '/');
            $whatsapp        = trim($_POST['whatsapp_number'] ?? '');
            $instructions    = trim($_POST['payment_instructions'] ?? '');
            $postageFee      = number_format(max(0, (float)($_POST['postage_fee']      ?? 7.00)), 2, '.', '');
            $postageFeeEast  = number_format(max(0, (float)($_POST['postage_fee_east'] ?? 12.00)), 2, '.', '');
            $wawpEndpoint    = trim($_POST['wawp_api_endpoint'] ?? '');
            $wawpApiKey      = trim($_POST['wawp_api_key'] ?? '');
            $wawpSenderId    = trim($_POST['wawp_sender_id'] ?? '');
            $notifMethod     = in_array($_POST['notification_method'] ?? '', ['wawp', 'wa_link'])
                               ? $_POST['notification_method'] : 'wa_link';

            $model->set('app_name', $appName);
            $model->set('base_url', $baseUrl);
            $model->set('whatsapp_number', $whatsapp);
            $model->set('payment_instructions', $instructions);
            $model->set('postage_fee', $postageFee);
            $model->set('postage_fee_east', $postageFeeEast);
            $model->set('wawp_api_endpoint', $wawpEndpoint);
            $model->set('wawp_api_key', $wawpApiKey);
            $model->set('wawp_sender_id', $wawpSenderId);
            $model->set('notification_method', $notifMethod);

            // Handle QR code upload
            if (!empty($_FILES['qr_code_image']['name'])) {
                $file = $_FILES['qr_code_image'];

                $uploadErr = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
                if ($uploadErr !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                    flash('error', 'Muat naik QR gagal: ' . $this->uploadErrorMessage($uploadErr));
                    redirect('/admin/settings');
                    return;
                }

                $allowed  = ['image/jpeg', 'image/png'];
                $maxSize  = 2 * 1024 * 1024;

                $finfo    = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);

                if (!in_array($mimeType, $allowed)) {
                    flash('error', 'Format fail tidak disokong. Sila muat naik JPEG atau PNG sahaja (WhatsApp tidak menerima WebP untuk QR code).');
                    redirect('/admin/settings');
                    return;
                }
                if ($file['size'] > $maxSize) {
                    flash('error', 'Saiz fail terlalu besar. Maksimum 2MB.');
                    redirect('/admin/settings');
                    return;
                }

                $ext      = $mimeType === 'image/png' ? 'png' : 'jpg';
                $filename = 'qr_' . time() . '.' . $ext;
                $dest     = PUBLIC_PATH . '/uploads/qr/' . $filename;

                if (!is_dir(PUBLIC_PATH . '/uploads/qr')) {
                    mkdir(PUBLIC_PATH . '/uploads/qr', 0755, true);
                }

                $oldQr = $model->get('qr_code_image');
                if ($oldQr && file_exists(PUBLIC_PATH . $oldQr)) {
                    unlink(PUBLIC_PATH . $oldQr);
                }

                move_uploaded_file($file['tmp_name'], $dest);
                $model->set('qr_code_image', '/uploads/qr/' . $filename);
            }

            flash('success', 'Tetapan berjaya disimpan.');
        } catch (\Throwable $e) {
            flash('error', 'Gagal menyimpan tetapan: ' . $e->getMessage());
        }
        redirect('/admin/settings');
    }

    public function blast(): void {
        csrf_verify();

        $settings = (new Setting())->all();
        $message  = trim($_POST['blast_message'] ?? '');

        if ($message === '') {
            flash('error', 'Sila masukkan mesej WhatsApp untuk blast.');
            redirect('/admin/settings');
            return;
        }

        $endpoint = trim($settings['wawp_api_endpoint'] ?? '');
        $apiKey   = trim($settings['wawp_api_key'] ?? '');
        $senderId = trim($settings['wawp_sender_id'] ?? '');

        if ($endpoint === '' || $apiKey === '') {
            flash('error', 'Sila lengkapkan WAWP API endpoint dan API Key dalam tetapan terlebih dahulu.');
            redirect('/admin/settings');
            return;
        }

        $broadcast = new WawpService($endpoint, $apiKey, $senderId);
        $customers = (new Order())->getCustomersWithOrders();

        if (empty($customers)) {
            flash('error', 'Tiada pelanggan ditemui untuk blast.');
            redirect('/admin/settings');
            return;
        }

        $sentCount = 0;
        $errors    = [];
        foreach ($customers as $customer) {
            $result = $broadcast->sendTextMessage($customer['phone'], $message);
            if ($result['success']) {
                $sentCount++;
            } else {
                $errors[] = trim($customer['phone'] . ': ' . ($result['error'] ?? 'Unknown error'));
            }
        }

        $adminNumber = trim($settings['whatsapp_number'] ?? '');
        if ($adminNumber !== '') {
            $summary = "WhatsApp blast dihantar ke {$sentCount} pelanggan.";
            if (!empty($errors)) {
                $summary .= " Kegagalan: " . count($errors) . ".";
            }
            $broadcast->sendTextMessage($adminNumber, $summary);
        }

        if (!empty($errors)) {
            flash('error', "Blast dihantar ke {$sentCount} pelanggan tetapi terdapat " . count($errors) . " kegagalan.");
        } else {
            flash('success', "Blast berjaya dihantar ke {$sentCount} pelanggan.");
        }
        redirect('/admin/settings');
    }

    public function testWawp(): void {
        header('Content-Type: application/json');

        $settings = (new Setting())->all();
        $endpoint = trim($settings['wawp_api_endpoint'] ?? '');
        $apiKey   = trim($settings['wawp_api_key'] ?? '');
        $senderId = trim($settings['wawp_sender_id'] ?? '');
        $adminNum = trim($settings['whatsapp_number'] ?? '');

        if ($endpoint === '' || $apiKey === '') {
            echo json_encode(['success' => false, 'message' => 'WAWP API Endpoint dan API Key belum dikonfigurasi. Sila simpan tetapan dahulu.']);
            return;
        }
        if ($adminNum === '') {
            echo json_encode(['success' => false, 'message' => 'Nombor WhatsApp Admin belum dikonfigurasi.']);
            return;
        }

        $wawp   = new WawpService($endpoint, $apiKey, $senderId);
        $result = $wawp->sendTextMessage($adminNum, "✅ Mesej ujian dari " . app_name() . ".\n\nKonfigurasi WAWP berjaya! Sistem boleh menghantar mesej WhatsApp.");

        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Mesej ujian berjaya dihantar ke ' . $adminNum . '. Semak WhatsApp anda.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal: ' . ($result['error'] ?? json_encode($result['data'] ?? 'Unknown error'))]);
        }
    }

    public function updateAutoCancel(): void {
        csrf_verify();
        $model   = new Setting();
        $enabled = isset($_POST['auto_cancel_enabled']) ? '1' : '0';
        $hours   = max(1, min(72, (int)($_POST['auto_cancel_hours'] ?? 2)));
        $model->set('auto_cancel_enabled', $enabled);
        $model->set('auto_cancel_hours', (string)$hours);
        flash('success', 'Tetapan auto-batalkan berjaya disimpan.');
        redirect('/admin/settings');
    }

    public function updateAi(): void {
        csrf_verify();

        $model   = new Setting();
        $baseUrl = trim($_POST['ai_base_url'] ?? '');
        $apiKey  = trim($_POST['ai_api_key'] ?? '');
        $aiModel = trim($_POST['ai_model'] ?? '');

        if ($baseUrl !== '') $model->set('ai_base_url', $baseUrl);
        if ($apiKey  !== '') $model->set('ai_api_key',  $apiKey);
        if ($aiModel !== '') $model->set('ai_model',    $aiModel);

        flash('success', 'Tetapan AI berjaya disimpan.');
        redirect('/admin/settings');
    }

    /**
     * Convert a PHP UPLOAD_ERR_* constant into a human-readable Malay message.
     */
    private function uploadErrorMessage(int $code): string {
        $maxUpload = ini_get('upload_max_filesize') ?: '?';
        $maxPost   = ini_get('post_max_size') ?: '?';
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                return "Fail melebihi had server ({$maxUpload}). Sila kurangkan saiz fail atau tingkatkan upload_max_filesize dalam php.ini.";
            case UPLOAD_ERR_FORM_SIZE:
                return 'Fail melebihi had borang.';
            case UPLOAD_ERR_PARTIAL:
                return 'Fail hanya dimuat naik sebahagian. Sila cuba lagi.';
            case UPLOAD_ERR_NO_FILE:
                return 'Tiada fail dipilih.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Direktori sementara hilang pada server.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Tidak dapat menulis fail ke cakera.';
            case UPLOAD_ERR_EXTENSION:
                return 'Sambungan PHP menghalang muat naik.';
            default:
                return "Ralat tidak diketahui (kod {$code}). Had server: upload_max_filesize={$maxUpload}, post_max_size={$maxPost}.";
        }
    }
}

