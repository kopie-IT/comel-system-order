<?php $pageTitle = 'Senarai Produk'; ?>
<div class="py-2">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <h2 class="text-lg font-bold text-gray-700">Senarai Produk</h2>
            <button type="button" id="toggle-nonaktif-btn"
                class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs font-semibold px-3 py-1.5 rounded-xl transition-colors">
                <i class="fa-solid fa-ban text-xs"></i> Non-Aktif
                <?php if (!empty($trashed)): ?>
                <span class="bg-gray-300 text-gray-600 text-xs font-bold px-1.5 py-0.5 rounded-full leading-none"><?= count($trashed) ?></span>
                <?php endif; ?>
            </button>
        </div>
        <a href="/admin/products/add"
           class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Tambah
        </a>
    </div>

    <!-- Search & Filter -->
    <form method="GET" action="/admin/products" class="flex flex-row gap-2 mb-4">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari produk..."
            class="flex-1 min-w-0 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400">
        <select name="category_id" onchange="this.form.submit()" class="border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-pink-400">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-sm font-semibold shrink-0">
            <i class="fa-solid fa-search"></i>
        </button>
        <!-- View toggle -->
        <button type="button" id="view-toggle"
            class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2.5 rounded-xl text-sm font-semibold shrink-0"
            title="Tukar paparan">
            <i class="fa-solid fa-list" id="toggle-icon"></i>
        </button>
    </form>

    <?php if (empty($products)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 text-center py-12 text-gray-400 text-sm">
            <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
            Tiada produk dijumpai.
        </div>
    <?php else: ?>

    <!-- ===== THUMBNAIL GRID (default) ===== -->
    <div id="view-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6">
        <?php foreach ($products as $p): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <!-- Thumbnail -->
            <a href="/admin/products/edit/<?= $p['id'] ?>" class="block aspect-square bg-gray-100 overflow-hidden">
                <?php if ($p['image']): ?>
                    <img src="<?= e($p['image']) ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-gray-200">
                        <i class="fa-solid fa-image text-4xl"></i>
                    </div>
                <?php endif; ?>
            </a>
            <!-- Info -->
            <div class="p-2 flex-1 flex flex-col gap-1">
                <a href="/admin/products/edit/<?= $p['id'] ?>" class="font-semibold text-gray-800 text-xs leading-tight line-clamp-2 hover:text-pink-500"><?= e($p['name']) ?></a>
                <p class="text-xs text-gray-400"><?= e($p['category_name']) ?></p>
                <p class="text-xs font-bold text-pink-500 mt-auto">
                    <?= $p['category_type'] === 'pakaian' ? '<span class="text-gray-400 font-normal">Ikut saiz</span>' : 'RM' . number_format($p['price'], 2) ?>
                </p>
            </div>
            <!-- Actions -->
            <div class="px-2 pb-2 flex gap-1">
                <a href="/admin/products/edit/<?= $p['id'] ?>"
                   class="flex-1 text-center text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-500 py-1.5 rounded-lg transition-colors">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <form method="POST" action="/admin/products/delete/<?= $p['id'] ?>" class="flex-1" id="del-g-<?= $p['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                     <button type="button"
                        data-confirm-form="del-g-<?= $p['id'] ?>"
                        data-confirm-title="Non-Aktif Produk"
                        data-confirm-message="Produk ini akan ditetapkan sebagai Non-Aktif dan tidak akan dipaparkan kepada pelanggan. Anda boleh pulihkannya kemudian."
                        class="w-full text-xs font-semibold bg-red-50 hover:bg-red-100 text-red-400 py-1.5 rounded-lg transition-colors">
                        <i class="fa-solid fa-ban"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== LIST VIEW (hidden by default) ===== -->
    <div id="view-list" class="hidden mb-6">

        <!-- Desktop table -->
        <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Produk</th>
                            <th class="px-5 py-3 text-left">Kategori</th>
                            <th class="px-5 py-3 text-left">Harga</th>
                            <th class="px-5 py-3 text-left">Stok</th>
                            <th class="px-5 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                                        <?php if ($p['image']): ?>
                                            <img src="<?= e($p['image']) ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <i class="fa-solid fa-image text-sm"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-medium text-gray-800"><?= e($p['name']) ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-500"><?= e($p['category_name']) ?></td>
                            <td class="px-5 py-3 font-semibold text-pink-500">
                                <?= $p['category_type'] === 'pakaian' ? '<span class="text-gray-400 text-xs">Ikut saiz</span>' : 'RM' . number_format($p['price'], 2) ?>
                            </td>
                            <td class="px-5 py-3">
                                <?php if ($p['category_type'] === 'pakaian'): ?>
                                    <span class="text-gray-400 text-xs">Ikut saiz</span>
                                <?php elseif ($p['stock'] < 1): ?>
                                    <span class="text-red-500 font-semibold text-xs">Habis</span>
                                <?php elseif ($p['stock'] <= 5): ?>
                                    <span class="text-orange-500 font-semibold"><?= $p['stock'] ?></span>
                                <?php else: ?>
                                    <span class="text-gray-600"><?= $p['stock'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-right space-x-3">
                                <a href="/admin/products/edit/<?= $p['id'] ?>"
                                   class="text-blue-500 hover:text-blue-700 text-xs font-semibold">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <form method="POST" action="/admin/products/delete/<?= $p['id'] ?>" class="inline" id="del-l-<?= $p['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                     <button type="button"
                                        data-confirm-form="del-l-<?= $p['id'] ?>"
                                        data-confirm-title="Non-Aktif Produk"
                                        data-confirm-message="Produk ini akan ditetapkan sebagai Non-Aktif dan tidak akan dipaparkan kepada pelanggan. Anda boleh pulihkannya kemudian."
                                        class="text-red-400 hover:text-red-600 text-xs font-semibold">
                                        <i class="fa-solid fa-ban"></i> Non-Aktif
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden space-y-3">
            <?php foreach ($products as $p): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex">
                <form method="POST" action="/admin/products/delete/<?= $p['id'] ?>"
                      id="del-m-<?= $p['id'] ?>" class="shrink-0">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="button"
                        data-confirm-form="del-m-<?= $p['id'] ?>"
                        data-confirm-title="Non-Aktif Produk"
                        data-confirm-message="Produk ini akan ditetapkan sebagai Non-Aktif. Anda boleh pulihkannya kemudian."
                        class="h-full w-7 bg-red-500 hover:bg-red-600 active:bg-red-700 text-white flex items-center justify-center transition-colors rounded-l-2xl">
                        <span class="text-[9px] font-bold tracking-widest uppercase"
                              style="writing-mode:vertical-rl;transform:rotate(180deg)">Non-Aktif</span>
                    </button>
                </form>
                <a href="/admin/products/edit/<?= $p['id'] ?>"
                   class="flex-1 p-3 flex items-center gap-3 active:bg-gray-50 transition-colors">
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        <?php if ($p['image']): ?>
                            <img src="<?= e($p['image']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-200">
                                <i class="fa-solid fa-image text-xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate"><?= e($p['name']) ?></p>
                        <p class="text-xs text-gray-400 mt-0.5"><?= e($p['category_name']) ?></p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-sm font-bold text-pink-500">
                                <?= $p['category_type'] === 'pakaian' ? '<span class="text-gray-400 text-xs font-normal">Ikut saiz</span>' : 'RM' . number_format($p['price'], 2) ?>
                            </span>
                            <?php if ($p['category_type'] !== 'pakaian'): ?>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                <?= $p['stock'] < 1 ? 'bg-red-100 text-red-600' : ($p['stock'] <= 5 ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-600') ?>">
                                <?= $p['stock'] < 1 ? 'Habis' : 'Stok: ' . $p['stock'] ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs shrink-0"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

    </div><!-- end #view-list -->

    <?php endif; ?>

    <!-- ===== DISABLED / TRASHED PRODUCTS ===== -->
    <?php if (!empty($trashed)): ?>
    <div class="mt-6" id="nonaktif-section" style="display:none">
        <div class="flex items-center gap-2 mb-3">
            <h3 class="text-sm font-bold text-gray-500">Produk Non-Aktif</h3>
            <span class="bg-gray-200 text-gray-500 text-xs font-bold px-2 py-0.5 rounded-full"><?= count($trashed) ?></span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <?php foreach ($trashed as $p): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col opacity-60">
                <!-- Thumbnail with disabled overlay -->
                <div class="relative aspect-square bg-gray-100 overflow-hidden">
                    <?php if ($p['image']): ?>
                        <img src="<?= e($p['image']) ?>" class="w-full h-full object-cover grayscale">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-200">
                            <i class="fa-solid fa-image text-4xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gray-900/30 flex items-center justify-center">
                        <span class="bg-gray-800/70 text-white text-xs font-bold px-2 py-1 rounded-lg">
                            <i class="fa-solid fa-ban mr-1"></i>Non-Aktif
                        </span>
                    </div>
                </div>
                <!-- Info -->
                <div class="p-2 flex-1 flex flex-col gap-1">
                    <p class="font-semibold text-gray-500 text-xs leading-tight line-clamp-2"><?= e($p['name']) ?></p>
                    <p class="text-xs text-gray-400"><?= e($p['category_name']) ?></p>
                </div>
                <!-- Restore action -->
                <div class="px-2 pb-2 flex gap-1">
                    <a href="/admin/products/edit/<?= $p['id'] ?>"
                       class="flex-1 text-center text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-500 py-1.5 rounded-lg transition-colors">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <form method="POST" action="/admin/products/restore/<?= $p['id'] ?>" class="flex-1">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit"
                            class="w-full text-xs font-semibold bg-green-50 hover:bg-green-100 text-green-600 py-1.5 rounded-lg transition-colors">
                            <i class="fa-solid fa-rotate-left"></i> Pulih
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
(function () {
    var STORAGE_KEY = 'product_view';
    var grid        = document.getElementById('view-grid');
    var list        = document.getElementById('view-list');
    var toggleBtn   = document.getElementById('view-toggle');
    var toggleIcon  = document.getElementById('toggle-icon');

    if (!grid || !toggleBtn) return;

    var current = localStorage.getItem(STORAGE_KEY) || 'grid';

    function applyView(v) {
        current = v;
        localStorage.setItem(STORAGE_KEY, v);
        if (v === 'grid') {
            grid.classList.remove('hidden');
            if (list) list.classList.add('hidden');
            toggleIcon.className = 'fa-solid fa-list';
            toggleBtn.title = 'Tukar ke paparan senarai';
        } else {
            grid.classList.add('hidden');
            if (list) list.classList.remove('hidden');
            toggleIcon.className = 'fa-solid fa-grip';
            toggleBtn.title = 'Tukar ke paparan grid';
        }
    }

    applyView(current);

    toggleBtn.addEventListener('click', function () {
        applyView(current === 'grid' ? 'list' : 'grid');
    });

    // Non-Aktif section toggle
    var nonAktifBtn     = document.getElementById('toggle-nonaktif-btn');
    var nonAktifSection = document.getElementById('nonaktif-section');

    if (nonAktifBtn) {
        nonAktifBtn.addEventListener('click', function () {
            if (!nonAktifSection) return;
            var visible = nonAktifSection.style.display !== 'none';
            nonAktifSection.style.display = visible ? 'none' : 'block';
            nonAktifBtn.classList.toggle('bg-gray-100', visible);
            nonAktifBtn.classList.toggle('text-gray-500', visible);
            nonAktifBtn.classList.toggle('bg-red-100', !visible);
            nonAktifBtn.classList.toggle('text-red-500', !visible);
        });
    }
})();
</script>
