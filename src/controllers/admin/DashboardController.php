<?php

class DashboardController {
    public function index(): void {
        $search   = trim($_GET['search'] ?? '');
        $settings = (new Setting())->all();

        // Auto-cancel expired pending orders
        if (($settings['auto_cancel_enabled'] ?? '1') === '1') {
            $hours = max(1, (int)($settings['auto_cancel_hours'] ?? 2));
            (new Order())->cancelExpired($hours);
        }

        $stats  = (new Order())->stats();
        $orders = (new Order())->dashboardOrders($search);
        view('admin/dashboard/index', [
            'stats'  => $stats,
            'orders' => $orders,
            'search' => $search,
        ], 'admin');
    }
}
