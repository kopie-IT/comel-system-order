<?php

class CartController {
    public function index(): void {
        $cart  = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }
        view('cart/index', ['cart' => $cart, 'total' => $total], 'public');
    }

    public function add(): void {
        csrf_verify();

        $productId = (int)($_POST['product_id'] ?? 0);
        $sizeId    = isset($_POST['size_id']) && $_POST['size_id'] !== '' ? (int)$_POST['size_id'] : null;
        $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

        $product = (new Product())->find($productId);
        if (!$product) {
            flash('error', 'Produk tidak dijumpai.');
            redirect('/');
            return;
        }

        $unitPrice  = (float)$product['price'];
        $sizeLabel  = null;
        $maxStock   = (int)$product['stock'];

        if ($product['category_type'] === 'pakaian') {
            if (!$sizeId) {
                flash('error', 'Sila pilih saiz.');
                redirect('/product/' . $productId);
                return;
            }
            $size = (new ProductSize())->find($sizeId);
            if (!$size || (int)$size['product_id'] !== $productId) {
                flash('error', 'Saiz tidak sah.');
                redirect('/product/' . $productId);
                return;
            }
            $unitPrice = (float)$size['price'];
            $sizeLabel = $size['size_label'];
            $maxStock  = (int)$size['stock'];
        }

        if ($maxStock < 1) {
            flash('error', 'Stok tidak mencukupi.');
            redirect('/product/' . $productId);
            return;
        }

        $cartKey = $productId . '_' . ($sizeId ?? '0');

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$cartKey])) {
            $newQty = $_SESSION['cart'][$cartKey]['quantity'] + $quantity;
            $_SESSION['cart'][$cartKey]['quantity'] = min($newQty, $maxStock);
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id'   => $productId,
                'product_name' => $product['name'],
                'image'        => $product['image'],
                'size_id'      => $sizeId,
                'size_label'   => $sizeLabel,
                'unit_price'   => $unitPrice,
                'quantity'     => min($quantity, $maxStock),
                'max_stock'    => $maxStock,
            ];
        }

        flash('success', 'Produk berjaya ditambah ke troli.');
        redirect('/cart');
    }

    public function update(): void {
        csrf_verify();
        $cartKey  = $_POST['cart_key'] ?? '';
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if (isset($_SESSION['cart'][$cartKey])) {
            $maxStock = $_SESSION['cart'][$cartKey]['max_stock'] ?? 999;
            $_SESSION['cart'][$cartKey]['quantity'] = min($quantity, $maxStock);
        }
        redirect('/cart');
    }

    public function remove(): void {
        csrf_verify();
        $cartKey = $_POST['cart_key'] ?? '';
        unset($_SESSION['cart'][$cartKey]);
        redirect('/cart');
    }
}
