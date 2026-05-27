<?php $pageTitle = 'Kategori'; ?>
<div class="py-2">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-700">Senarai Kategori</h2>
        <a href="/admin/categories/add"
           class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Tambah
        </a>
    </div>

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
                                data-confirm-title="Padam Kategori"
                                data-confirm-message="Adakah anda pasti mahu memadam kategori ini?"
                                class="text-red-400 hover:text-red-600 text-xs font-semibold">
                                <i class="fa-solid fa-trash"></i> Padam
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
