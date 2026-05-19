<?php
$isEdit    = !empty($product);
$pageTitle = $isEdit ? 'Edit Produk' : 'Tambah Produk';
$old       = $old ?? ($product ?? []);
?>
<div class="py-2 max-w-2xl">
    <a href="/admin/products" class="inline-flex items-center gap-2 text-pink-500 text-sm mb-4">
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

    <form method="POST" action="/admin/products/<?= $isEdit ? 'edit/' . $product['id'] : 'add' ?>"
          enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- Image upload -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Gambar Produk</label>
            <?php if ($isEdit && $product['image']): ?>
                <img src="<?= e($product['image']) ?>" id="img-preview"
                     class="w-32 h-32 object-cover rounded-xl border mb-2">
            <?php else: ?>
                <img id="img-preview" class="w-32 h-32 object-cover rounded-xl border mb-2 hidden">
            <?php endif; ?>
            <input type="file" name="image" id="image-input" accept="image/jpeg,image/png,image/webp"
                <?= $isEdit ? '' : 'required' ?>
                class="block text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0
                       file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-600 hover:file:bg-pink-100">
            <p class="text-xs text-gray-400 mt-1">JPEG, PNG atau WebP. Maks 2MB.</p>
        </div>

        <!-- Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Produk</label>
            <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" required
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Penerangan</label>
            <textarea name="description" rows="3"
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 resize-none"><?= e($old['description'] ?? '') ?></textarea>
        </div>

        <!-- Category -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
            <select name="category_id" id="category-select" required
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                            data-type="<?= $cat['type'] ?>"
                            <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Price & Stock (for non-pakaian) -->
        <div id="price-stock-section">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Harga (RM)</label>
                    <input type="number" name="price" value="<?= e($old['price'] ?? '0') ?>"
                           step="0.01" min="0"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Stok</label>
                    <input type="number" name="stock" value="<?= e($old['stock'] ?? '0') ?>"
                           min="0"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                </div>
            </div>
        </div>

        <!-- Sizes section (for pakaian) -->
        <div id="sizes-section" class="hidden">
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-gray-700">Saiz & Harga</label>
                <button type="button" id="add-size-btn"
                    class="text-pink-500 hover:text-pink-700 text-sm font-semibold">
                    <i class="fa-solid fa-plus mr-1"></i> Tambah Saiz
                </button>
            </div>
            <div id="sizes-container" class="space-y-2">
                <?php foreach ($sizes as $i => $size): ?>
                <div class="size-row flex gap-2 items-center">
                    <input type="text" name="size_label[]" value="<?= e($size['size_label']) ?>"
                           placeholder="Saiz (cth: S, M, 0-3M)"
                           class="flex-1 border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <input type="number" name="size_price[]" value="<?= e($size['price']) ?>"
                           placeholder="Harga" step="0.01" min="0"
                           class="w-28 border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <input type="number" name="size_stock[]" value="<?= e($size['stock']) ?>"
                           placeholder="Stok" min="0"
                           class="w-24 border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <button type="button" class="remove-size text-red-400 hover:text-red-600 px-2">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                class="bg-pink-500 hover:bg-pink-600 text-white font-bold px-6 py-3 rounded-xl transition-colors text-sm">
                <?= $isEdit ? 'Kemaskini' : 'Simpan' ?>
            </button>
            <a href="/admin/products"
               class="border-2 border-gray-200 text-gray-600 font-semibold px-6 py-3 rounded-xl text-sm hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
(function () {
    const catSelect       = document.getElementById('category-select');
    const priceStockSec   = document.getElementById('price-stock-section');
    const sizesSec        = document.getElementById('sizes-section');
    const sizesContainer  = document.getElementById('sizes-container');
    const addSizeBtn      = document.getElementById('add-size-btn');
    const imgInput        = document.getElementById('image-input');
    const imgPreview      = document.getElementById('img-preview');

    function toggleSections() {
        const selected = catSelect.options[catSelect.selectedIndex];
        const type     = selected ? selected.dataset.type : '';
        if (type === 'pakaian') {
            priceStockSec.classList.add('hidden');
            sizesSec.classList.remove('hidden');
        } else {
            priceStockSec.classList.remove('hidden');
            sizesSec.classList.add('hidden');
        }
    }

    catSelect.addEventListener('change', toggleSections);
    toggleSections();

    function addSizeRow(label, price, stock) {
        const row = document.createElement('div');
        row.className = 'size-row flex gap-2 items-center';
        row.innerHTML = `
            <input type="text" name="size_label[]" value="${label||''}" placeholder="Saiz (cth: S, M, 0-3M)"
                   class="flex-1 border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
            <input type="number" name="size_price[]" value="${price||''}" placeholder="Harga" step="0.01" min="0"
                   class="w-28 border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
            <input type="number" name="size_stock[]" value="${stock||''}" placeholder="Stok" min="0"
                   class="w-24 border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
            <button type="button" class="remove-size text-red-400 hover:text-red-600 px-2">
                <i class="fa-solid fa-times"></i>
            </button>`;
        sizesContainer.appendChild(row);
        row.querySelector('.remove-size').addEventListener('click', function () { row.remove(); });
    }

    addSizeBtn.addEventListener('click', function () { addSizeRow(); });

    document.querySelectorAll('.remove-size').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.closest('.size-row').remove(); });
    });

    imgInput.addEventListener('change', function () {
        const file = imgInput.files[0];
        if (file) {
            imgPreview.src = URL.createObjectURL(file);
            imgPreview.classList.remove('hidden');
        }
    });
})();
</script>
