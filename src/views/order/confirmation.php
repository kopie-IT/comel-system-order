<?php
$pageTitle = 'Pengesahan Pesanan';
$waNumber  = $settings['whatsapp_number'] ?? '';
$qrImage   = $settings['qr_code_image'] ?? '';
$payInstr  = $settings['payment_instructions'] ?? '';

// Build WhatsApp message
$itemLines = '';
foreach ($items as $item) {
    $itemLines .= '- ' . $item['product_name'];
    if ($item['size_label']) $itemLines .= ' (' . $item['size_label'] . ')';
    $itemLines .= ' x' . $item['quantity'] . ' = RM' . number_format($item['subtotal'], 2) . "\n";
}
$waMsg = "Pesanan Baru!\n"
    . "No. Pesanan: " . $order['order_number'] . "\n"
    . "Nama: " . $order['customer_name'] . "\n"
    . "Telefon: " . $order['customer_phone'] . "\n"
    . "Alamat: " . $order['address'] . "\n\n"
    . "Item:\n" . $itemLines . "\n"
    . "Jumlah: RM" . number_format($order['total'], 2);
$waUrl = 'https://wa.me/' . preg_replace('/\D/', '', $waNumber) . '?text=' . rawurlencode($waMsg);
?>
<div class="py-2">
    <!-- Success header -->
    <div class="text-center mb-6">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-circle-check text-green-500 text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800">Pesanan Berjaya!</h2>
        <p class="text-gray-500 text-sm mt-1">No. Pesanan: <strong class="text-gray-700"><?= e($order['order_number']) ?></strong></p>
    </div>

    <!-- Order items -->
    <div class="card p-4 mb-4">
        <h3 class="font-semibold text-gray-700 mb-3">Ringkasan Pesanan</h3>
        <div class="space-y-2 mb-3">
            <?php foreach ($items as $item): ?>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">
                    <?= e($item['product_name']) ?>
                    <?php if ($item['size_label']): ?><span class="text-gray-400">(<?= e($item['size_label']) ?>)</span><?php endif; ?>
                    x<?= $item['quantity'] ?>
                </span>
                <span class="font-semibold">RM<?= number_format($item['subtotal'], 2) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="border-t pt-3 flex justify-between">
            <span class="font-bold text-gray-700">Jumlah</span>
            <span class="font-bold text-pink-500 text-lg">RM<?= number_format($order['total'], 2) ?></span>
        </div>
    </div>

    <!-- Payment QR -->
    <?php if ($qrImage): ?>
    <div class="card p-4 mb-4 text-center">
        <h3 class="font-semibold text-gray-700 mb-3">Bayaran via QR Code</h3>
        <img src="<?= e($qrImage) ?>" alt="QR Code Bayaran" class="mx-auto max-w-xs w-full rounded-xl border">
        <?php if ($payInstr): ?>
        <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-left">
            <p class="text-xs text-yellow-800 font-semibold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Arahan Pembayaran</p>
            <p class="text-xs text-yellow-700"><?= nl2br(e($payInstr)) ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- WhatsApp notify -->
    <?php if ($waNumber): ?>
    <a href="<?= $waUrl ?>" target="_blank"
       class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-xl text-center mb-3 transition-colors">
        <i class="fa-brands fa-whatsapp mr-2 text-lg"></i> Hantar Notifikasi ke Admin
    </a>
    <?php endif; ?>

    <a href="/" class="block w-full border-2 border-pink-300 text-pink-500 font-bold py-3.5 rounded-xl text-center hover:bg-pink-50 transition-colors">
        <i class="fa-solid fa-house mr-2"></i> Kembali ke Laman Utama
    </a>
</div>
