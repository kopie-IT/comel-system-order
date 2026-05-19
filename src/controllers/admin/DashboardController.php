<?php

class DashboardController {
    public function index(): void {
        $stats = (new Order())->stats();
        view('admin/dashboard/index', ['stats' => $stats], 'admin');
    }
}
