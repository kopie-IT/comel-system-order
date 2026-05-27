<?php

class AdminOrderController {
    public function index(): void {
        $status = $_GET['status'] ?? '';
        $order  = new Order();
        if ($status === 'all') {
            $orders = $order->all('');
        } elseif ($status === '') {
            // "Active" tab — only show new pending orders awaiting action.
            // Confirmed and cancelled have their own tabs.
            $orders = $order->all('pending');
        } else {
            $orders = $order->all($status);
        }
        view('admin/orders/index', ['orders' => $orders, 'status' => $status], 'admin');
    }

    /**
     * Picking list of every product (size+variant) currently inside a pending order.
     * Helps the admin see at-a-glance what needs to be prepared/packed.
     */
    public function pendingItems(): void {
        $orderModel = new Order();
        $aggregated = $orderModel->pendingItemsAggregated();
        $detailed   = $orderModel->pendingItemsDetailed();
        view('admin/orders/pending_items', [
            'aggregated' => $aggregated,
            'detailed'   => $detailed,
        ], 'admin');
    }

    public function show(int $id): void {
        $order    = (new Order())->find($id);
        if (!$order) { redirect('/admin/orders'); return; }
        $items    = (new OrderItem())->forOrder($id);
        $settings = (new Setting())->all();
        view('admin/orders/show', ['order' => $order, 'items' => $items, 'settings' => $settings], 'admin');
    }

    public function notify(int $id): void {
        csrf_verify();
        $order = (new Order())->find($id);
        if (!$order) { redirect('/admin/orders'); return; }

        $settings      = (new Setting())->all();
        $notifMethod   = trim($settings['notification_method'] ?? 'wa_link');
        $endpoint      = trim($settings['wawp_api_endpoint'] ?? '');
        $apiKey        = trim($settings['wawp_api_key'] ?? '');
        $senderId      = trim($settings['wawp_sender_id'] ?? '');
        $customerPhone = $order['customer_phone'] ?? '';
        $slipPath      = $order['courier_slip'] ?? '';

        if ($endpoint === '' || $apiKey === '') {
            flash('error', 'WAWP tidak dikonfigurasi. Semak tetapan WAWP API Endpoint dan API Key.');
            redirect('/admin/orders/' . $id);
            return;
        }
        if ($slipPath === '') {
            flash('error', 'Tiada slip penghantaran. Muat naik slip dahulu.');
            redirect('/admin/orders/' . $id);
            return;
        }

        // Build items list
        $items     = (new OrderItem())->forOrder($id);
        $itemLines = '';
        foreach ($items as $item) {
            $itemLines .= '• ' . $item['product_name'];
            if (!empty($item['size_label'])) {
                $itemLines .= ' (' . $item['size_label'] . ')';
            }
            $itemLines .= ' x' . $item['quantity'] . ' — RM' . number_format((float)$item['subtotal'], 2) . "\n";
        }

        $trackingLine = !empty($order['tracking_number']) ? "\n*Nota: " . $order['tracking_number'] . "*\n" : '';
        $refUrl       = base_url() . '/order/ref/' . $order['order_number'];

        $tplModel = new WhatsappTemplate();
        $logModel = new WhatsappLog();

        $msg = $tplModel->renderBySlug('courier_notify', [
            'order_number'  => $order['order_number'],
            'item_lines'    => $itemLines,
            'tracking_line' => $trackingLine,
            'ref_url'       => $refUrl,
        ]);

        // Fallback to hardcoded message if template is missing/empty
        if ($msg === '') {
            $msg = "Hi! Salam 😊\n\n"
                . "Pesanan anda *" . $order['order_number'] . "* dah kami hantar ya!\n\n"
                . "*Item Pesanan:*\n" . $itemLines
                . $trackingLine . "\n"
                . "Ni slip penghantaran untuk pesanan anda 📦\n\n"
                . "Rujukan pesanan: " . $refUrl . "\n\n"
                . "Terima kasih kerana membeli dengan kami! "
                . "Kalau ada apa-apa pertanyaan, jangan segan tanya ya 🙏";
        }

        $wawp    = new WawpService($endpoint, $apiKey, $senderId);
        $slipUrl = base_url() . $slipPath;
        $result  = $wawp->sendImageMessage($customerPhone, $slipUrl, $msg);

        if (!$result['success']) {
            // Fallback: base64 from disk
            $filePath = ROOT_PATH . '/public' . $slipPath;
            $result   = $wawp->sendImageFile($customerPhone, $filePath, $msg);
        }
        if (!$result['success']) {
            // Last resort: text only
            $textResult = $wawp->sendTextMessage($customerPhone, $msg);
            $logModel->log('courier_notify', $customerPhone, $msg, $textResult['success'], $textResult['error'] ?? '');
            flash('success', 'Notifikasi teks dihantar. Slip tidak dapat dilampirkan — semak konfigurasi Base URL.');
        } else {
            $logModel->log('courier_notify', $customerPhone, $msg, true, '');
            flash('success', 'Notifikasi WhatsApp dengan slip berjaya dihantar kepada pelanggan.');
        }

        redirect('/admin/orders/' . $id);
    }

    public function courier(int $id): void {
        $order = (new Order())->find($id);
        if (!$order) { redirect('/admin/orders'); return; }
        $items = (new OrderItem())->forOrder($id);
        view('admin/orders/courier', ['order' => $order, 'items' => $items], 'admin');
    }

    public function destroy(int $id): void {
        csrf_verify();
        $orderModel = new Order();
        $order      = $orderModel->find($id);
        if (!$order) {
            flash('error', 'Pesanan tidak dijumpai.');
            redirect('/admin/orders');
            return;
        }

        // Restore stock for non-cancelled orders before deleting
        if ($order['status'] !== 'cancelled') {
            $items = (new OrderItem())->forOrder($id);
            foreach ($items as $item) {
                if (!empty($item['size_id'])) {
                    (new ProductSize())->incrementStock((int)$item['size_id'], (int)$item['quantity']);
                } elseif (!empty($item['product_id'])) {
                    (new Product())->incrementStock((int)$item['product_id'], (int)$item['quantity']);
                }
            }
        }

        try {
            $orderModel->delete($id);
            flash('success', 'Pesanan #' . $order['order_number'] . ' berjaya dipadam.');
        } catch (\Throwable $e) {
            flash('error', 'Gagal memadam pesanan: ' . $e->getMessage());
        }
        redirect('/admin/orders');
    }

    public function updateStatus(int $id): void {
        csrf_verify();
        $status  = $_POST['status'] ?? '';
        $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $allowed)) {
            flash('error', 'Status tidak sah.');
            redirect('/admin/orders/' . $id);
            return;
        }

        $orderModel = new Order();
        $oldOrder   = $orderModel->find($id);

        // Restore stock when cancelling a non-cancelled order
        if ($status === 'cancelled' && $oldOrder && $oldOrder['status'] !== 'cancelled') {
            $items = (new OrderItem())->forOrder($id);
            foreach ($items as $item) {
                if (!empty($item['size_id'])) {
                    (new ProductSize())->incrementStock((int)$item['size_id'], (int)$item['quantity']);
                } elseif (!empty($item['product_id'])) {
                    (new Product())->incrementStock((int)$item['product_id'], (int)$item['quantity']);
                }
            }
        }

        $orderModel->updateStatus($id, $status);
        flash('success', 'Status pesanan berjaya dikemaskini.');
        redirect('/admin/orders/' . $id);
    }
}
