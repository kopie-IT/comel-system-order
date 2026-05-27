<?php

class AdminCourierSlipController {

    public function upload(int $orderId): void {
        csrf_verify();

        $order = (new Order())->find($orderId);
        if (!$order) {
            flash('error', 'Pesanan tidak dijumpai.');
            redirect('/admin/orders');
            return;
        }

        $trackingNumber = trim($_POST['tracking_number'] ?? '');

        $slipPath = $order['courier_slip'] ?? '';

        // Handle slip image upload if provided
        if (!empty($_FILES['courier_slip']['name'])) {
            $file = $_FILES['courier_slip'];

            // Catch PHP-level upload failures (oversize, partial, no tmp dir, etc.)
            // before they explode in finfo::file().
            $uploadErr = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($uploadErr !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                flash('error', 'Muat naik fail gagal: ' . $this->uploadErrorMessage($uploadErr));
                redirect('/admin/orders/' . $orderId);
                return;
            }

            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            $maxSize = 15 * 1024 * 1024; // 15MB

            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowed)) {
                flash('error', 'Format fail tidak disokong. Sila muat naik JPEG, PNG, WebP atau PDF.');
                redirect('/admin/orders/' . $orderId);
                return;
            }
            if ($file['size'] > $maxSize) {
                flash('error', 'Saiz fail terlalu besar. Maksimum 5MB.');
                redirect('/admin/orders/' . $orderId);
                return;
            }

            $uploadDir = ROOT_PATH . '/public/uploads/courier/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Delete old slip if exists
            if ($slipPath && file_exists(ROOT_PATH . '/public' . $slipPath)) {
                unlink(ROOT_PATH . '/public' . $slipPath);
            }

            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'slip_' . $orderId . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . $filename;
            move_uploaded_file($file['tmp_name'], $destPath);

            // Auto-resize + recompress large images so storage and WhatsApp
            // transfers stay light. PDFs and unsupported formats are skipped
            // by the helper. JPEG quality 82, max width 600 px.
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                @image_resize_to_path($destPath, 600, 82);
            }

            $slipPath = '/uploads/courier/' . $filename;
        }

        (new Order())->updateCourierSlip($orderId, $trackingNumber, $slipPath);

        // Wait 5 seconds to ensure file is fully written before sending
        if ($slipPath !== '') {
            sleep(5);
            try {
                $settings      = (new Setting())->all();
                $endpoint      = trim($settings['wawp_api_endpoint'] ?? '');
                $apiKey        = trim($settings['wawp_api_key'] ?? '');
                $senderId      = trim($settings['wawp_sender_id'] ?? '');
                $customerPhone = $order['customer_phone'] ?? '';

                if ($endpoint !== '' && $apiKey !== '' && $customerPhone !== '') {
                    $items     = (new OrderItem())->forOrder($orderId);
                    $itemLines = '';
                    foreach ($items as $item) {
                        $itemLines .= '• ' . $item['product_name'];
                        if (!empty($item['size_label'])) {
                            $itemLines .= ' (' . $item['size_label'] . ')';
                        }
                        $itemLines .= ' x' . $item['quantity'] . ' — RM' . number_format((float)$item['subtotal'], 2) . "\n";
                    }

                    $nota   = $trackingNumber !== '' ? "\n*Nota: " . $trackingNumber . "*\n" : '';
                    $refUrl = base_url() . '/order/ref/' . $order['order_number'];

                    $msg = "Hi! Salam 😊\n\n"
                        . "Pesanan anda *" . $order['order_number'] . "* dah kami hantar ya!\n\n"
                        . "*Item Pesanan:*\n" . $itemLines
                        . $nota . "\n"
                        . "Ni slip penghantaran untuk pesanan anda 📦\n\n"
                        . "Rujukan pesanan: " . $refUrl . "\n\n"
                        . "Terima kasih kerana membeli dengan kami! "
                        . "Kalau ada apa-apa pertanyaan, jangan segan tanya ya 🙏";

                    $wawp    = new WawpService($endpoint, $apiKey, $senderId);
                    $slipUrl = base_url() . $slipPath;
                    $result  = $wawp->sendImageMessage($customerPhone, $slipUrl, $msg);

                    if (!$result['success']) {
                        $filePath = ROOT_PATH . '/public' . $slipPath;
                        $result   = $wawp->sendImageFile($customerPhone, $filePath, $msg);
                    }
                    if (!$result['success']) {
                        $wawp->sendTextMessage($customerPhone, $msg);
                    }
                }
            } catch (Exception $ignored) {}
        }

        flash('success', 'Slip berjaya disimpan dan notifikasi WhatsApp telah dihantar kepada pelanggan.');
        redirect('/admin/orders/' . $orderId);
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
