<?php
$pageTitle  = 'Senarai Item Pending';
$totalQty   = 0;
$totalLines = count($aggregated);
foreach ($aggregated as $row) {
    $totalQty += (int)$row['total_quantity'];
}

// Group detailed list by product+size+variant key for the breakdown section
$grouped = [];
foreach ($detailed as $item) {
    $key = $item['product_id'] . '|' . ($item['size_label'] ?? '') . '|' . ($item['variant_label'] ?? '');
    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'product_name'  => $item['product_name'],
            'size_label'    => $item['size_label'],
            'variant_label' => $item['variant_label'],
            'product_image' => $item['product_image'],
            'orders'        => [],
        ];
    }
    $grouped[$key]['orders'][] = $item;
}
?>
<div class="py-2">
    <a href="/admin/orders" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Senarai Pesanan
    </a>

    <!-- Header card -->
    <div class="rounded-2xl shadow-sm border border-yellow-200 p-4 mb-4 flex items-center justify-between bg-gradient-to-br from-yellow-400 to-orange-500 text-white">
        <div>
            <h2 class="text-lg font-bold">Item Perlu Disediakan</h2>
            <p class="text-xs text-white/80 mt-0.5">Senarai produk dari semua pesanan yang masih <strong>Pending</strong></p>
        </div>
        <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center shrink-0 ring-2 ring-white/30">
            <i class="fa-solid fa-clipboard-list text-white text-lg"></i>
        </div>
    </div>

    <?php if (empty($aggregated)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 text-center py-10 text-gray-400 text-sm">
            <i class="fa-solid fa-circle-check text-4xl mb-3 block text-green-300"></i>
            <p class="font-semibold text-gray-500 mb-1">Tiada pesanan pending</p>
            <p class="text-xs">Semua pesanan sudah disahkan, dilengkapkan, atau dibatalkan.</p>
        </div>
    <?php else: ?>

    <!-- Stats summary -->
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-boxes-stacked text-pink-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Jumlah Kuantiti</p>
                <p class="text-xl font-bold text-gray-700"><?= $totalQty ?></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-tags text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Produk Unik</p>
                <p class="text-xl font-bold text-gray-700"><?= $totalLines ?></p>
            </div>
        </div>
    </div>

    <!-- Aggregated picking list -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-3 mb-4">
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-pink-400"></i> Ringkasan Item
            </h3>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-1.5 bg-pink-100 hover:bg-pink-200 text-pink-600 text-xs font-semibold px-3 py-1.5 rounded-full transition-colors print:hidden">
                <i class="fa-solid fa-print"></i> Cetak
            </button>
        </div>

        <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-3 space-y-2">
            <?php foreach ($aggregated as $row): ?>
            <div class="bg-white border border-rose-100 rounded-xl p-3 flex items-center gap-3">
                <!-- Image -->
                <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 shrink-0 border border-rose-100">
                    <?php if (!empty($row['product_image'])): ?>
                        <img src="<?= e($row['product_image']) ?>" class="w-full h-full object-cover" alt="">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-200">
                            <i class="fa-solid fa-image text-lg"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Details -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm leading-tight"><?= e($row['product_name']) ?></p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <?php if (!empty($row['size_label'])): ?>
                            <span class="text-[10px] bg-gray-100 text-gray-600 font-semibold px-2 py-0.5 rounded-full">
                                Saiz: <?= e($row['size_label']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($row['variant_label'])): ?>
                            <span class="text-[10px] bg-purple-100 text-purple-700 font-semibold px-2 py-0.5 rounded-full">
                                <?= e($row['variant_label']) ?>
                            </span>
                        <?php endif; ?>
                        <span class="text-[10px] bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full">
                            Dari <?= (int)$row['order_count'] ?> pesanan
                        </span>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="text-right shrink-0">
                    <p class="text-xs text-gray-400">Kuantiti</p>
                    <p class="text-2xl font-bold text-pink-500 leading-tight">
                        <?= (int)$row['total_quantity'] ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Detailed breakdown per item -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-3 print:hidden">
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-rectangle-list text-pink-400"></i> Pecahan Mengikut Pesanan
            </h3>
            <span class="text-xs bg-pink-100 text-pink-700 font-semibold px-2 py-0.5 rounded-full">
                <?= count($grouped) ?> kumpulan
            </span>
        </div>

        <div class="space-y-3">
            <?php foreach ($grouped as $group): ?>
            <details class="bg-rose-50/60 border border-rose-100 rounded-xl group">
                <summary class="cursor-pointer p-3 flex items-center gap-3 list-none">
                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-white shrink-0 border border-rose-100">
                        <?php if (!empty($group['product_image'])): ?>
                            <img src="<?= e($group['product_image']) ?>" class="w-full h-full object-cover" alt="">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-200">
                                <i class="fa-solid fa-image text-sm"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate"><?= e($group['product_name']) ?></p>
                        <p class="text-xs text-gray-500">
                            <?php if (!empty($group['size_label'])): ?>Saiz: <?= e($group['size_label']) ?><?php endif; ?>
                            <?php if (!empty($group['size_label']) && !empty($group['variant_label'])): ?> · <?php endif; ?>
                            <?php if (!empty($group['variant_label'])): ?><?= e($group['variant_label']) ?><?php endif; ?>
                            <?php if (empty($group['size_label']) && empty($group['variant_label'])): ?>—<?php endif; ?>
                        </p>
                    </div>
                    <span class="text-xs bg-pink-100 text-pink-700 font-bold px-2 py-1 rounded-full shrink-0">
                        <?= count($group['orders']) ?> pesanan
                    </span>
                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs shrink-0 group-open:rotate-180 transition-transform"></i>
                </summary>

                <div class="border-t border-rose-100 divide-y divide-rose-100">
                    <?php foreach ($group['orders'] as $ord): ?>
                    <a href="/admin/orders/<?= (int)$ord['order_id'] ?>"
                       class="flex items-center justify-between gap-2 px-3 py-2 hover:bg-white transition-colors">
                        <div class="min-w-0">
                            <p class="font-semibold text-pink-500 text-xs font-mono"><?= e($ord['order_number']) ?></p>
                            <p class="text-xs text-gray-500 truncate">
                                <?= e($ord['customer_name']) ?> · <?= e($ord['customer_phone']) ?>
                            </p>
                            <p class="text-[11px] text-gray-400 mt-0.5">
                                <?= date('d/m/Y h:i A', strtotime($ord['order_created_at'])) ?>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[10px] text-gray-400">Qty</p>
                            <p class="font-bold text-gray-700 text-base">x<?= (int)$ord['quantity'] ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>

    <?php endif; ?>
</div>

<style>
@media print {
    body { background: white; }
    .print\:hidden { display: none !important; }
}
</style>
