<?php

class AdminCustomerController {

    public function index(): void {
        $search    = trim($_GET['search'] ?? '');
        $customers = (new Customer())->all($search);
        view('admin/customers/index', ['customers' => $customers, 'search' => $search], 'admin');
    }

    public function show(int $id): void {
        $customer = (new Customer())->findById($id);
        if (!$customer) { redirect('/admin/customers'); return; }
        $orders    = (new Order())->findByCustomerId($id);
        $addresses = (new CustomerAddress())->findByCustomer($id);
        view('admin/customers/show', [
            'customer'  => $customer,
            'orders'    => $orders,
            'addresses' => $addresses,
        ], 'admin');
    }
}
