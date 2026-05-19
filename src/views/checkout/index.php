<?php $pageTitle = 'Checkout'; ?>
<div class="py-2">
    <a href="/cart" class="inline-flex items-center gap-2 text-pink-500 text-sm mb-4">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Troli
    </a>
    <h2 class="text-xl font-bold text-gray-800 mb-4">Maklumat Penghantaran</h2>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <?php foreach ($errors as $err): ?>
            <p class="text-red-600 text-sm"><i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/checkout" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="card p-4 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Penuh</label>
                <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" required
                    placeholder="Contoh: Siti Aminah"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombor Telefon</label>
                <input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" required
                    placeholder="Contoh: 0123456789"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Penghantaran</label>
                <textarea name="address" required rows="3"
                    placeholder="No. rumah, jalan, bandar, poskod, negeri"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 resize-none"><?= e($old['address'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Order summary -->
        <div class="card p-4">
            <h3 class="font-semibold text-gray-700 mb-3">Ringkasan Pesanan</h3>
            <div class="space-y-2 mb-3">
                <?php foreach ($cart as $item): ?>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">
                        <?= e($item['product_name']) ?>
                        <?php if ($item['size_label']): ?><span class="text-gray-400">(<?= e($item['size_label']) ?>)</span><?php endif; ?>
                        x<?= $item['quantity'] ?>
                    </span>
                    <span class="font-semibold">RM<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="border-t pt-3 flex justify-between">
                <span class="font-bold text-gray-700">Jumlah</span>
                <span class="font-bold text-pink-500 text-lg">RM<?= number_format($total, 2) ?></span>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-4 rounded-xl transition-colors text-base">
            <i class="fa-solid fa-check mr-2"></i> Hantar Pesanan
        </button>
    </form>
</div>
