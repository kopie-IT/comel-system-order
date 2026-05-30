<?php

class CheckoutController {
    public function index(): void {
        $cart    = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            redirect('/cart');
            return;
        }
        $settings        = (new Setting())->all();
        $postageWest     = (float)($settings['postage_fee']      ?? '7.00');
        $postageEast     = (float)($settings['postage_fee_east'] ?? '12.00');
        // Default postage shown before state selection = West rate
        $postage         = $postageWest;
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }
        $grandTotal = $total + $postage;
        view('checkout/index', [
            'cart'            => $cart,
            'total'           => $total,
            'postage'         => $postage,
            'grandTotal'      => $grandTotal,
            'postageFeeWest'  => $postageWest,
            'postageFeeEast'  => $postageEast,
        ], 'public');
    }

    public function process(): void {
        csrf_verify();

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            redirect('/cart');
            return;
        }

        $name    = strtoupper(trim($_POST['name'] ?? ''));
        $phone   = preg_replace('/\D+/', '', trim($_POST['phone'] ?? ''));
        if ($phone !== '' && $phone[0] === '0') {
            $phone = '6' . $phone;
        }
        $address = strtoupper(trim($_POST['address'] ?? ''));
        $state   = trim($_POST['state'] ?? '');

        // Validate state against allowed list to prevent tampering
        $validStates = malaysia_states_flat();
        if ($state !== '' && !in_array($state, $validStates, true)) {
            $state = '';
        }

        $errors = [];
        if ($name === '')    { $errors[] = 'Nama diperlukan.'; }
        if ($phone === '')   { $errors[] = 'Nombor telefon diperlukan.'; }
        if ($address === '') { $errors[] = 'Alamat diperlukan.'; }
        if ($state === '')   { $errors[] = 'Sila pilih negeri.'; }

        $settings    = (new Setting())->all();
        $postageWest = (float)($settings['postage_fee']      ?? '7.00');
        $postageEast = (float)($settings['postage_fee_east'] ?? '12.00');
        $postage     = postage_for_state($state, $settings);

        if (!empty($errors)) {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['unit_price'] * $item['quantity'];
            }
            view('checkout/index', [
                'cart'            => $cart,
                'total'           => $total,
                'postage'         => $postage,
                'grandTotal'      => $total + $postage,
                'errors'          => $errors,
                'old'             => ['name' => $name, 'phone' => $phone, 'address' => $address, 'state' => $state],
                'postageFeeWest'  => $postageWest,
                'postageFeeEast'  => $postageEast,
            ], 'public');
            return;
        }

        $db = getDB();
        $db->beginTransaction();

        try {
            // Upsert customer
            $customerId = (new Customer())->createOrUpdate($name, $phone);

            // Find or create address record (deduplicate by normalized comparison)
            $addrModel = new CustomerAddress();
            if (!$addrModel->findMatch($customerId, $name, $address)) {
                $addrModel->create($customerId, $name, $address, $state);
            }

            // Calculate total
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['unit_price'] * $item['quantity'];
            }
            $total += $postage; // Add postage fee

            // Create order
            $orderModel = new Order();
            $order      = $orderModel->create($customerId, $address, $total, $state, $postage);

            // Create order items and decrement stock
            $itemModel     = new OrderItem();
            $productModel  = new Product();
            $sizeModel     = new ProductSize();
            $variantModel  = new ProductVariant();

            foreach ($cart as $item) {
                $itemModel->create($order['id'], $item);
                if (!empty($item['size_id'])) {
                    $sizeModel->decrementStock($item['size_id'], $item['quantity']);
                } elseif (!empty($item['variant_id'])) {
                    $variantModel->decrementStock($item['variant_id'], $item['quantity']);
                } else {
                    $productModel->decrementStock($item['product_id'], $item['quantity']);
                }
            }

            $db->commit();

            // Auto send order confirmation + QR payment image to customer WhatsApp
            $settings    = (new Setting())->all();
            $notifMethod = trim($settings['notification_method'] ?? 'wa_link');
            $endpoint    = trim($settings['wawp_api_endpoint'] ?? '');
            $apiKey      = trim($settings['wawp_api_key'] ?? '');
            $senderId    = trim($settings['wawp_sender_id'] ?? '');
            $qrImage     = trim($settings['qr_code_image'] ?? '');
            $waNumber    = trim($settings['whatsapp_number'] ?? '');

            // Build item lines (shared by both notification methods)
            $itemLines = '';
            $lineNum   = 1;
            foreach ($cart as $item) {
                $itemLines .= $lineNum . '. ' . $item['product_name'];
                if (!empty($item['size_label'])) {
                    $itemLines .= ' (' . $item['size_label'] . ')';
                }
                if (!empty($item['variant_label'])) {
                    $itemLines .= ' [' . $item['variant_label'] . ']';
                }
                $qty   = (int)($item['quantity'] ?? 1);
                $price = (float)($item['unit_price'] ?? 0);
                $itemLines .= ' x' . $qty;
                $itemLines .= ' — RM' . number_format($price * $qty, 2) . "\n";
                $lineNum++;
            }

            if ($notifMethod === 'wawp' && $endpoint !== '' && $apiKey !== '') {
                try {
                    $tplModel  = new WhatsappTemplate();
                    $logModel  = new WhatsappLog();
                    $wawp      = new WawpService($endpoint, $apiKey, $senderId);

                    $orderText = $tplModel->renderBySlug('order_confirmation', [
                        'app_name'      => app_name(),
                        'order_number'  => $order['order_number'],
                        'customer_name' => $name,
                        'address'       => $address . ($state !== '' ? ', ' . strtoupper($state) : ''),
                        'item_lines'    => $itemLines,
                        'total'         => number_format($total, 2),
                    ]);

                    $result = $wawp->sendTextMessage($phone, $orderText);
                    $logModel->log('order_confirmation', $phone, $orderText, $result['success'], $result['error'] ?? '');

                    if ($qrImage !== '') {
                        $qrCaption = $tplModel->renderBySlug('qr_payment', [
                            'total' => number_format($total, 2),
                        ]);

                        // WAWP's documented happy path: fetch a public HTTPS URL.
                        // Auto-upgrade http:// to https:// so a misconfigured base_url
                        // doesn't kill the image (most hosts serve both).
                        $qrUrl = rtrim(base_url(), '/') . '/' . ltrim($qrImage, '/');
                        if (stripos($qrUrl, 'http://') === 0) {
                            $qrUrl = 'https://' . substr($qrUrl, 7);
                        }

                        // Attempt 1: URL-based send (works on any public HTTPS host).
                        $qrResult = $wawp->sendImageMessage($phone, $qrUrl, $qrCaption);

                        // Attempt 2: multipart upload from disk (works on localhost
                        // or where base_url isn't reachable by wawp.net).
                        if (!$qrResult['success']) {
                            $qrLocalPath = ROOT_PATH . '/' . ltrim($qrImage, '/');
                            if (file_exists($qrLocalPath) && is_readable($qrLocalPath)) {
                                $qrResult = $wawp->sendImageFile($phone, $qrLocalPath, $qrCaption);
                            }
                        }

                        // Attempt 3: text-only fallback so the customer at least
                        // receives a clickable link to the QR if both image attempts fail.
                        if (!$qrResult['success']) {
                            $qrTextOnly = $qrCaption . "\n\n📎 QR Pembayaran: " . $qrUrl;
                            $qrResult   = $wawp->sendTextMessage($phone, $qrTextOnly);
                        }

                        $logModel->log('qr_payment', $phone, $qrCaption, $qrResult['success'], $qrResult['error'] ?? '');
                    }
                    flash('wawp_phone', $phone);
                } catch (Exception $sendErr) {
                    // Log so issues surface in WhatsApp logs instead of being swallowed.
                    try {
                        (new WhatsappLog())->log('qr_payment', $phone, 'send-exception', false, $sendErr->getMessage());
                    } catch (Exception $ignored) {}
                }
            } elseif ($notifMethod === 'wa_link' && $waNumber !== '') {
                // Build wa.me link and pass to confirmation page
                $waMsg = "Pesanan Baru! 🛍️\n\n"
                    . "No. Pesanan: " . $order['order_number'] . "\n"
                    . "Nama: " . $name . "\n"
                    . "Telefon: " . $phone . "\n"
                    . "Alamat: " . $address . "\n"
                    . "Negeri: " . $state . "\n\n"
                    . "Item:\n" . $itemLines
                    . "\nPostaj: RM" . number_format($postage, 2)
                    . "\nJumlah: RM" . number_format($total, 2);
                $waUrl = 'https://wa.me/' . preg_replace('/\D/', '', $waNumber) . '?text=' . rawurlencode($waMsg);
                flash('wa_notify_url', $waUrl);
            }

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
                'cart'            => $cart,
                'total'           => $total,
                'postage'         => $postage,
                'grandTotal'      => $total + $postage,
                'errors'          => ['Ralat sistem. Sila cuba lagi.'],
                'old'             => ['name' => $name, 'phone' => $phone, 'address' => $address, 'state' => $state],
                'postageFeeWest'  => $postageWest,
                'postageFeeEast'  => $postageEast,
            ], 'public');
        }
    }
}
