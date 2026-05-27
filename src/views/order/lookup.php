<?php $pageTitle = 'Semak Pesanan'; ?>
<div class="py-2">
    <a href="/" class="inline-flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h2 class="text-xl font-bold text-gray-800 mb-1">Semak Pesanan Saya</h2>
    <p class="text-gray-500 text-sm mb-5">Masukkan nombor telefon anda untuk melihat semua pesanan.</p>

    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <p class="text-red-600 text-sm"><i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($error) ?></p>
    </div>
    <?php endif; ?>

    <form method="POST" action="/order/track" class="mb-6">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="card p-4 space-y-3">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombor Telefon</label>
                <input type="tel" name="phone" value="<?= e($old_phone ?? '') ?>" required
                    placeholder="Contoh: 0123456789"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            </div>
            <button type="submit"
                class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                <i class="fa-solid fa-magnifying-glass mr-2"></i> Cari Pesanan
            </button>
        </div>
    </form>

    <?php if (!empty($orders)): ?>
    <h3 class="font-bold text-gray-700 mb-3">Pesanan untuk <?= e($phone) ?></h3>
    <div class="space-y-3">
        <?php foreach ($orders as $order): ?>
        <?php
            $statusColor = match($order['status']) {
                'pending'   => 'bg-yellow-100 text-yellow-700',
                'confirmed' => 'bg-blue-100 text-blue-700',
                'completed' => 'bg-green-100 text-green-700',
                'cancelled' => 'bg-red-100 text-red-700',
                default     => 'bg-gray-100 text-gray-700',
            };
            $statusLabel = match($order['status']) {
                'pending'   => 'Menunggu',
                'confirmed' => 'Confirmed',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                default     => $order['status'],
            };
        ?>
        <div class="card p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Order ID</p>
                    <span class="font-bold text-gray-800 text-sm font-mono"><?= e($order['order_number']) ?></span>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $statusColor ?>">
                    <?= $statusLabel ?>
                </span>
            </div>
            <p class="text-xs text-gray-500 mb-1"><i class="fa-solid fa-location-dot mr-1"></i><?= e($order['address']) ?></p>
            <p class="text-xs text-gray-500 mb-3"><i class="fa-solid fa-calendar mr-1"></i><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
            <div class="flex items-center justify-between">
                <span class="font-bold text-pink-500">RM<?= number_format($order['total'], 2) ?></span>
                <div class="flex gap-2">
                    <a href="/order/confirmation/<?= e($order['order_number']) ?>"
                       class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-3 py-1.5 rounded-lg transition-colors">
                        <i class="fa-solid fa-eye mr-1"></i> Lihat
                    </a>
                    <?php if ($order['status'] === 'pending'): ?>
                    <a href="/order/update/<?= e($order['order_number']) ?>?phone=<?= urlencode($phone) ?>"
                       class="text-xs bg-pink-500 hover:bg-pink-600 text-white font-semibold px-3 py-1.5 rounded-lg transition-colors">
                        <i class="fa-solid fa-pen mr-1"></i> Kemaskini
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
