<?php

class OrderController {
    public function confirmation(string $orderNumber): void {
        $order = (new Order())->findByOrderNumber($orderNumber);
        if (!$order) {
            http_response_code(404);
            view('errors/404', [], 'public');
            return;
        }
        $items    = (new OrderItem())->forOrder($order['id']);
        $settings = (new Setting())->all();
        view('order/confirmation', [
            'order'    => $order,
            'items'    => $items,
            'settings' => $settings,
        ], 'public');
    }
}
