<?php $pageTitle = 'Troli Saya'; ?>
<div class="py-2">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Troli Saya</h2>

    <?php if (empty($cart)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fa-solid fa-cart-shopping text-5xl mb-4"></i>
            <p class="text-lg font-semibold mb-2">Troli anda kosong</p>
            <a href="/" class="inline-block mt-2 bg-pink-500 text-white px-6 py-3 rounded-xl font-semibold text-sm">
                Teruskan Membeli-belah
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-3 mb-6">
            <?php foreach ($cart as $key => $item): ?>
            <div class="card p-3 flex gap-3 items-start">
                <!-- Image -->
                <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                    <?php if ($item['image']): ?>
                        <img src="<?= e($item['image']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <i class="fa-solid fa-image"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Details -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm leading-tight"><?= e($item['product_name']) ?></p>
                    <?php if ($item['size_label']): ?>
                        <p class="text-xs text-gray-400 mt-0.5">Saiz: <?= e($item['size_label']) ?></p>
                    <?php endif; ?>
                    <p class="text-pink-500 font-bold text-sm mt-1">
                        RM<?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                    </p>

                    <!-- Quantity controls -->
                    <div class="flex items-center gap-2 mt-2">
                        <form method="POST" action="/cart/update" class="flex items-center gap-2">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="cart_key" value="<?= e($key) ?>">
                            <button type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>"
                                class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:border-pink-400 text-xs">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <span class="w-6 text-center font-semibold text-sm"><?= $item['quantity'] ?></span>
                            <button type="submit" name="quantity" value="<?= min($item['max_stock'], $item['quantity'] + 1) ?>"
                                class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:border-pink-400 text-xs">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </form>

                        <!-- Remove -->
                        <form method="POST" action="/cart/remove" class="ml-auto">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="cart_key" value="<?= e($key) ?>">
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs p-1">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Total & Checkout -->
        <div class="card p-4">
            <div class="flex justify-between items-center mb-4">
                <span class="font-semibold text-gray-700">Jumlah</span>
                <span class="text-xl font-bold text-pink-500">RM<?= number_format($total, 2) ?></span>
            </div>
            <a href="/checkout"
               class="block w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3.5 rounded-xl text-center transition-colors">
                <i class="fa-solid fa-credit-card mr-2"></i> Teruskan ke Pembayaran
            </a>
        </div>
    <?php endif; ?>
</div>
