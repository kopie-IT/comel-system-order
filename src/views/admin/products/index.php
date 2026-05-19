<?php $pageTitle = 'Senarai Produk'; ?>
<div class="py-2">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-700">Senarai Produk</h2>
        <a href="/admin/products/add"
           class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Tambah
        </a>
    </div>

    <!-- Search & Filter -->
    <form method="GET" action="/admin/products" class="flex gap-2 mb-4">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari produk..."
            class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-pink-400">
        <select name="category_id" class="border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm font-semibold">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if (empty($products)): ?>
            <div class="text-center py-10 text-gray-400 text-sm">Tiada produk dijumpai.</div>
        <?php else: ?>
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
                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                                    <?php if ($p['image']): ?>
                                        <img src="<?= e($p['image']) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">
                                            <i class="fa-solid fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="font-medium text-gray-800"><?= e($p['name']) ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-500"><?= e($p['category_name']) ?></td>
                        <td class="px-5 py-3 font-semibold text-pink-500">RM<?= number_format($p['price'], 2) ?></td>
                        <td class="px-5 py-3">
                            <span class="<?= $p['stock'] < 1 ? 'text-red-500' : 'text-gray-600' ?>">
                                <?= $p['category_type'] === 'pakaian' ? '<span class="text-gray-400 text-xs">Ikut saiz</span>' : $p['stock'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right space-x-2">
                            <a href="/admin/products/edit/<?= $p['id'] ?>"
                               class="text-blue-500 hover:text-blue-700 text-xs font-semibold">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            <form method="POST" action="/admin/products/delete/<?= $p['id'] ?>" class="inline"
                                  onsubmit="return confirm('Padam produk ini?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="text-red-400 hover:text-red-600 text-xs font-semibold">
                                    <i class="fa-solid fa-trash"></i> Padam
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
