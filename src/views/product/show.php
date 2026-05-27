<?php
$pageTitle = e($product['name']);
$images    = $images ?? ($product['image'] ? [$product['image']] : []);

// Build WhatsApp share URL with product info
$waNumber   = (new Setting())->get('whatsapp_number', '601234567890');
$waLink     = 'https://wa.me/' . preg_replace('/\D/', '', $waNumber);
$imgUrl     = !empty($images) ? base_url() . $images[0] : '';
$minPrice   = $product['category_type'] === 'pakaian' && !empty($sizes)
    ? min(array_column($sizes, 'price'))
    : (float)$product['price'];
$priceStr   = 'RM' . number_format($minPrice, 2) . ($product['category_type'] === 'pakaian' ? ' (ikut saiz)' : '');
$waMsg      = "Salam! Saya berminat dengan produk ini:\n\n"
    . "*" . $product['name'] . "*\n"
    . "Harga: " . $priceStr . "\n"
    . ($imgUrl ? "\nGambar: " . $imgUrl . "\n" : "")
    . "\nBoleh saya dapatkan maklumat lanjut?";
$waShareUrl = $waLink . '?text=' . rawurlencode($waMsg);
?>
<div class="py-2">
    <a href="/category/<?= $product['category_id'] ?>" class="inline-flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <!-- Product image gallery -->
    <div class="card overflow-hidden mb-4">
        <?php if (!empty($images)): ?>
        <!-- Main image -->
        <div class="relative aspect-square bg-gray-100">
            <img id="main-product-image"
                 src="<?= e($images[0]) ?>"
                 alt="<?= e($product['name']) ?>"
                 class="w-full h-full object-cover"
                 style="transition: opacity 0.3s ease;">
            <?php if (count($images) > 1): ?>
            <!-- Dot indicators -->
            <div class="absolute bottom-2 left-0 right-0 flex justify-center gap-1.5 pointer-events-none">
                <?php foreach ($images as $i => $_): ?>
                <span class="main-dot w-2 h-2 rounded-full transition-colors <?= $i === 0 ? 'bg-pink-500' : 'bg-white/70' ?>"></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <!-- Thumbnails (only when multiple images) -->
        <?php if (count($images) > 1): ?>
        <div class="flex gap-2 p-3 overflow-x-auto">
            <?php foreach ($images as $i => $img): ?>
            <button type="button"
                    class="thumb shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 transition-colors <?= $i === 0 ? 'border-pink-500' : 'border-gray-200' ?>"
                    data-index="<?= $i ?>" data-src="<?= e($img) ?>">
                <img src="<?= e($img) ?>" class="w-full h-full object-cover" alt="">
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="aspect-square bg-gray-100 flex items-center justify-center text-gray-300">
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

        <!-- Price + Quantity inline -->
        <?php
        $displayPrice = (float)$product['price'];
        if ($product['category_type'] === 'pakaian' && !empty($sizes)) {
            $inStockPrices = [];
            foreach ($sizes as $s) {
                if ((int)$s['stock'] > 0) $inStockPrices[] = (float)$s['price'];
            }
            if (!empty($inStockPrices)) $displayPrice = min($inStockPrices);
        }
        ?>
        <div class="flex items-center justify-between mb-3">
            <div id="price-display" class="text-2xl font-bold text-pink-500">
                <?= $product['category_type'] === 'pakaian' ? '<span class="text-base font-normal text-gray-400 mr-1">Dari</span>' : '' ?>RM<?= number_format($displayPrice, 2) ?>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="qty-minus"
                    class="w-9 h-9 rounded-xl border-2 border-gray-200 flex items-center justify-center text-gray-600 hover:border-pink-400 active:bg-pink-50">
                    <i class="fa-solid fa-minus text-xs"></i>
                </button>
                <input type="number" id="quantity" value="1" min="1"
                    class="w-12 text-center border-2 border-gray-200 rounded-xl py-1.5 font-semibold text-gray-800 focus:outline-none focus:border-pink-400 text-sm">
                <button type="button" id="qty-plus"
                    class="w-9 h-9 rounded-xl border-2 border-gray-200 flex items-center justify-center text-gray-600 hover:border-pink-400 active:bg-pink-50">
                    <i class="fa-solid fa-plus text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Add to cart form -->
    <form method="POST" action="/cart/add" class="card p-4 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <input type="hidden" name="quantity" id="quantity-hidden" value="1">

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
                        <?php endif; ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($variants)): ?>
        <!-- Variant selector -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Varian</label>
            <div class="grid grid-cols-3 gap-2">
                <?php foreach ($variants as $variant): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="variant_id" value="<?= $variant['id'] ?>"
                           data-price="<?= $variant['price'] ?>"
                           data-stock="<?= $variant['stock'] ?>"
                           data-image="<?= e($variant['image'] ?? '') ?>"
                           class="sr-only variant-radio"
                           <?= $product['category_type'] !== 'pakaian' ? 'required' : '' ?>>
                    <div class="variant-btn border-2 border-gray-200 rounded-xl p-2 text-center transition-all
                        <?= (int)$variant['stock'] < 1 ? 'opacity-40 cursor-not-allowed' : 'hover:border-pink-400' ?>"
                        <?= (int)$variant['stock'] < 1 ? 'data-disabled="1"' : '' ?>>
                        <?php if (!empty($variant['image'])): ?>
                        <div class="w-full aspect-square rounded-lg overflow-hidden mb-1 bg-gray-100">
                            <img src="<?= e($variant['image']) ?>" class="w-full h-full object-cover" alt="<?= e($variant['variant_label']) ?>">
                        </div>
                        <?php endif; ?>
                        <p class="font-semibold text-sm text-gray-700"><?= e($variant['variant_label']) ?></p>
                        <?php if ((float)$variant['price'] > 0): ?>
                        <p class="text-xs text-pink-500">RM<?= number_format((float)$variant['price'], 2) ?></p>
                        <?php endif; ?>
                        <?php if ((int)$variant['stock'] < 1): ?>
                            <p class="text-xs text-red-400">Habis</p>
                        <?php endif; ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action buttons -->
        <div class="flex gap-2">
            <button type="button" onclick="shareToWhatsApp()"
               title="Tanya melalui WhatsApp"
               class="w-14 h-14 bg-green-500 hover:bg-green-600 active:bg-green-700 text-white rounded-xl flex items-center justify-center shrink-0 transition-colors">
                <i class="fa-brands fa-whatsapp text-2xl"></i>
            </button>
            <button type="submit"
                class="flex-1 bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-3.5 rounded-xl transition-colors text-base">
                <i class="fa-solid fa-cart-plus mr-2"></i> Tambah ke Troli
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    var images   = <?= json_encode($images) ?>;
    var mainImg  = document.getElementById('main-product-image');
    var dots     = document.querySelectorAll('.main-dot');
    var thumbs   = document.querySelectorAll('.thumb');
    var current  = 0;
    var autoTimer = null;

    function goTo(index) {
        current = index;
        if (mainImg) {
            mainImg.style.opacity = '0';
            setTimeout(function () {
                mainImg.src = images[index];
                mainImg.style.opacity = '1';
            }, 300);
        }
        dots.forEach(function (d, i) {
            d.classList.toggle('bg-pink-500', i === index);
            d.classList.toggle('bg-white/70', i !== index);
        });
        thumbs.forEach(function (t, i) {
            t.classList.toggle('border-pink-500', i === index);
            t.classList.toggle('border-gray-200', i !== index);
        });
    }

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            clearInterval(autoTimer);
            goTo(parseInt(thumb.dataset.index));
        });
    });

    // Auto-rotate when multiple images
    if (images.length > 1) {
        autoTimer = setInterval(function () {
            goTo((current + 1) % images.length);
        }, 3000);
    }

    // Qty & size logic
    var qtyInput    = document.getElementById('quantity');
    var qtyHidden   = document.getElementById('quantity-hidden');
    var priceDisp   = document.getElementById('price-display');
    var maxStock    = <?= $product['category_type'] !== 'pakaian' ? (int)$product['stock'] : 999 ?>;

    function syncQty() {
        if (qtyHidden) qtyHidden.value = qtyInput.value;
    }

    document.querySelectorAll('.size-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.size-btn').forEach(function (btn) {
                btn.classList.remove('border-pink-500', 'bg-pink-50');
                btn.classList.add('border-gray-200');
            });
            var btn = radio.nextElementSibling;
            btn.classList.add('border-pink-500', 'bg-pink-50');
            btn.classList.remove('border-gray-200');
            priceDisp.textContent = 'RM' + parseFloat(radio.dataset.price).toFixed(2);
            maxStock = parseInt(radio.dataset.stock) || 1;
            qtyInput.max = maxStock;
            if (parseInt(qtyInput.value) > maxStock) { qtyInput.value = maxStock; syncQty(); }
        });
    });

    document.querySelectorAll('.variant-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.variant-btn').forEach(function (btn) {
                btn.classList.remove('border-pink-500', 'bg-pink-50');
                btn.classList.add('border-gray-200');
            });
            var btn = radio.nextElementSibling;
            btn.classList.add('border-pink-500', 'bg-pink-50');
            btn.classList.remove('border-gray-200');
            // Only update price display if variant has its own price (> 0)
            var variantPrice = parseFloat(radio.dataset.price);
            if (variantPrice > 0) {
                priceDisp.textContent = 'RM' + variantPrice.toFixed(2);
            }
            var variantStock = parseInt(radio.dataset.stock) || 0;
            maxStock = variantStock;
            qtyInput.max = maxStock;
            if (parseInt(qtyInput.value) > maxStock) { qtyInput.value = maxStock; syncQty(); }
            // Swap main product image if variant has its own image
            var variantImage = radio.dataset.image;
            if (variantImage && mainImg) {
                mainImg.style.opacity = '0';
                setTimeout(function () {
                    mainImg.src = variantImage;
                    mainImg.style.opacity = '1';
                }, 300);
                // Update dot/thumb highlights to none (variant image is not in the gallery)
                dots.forEach(function (d) {
                    d.classList.remove('bg-pink-500');
                    d.classList.add('bg-white/70');
                });
                thumbs.forEach(function (t) {
                    t.classList.remove('border-pink-500');
                    t.classList.add('border-gray-200');
                });
            }
        });
    });

    document.getElementById('qty-minus').addEventListener('click', function () {
        var v = parseInt(qtyInput.value);
        if (v > 1) { qtyInput.value = v - 1; syncQty(); }
    });
    document.getElementById('qty-plus').addEventListener('click', function () {
        var v = parseInt(qtyInput.value);
        if (v < maxStock) { qtyInput.value = v + 1; syncQty(); }
    });
    qtyInput.addEventListener('change', syncQty);
})();

function shareToWhatsApp() {
    var imgUrl  = <?= json_encode($imgUrl) ?>;
    var waUrl   = <?= json_encode($waShareUrl) ?>;
    var waMsg   = <?= json_encode($waMsg) ?>;

    // Mobile: try Web Share API to attach image file directly
    if (imgUrl && navigator.share) {
        fetch(imgUrl)
            .then(function (r) { return r.blob(); })
            .then(function (blob) {
                var file = new File([blob], 'produk.jpg', { type: blob.type || 'image/jpeg' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    return navigator.share({ files: [file], text: waMsg });
                }
                // canShare returned false — fall back
                window.open(waUrl, '_blank');
            })
            .catch(function () {
                window.open(waUrl, '_blank');
            });
        return;
    }
    // Desktop / no Web Share API — open wa.me link
    window.open(waUrl, '_blank');
}
</script>
