<?php
$pageTitle    = 'Checkout';
$malaysiaStates = malaysia_states();
$postageWest  = (float)($postageFeeWest ?? 7.00);
$postageEast  = (float)($postageFeeEast ?? 12.00);
$selectedState = $old['state'] ?? '';
?>
<div class="py-2">
    <a href="/cart" class="inline-flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Troli
    </a>

    <!-- Page header card -->
    <div class="rounded-2xl shadow-sm border border-blue-200 p-4 mb-4 flex items-center justify-between bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
        <div>
            <h2 class="text-lg font-bold">Maklumat Penghantaran</h2>
            <p class="text-xs text-white/80 mt-0.5">Isi maklumat untuk penghantaran pesanan</p>
        </div>
        <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center shrink-0 ring-2 ring-white/30">
            <i class="fa-solid fa-truck text-white text-lg"></i>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <?php foreach ($errors as $err): ?>
            <p class="text-red-600 text-sm"><i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/checkout" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- Shipping info container -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-pink-400"></i> Maklumat Penerima
                </h3>
            </div>
            <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-3 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Penuh <span class="text-pink-500">*</span></label>
                    <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" required
                        placeholder="Contoh: SITI AMINAH"
                        oninput="this.value=this.value.toUpperCase()"
                        class="w-full border-2 border-rose-100 bg-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nombor Telefon <span class="text-pink-500">*</span></label>
                    <input id="checkout-phone" type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" required
                        placeholder="Contoh: 60123456789"
                        class="w-full border-2 border-rose-100 bg-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                    <p class="text-xs text-gray-400 mt-1">Nombor akan diubah kepada format WhatsApp (contoh: 60123456789).</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Penghantaran (Penerima) <span class="text-pink-500">*</span></label>
                    <textarea name="address" required rows="3"
                        placeholder="NO. RUMAH, JALAN, BANDAR, POSKOD"
                        oninput="this.value=this.value.toUpperCase()"
                        class="w-full border-2 border-rose-100 bg-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 resize-none uppercase"><?= e($old['address'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Negeri (Penerima)<span class="text-pink-500">*</span></label>
                    <select id="checkout-state" name="state" required
                        data-postage-west="<?= number_format($postageWest, 2, '.', '') ?>"
                        data-postage-east="<?= number_format($postageEast, 2, '.', '') ?>"
                        class="w-full border-2 border-rose-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 bg-white">
                        <option value="">-- Sila Pilih Negeri --</option>
                        <?php foreach ($malaysiaStates as $group => $states): ?>
                        <optgroup label="<?= e($group) ?>">
                            <?php foreach ($states as $state): ?>
                            <option value="<?= e($state) ?>"
                                data-zone="<?= is_east_malaysia($state) ? 'east' : 'west' ?>"
                                <?= $selectedState === $state ? 'selected' : '' ?>>
                                <?= e($state) ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <p id="state-postage-hint" class="text-xs text-gray-400 mt-1">
                        <i class="fa-solid fa-circle-info mr-1"></i>Caj postaj dikira mengikut negeri. Sabah/Sarawak/Labuan: RM<?= number_format($postageEast, 2) ?>. Lain-lain: RM<?= number_format($postageWest, 2) ?>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Order summary container -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-pink-400"></i> Ringkasan Pesanan
                </h3>
                <span class="text-xs bg-pink-100 text-pink-700 font-semibold px-2 py-0.5 rounded-full">
                    <?= count($cart) ?> item
                </span>
            </div>

            <!-- Items list -->
            <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-3 space-y-2 mb-3">
                <?php foreach ($cart as $item): ?>
                <div class="flex justify-between text-sm bg-white rounded-lg px-3 py-2 border border-rose-100">
                    <span class="text-gray-700">
                        <?= e($item['product_name']) ?>
                        <?php if ($item['size_label']): ?><span class="text-gray-400">(<?= e($item['size_label']) ?>)</span><?php endif; ?>
                        <span class="text-gray-400">x<?= $item['quantity'] ?></span>
                    </span>
                    <span class="font-semibold text-gray-700">RM<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Totals -->
            <div class="space-y-2 px-1">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span id="summary-subtotal" data-subtotal="<?= number_format($total, 2, '.', '') ?>" class="font-semibold text-gray-700">RM<?= number_format($total, 2) ?></span>
                </div>
                <!-- Postage row hidden until a state is chosen -->
                <div id="summary-postage-row" class="<?= $selectedState !== '' ? 'flex' : 'hidden' ?> justify-between text-sm">
                    <span class="text-gray-500"><i class="fa-solid fa-truck text-gray-400 mr-1"></i> Postaj <span id="summary-zone" class="text-xs text-gray-400"></span></span>
                    <span id="summary-postage" class="font-semibold text-gray-700">RM<?= number_format($postage, 2) ?></span>
                </div>
                <!-- Hint shown until a state is chosen -->
                <div id="summary-postage-hint" class="<?= $selectedState !== '' ? 'hidden' : 'flex' ?> items-start gap-2 text-xs text-gray-400 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2">
                    <i class="fa-solid fa-circle-info mt-0.5"></i>
                    <span>Caj postaj akan dikira selepas anda memilih negeri.</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="font-bold text-gray-700">Jumlah</span>
                    <span id="summary-total" class="font-bold text-pink-500 text-lg">RM<?= number_format($selectedState !== '' ? $grandTotal : $total, 2) ?></span>
                </div>
            </div>
        </div>

        <button type="button" id="submit-order-btn"
            class="w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-4 rounded-xl transition-colors text-base">
            <i class="fa-solid fa-check mr-2"></i> Hantar Pesanan
        </button>
    </form>
</div>

<!-- Order confirmation modal -->
<div id="order-confirm-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="w-14 h-14 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-bag-shopping text-pink-500 text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-1">Sahkan Pesanan</h3>
        <p class="text-sm text-gray-500 mb-5">Adakah anda sudah bersedia untuk meneruskan pembayaran?</p>
        <div class="flex flex-col gap-2">
            <button type="button" id="confirm-proceed-btn"
                class="w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                <i class="fa-solid fa-credit-card mr-2"></i> Ya, Teruskan Pembayaran
            </button>
            <a href="/"
               class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-xl transition-colors text-sm text-center block">
                <i class="fa-solid fa-shop mr-2"></i> Teruskan Membeli-belah
            </a>
            <button type="button" id="confirm-cancel-btn"
                class="w-full text-gray-400 hover:text-gray-600 text-xs py-2 transition-colors">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var phoneInput = document.getElementById('checkout-phone');
    function formatWhatsAppPhone(value) {
        var digits = value.replace(/\D+/g, '');
        if (digits === '') return '';
        if (digits.startsWith('0')) {
            digits = '6' + digits;
        }
        return digits;
    }

    function normalize() {
        if (!phoneInput) return;
        phoneInput.value = formatWhatsAppPhone(phoneInput.value);
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', normalize);
        phoneInput.addEventListener('input', function () {
            var value = phoneInput.value.replace(/\D+/g, '');
            phoneInput.value = value;
        });
        phoneInput.form && phoneInput.form.addEventListener('submit', normalize);
    }

    // Dynamic postage based on selected state
    var stateSelect      = document.getElementById('checkout-state');
    var subtotalEl       = document.getElementById('summary-subtotal');
    var postageEl        = document.getElementById('summary-postage');
    var totalEl          = document.getElementById('summary-total');
    var zoneEl           = document.getElementById('summary-zone');
    var postageRowEl     = document.getElementById('summary-postage-row');
    var postageHintEl    = document.getElementById('summary-postage-hint');

    function formatRM(value) {
        return 'RM' + Number(value).toFixed(2);
    }

    function updatePostage() {
        if (!stateSelect || !subtotalEl || !postageEl || !totalEl) return;

        var west     = parseFloat(stateSelect.dataset.postageWest || '0');
        var east     = parseFloat(stateSelect.dataset.postageEast || '0');
        var subtotal = parseFloat(subtotalEl.dataset.subtotal || '0');
        var selected = stateSelect.options[stateSelect.selectedIndex];
        var zone     = selected ? selected.getAttribute('data-zone') : '';

        if (zone === 'east' || zone === 'west') {
            var postage   = (zone === 'east') ? east : west;
            var zoneLabel = (zone === 'east') ? '(Sabah/Sarawak)' : '(Semenanjung)';

            postageEl.textContent = formatRM(postage);
            totalEl.textContent   = formatRM(subtotal + postage);
            if (zoneEl) zoneEl.textContent = zoneLabel;

            if (postageRowEl)  { postageRowEl.classList.remove('hidden');  postageRowEl.classList.add('flex'); }
            if (postageHintEl) { postageHintEl.classList.add('hidden');    postageHintEl.classList.remove('flex'); }
        } else {
            // No state selected — hide postage row, show only subtotal as total
            totalEl.textContent = formatRM(subtotal);
            if (zoneEl) zoneEl.textContent = '';

            if (postageRowEl)  { postageRowEl.classList.add('hidden');     postageRowEl.classList.remove('flex'); }
            if (postageHintEl) { postageHintEl.classList.remove('hidden'); postageHintEl.classList.add('flex'); }
        }
    }

    if (stateSelect) {
        stateSelect.addEventListener('change', updatePostage);
        updatePostage();
    }

    // Order confirmation modal
    var form        = document.querySelector('form[action="/checkout"]');
    var submitBtn   = document.getElementById('submit-order-btn');
    var modal       = document.getElementById('order-confirm-modal');
    var proceedBtn  = document.getElementById('confirm-proceed-btn');
    var cancelBtn   = document.getElementById('confirm-cancel-btn');

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    submitBtn.addEventListener('click', function () {
        // Trigger native HTML5 validation first
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        openModal();
    });

    proceedBtn.addEventListener('click', function () {
        closeModal();
        // Disable button to prevent double submit
        proceedBtn.disabled = true;
        proceedBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Menghantar...';
        form.submit();
    });

    cancelBtn.addEventListener('click', closeModal);

    // Close on backdrop click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
})();
</script>
