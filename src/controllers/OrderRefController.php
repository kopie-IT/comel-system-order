<?php

class OrderRefController {
    public function show(string $orderNumber): void {
        $order = (new Order())->findByOrderNumber($orderNumber);
        if (!$order) {
            http_response_code(404);
            view('errors/404', [], 'public');
            return;
        }

        // If admin is logged in, redirect to admin order page
        if (!empty($_SESSION['admin_id'])) {
            redirect('/admin/orders/' . $order['id']);
            return;
        }

        $items = (new OrderItem())->forOrder($order['id']);
        view('order/ref', [
            'order' => $order,
            'items' => $items,
        ], 'public');
    }
}
