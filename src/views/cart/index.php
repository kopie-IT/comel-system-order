<?php
$pageTitle  = 'Troli Saya';
$itemCount  = $itemCount ?? 0;
$lineCount  = is_array($cart) ? count($cart) : 0;
?>
<div class="py-2">

    <!-- Page header -->
    <div class="rounded-2xl shadow-sm border border-blue-200 p-4 mb-4 flex items-center justify-between bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
        <div>
            <h2 class="text-lg font-bold">Troli Saya</h2>
            <p class="text-xs text-white/80 mt-0.5">
                <?php if ($lineCount > 0): ?>
                    <?= $lineCount ?> item · <?= $itemCount ?> kuantiti
                <?php else: ?>
                    Troli kosong
                <?php endif; ?>
            </p>
        </div>
        <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center shrink-0 relative ring-2 ring-white/30">
            <i class="fa-solid fa-cart-shopping text-white text-lg"></i>
            <?php if ($lineCount > 0): ?>
            <span class="absolute -top-1 -right-1 bg-pink-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center border-2 border-white leading-none">
                <?= $itemCount > 99 ? '99+' : $itemCount ?>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($cart)): ?>
        <div class="card p-10 text-center">
            <i class="fa-solid fa-cart-shopping text-5xl mb-4 block text-gray-200"></i>
            <p class="text-base font-semibold text-gray-500 mb-1">Troli anda kosong</p>
            <p class="text-xs text-gray-400 mb-5">Tambah produk ke troli untuk meneruskan pembelian.</p>
            <a href="/" class="inline-block bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white px-6 py-3 rounded-xl font-semibold text-sm transition-colors">
                <i class="fa-solid fa-shop mr-2"></i> Teruskan Membeli-belah
            </a>
        </div>
    <?php else: ?>

        <!-- Items list: each product in its own container card, all wrapped in a soft white container -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-3 mb-4">
            <div class="flex items-center justify-between mb-3 px-1">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-pink-400"></i> Senarai Item
                </h3>
                <span class="text-xs bg-pink-100 text-pink-700 font-semibold px-2 py-0.5 rounded-full">
                    <?= $lineCount ?> item
                </span>
            </div>

            <div class="space-y-3">
                <?php foreach ($cart as $key => $item): ?>
                <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-3 flex gap-3 items-start hover:bg-rose-50 hover:border-rose-200 transition-colors">
                    <!-- Image -->
                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-white shrink-0 border border-rose-100">
                        <?php if ($item['image']): ?>
                            <img src="<?= e($item['image']) ?>" class="w-full h-full object-cover" alt="<?= e($item['product_name']) ?>">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-200">
                                <i class="fa-solid fa-image text-xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-gray-800 text-sm leading-tight flex-1 min-w-0"><?= e($item['product_name']) ?></p>
                            <!-- Remove -->
                            <form method="POST" action="/cart/remove" onsubmit="return confirm('Buang item ini dari troli?');" class="shrink-0">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="cart_key" value="<?= e($key) ?>">
                                <button type="submit"
                                    class="w-7 h-7 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors"
                                    title="Buang item">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>

                        <?php if (!empty($item['size_label']) || !empty($item['variant_label'])): ?>
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            <?php if (!empty($item['size_label'])): ?>
                                <span class="text-[10px] bg-gray-100 text-gray-600 font-semibold px-2 py-0.5 rounded-full">
                                    Saiz: <?= e($item['size_label']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($item['variant_label'])): ?>
                                <span class="text-[10px] bg-purple-100 text-purple-700 font-semibold px-2 py-0.5 rounded-full">
                                    <?= e($item['variant_label']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <p class="text-[11px] text-gray-400 mt-1">
                            RM<?= number_format($item['unit_price'], 2) ?> seunit
                        </p>

                        <div class="flex items-end justify-between mt-2 gap-2">
                            <!-- Quantity controls -->
                            <div class="flex items-center gap-1 bg-gray-50 rounded-xl p-0.5 border border-gray-100">
                                <form method="POST" action="/cart/update">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="cart_key" value="<?= e($key) ?>">
                                    <button type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-500 hover:bg-white hover:text-pink-500 transition-colors text-xs disabled:opacity-40 disabled:cursor-not-allowed"
                                        <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                </form>
                                <span class="w-7 text-center font-bold text-sm text-gray-700"><?= $item['quantity'] ?></span>
                                <form method="POST" action="/cart/update">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="cart_key" value="<?= e($key) ?>">
                                    <button type="submit" name="quantity" value="<?= min($item['max_stock'], $item['quantity'] + 1) ?>"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-500 hover:bg-white hover:text-pink-500 transition-colors text-xs disabled:opacity-40 disabled:cursor-not-allowed"
                                        <?= $item['quantity'] >= $item['max_stock'] ? 'disabled' : '' ?>>
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Line total -->
                            <span class="text-pink-500 font-bold text-base">
                                RM<?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Subtotal + checkout card -->
        <div class="card p-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500">Subtotal</span>
                <span class="text-sm font-semibold text-gray-700">RM<?= number_format($total, 2) ?></span>
            </div>
            <div class="flex items-start gap-2 text-xs text-gray-400 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2 mb-3">
                <i class="fa-solid fa-truck mt-0.5"></i>
                <span>Caj postaj akan dikira di halaman checkout selepas anda memilih negeri.</span>
            </div>
            <div class="border-t pt-3 flex justify-between items-center mb-4">
                <span class="font-bold text-gray-700">Anggaran Subtotal</span>
                <span class="text-xl font-bold text-pink-500">RM<?= number_format($total, 2) ?></span>
            </div>
            <a href="/checkout"
               class="block w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-3.5 rounded-xl text-center transition-colors">
                <i class="fa-solid fa-credit-card mr-2"></i> Teruskan ke Pembayaran
            </a>
            <a href="/"
               class="block w-full mt-2 text-center text-gray-500 hover:text-pink-500 text-xs font-semibold py-2 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Teruskan Membeli-belah
            </a>
        </div>
    <?php endif; ?>
</div>
