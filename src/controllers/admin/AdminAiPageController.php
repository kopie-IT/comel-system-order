<?php

class AdminAiPageController {
    public function index(): void {
        $orderId = (int)($_GET['order_id'] ?? 0);
        $order   = null;
        if ($orderId > 0) {
            $order = (new Order())->find($orderId);
        }
        view('admin/ai/chat', ['order' => $order], 'admin');
    }
}
