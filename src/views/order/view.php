<?php $pageTitle = 'Semak Pesanan Saya'; ?>
<div class="py-2">
    <a href="/" class="inline-flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h2 class="text-xl font-bold text-gray-800 mb-1">Semak Pesanan Saya</h2>
    <p class="text-gray-500 text-sm mb-5">Masukkan Order ID dan nombor telefon untuk melihat pesanan anda.</p>

    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <p class="text-red-600 text-sm"><i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($error) ?></p>
    </div>
    <?php endif; ?>

    <!-- Search form -->
    <form method="POST" action="/order/view" class="card p-4 space-y-3 mb-5">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Order ID <span class="text-pink-500">*</span></label>
            <input type="text" name="order_number" value="<?= e($old['order_number'] ?? '') ?>"
                   placeholder="Contoh: CML-000001"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 font-mono tracking-wider"
                   oninput="this.value = this.value.toUpperCase()">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nombor Telefon <span class="text-pink-500">*</span></label>
            <input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>"
                   placeholder="Contoh: 0123456789"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
        </div>
        <button type="submit"
            class="w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-3 rounded-xl transition-colors text-sm">
            <i class="fa-solid fa-magnifying-glass mr-2"></i> Semak Pesanan
        </button>
    </form>

    <?php if (!empty($order)):
        $statusColor = ['pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
        $statusLabel = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
        $cls = $statusColor[$order['status']] ?? 'bg-gray-100 text-gray-600';
        $lbl = $statusLabel[$order['status']] ?? $order['status'];
    ?>

    <!-- Order header card -->
    <div class="card p-4 mb-3">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Order ID</p>
                <p class="font-bold text-gray-800 font-mono"><?= e($order['order_number']) ?></p>
                <p class="text-xs text-gray-400 mt-1"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
            </div>
            <span class="text-xs font-semibold px-3 py-1.5 rounded-full <?= $cls ?>"><?= $lbl ?></span>
        </div>
        <div class="border-t pt-3 space-y-2">
            <?php if ($order['tracking_number']): ?>
            <div class="flex gap-3">
                <span class="text-xs text-gray-400 w-16 shrink-0">Tracking</span>
                <span class="text-xs font-bold text-blue-600 font-mono"><?= e($order['tracking_number']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order items -->
    <div class="card p-4 mb-3">
        <h3 class="font-semibold text-gray-700 text-sm mb-3">Item Pesanan</h3>
        <div class="space-y-3">
            <?php foreach ($items as $item): ?>
            <div class="flex items-center gap-3">
                <?php if (!empty($item['product_image'])): ?>
                <img src="<?= e($item['product_image']) ?>" alt=""
                     class="w-12 h-12 rounded-xl object-cover border border-gray-100 shrink-0">
                <?php else: ?>
                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-box text-gray-300 text-sm"></i>
                </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate"><?= e($item['product_name']) ?></p>
                    <?php if ($item['size_label']): ?>
                    <p class="text-xs text-gray-400"><?= e($item['size_label']) ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-500">x<?= $item['quantity'] ?></p>
                </div>
                <p class="font-bold text-pink-500 text-sm shrink-0">RM<?= number_format($item['subtotal'], 2) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="border-t mt-3 pt-3 flex justify-between">
            <span class="font-bold text-gray-700 text-sm">Jumlah</span>
            <span class="font-bold text-pink-500">RM<?= number_format($order['total'], 2) ?></span>
        </div>
    </div>

    <!-- Info: new order for more items -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
        <p class="text-xs text-blue-700">
            <i class="fa-solid fa-circle-info mr-1.5"></i>
            Untuk menambah item baru, sila buat pesanan baru. Setiap pesanan adalah berasingan.
        </p>
    </div>

    <a href="/"
       class="block w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-3.5 rounded-xl text-center transition-colors text-sm">
        <i class="fa-solid fa-cart-shopping mr-2"></i> Buat Pesanan Baru
    </a>

    <?php endif; ?>
</div>
