<?php
$isEdit    = !empty($product);
$pageTitle = $isEdit ? 'Edit Produk' : 'Tambah Produk';
$old       = $old ?? ($product ?? []);
$images    = $images ?? [];
?>
<div class="py-2 max-w-2xl">
    <a href="/admin/products" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h2 class="text-lg font-bold text-gray-700 mb-4"><?= $pageTitle ?></h2>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <?php foreach ($errors as $err): ?>
            <p class="text-red-600 text-sm"><i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/products/<?= $isEdit ? 'edit/' . $product['id'] : 'add' ?>"
          enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-6 space-y-5">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- Image upload -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-gray-700">Gambar Produk <span class="text-pink-500">*</span></label>
                <button type="button" id="add-image-btn"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-pink-500 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-3 py-1.5 rounded-xl transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Gambar Lain
                </button>
            </div>

            <?php if ($isEdit && !empty($images)): ?>
            <!-- Existing images grid -->
            <div class="mb-3">
                <p class="text-xs text-gray-500 mb-2">Gambar sedia ada — klik untuk tandakan padam:</p>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($images as $img): ?>
                    <div class="relative w-20 h-20 rounded-xl overflow-hidden border-2 border-gray-200 group cursor-pointer"
                         onclick="toggleDeleteImage(this)">
                        <img src="<?= e($img['image']) ?>" class="w-full h-full object-cover">
                        <input type="checkbox" name="delete_image[]" value="<?= $img['id'] ?>" class="delete-img-cb sr-only">
                        <div class="delete-overlay absolute inset-0 bg-red-500/70 hidden items-center justify-center rounded-xl">
                            <i class="fa-solid fa-trash text-white text-xl"></i>
                        </div>
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                            <i class="fa-solid fa-trash text-white text-lg"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Image rows -->
            <div id="image-inputs-container" class="space-y-2">
                <label class="image-input-row flex items-center gap-3 p-3 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-pink-400 transition-colors bg-gray-50">
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-white border border-gray-200 shrink-0 flex items-center justify-center">
                        <img class="img-thumb w-full h-full object-cover hidden" alt="">
                        <i class="fa-solid fa-image text-gray-300 text-2xl thumb-placeholder"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-600">Gambar 1</p>
                        <p class="img-filename text-xs text-gray-400 truncate">Klik untuk pilih gambar</p>
                    </div>
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" class="image-file-input sr-only">
                    <button type="button" class="remove-image-row hidden w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-600 rounded-xl hover:bg-red-100 transition-colors shrink-0"
                            onclick="event.preventDefault();this.closest('.image-input-row').remove()">
                        <i class="fa-solid fa-trash text-sm"></i>
                    </button>
                </label>
            </div>

            <p class="text-xs text-gray-400 mt-2">JPEG, PNG atau WebP. Maks 2MB setiap gambar.</p>
        </div>

        <!-- Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Produk <span class="text-pink-500">*</span></label>
            <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" required
                placeholder="Contoh: Baju Romper Bayi"
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Penerangan</label>
            <textarea name="description" rows="3" placeholder="Penerangan produk..."
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 resize-none"><?= e($old['description'] ?? '') ?></textarea>
        </div>

        <!-- Category -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori <span class="text-pink-500">*</span></label>
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
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Harga (RM)</label>
                    <input type="number" name="price" value="<?= e($old['price'] ?? '0') ?>"
                           step="0.01" min="0" placeholder="0.00"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Stok</label>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="adjustStock(-1)"
                            class="w-9 h-10 rounded-xl bg-gray-200 hover:bg-gray-300 active:bg-gray-400 flex items-center justify-center text-gray-700 transition-colors shrink-0">
                            <i class="fa-solid fa-minus text-xs"></i>
                        </button>
                        <input type="number" name="stock" id="stock-input" value="<?= e($old['stock'] ?? '0') ?>"
                               min="0" placeholder="0"
                            class="flex-1 border-2 border-gray-200 rounded-xl px-2 py-3 text-sm focus:outline-none focus:border-pink-400 text-center">
                        <button type="button" onclick="adjustStock(1)"
                            class="w-9 h-10 rounded-xl bg-pink-500 hover:bg-pink-600 active:bg-pink-700 flex items-center justify-center text-white transition-colors shrink-0">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sizes section (for pakaian) -->
        <div id="sizes-section" class="hidden">
            <div class="flex items-center justify-between mb-3">
                <label class="text-sm font-semibold text-gray-700">Saiz & Harga</label>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="applyAllSamePrice()"
                        class="inline-flex items-center gap-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-600 font-semibold px-3 py-1.5 rounded-lg transition-colors">
                        <i class="fa-solid fa-equals text-xs"></i> Sama Semua Harga
                    </button>
                    <button type="button" id="add-size-btn"
                        class="inline-flex items-center gap-1 text-pink-500 hover:text-pink-700 text-sm font-semibold">
                        <i class="fa-solid fa-plus"></i> Tambah Saiz
                    </button>
                </div>
            </div>

            <!-- Column headers -->
            <div class="grid grid-cols-[1fr_5.5rem_4rem_2rem] gap-2 px-1 mb-1">
                <span class="text-xs text-gray-400 font-semibold">Label Saiz</span>
                <span class="text-xs text-gray-400 font-semibold">Harga (RM)</span>
                <span class="text-xs text-gray-400 font-semibold">Stok</span>
                <span></span>
            </div>

            <div id="sizes-container" class="space-y-2">
                <?php foreach ($sizes as $i => $size): ?>
                <div class="size-row grid grid-cols-[1fr_5.5rem_4rem_2rem] gap-2 items-center bg-gray-50 rounded-xl p-2">
                    <input type="text" name="size_label[]" value="<?= e($size['size_label']) ?>"
                           placeholder="Saiz (cth: S, M, 0-3M)"
                           class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <input type="number" name="size_price[]" value="<?= e($size['price']) ?>"
                           placeholder="0.00" step="0.01" min="0"
                           class="w-full border-2 border-gray-200 rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <input type="number" name="size_stock[]" value="<?= e($size['stock']) ?>"
                           placeholder="0" min="0"
                           class="w-full border-2 border-gray-200 rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <button type="button" class="remove-size w-8 h-9 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                        <i class="fa-solid fa-times text-sm"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Variants section (designs/colors for all products) -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-gray-700">Varian Produk (Warna/Rekaan)</label>
                <button type="button" id="add-variant-btn"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-pink-500 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-3 py-1.5 rounded-xl transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Varian
                </button>
            </div>
            <p class="text-xs text-gray-400 mb-2">Tambah varian jika produk ada pelbagai warna atau rekaan. Kosongkan jika tiada varian.</p>
            <div class="grid grid-cols-[2.5rem_1fr_5.5rem_4rem_2rem] gap-2 px-1 mb-1">
                <span class="text-xs text-gray-400 font-semibold">Gambar</span>
                <span class="text-xs text-gray-400 font-semibold">Label Varian</span>
                <span class="text-xs text-gray-400 font-semibold">Harga (RM)</span>
                <span class="text-xs text-gray-400 font-semibold">Stok</span>
                <span></span>
            </div>
            <div id="variants-container" class="space-y-2">
                <?php foreach ($variants as $variant): ?>
                <div class="variant-row grid grid-cols-[2.5rem_1fr_5.5rem_4rem_2rem] gap-2 items-center bg-gray-50 rounded-xl p-2">
                    <!-- Variant image thumbnail / upload trigger -->
                    <label class="variant-img-label relative w-9 h-9 rounded-lg overflow-hidden border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:border-pink-400 transition-colors shrink-0 bg-white">
                        <?php if (!empty($variant['image'])): ?>
                            <img class="variant-img-thumb w-full h-full object-cover" src="<?= e($variant['image']) ?>" alt="">
                            <i class="fa-solid fa-image text-gray-300 text-sm variant-img-placeholder hidden"></i>
                        <?php else: ?>
                            <img class="variant-img-thumb w-full h-full object-cover hidden" alt="">
                            <i class="fa-solid fa-image text-gray-300 text-sm variant-img-placeholder"></i>
                        <?php endif; ?>
                        <input type="file" name="variant_image[]" accept="image/jpeg,image/png,image/webp" class="variant-img-input sr-only">
                    </label>
                    <input type="text" name="variant_label[]" value="<?= e($variant['variant_label']) ?>"
                           placeholder="Contoh: Merah, Biru, Corak Bunga"
                           class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <input type="number" name="variant_price[]" value="<?= e($variant['price']) ?>"
                           placeholder="0.00" step="0.01" min="0"
                           class="w-full border-2 border-gray-200 rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <input type="number" name="variant_stock[]" value="<?= e($variant['stock']) ?>"
                           placeholder="0" min="0"
                           class="w-full border-2 border-gray-200 rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-pink-400">
                    <button type="button" class="remove-variant w-8 h-9 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                        <i class="fa-solid fa-times text-sm"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit"
                class="flex-1 sm:flex-none bg-pink-500 hover:bg-pink-600 text-white font-bold px-6 py-3 rounded-xl transition-colors text-sm text-center">
                <i class="fa-solid fa-floppy-disk mr-2"></i><?= $isEdit ? 'Kemaskini Produk' : 'Simpan Produk' ?>
            </button>
            <a href="/admin/products"
               class="flex-1 sm:flex-none bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-xl text-sm text-center transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
(function () {
    const catSelect      = document.getElementById('category-select');
    const priceStockSec  = document.getElementById('price-stock-section');
    const sizesSec       = document.getElementById('sizes-section');
    const sizesContainer = document.getElementById('sizes-container');
    const addSizeBtn     = document.getElementById('add-size-btn');
    const imgContainer   = document.getElementById('image-inputs-container');
    const addImageBtn    = document.getElementById('add-image-btn');

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

    // Bind preview + filename to an image row
    function bindImageRow(row) {
        const input       = row.querySelector('.image-file-input');
        const thumb       = row.querySelector('.img-thumb');
        const placeholder = row.querySelector('.thumb-placeholder');
        const filename    = row.querySelector('.img-filename');

        input.addEventListener('change', function () {
            const file = input.files[0];
            if (file) {
                thumb.src = URL.createObjectURL(file);
                thumb.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
                if (filename) filename.textContent = file.name;
                row.classList.remove('border-gray-300');
                row.classList.add('border-pink-400', 'bg-pink-50');
            }
        });
    }

    // Bind the first row
    bindImageRow(imgContainer.querySelector('.image-input-row'));

    // Add another image input row
    addImageBtn.addEventListener('click', function () {
        const count = imgContainer.querySelectorAll('.image-input-row').length + 1;
        const row   = document.createElement('label');
        row.className = 'image-input-row flex items-center gap-3 p-3 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-pink-400 transition-colors bg-gray-50';
        row.innerHTML = `
            <div class="w-14 h-14 rounded-xl overflow-hidden bg-white border border-gray-200 shrink-0 flex items-center justify-center">
                <img class="img-thumb w-full h-full object-cover hidden" alt="">
                <i class="fa-solid fa-image text-gray-300 text-2xl thumb-placeholder"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-600">Gambar ${count}</p>
                <p class="img-filename text-xs text-gray-400 truncate">Klik untuk pilih gambar</p>
            </div>
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" class="image-file-input sr-only">
            <button type="button" class="remove-image-row w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-600 rounded-xl hover:bg-red-100 transition-colors shrink-0"
                    onclick="event.preventDefault();this.closest('.image-input-row').remove()">
                <i class="fa-solid fa-trash text-sm"></i>
            </button>`;
        imgContainer.appendChild(row);
        bindImageRow(row);
    });

    function makeSizeRow(label, price, stock) {
        const row = document.createElement('div');
        row.className = 'size-row bg-gray-50 md:bg-transparent rounded-xl md:rounded-none p-3 md:p-0 space-y-2 md:space-y-0 md:grid md:grid-cols-[1fr_7rem_6rem_2rem] md:gap-2 md:items-center';
        row.className = 'size-row grid grid-cols-[1fr_5.5rem_4rem_2rem] gap-2 items-center bg-gray-50 rounded-xl p-2';
        row.innerHTML = `
            <input type="text" name="size_label[]" value="${label||''}" placeholder="Saiz (cth: S, M, 0-3M)"
                   class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
            <input type="number" name="size_price[]" value="${price||''}" placeholder="0.00" step="0.01" min="0"
                   class="w-full border-2 border-gray-200 rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-pink-400">
            <input type="number" name="size_stock[]" value="${stock||''}" placeholder="0" min="0"
                   class="w-full border-2 border-gray-200 rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-pink-400">
            <button type="button" class="remove-size w-8 h-9 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                <i class="fa-solid fa-times text-sm"></i>
            </button>`;
        sizesContainer.appendChild(row);
        row.querySelector('.remove-size').addEventListener('click', () => row.remove());
    }

    addSizeBtn.addEventListener('click', () => makeSizeRow());

    document.querySelectorAll('.remove-size').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('.size-row').remove());
    });

    // --- Variants ---
    const variantsContainer = document.getElementById('variants-container');
    const addVariantBtn     = document.getElementById('add-variant-btn');

    function bindVariantImageRow(row) {
        const input       = row.querySelector('.variant-img-input');
        const thumb       = row.querySelector('.variant-img-thumb');
        const placeholder = row.querySelector('.variant-img-placeholder');
        const label       = row.querySelector('.variant-img-label');
        input.addEventListener('change', function () {
            const file = input.files[0];
            if (file) {
                thumb.src = URL.createObjectURL(file);
                thumb.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
                label.classList.remove('border-gray-300');
                label.classList.add('border-pink-400');
            }
        });
    }

    function makeVariantRow(label, price, stock) {
        const row = document.createElement('div');
        row.className = 'variant-row grid grid-cols-[2.5rem_1fr_5.5rem_4rem_2rem] gap-2 items-center bg-gray-50 rounded-xl p-2';
        row.innerHTML = `
            <label class="variant-img-label relative w-9 h-9 rounded-lg overflow-hidden border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:border-pink-400 transition-colors shrink-0 bg-white">
                <img class="variant-img-thumb w-full h-full object-cover hidden" alt="">
                <i class="fa-solid fa-image text-gray-300 text-sm variant-img-placeholder"></i>
                <input type="file" name="variant_image[]" accept="image/jpeg,image/png,image/webp" class="variant-img-input sr-only">
            </label>
            <input type="text" name="variant_label[]" value="${label||''}" placeholder="Contoh: Merah, Biru, Corak Bunga"
                   class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
            <input type="number" name="variant_price[]" value="${price||''}" placeholder="0.00" step="0.01" min="0"
                   class="w-full border-2 border-gray-200 rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-pink-400">
            <input type="number" name="variant_stock[]" value="${stock||''}" placeholder="0" min="0"
                   class="w-full border-2 border-gray-200 rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-pink-400">
            <button type="button" class="remove-variant w-8 h-9 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                <i class="fa-solid fa-times text-sm"></i>
            </button>`;
        variantsContainer.appendChild(row);
        bindVariantImageRow(row);
        row.querySelector('.remove-variant').addEventListener('click', () => row.remove());
    }

    addVariantBtn.addEventListener('click', () => makeVariantRow());

    // Bind image preview and remove on existing PHP-rendered rows
    document.querySelectorAll('.variant-row').forEach(row => {
        bindVariantImageRow(row);
        row.querySelector('.remove-variant').addEventListener('click', () => row.remove());
    });
})();

function toggleDeleteImage(wrapper) {
    const cb      = wrapper.querySelector('.delete-img-cb');
    const overlay = wrapper.querySelector('.delete-overlay');
    cb.checked = !cb.checked;
    if (cb.checked) {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        wrapper.classList.add('border-red-400');
        wrapper.classList.remove('border-gray-200');
    } else {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        wrapper.classList.remove('border-red-400');
        wrapper.classList.add('border-gray-200');
    }
}

function adjustStock(delta) {
    const input = document.getElementById('stock-input');
    const val = parseInt(input.value) || 0;
    input.value = Math.max(0, val + delta);
}

function applyAllSamePrice() {
    const priceInputs = document.querySelectorAll('input[name="size_price[]"]');
    if (priceInputs.length === 0) return;
    const firstPrice = priceInputs[0].value;
    if (!firstPrice || parseFloat(firstPrice) <= 0) {
        showAlertModal('Sila masukkan harga untuk saiz pertama dahulu.', 'Harga Diperlukan');
        return;
    }
    priceInputs.forEach(inp => { inp.value = firstPrice; });
}
</script>
