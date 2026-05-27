<?php
$pageTitle   = 'Pesanan ' . e($order['order_number']);
$statusMap   = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
$statusColor = [
    'pending'   => 'bg-yellow-100 text-yellow-700',
    'confirmed' => 'bg-blue-100 text-blue-700',
    'completed' => 'bg-green-100 text-green-700',
    'cancelled' => 'bg-red-100 text-red-700',
];
?>
<div class="py-2">
    <!-- Order header -->
    <div class="card p-4 mb-4 text-center">
        <p class="text-xs text-gray-400 mb-1">No. Pesanan</p>
        <p class="font-bold text-gray-800 text-lg font-mono"><?= e($order['order_number']) ?></p>
        <span class="inline-block mt-2 px-3 py-1 rounded-xl text-sm font-semibold <?= $statusColor[$order['status']] ?? 'bg-gray-100 text-gray-600' ?>">
            <?= $statusMap[$order['status']] ?? $order['status'] ?>
        </span>
        <p class="text-xs text-gray-400 mt-2"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
    </div>

    <!-- Order items -->
    <div class="card p-4 mb-4">
        <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-bag-shopping text-pink-400 text-sm"></i> Item Pesanan
        </h3>
        <div class="space-y-2 mb-3">
            <?php foreach ($items as $item): ?>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">
                    <?= e($item['product_name']) ?>
                    <?php if ($item['size_label']): ?>
                    <span class="text-gray-400 text-xs">(Saiz: <?= e($item['size_label']) ?>)</span>
                    <?php endif; ?>
                    <span class="text-gray-400">x<?= $item['quantity'] ?></span>
                </span>
                <span class="font-semibold shrink-0 ml-2">RM<?= number_format((float)$item['subtotal'], 2) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="border-t pt-3 flex justify-between">
            <span class="font-bold text-gray-700">Jumlah</span>
            <span class="font-bold text-pink-500 text-lg">RM<?= number_format((float)$order['total'], 2) ?></span>
        </div>
    </div>

    <!-- Courier slip -->
    <?php if ($order['courier_slip']): ?>
    <?php
    $slipExt = strtolower(pathinfo($order['courier_slip'], PATHINFO_EXTENSION));
    $isImg   = in_array($slipExt, ['jpg','jpeg','png','webp']);
    ?>
    <div class="card p-4 mb-4">
        <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-truck-fast text-orange-400 text-sm"></i> Slip Penghantaran
        </h3>
        <?php if (!empty($order['tracking_number'])): ?>
        <div class="bg-orange-50 rounded-xl p-3 mb-3">
            <p class="text-xs text-gray-500 mb-0.5">Nota</p>
            <p class="font-bold text-gray-800"><?= e($order['tracking_number']) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($isImg): ?>
        <img src="<?= e($order['courier_slip']) ?>" alt="Slip Penghantaran"
             class="w-full rounded-xl border border-gray-200 max-h-[400px] object-contain">
        <?php else: ?>
        <a href="<?= e($order['courier_slip']) ?>" target="_blank"
           class="inline-flex items-center gap-2 bg-red-50 text-red-600 hover:bg-red-100 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <i class="fa-solid fa-file-pdf text-lg"></i> Lihat Slip PDF
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <a href="/" class="block w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-3.5 rounded-xl text-center transition-colors">
        <i class="fa-solid fa-house mr-2"></i> Kembali ke Laman Utama
    </a>
</div>
