<?php $pageTitle = 'Edit Template: ' . e($tpl['name']); ?>

<div class="py-2">

    <!-- Back + header -->
    <div class="flex items-center gap-3 mb-4">
        <a href="/admin/whatsapp" class="text-gray-400 hover:text-pink-500 transition-colors">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
        <h2 class="text-lg font-bold text-gray-700">
            <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> Edit Template
        </h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Edit form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <form method="POST" action="/admin/whatsapp/edit/<?= $tpl['id'] ?>" id="edit-form">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <!-- Template name -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Nama Template
                        </label>
                        <input type="text" name="name" value="<?= e($tpl['name']) ?>"
                            required
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-pink-400 focus:outline-none transition-colors">
                    </div>

                    <!-- Slug (read-only) -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Slug <span class="text-xs font-normal text-gray-400">(tidak boleh diubah)</span>
                        </label>
                        <input type="text" value="<?= e($tpl['slug']) ?>" disabled
                            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm text-gray-400 font-mono cursor-not-allowed">
                    </div>

                    <!-- Body -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-semibold text-gray-700">
                                Kandungan Mesej
                            </label>
                            <span id="char-count" class="text-xs text-gray-400">0 aksara</span>
                        </div>
                        <textarea name="body" id="body-textarea" rows="12" required
                            oninput="updateCharCount(this); updatePreview(this.value)"
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-pink-400 focus:outline-none resize-y font-mono leading-relaxed transition-colors"><?= e($tpl['body']) ?></textarea>
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            Gunakan <code class="bg-gray-100 px-1 rounded">*teks*</code> untuk bold dalam WhatsApp.
                            Tekan Enter untuk baris baru.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="submit"
                            onclick="showSubmitOverlay()"
                            class="flex-1 bg-pink-500 hover:bg-pink-600 text-white font-semibold py-3 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Template
                        </button>
                        <a href="/admin/whatsapp"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </a>
                    </div>
                </form>

                <!-- Reset to default -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <form method="POST" action="/admin/whatsapp/reset/<?= $tpl['id'] ?>" id="reset-form">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="button"
                            data-confirm-form="reset-form"
                            data-confirm-title="Tetapkan Semula Template"
                            data-confirm-message="Teks template akan dikembalikan kepada teks asal sistem. Perubahan anda akan hilang. Teruskan?"
                            class="text-xs text-gray-400 hover:text-red-500 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-rotate-left"></i> Tetapkan semula kepada teks asal
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right panel: placeholders + preview -->
        <div class="space-y-4">

            <!-- Placeholders -->
            <?php if (!empty($placeholders)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-700 mb-3 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-tags text-blue-400"></i> Placeholder Tersedia
                </h3>
                <p class="text-xs text-gray-400 mb-3">Klik placeholder untuk salin, kemudian tampal ke dalam mesej.</p>
                <div class="space-y-2">
                    <?php foreach ($placeholders as $ph => $desc): ?>
                    <div class="flex items-start gap-2">
                        <button type="button"
                            onclick="insertPlaceholder('<?= e($ph) ?>')"
                            class="shrink-0 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-mono px-2 py-1 rounded-lg transition-colors border border-blue-100">
                            <?= e($ph) ?>
                        </button>
                        <span class="text-xs text-gray-500 pt-1"><?= e($desc) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Live preview -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-700 mb-3 text-sm flex items-center gap-2">
                    <i class="fa-brands fa-whatsapp text-green-500"></i> Pratonton Mesej
                </h3>
                <!-- WhatsApp bubble mockup -->
                <div class="bg-[#e5ddd5] rounded-xl p-3 min-h-[120px]">
                    <div class="bg-white rounded-xl rounded-tl-none px-3 py-2 shadow-sm max-w-[90%]">
                        <p id="preview-text" class="text-xs text-gray-800 whitespace-pre-wrap leading-relaxed break-words"></p>
                        <p class="text-[10px] text-gray-400 text-right mt-1">12:00 PM ✓✓</p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Pratonton tidak menunjukkan format bold sebenar WhatsApp.
                </p>
            </div>

            <!-- Tips -->
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <h3 class="font-bold text-amber-700 mb-2 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-lightbulb text-amber-500"></i> Tips
                </h3>
                <ul class="text-xs text-amber-700 space-y-1.5 list-disc list-inside">
                    <li>Gunakan <code class="bg-amber-100 px-1 rounded">*teks*</code> untuk <strong>bold</strong></li>
                    <li>Gunakan emoji untuk mesej lebih mesra</li>
                    <li>Placeholder <code class="bg-amber-100 px-1 rounded">{{item_lines}}</code> akan diganti dengan senarai item pesanan secara automatik</li>
                    <li>Simpan dahulu sebelum uji hantar</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Submit overlay -->
<div id="submit-overlay" class="fixed inset-0 z-[400] hidden items-center justify-center" style="background:rgba(0,0,0,0.45)">
    <div class="bg-white rounded-2xl shadow-2xl px-8 py-6 flex flex-col items-center gap-3">
        <i class="fa-solid fa-spinner fa-spin text-pink-500 text-2xl"></i>
        <p class="text-sm font-semibold text-gray-700">Menyimpan template...</p>
    </div>
</div>

<script>
// Character count
function updateCharCount(el) {
    document.getElementById('char-count').textContent = el.value.length + ' aksara';
}

// Live preview
function updatePreview(text) {
    document.getElementById('preview-text').textContent = text;
}

// Insert placeholder at cursor position
function insertPlaceholder(ph) {
    var ta = document.getElementById('body-textarea');
    var start = ta.selectionStart;
    var end   = ta.selectionEnd;
    var val   = ta.value;
    ta.value  = val.substring(0, start) + ph + val.substring(end);
    ta.selectionStart = ta.selectionEnd = start + ph.length;
    ta.focus();
    updateCharCount(ta);
    updatePreview(ta.value);
}

// Submit overlay
function showSubmitOverlay() {
    var overlay = document.getElementById('submit-overlay');
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
}

// Init
(function () {
    var ta = document.getElementById('body-textarea');
    updateCharCount(ta);
    updatePreview(ta.value);
})();
</script>
