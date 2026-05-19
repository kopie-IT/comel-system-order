<?php

class AdminOrderController {
    public function index(): void {
        $status = $_GET['status'] ?? '';
        $orders = (new Order())->all($status);
        view('admin/orders/index', ['orders' => $orders, 'status' => $status], 'admin');
    }

    public function show(int $id): void {
        $order = (new Order())->find($id);
        if (!$order) { redirect('/admin/orders'); return; }
        $items = (new OrderItem())->forOrder($id);
        view('admin/orders/show', ['order' => $order, 'items' => $items], 'admin');
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
        (new Order())->updateStatus($id, $status);
        flash('success', 'Status pesanan berjaya dikemaskini.');
        redirect('/admin/orders/' . $id);
    }
}
