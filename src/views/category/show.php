<?php $pageTitle = e($category['name']); ?>
<div class="py-2">
    <a href="/" class="inline-flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <!-- Category header card -->
    <div class="rounded-2xl shadow-sm border border-blue-200 p-4 mb-4 flex items-center justify-between bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
        <div>
            <h2 class="text-lg font-bold"><?= e($category['name']) ?></h2>
            <p class="text-xs text-white/80 mt-0.5">
                <?= !empty($products) ? count($products) . ' produk tersedia' : 'Tiada produk' ?>
            </p>
        </div>
        <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center shrink-0 ring-2 ring-white/30">
            <i class="fa-solid <?= $category['type'] === 'pakaian' ? 'fa-shirt' : 'fa-baby' ?> text-white text-lg"></i>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="card p-10 text-center text-gray-400">
            <i class="fa-solid fa-box-open text-4xl mb-3 block text-gray-300"></i>
            <p class="font-semibold text-gray-500 mb-1">Tiada produk tersedia</p>
            <p class="text-xs">Semua produk dalam kategori ini sedang kehabisan stok.</p>
        </div>
    <?php else: ?>
        <!-- Products container -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-grip text-pink-400"></i> Senarai Produk
                </h3>
                <span class="text-xs bg-pink-100 text-pink-700 font-semibold px-2 py-0.5 rounded-full">
                    <?= count($products) ?> produk
                </span>
            </div>

            <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-3">
                <div class="grid grid-cols-2 gap-3">
                    <?php foreach ($products as $product): ?>
                    <a href="/product/<?= $product['id'] ?>"
                       class="bg-white border border-rose-100 rounded-xl overflow-hidden hover:shadow-md hover:border-rose-200 transition-all active:scale-95">
                        <div class="aspect-square bg-gray-100 overflow-hidden relative product-gallery"
                             data-images="<?= htmlspecialchars(json_encode($product['images'] ?? []), ENT_QUOTES) ?>">
                            <?php if (!empty($product['images'])): ?>
                                <img src="<?= e($product['images'][0]) ?>" alt="<?= e($product['name']) ?>"
                                     class="w-full h-full object-cover"
                                     style="transition: opacity 0.4s ease;">
                                <?php if (count($product['images']) > 1): ?>
                                <!-- Dot indicators -->
                                <div class="absolute bottom-1.5 left-0 right-0 flex justify-center gap-1 pointer-events-none">
                                    <?php foreach ($product['images'] as $i => $_): ?>
                                    <span class="gallery-dot w-1.5 h-1.5 rounded-full <?= $i === 0 ? 'bg-pink-500' : 'bg-white/70' ?>"></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-200">
                                    <i class="fa-solid fa-image text-4xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-3">
                            <p class="font-semibold text-gray-800 text-sm leading-tight line-clamp-2 mb-1"><?= e($product['name']) ?></p>
                            <p class="text-pink-500 font-bold text-sm">
                                <?= $product['category_type'] === 'pakaian' ? 'Dari ' : '' ?>RM<?= number_format((float)($product['display_price'] ?? $product['price']), 2) ?>
                            </p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.product-gallery').forEach(function (wrapper) {
    var images = JSON.parse(wrapper.dataset.images || '[]');
    if (images.length <= 1) return;
    var img    = wrapper.querySelector('img');
    var dots   = wrapper.querySelectorAll('.gallery-dot');
    if (!img) return;
    var current = 0;
    setInterval(function () {
        img.style.opacity = '0';
        setTimeout(function () {
            current = (current + 1) % images.length;
            img.src = images[current];
            img.style.opacity = '1';
            dots.forEach(function (d, i) {
                d.classList.toggle('bg-pink-500', i === current);
                d.classList.toggle('bg-white/70', i !== current);
            });
        }, 400);
    }, 3000);
});
</script>
