<?php $pageTitle = 'Kategori'; ?>
<?php $trashed = (new Category())->trashed(); ?>
<div class="py-2">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2">
            <h2 class="text-lg font-bold text-gray-700">Senarai Kategori</h2>
            <button type="button" id="toggle-nonaktif-cat"
                class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs font-semibold px-3 py-1.5 rounded-xl transition-colors">
                <i class="fa-solid fa-ban text-xs"></i> Non-Aktif
                <?php if (!empty($trashed)): ?>
                <span class="bg-gray-300 text-gray-600 text-xs font-bold px-1.5 py-0.5 rounded-full leading-none"><?= count($trashed) ?></span>
                <?php endif; ?>
            </button>
        </div>
        <a href="/admin/categories/add"
           class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
           <i class="fa-solid fa-plus mr-1"></i> Tambah
        </a>
    </div>

    <div id="aktif-section">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if (empty($categories)): ?>
            <div class="text-center py-10 text-gray-400 text-sm">Tiada kategori.</div>
        <?php else: ?>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Nama</th>
                    <th class="px-5 py-3 text-left">Jenis</th>
                    <th class="px-5 py-3 text-left">Produk</th>
                    <th class="px-5 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($categories as $cat): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800"><?= e($cat['name']) ?></td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 rounded-lg text-xs font-semibold
                            <?= $cat['type'] === 'pakaian' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' ?>">
                            <?= $cat['type'] === 'pakaian' ? 'Pakaian' : 'Produk' ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-500"><?= $cat['product_count'] ?></td>
                    <td class="px-5 py-3 text-right space-x-2">
                        <a href="/admin/categories/edit/<?= $cat['id'] ?>"
                           class="text-blue-500 hover:text-blue-700 text-xs font-semibold">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <form method="POST" action="/admin/categories/delete/<?= $cat['id'] ?>" class="inline" id="del-cat-<?= $cat['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="button"
                                data-confirm-form="del-cat-<?= $cat['id'] ?>"
                                data-confirm-title="Non-Aktif Kategori"
                                data-confirm-message="Kategori ini akan ditetapkan sebagai Non-Aktif. Anda boleh pulihkannya kemudian atau padam sepenuhnya."
                                class="text-red-400 hover:text-red-600 text-xs font-semibold">
                                <i class="fa-solid fa-ban"></i> Non-Aktif
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    </div>

    <div id="nonaktif-section" class="mt-5 hidden">
        <h3 class="text-sm font-bold text-gray-500 mb-2"><i class="fa-solid fa-ban mr-1"></i> Kategori Non-Aktif</h3>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <?php if (empty($trashed)): ?>
                <div class="text-center py-6 text-gray-400 text-sm">Tiada kategori non-aktif.</div>
            <?php else: ?>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Nama</th>
                        <th class="px-5 py-3 text-left">Jenis</th>
                        <th class="px-5 py-3 text-left">Padam Pada</th>
                        <th class="px-5 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($trashed as $cat): ?>
                    <tr class="hover:bg-gray-50 text-gray-500">
                        <td class="px-5 py-3 font-medium text-gray-500 line-through"><?= e($cat['name']) ?></td>
                        <td class="px-5 py-3 text-xs">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold
                                <?= $cat['type'] === 'pakaian' ? 'bg-pink-50 text-pink-400' : 'bg-blue-50 text-blue-400' ?>">
                                <?= $cat['type'] === 'pakaian' ? 'Pakaian' : 'Produk' ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400"><?= e($cat['deleted_at']) ?></td>
                        <td class="px-5 py-3 text-right space-x-2">
                            <form method="POST" action="/admin/categories/restore/<?= $cat['id'] ?>" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="text-blue-400 hover:text-blue-600 text-xs font-semibold">
                                    <i class="fa-solid fa-rotate-left"></i> Pulihkan
                                </button>
                            </form>
                            <form method="POST" action="/admin/categories/force-delete/<?= $cat['id'] ?>" class="inline" id="fd-cat-<?= $cat['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="button"
                                    data-confirm-form="fd-cat-<?= $cat['id'] ?>"
                                    data-confirm-title="Padam Sepenuhnya"
                                    data-confirm-message="Tindakan ini adalah PERMANEN. Semua data kategori ini akan dipadam sepenuhnya dari sistem. Adakah anda pasti?"
                                    class="text-red-500 hover:text-red-700 text-xs font-bold">
                                    <i class="fa-solid fa-trash"></i> Padam Sepenuhnya
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (() => {
        const btn = document.getElementById('toggle-nonaktif-cat');
        const aktifSection   = document.getElementById('aktif-section');
        const nonAktifSection = document.getElementById('nonaktif-section');
        if (btn) {
            btn.addEventListener('click', () => {
                const visible = nonAktifSection.style.display !== 'none';
                nonAktifSection.style.display = visible ? 'none' : 'block';
                aktifSection.style.display      = visible ? ''     : 'none';
                btn.classList.toggle('bg-gray-100', visible);
                btn.classList.toggle('text-gray-500', visible);
                btn.classList.toggle('bg-red-100', !visible);
                btn.classList.toggle('text-red-500', !visible);
            });
        }
    })();
    </script>
</div>
