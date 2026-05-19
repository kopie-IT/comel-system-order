<?php

class AdminSettingsController {
    public function index(): void {
        $settings = (new Setting())->all();
        view('admin/settings/index', ['settings' => $settings], 'admin');
    }

    public function update(): void {
        csrf_verify();

        $model        = new Setting();
        $whatsapp     = trim($_POST['whatsapp_number'] ?? '');
        $instructions = trim($_POST['payment_instructions'] ?? '');

        $model->set('whatsapp_number', $whatsapp);
        $model->set('payment_instructions', $instructions);

        // Handle QR code upload
        if (!empty($_FILES['qr_code_image']['name'])) {
            $file     = $_FILES['qr_code_image'];
            $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize  = 2 * 1024 * 1024; // 2MB

            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowed)) {
                flash('error', 'Format fail tidak disokong. Sila muat naik JPEG, PNG atau WebP.');
                redirect('/admin/settings');
                return;
            }
            if ($file['size'] > $maxSize) {
                flash('error', 'Saiz fail terlalu besar. Maksimum 2MB.');
                redirect('/admin/settings');
                return;
            }

            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'qr_' . time() . '.' . strtolower($ext);
            $dest     = ROOT_PATH . '/public/uploads/qr/' . $filename;

            if (!is_dir(ROOT_PATH . '/public/uploads/qr')) {
                mkdir(ROOT_PATH . '/public/uploads/qr', 0755, true);
            }

            // Delete old QR image
            $oldQr = $model->get('qr_code_image');
            if ($oldQr && file_exists(ROOT_PATH . '/public' . $oldQr)) {
                unlink(ROOT_PATH . '/public' . $oldQr);
            }

            move_uploaded_file($file['tmp_name'], $dest);
            $model->set('qr_code_image', '/uploads/qr/' . $filename);
        }

        flash('success', 'Tetapan berjaya disimpan.');
        redirect('/admin/settings');
    }
}
