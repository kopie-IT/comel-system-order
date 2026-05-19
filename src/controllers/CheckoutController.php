<?php

class CheckoutController {
    public function index(): void {
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            redirect('/cart');
            return;
        }
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }
        view('checkout/index', ['cart' => $cart, 'total' => $total], 'public');
    }

    public function process(): void {
        csrf_verify();

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            redirect('/cart');
            return;
        }

        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $errors = [];
        if ($name === '')    { $errors[] = 'Nama diperlukan.'; }
        if ($phone === '')   { $errors[] = 'Nombor telefon diperlukan.'; }
        if ($address === '') { $errors[] = 'Alamat diperlukan.'; }

        if (!empty($errors)) {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['unit_price'] * $item['quantity'];
            }
            view('checkout/index', [
                'cart'   => $cart,
                'total'  => $total,
                'errors' => $errors,
                'old'    => ['name' => $name, 'phone' => $phone, 'address' => $address],
            ], 'public');
            return;
        }

        $db = getDB();
        $db->beginTransaction();

        try {
            // Upsert customer
            $customerId = (new Customer())->createOrUpdate($name, $phone);

            // Calculate total
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['unit_price'] * $item['quantity'];
            }

            // Create order
            $orderModel = new Order();
            $order      = $orderModel->create($customerId, $address, $total);

            // Create order items and decrement stock
            $itemModel    = new OrderItem();
            $productModel = new Product();
            $sizeModel    = new ProductSize();

            foreach ($cart as $item) {
                $itemModel->create($order['id'], $item);
                if ($item['size_id']) {
                    $sizeModel->decrementStock($item['size_id'], $item['quantity']);
                } else {
                    $productModel->decrementStock($item['product_id'], $item['quantity']);
                }
            }

            $db->commit();

            // Clear cart
            unset($_SESSION['cart']);

            redirect('/order/confirmation/' . $order['order_number']);

        } catch (Exception $e) {
            $db->rollBack();
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['unit_price'] * $item['quantity'];
            }
            view('checkout/index', [
                'cart'   => $cart,
                'total'  => $total,
                'errors' => ['Ralat sistem. Sila cuba lagi.'],
                'old'    => ['name' => $name, 'phone' => $phone, 'address' => $address],
            ], 'public');
        }
    }
}
