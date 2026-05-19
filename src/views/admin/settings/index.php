<?php $pageTitle = 'Tetapan'; ?>
<div class="py-2 max-w-lg">
    <h2 class="text-lg font-bold text-gray-700 mb-5">Tetapan Sistem</h2>

    <form method="POST" action="/admin/settings" enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- WhatsApp number -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> Nombor WhatsApp Admin
            </label>
            <input type="text" name="whatsapp_number"
                   value="<?= e($settings['whatsapp_number'] ?? '') ?>"
                   placeholder="Contoh: 60123456789"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            <p class="text-xs text-gray-400 mt-1">Format antarabangsa tanpa '+'. Contoh: 60123456789</p>
        </div>

        <!-- QR code upload -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fa-solid fa-qrcode text-gray-500 mr-1"></i> Gambar QR Code Bayaran
            </label>
            <?php if (!empty($settings['qr_code_image'])): ?>
                <div class="mb-3">
                    <p class="text-xs text-gray-400 mb-1">QR Code semasa:</p>
                    <img src="<?= e($settings['qr_code_image']) ?>" alt="QR Code"
                         class="w-40 h-40 object-contain border rounded-xl bg-gray-50 p-2">
                </div>
            <?php endif; ?>
            <input type="file" name="qr_code_image" accept="image/jpeg,image/png,image/webp"
                class="block text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0
                       file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-600 hover:file:bg-pink-100">
            <p class="text-xs text-gray-400 mt-1">JPEG, PNG atau WebP. Maks 2MB. Kosongkan jika tidak mahu tukar.</p>
        </div>

        <!-- Payment instructions -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fa-solid fa-file-lines text-gray-500 mr-1"></i> Arahan Pembayaran
            </label>
            <textarea name="payment_instructions" rows="4"
                placeholder="Masukkan arahan pembayaran untuk pelanggan..."
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 resize-none"><?= e($settings['payment_instructions'] ?? '') ?></textarea>
        </div>

        <button type="submit"
            class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded-xl transition-colors text-sm">
            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Tetapan
        </button>
    </form>
</div>
