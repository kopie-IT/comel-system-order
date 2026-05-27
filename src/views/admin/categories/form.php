<?php
$isEdit    = !empty($category);
$pageTitle = $isEdit ? 'Edit Kategori' : 'Tambah Kategori';
$old       = $old ?? ($category ?? []);
?>
<div class="py-2 max-w-lg">
    <a href="/admin/categories" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h2 class="text-lg font-bold text-gray-700 mb-5"><?= $pageTitle ?></h2>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <?php foreach ($errors as $err): ?>
            <p class="text-red-600 text-sm"><i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/categories/<?= $isEdit ? 'edit/' . $category['id'] : 'add' ?>"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kategori</label>
            <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" required
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Kategori</label>
            <select name="type" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                <option value="pakaian" <?= ($old['type'] ?? '') === 'pakaian' ? 'selected' : '' ?>>Pakaian (Clothing)</option>
                <option value="produk"  <?= ($old['type'] ?? 'produk') === 'produk' ? 'selected' : '' ?>>Produk (Product)</option>
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                class="bg-pink-500 hover:bg-pink-600 text-white font-bold px-6 py-3 rounded-xl transition-colors text-sm">
                <?= $isEdit ? 'Kemaskini' : 'Simpan' ?>
            </button>
            <a href="/admin/categories"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-xl text-sm transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
