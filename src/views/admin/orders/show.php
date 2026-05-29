<?php
$pageTitle = 'Pesanan #' . e($order['order_number']);
$statusMap   = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
$statusColor = [
    'pending'   => 'bg-yellow-100 text-yellow-700',
    'confirmed' => 'bg-blue-100 text-blue-700',
    'completed' => 'bg-green-100 text-green-700',
    'cancelled' => 'bg-red-100 text-red-700',
];
?>
<div class="py-2 max-w-2xl">
    <div class="flex items-center justify-between mb-4">
        <a href="/admin/orders" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        <form id="del-order-<?= $order['id'] ?>" method="POST" action="/admin/orders/<?= $order['id'] ?>/delete">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        </form>
        <button type="button"
            data-confirm-form="del-order-<?= $order['id'] ?>"
            data-confirm-title="Padam Pesanan"
            data-confirm-message="Padam pesanan <?= e($order['order_number']) ?>? Semua item pesanan akan dipadam dan stok akan dipulihkan. Tindakan ini tidak boleh dibatalkan."
            class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-500 font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
            <i class="fa-solid fa-trash"></i> Padam Pesanan
        </button>
    </div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800"><?= e($order['order_number']) ?></h2>
        <div class="flex gap-2">
            <a href="https://www.jtexpress.my/trajectoryQuery" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-white text-sm font-semibold px-3 py-2 rounded-xl transition-colors">
                <i class="fa-solid fa-truck"></i> J&T Track
            </a>
            <a href="/admin/orders/<?= $order['id'] ?>/courier"
               class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-3 py-2 rounded-xl transition-colors">
                <i class="fa-solid fa-print"></i> Label Kurier
            </a>
        </div>
    </div>

    <!-- Order header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <div class="flex items-start justify-between mb-3">
            <p class="text-sm text-gray-400"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
            <span class="px-3 py-1 rounded-xl text-sm font-semibold <?= $statusColor[$order['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                <?= $statusMap[$order['status']] ?? $order['status'] ?>
            </span>
        </div>
        <!-- Inline status change â€” right below the status badge -->
        <form method="POST" action="/admin/orders/<?= $order['id'] ?>/status" class="flex gap-2 items-center mb-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <select name="status" class="flex-1 border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-pink-400">
                <?php foreach ($statusMap as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $order['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition-colors whitespace-nowrap">
                <i class="fa-solid fa-check mr-1"></i> Tukar
            </button>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Nama Pelanggan</p>
                <p class="font-semibold text-gray-700"><?= e($order['customer_name']) ?></p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Nombor Telefon</p>
                <p class="font-semibold text-gray-700"><?= e($order['customer_phone']) ?></p>
            </div>
            <div class="md:col-span-2">
                <p class="text-gray-400 text-xs mb-0.5">Alamat Penghantaran</p>
                <p class="font-semibold text-gray-700"><?= nl2br(e($order['address'])) ?></p>
            </div>
            <?php if (!empty($order['state'])): ?>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Negeri</p>
                <p class="font-semibold text-gray-700">
                    <?= e($order['state']) ?>
                    <?php if (is_east_malaysia($order['state'])): ?>
                    <span class="ml-1 text-xs bg-orange-100 text-orange-700 font-semibold px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-mountain-sun"></i> Sabah/Sarawak
                    </span>
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
            <?php if (!empty($order['postage']) && (float)$order['postage'] > 0): ?>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Caj Postaj</p>
                <p class="font-semibold text-gray-700">RM<?= number_format((float)$order['postage'], 2) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($order['tracking_number']): ?>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Nota</p>
                <p class="font-semibold text-blue-600"><?= e($order['tracking_number']) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($order['courier_slip']): ?>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Slip Kurier</p>
                <a href="<?= e($order['courier_slip']) ?>" target="_blank"
                   class="inline-flex items-center gap-1 text-pink-500 hover:underline text-sm font-semibold">
                    <i class="fa-solid fa-file-image"></i> Lihat Slip
                </a>
            </div>
            <?php endif; ?>
        </div>
        <!-- Copy customer info button -->
        <div class="border-t mt-4 pt-3">
            <button onclick="copyCustomerInfo()"
                class="w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-copy"></i> Salin Maklumat Pelanggan
            </button>
        </div>
    </div>

    <!-- Order items -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-5 py-3 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-700 text-sm">Item Pesanan</h3>
            <a href="/admin/ai?order_id=<?= $order['id'] ?>"
               class="inline-flex items-center gap-1.5 bg-purple-100 hover:bg-purple-200 text-purple-600 text-xs font-semibold px-3 py-1.5 rounded-full transition-colors">
                <i class="fa-solid fa-robot text-xs"></i> Buka AI
            </a>
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Produk</th>
                    <th class="px-5 py-3 text-left">Saiz</th>
                    <th class="px-5 py-3 text-center">Qty</th>
                    <th class="px-5 py-3 text-right">Harga</th>
                    <th class="px-5 py-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($item['product_image'])): ?>
                            <img src="<?= e($item['product_image']) ?>" alt=""
                                 class="w-10 h-10 rounded-lg object-cover border border-gray-100 shrink-0 cursor-pointer hover:opacity-75 transition-opacity"
                                 onclick="openImgModal('<?= e($item['product_image']) ?>')"
                                 title="Klik untuk besarkan">
                            <?php else: ?>
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-box text-gray-300 text-sm"></i>
                            </div>
                            <?php endif; ?>
                            <span class="font-medium text-gray-800"><?= e($item['product_name']) ?></span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-500">
                        <?php if ($item['size_label']): ?>
                        <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                            Saiz: <?= e($item['size_label']) ?>
                        </span>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-center"><?= $item['quantity'] ?></td>
                    <td class="px-5 py-3 text-right">RM<?= number_format($item['unit_price'], 2) ?></td>
                    <td class="px-5 py-3 text-right font-semibold">RM<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="border-t-2 border-gray-200">
                <tr>
                    <td colspan="4" class="px-5 py-3 text-right font-bold text-gray-700">Jumlah</td>
                    <td class="px-5 py-3 text-right font-bold text-pink-500 text-base">RM<?= number_format($order['total'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden divide-y divide-gray-100">
            <?php foreach ($items as $item): ?>
            <div class="p-4 flex items-center gap-3">
                <?php if (!empty($item['product_image'])): ?>
                <img src="<?= e($item['product_image']) ?>" alt=""
                     class="w-14 h-14 rounded-xl object-cover border border-gray-100 shrink-0 cursor-pointer hover:opacity-75 transition-opacity"
                     onclick="openImgModal('<?= e($item['product_image']) ?>')"
                     title="Klik untuk besarkan">
                <?php else: ?>
                <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-box text-gray-300 text-xl"></i>
                </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate"><?= e($item['product_name']) ?></p>
                    <?php if ($item['size_label']): ?>
                    <p class="text-xs text-gray-500">
                        <span class="font-semibold text-gray-400">Saiz:</span> <?= e($item['size_label']) ?>
                    </p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-500 mt-0.5">x<?= $item['quantity'] ?> &times; RM<?= number_format($item['unit_price'], 2) ?></p>
                </div>
                <p class="font-bold text-pink-500 text-sm shrink-0">RM<?= number_format($item['subtotal'], 2) ?></p>
            </div>
            <?php endforeach; ?>
            <div class="px-4 py-3 flex justify-between font-bold text-sm border-t-2 border-gray-200">
                <span class="text-gray-700">Jumlah</span>
                <span class="text-pink-500">RM<?= number_format($order['total'], 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Courier slip upload -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-3">
            <i class="fa-solid fa-truck-fast mr-2 text-orange-400"></i>Maklumat Penghantaran Kurier
        </h3>
        <form method="POST" action="/admin/orders/<?= $order['id'] ?>/slip" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nota</label>
                <input type="text" name="tracking_number"
                       value="<?= e($order['tracking_number'] ?? '') ?>"
                       placeholder="Contoh: JT1234567890MY atau nota penghantaran..."
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400">
            </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Slip Kurier (Gambar/PDF, maks 15MB)</label>
            <?php if ($order['courier_slip']): ?>
            <?php $slipExt = strtolower(pathinfo($order['courier_slip'], PATHINFO_EXTENSION));
                  $isImg   = in_array($slipExt, ['jpg','jpeg','png','webp']);
                  $slipFullPath = PUBLIC_PATH . $order['courier_slip'];
                  $slipSize     = file_exists($slipFullPath) ? filesize($slipFullPath) : 0;
                  $slipSizeStr  = $slipSize > 0
                      ? ($slipSize >= 1048576
                          ? number_format($slipSize / 1048576, 2) . ' MB'
                          : number_format($slipSize / 1024, 1) . ' KB')
                      : '—';
            ?>
            <div class="mb-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                <p class="text-xs font-semibold text-gray-500 mb-2"><i class="fa-solid fa-receipt mr-1 text-orange-400"></i>Slip semasa:</p>
                <?php if ($isImg): ?>
                <img src="<?= e($order['courier_slip']) ?>" alt="Slip Kurier"
                     class="max-h-[300px] w-auto rounded-xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity mb-1 object-contain"
                     onclick="openImgModal('<?= e($order['courier_slip']) ?>')"
                     title="Klik untuk besarkan">
                <p class="text-xs text-gray-400"><i class="fa-solid fa-hand-pointer mr-1"></i>Klik untuk besarkan</p>
                <?php else: ?>
                <a href="<?= e($order['courier_slip']) ?>" target="_blank"
                   class="inline-flex items-center gap-2 bg-red-50 text-red-600 hover:bg-red-100 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                    <i class="fa-solid fa-file-pdf text-lg"></i> Lihat Slip PDF
                </a>
                <?php endif; ?>
                <p class="text-xs text-gray-400 mt-2">
                    <i class="fa-solid fa-weight-hanging mr-1"></i>Saiz fail: <span class="font-semibold text-gray-500"><?= $slipSizeStr ?></span>
                    &nbsp;&middot;&nbsp; Muat naik baru untuk ganti slip semasa.
                </p>
            </div>
            <?php endif; ?>
            <input type="file" name="courier_slip" id="courier_slip_input" accept="image/jpeg,image/png,image/webp,application/pdf"
                class="block text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0
                       file:text-sm file:font-semibold file:bg-orange-500 file:text-white hover:file:bg-orange-600 file:cursor-pointer file:transition-colors">
            <p id="slip-size-preview" class="text-xs text-gray-400 mt-1 hidden">
                <i class="fa-solid fa-weight-hanging mr-1"></i>Saiz fail dipilih: <span id="slip-size-value" class="font-semibold text-gray-600"></span>
            </p>
        </div>
            <button type="submit"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-xl text-sm transition-colors">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan & Sahkan Pesanan
            </button>
        </form>
    </div>

</div>

<!-- Image popup modal -->
<div id="img-modal"
     class="fixed inset-0 bg-black/80 z-[200] hidden items-center justify-center p-4"
     onclick="closeImgModal()">
    <div class="relative max-w-xl w-full" onclick="event.stopPropagation()">
        <button onclick="closeImgModal()"
            class="absolute -top-12 right-0 w-10 h-10 bg-white/20 hover:bg-white/30 text-white rounded-full flex items-center justify-center transition-colors">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
        <img id="img-modal-img" src="" alt=""
             class="w-full rounded-2xl shadow-2xl max-h-[80vh] object-contain">
    </div>
</div>

<script>
function openImgModal(src) {
    document.getElementById('img-modal-img').src = src;
    var m = document.getElementById('img-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeImgModal() {
    var m = document.getElementById('img-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
function copyCustomerInfo() {
    var text = <?= json_encode(
        $order['customer_name'] . "\n" .
        $order['customer_phone'] . "\n" .
        $order['address'] .
        (!empty($order['state']) ? "\n" . $order['state'] : '')
    ) ?>;
    navigator.clipboard.writeText(text).then(function () {
        var btn = document.querySelector('[onclick="copyCustomerInfo()"]');
        if (!btn) return;
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i> Disalin!';
        btn.classList.replace('bg-pink-500', 'bg-green-500');
        setTimeout(function () {
            btn.innerHTML = orig;
            btn.classList.replace('bg-green-500', 'bg-pink-500');
        }, 2000);
    });
}

// Auto-fill tracking number when slip uploaded but field is empty
(function () {
    var form     = document.querySelector('form[action*="/slip"]');
    if (!form) return;
    var tracking = form.querySelector('input[name="tracking_number"]');
    var slip     = form.querySelector('input[name="courier_slip"]');
    var preview  = document.getElementById('slip-size-preview');
    var sizeVal  = document.getElementById('slip-size-value');
    if (!tracking || !slip) return;

    // Show file size when a file is selected
    slip.addEventListener('change', function () {
        if (slip.files && slip.files.length > 0) {
            var bytes = slip.files[0].size;
            var str   = bytes >= 1048576
                ? (bytes / 1048576).toFixed(2) + ' MB'
                : (bytes / 1024).toFixed(1) + ' KB';
            sizeVal.textContent = str;
            preview.classList.remove('hidden');

            // Warn if over 15MB
            if (bytes > 15 * 1024 * 1024) {
                sizeVal.classList.add('text-red-500');
                sizeVal.textContent += ' — MELEBIHI 15MB!';
            } else {
                sizeVal.classList.remove('text-red-500');
            }
        } else {
            preview.classList.add('hidden');
        }
    });

    form.addEventListener('submit', function () {
        if (tracking.value.trim() === '' && slip.files && slip.files.length > 0) {
            tracking.value = 'Slip penghantaran telah diupload';
        }
    });
})();
</script>
