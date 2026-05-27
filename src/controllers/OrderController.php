<?php

class OrderController {

    public function confirmation(string $orderNumber): void {
        $order = (new Order())->findByOrderNumber($orderNumber);
        if (!$order) {
            http_response_code(404);
            view('errors/404', [], 'public');
            return;
        }
        $items       = (new OrderItem())->forOrder($order['id']);
        $settings    = (new Setting())->all();
        $wawpPhone   = flash('wawp_phone');
        $waNotifyUrl = flash('wa_notify_url');
        view('order/confirmation', [
            'order'       => $order,
            'items'       => $items,
            'settings'    => $settings,
            'wawpPhone'   => $wawpPhone,
            'waNotifyUrl' => $waNotifyUrl,
        ], 'public');
    }

    public function lookup(): void {
        view('order/lookup', [], 'public');
    }

    public function lookupByPhone(): void {
        csrf_verify();
        $phone = trim($_POST['phone'] ?? '');
        if ($phone === '') {
            view('order/lookup', ['error' => 'Sila masukkan nombor telefon.'], 'public');
            return;
        }
        $orders = (new Order())->findByPhone($phone);
        if (empty($orders)) {
            view('order/lookup', [
                'error'     => 'Tiada pesanan dijumpai untuk nombor telefon ini.',
                'old_phone' => $phone,
            ], 'public');
            return;
        }
        view('order/lookup', ['orders' => $orders, 'phone' => $phone], 'public');
    }

    public function showUpdate(string $orderNumber): void {
        $phone = trim($_GET['phone'] ?? '');
        $order = (new Order())->findByOrderNumber($orderNumber);
        if (!$order || $order['customer_phone'] !== $phone) {
            http_response_code(404);
            view('errors/404', [], 'public');
            return;
        }
        $items = (new OrderItem())->forOrder($order['id']);
        view('order/update', ['order' => $order, 'items' => $items, 'phone' => $phone], 'public');
    }

    public function processUpdate(string $orderNumber): void {
        csrf_verify();
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $order   = (new Order())->findByOrderNumber($orderNumber);

        if (!$order || $order['customer_phone'] !== $phone) {
            http_response_code(404);
            view('errors/404', [], 'public');
            return;
        }
        if ($address === '') {
            $items = (new OrderItem())->forOrder($order['id']);
            view('order/update', ['order' => $order, 'items' => $items, 'phone' => $phone, 'error' => 'Alamat tidak boleh kosong.'], 'public');
            return;
        }
        if ($order['status'] !== 'pending') {
            $items = (new OrderItem())->forOrder($order['id']);
            view('order/update', ['order' => $order, 'items' => $items, 'phone' => $phone, 'error' => 'Pesanan ini tidak boleh dikemaskini kerana status sudah berubah.'], 'public');
            return;
        }
        (new Order())->updateAddress($order['id'], $address);
        flash('success', 'Alamat pesanan ' . $orderNumber . ' berjaya dikemaskini.');
        redirect('/order/track?phone=' . urlencode($phone));
    }

    public function showView(): void {
        view('order/view', [], 'public');
    }

    public function processView(): void {
        csrf_verify();
        $phone    = trim($_POST['phone'] ?? '');
        $orderNum = strtoupper(trim($_POST['order_number'] ?? ''));

        if ($phone === '' || $orderNum === '') {
            view('order/view', [
                'error' => 'Sila masukkan Order ID dan nombor telefon.',
                'old'   => ['phone' => $phone, 'order_number' => $orderNum],
            ], 'public');
            return;
        }

        $order = (new Order())->findByOrderNumber($orderNum);

        if (!$order || $order['customer_phone'] !== $phone) {
            view('order/view', [
                'error' => 'Order ID atau nombor telefon tidak sepadan. Sila semak semula.',
                'old'   => ['phone' => $phone, 'order_number' => $orderNum],
            ], 'public');
            return;
        }

        $items = (new OrderItem())->forOrder($order['id']);
        view('order/view', ['order' => $order, 'items' => $items], 'public');
    }
}
