<?php $pageTitle = 'Comel Baby Store'; ?>
<div class="py-4">
    <h2 class="text-xl font-bold text-gray-800 mb-1">Selamat Datang!</h2>
    <p class="text-gray-500 text-sm mb-6">Pilih kategori untuk mula membeli-belah.</p>

    <?php if (empty($categories)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fa-solid fa-box-open text-4xl mb-3"></i>
            <p>Tiada kategori tersedia.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 gap-4">
            <?php foreach ($categories as $cat): ?>
            <a href="/category/<?= $cat['id'] ?>"
               class="card p-5 flex flex-col items-center gap-3 hover:shadow-md transition-shadow active:scale-95">
                <div class="w-14 h-14 rounded-full flex items-center justify-center
                    <?= $cat['type'] === 'pakaian' ? 'bg-pink-100' : 'bg-blue-100' ?>">
                    <i class="fa-solid <?= $cat['type'] === 'pakaian' ? 'fa-shirt text-pink-500' : 'fa-baby text-blue-500' ?> text-2xl"></i>
                </div>
                <span class="font-semibold text-gray-700 text-center text-sm"><?= e($cat['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
