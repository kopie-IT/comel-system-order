<?php

class CartController {
    public function index(): void {
        $cart  = $_SESSION['cart'] ?? [];
        $total = 0;
        $count = 0;
        foreach ($cart as $item) {
            $total += $item['unit_price'] * $item['quantity'];
            $count += (int)$item['quantity'];
        }
        view('cart/index', [
            'cart'      => $cart,
            'total'     => $total,
            'itemCount' => $count,
        ], 'public');
    }

    public function add(): void {
        csrf_verify();

        $productId = (int)($_POST['product_id'] ?? 0);
        $sizeId    = isset($_POST['size_id'])    && $_POST['size_id']    !== '' ? (int)$_POST['size_id']    : null;
        $variantId = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? (int)$_POST['variant_id'] : null;
        $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

        $product = (new Product())->find($productId);
        if (!$product) {
            flash('error', 'Produk tidak dijumpai.');
            redirect('/');
            return;
        }

        $unitPrice     = (float)$product['price'];
        $sizeLabel     = null;
        $variantLabel  = null;
        $maxStock      = (int)$product['stock'];

        // Check if product actually has sizes defined
        $allSizes = (new ProductSize())->forProduct($productId);
        $hasSizes = !empty($allSizes);

        if ($product['category_type'] === 'pakaian' && $hasSizes) {
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

        // Validate variant if provided
        if ($variantId) {
            $variant = (new ProductVariant())->find($variantId);
            if (!$variant || (int)$variant['product_id'] !== $productId) {
                flash('error', 'Varian tidak sah.');
                redirect('/product/' . $productId);
                return;
            }
            $variantLabel = $variant['variant_label'];
            // If variant has its own price, use it
            if ((float)$variant['price'] > 0) {
                $unitPrice = (float)$variant['price'];
            }
            // Use variant stock if no size selected
            if (!$sizeId) {
                $maxStock = (int)$variant['stock'];
            }
        }

        // Check if variant is required (product has variants but none selected)
        $allVariants = (new ProductVariant())->forProduct($productId);
        if (!empty($allVariants) && !$variantId) {
            flash('error', 'Sila pilih varian.');
            redirect('/product/' . $productId);
            return;
        }

        if ($maxStock < 1) {
            flash('error', 'Stok tidak mencukupi.');
            redirect('/product/' . $productId);
            return;
        }

        $cartKey = $productId . '_' . ($sizeId ?? '0') . '_' . ($variantId ?? '0');

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$cartKey])) {
            $newQty = $_SESSION['cart'][$cartKey]['quantity'] + $quantity;
            $_SESSION['cart'][$cartKey]['quantity'] = min($newQty, $maxStock);
            $addedQty = $_SESSION['cart'][$cartKey]['quantity'];
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id'    => $productId,
                'product_name'  => $product['name'],
                'image'         => $product['image'],
                'size_id'       => $sizeId,
                'size_label'    => $sizeLabel,
                'variant_id'    => $variantId,
                'variant_label' => $variantLabel,
                'unit_price'    => $unitPrice,
                'quantity'      => min($quantity, $maxStock),
                'max_stock'     => $maxStock,
            ];
            $addedQty = $_SESSION['cart'][$cartKey]['quantity'];
        }

        // Stash a rich payload for the "added to cart" TV-switch-on modal
        // (rendered by the public layout). Suppresses the plain flash banner.
        $_SESSION['cart_just_added'] = [
            'product_id'    => $productId,
            'product_name'  => $product['name'],
            'image'         => $product['image'],
            'size_label'    => $sizeLabel,
            'variant_label' => $variantLabel,
            'unit_price'    => $unitPrice,
            'quantity'      => $addedQty,
            'category_id'   => (int)$product['category_id'],
        ];

        // Redirect to the originating category so the user can keep browsing
        // similar products. The modal lets them choose Continue or Checkout.
        redirect('/category/' . (int)$product['category_id']);
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
