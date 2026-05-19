<?php $pageTitle = e($product['name']); ?>
<div class="py-2">
    <a href="/category/<?= $product['category_id'] ?>" class="inline-flex items-center gap-2 text-pink-500 text-sm mb-4">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <!-- Product image -->
    <div class="card overflow-hidden mb-4 aspect-square bg-gray-100">
        <?php if ($product['image']): ?>
            <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="w-full h-full object-cover">
        <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <i class="fa-solid fa-image text-6xl"></i>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product info -->
    <div class="card p-4 mb-4">
        <h1 class="text-xl font-bold text-gray-800 mb-1"><?= e($product['name']) ?></h1>
        <?php if ($product['description']): ?>
            <p class="text-gray-500 text-sm mb-3"><?= nl2br(e($product['description'])) ?></p>
        <?php endif; ?>

        <!-- Price -->
        <div id="price-display" class="text-2xl font-bold text-pink-500 mb-3">
            RM<?= number_format((float)$product['price'], 2) ?>
        </div>

        <!-- Stock -->
        <?php if ($product['category_type'] !== 'pakaian'): ?>
            <p class="text-xs text-gray-400 mb-3">
                Stok: <?= (int)$product['stock'] > 0 ? (int)$product['stock'] . ' unit' : '<span class="text-red-500">Habis</span>' ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Add to cart form -->
    <form method="POST" action="/cart/add" class="card p-4 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

        <?php if ($product['category_type'] === 'pakaian' && !empty($sizes)): ?>
        <!-- Size selector -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Saiz</label>
            <div class="grid grid-cols-3 gap-2">
                <?php foreach ($sizes as $size): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="size_id" value="<?= $size['id'] ?>"
                           data-price="<?= $size['price'] ?>"
                           data-stock="<?= $size['stock'] ?>"
                           class="sr-only size-radio" required>
                    <div class="size-btn border-2 border-gray-200 rounded-xl p-2 text-center transition-all
                        <?= (int)$size['stock'] < 1 ? 'opacity-40 cursor-not-allowed' : 'hover:border-pink-400' ?>"
                        <?= (int)$size['stock'] < 1 ? 'data-disabled="1"' : '' ?>>
                        <p class="font-semibold text-sm text-gray-700"><?= e($size['size_label']) ?></p>
                        <p class="text-xs text-pink-500">RM<?= number_format((float)$size['price'], 2) ?></p>
                        <?php if ((int)$size['stock'] < 1): ?>
                            <p class="text-xs text-red-400">Habis</p>
                        <?php else: ?>
                            <p class="text-xs text-gray-400"><?= $size['stock'] ?> unit</p>
                        <?php endif; ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quantity -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Kuantiti</label>
            <div class="flex items-center gap-3">
                <button type="button" id="qty-minus"
                    class="w-10 h-10 rounded-xl border-2 border-gray-200 flex items-center justify-center text-gray-600 hover:border-pink-400 active:bg-pink-50">
                    <i class="fa-solid fa-minus text-sm"></i>
                </button>
                <input type="number" name="quantity" id="quantity" value="1" min="1"
                    class="w-16 text-center border-2 border-gray-200 rounded-xl py-2 font-semibold text-gray-800 focus:outline-none focus:border-pink-400">
                <button type="button" id="qty-plus"
                    class="w-10 h-10 rounded-xl border-2 border-gray-200 flex items-center justify-center text-gray-600 hover:border-pink-400 active:bg-pink-50">
                    <i class="fa-solid fa-plus text-sm"></i>
                </button>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-3.5 rounded-xl transition-colors text-base">
            <i class="fa-solid fa-cart-plus mr-2"></i> Tambah ke Troli
        </button>
    </form>
</div>

<script>
(function () {
    const qtyInput  = document.getElementById('quantity');
    const priceDisp = document.getElementById('price-display');
    let maxStock    = <?= $product['category_type'] !== 'pakaian' ? (int)$product['stock'] : 999 ?>;

    // Size selection
    document.querySelectorAll('.size-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.size-btn').forEach(function (btn) {
                btn.classList.remove('border-pink-500', 'bg-pink-50');
                btn.classList.add('border-gray-200');
            });
            const btn = radio.nextElementSibling;
            btn.classList.add('border-pink-500', 'bg-pink-50');
            btn.classList.remove('border-gray-200');
            priceDisp.textContent = 'RM' + parseFloat(radio.dataset.price).toFixed(2);
            maxStock = parseInt(radio.dataset.stock) || 1;
            qtyInput.max = maxStock;
            if (parseInt(qtyInput.value) > maxStock) qtyInput.value = maxStock;
        });
    });

    document.getElementById('qty-minus').addEventListener('click', function () {
        const v = parseInt(qtyInput.value);
        if (v > 1) qtyInput.value = v - 1;
    });
    document.getElementById('qty-plus').addEventListener('click', function () {
        const v = parseInt(qtyInput.value);
        if (v < maxStock) qtyInput.value = v + 1;
    });
})();
</script>
