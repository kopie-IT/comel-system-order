<?php $pageTitle = e($category['name']); ?>
<div class="py-2">
    <a href="/" class="inline-flex items-center gap-2 text-pink-500 text-sm mb-4">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h2 class="text-xl font-bold text-gray-800 mb-4"><?= e($category['name']) ?></h2>

    <?php if (empty($products)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fa-solid fa-box-open text-4xl mb-3"></i>
            <p>Tiada produk dalam kategori ini.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 gap-4">
            <?php foreach ($products as $product): ?>
            <a href="/product/<?= $product['id'] ?>" class="card overflow-hidden hover:shadow-md transition-shadow active:scale-95">
                <div class="aspect-square bg-gray-100 overflow-hidden">
                    <?php if ($product['image']): ?>
                        <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>"
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <i class="fa-solid fa-image text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-3">
                    <p class="font-semibold text-gray-800 text-sm leading-tight line-clamp-2"><?= e($product['name']) ?></p>
                    <p class="text-pink-500 font-bold text-sm mt-1">
                        <?php if ($product['category_type'] === 'pakaian'): ?>
                            Dari RM<?= number_format((float)$product['price'], 2) ?>
                        <?php else: ?>
                            RM<?= number_format((float)$product['price'], 2) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
