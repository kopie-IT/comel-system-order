<?php $pageTitle = 'Kemaskini Pesanan'; ?>
<div class="py-2">
    <a href="/order/track?phone=<?= urlencode($phone) ?>" class="inline-flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Senarai Pesanan
    </a>
    <h2 class="text-xl font-bold text-gray-800 mb-1">Kemaskini Pesanan</h2>
    <p class="text-gray-500 text-sm mb-5">Order ID: <strong class="text-gray-700"><?= e($order['order_number']) ?></strong></p>

    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <p class="text-red-600 text-sm"><i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($error) ?></p>
    </div>
    <?php endif; ?>

    <!-- Order summary -->
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

    <!-- Update form -->
    <form method="POST" action="/order/update/<?= e($order['order_number']) ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="phone" value="<?= e($phone) ?>">
        <div class="card p-4 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" value="<?= e($order['customer_name']) ?>" disabled
                    class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombor Telefon</label>
                <input type="text" value="<?= e($order['customer_phone']) ?>" disabled
                    class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Penghantaran <span class="text-pink-500">*</span></label>
                <textarea name="address" required rows="3"
                    placeholder="No. rumah, jalan, bandar, poskod, negeri"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 resize-none"><?= e($order['address']) ?></textarea>
                <p class="text-xs text-gray-400 mt-1"><i class="fa-solid fa-circle-info mr-1"></i>Hanya alamat penghantaran boleh dikemaskini semasa status <strong>Menunggu</strong>.</p>
            </div>
        </div>

        <button type="submit"
            class="w-full mt-4 bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-4 rounded-xl transition-colors text-base">
            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
        </button>
    </form>
</div>
