<?php $pageTitle = 'Profil Pelanggan'; ?>
<div class="py-2 max-w-2xl">
    <a href="/admin/customers" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <!-- Customer info -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-pink-100 rounded-full flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user text-pink-500 text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-800"><?= e($customer['name']) ?></h2>
                <p class="text-sm text-gray-500"><?= e($customer['phone']) ?></p>
                <p class="text-xs text-gray-400 mt-0.5">Daftar: <?= date('d M Y', strtotime($customer['created_at'])) ?></p>
            </div>
            <div class="ml-auto flex gap-3 text-center">
                <div>
                    <p class="text-2xl font-bold text-pink-500"><?= count($orders) ?></p>
                    <p class="text-xs text-gray-400">Pesanan</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-blue-500"><?= count($addresses) ?></p>
                    <p class="text-xs text-gray-400">Alamat</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Addresses -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-5 py-3 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-700 text-sm">
                <i class="fa-solid fa-location-dot mr-2 text-blue-400"></i>Senarai Alamat
            </h3>
            <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full"><?= count($addresses) ?></span>
        </div>
        <?php if (empty($addresses)): ?>
            <div class="px-5 py-4 text-sm text-gray-400">Tiada alamat disimpan.</div>
        <?php else: ?>
        <div class="divide-y divide-gray-100">
            <?php foreach ($addresses as $i => $addr): ?>
            <div class="px-5 py-3 flex items-start gap-3">
                <span class="text-xs bg-gray-100 text-gray-500 font-bold px-2 py-0.5 rounded-full shrink-0 mt-0.5"><?= $i + 1 ?></span>
                <div class="flex-1">
                    <?php if (!empty($addr['name'])): ?>
                    <p class="text-xs font-semibold text-pink-600 mb-0.5">
                        <i class="fa-solid fa-user text-xs mr-1"></i><?= e($addr['name']) ?>
                    </p>
                    <?php endif; ?>
                    <p class="text-sm text-gray-700"><?= e($addr['address']) ?></p>
                    <?php if (!empty($addr['state'])): ?>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <i class="fa-solid fa-location-dot text-xs mr-0.5"></i><?= e($addr['state']) ?>
                        <?php if (is_east_malaysia($addr['state'])): ?>
                        <span class="ml-1 bg-orange-100 text-orange-700 font-semibold px-1.5 py-0.5 rounded">Sabah/Sarawak</span>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-0.5"><?= date('d/m/Y', strtotime($addr['created_at'])) ?></p>
                </div>
                <button type="button"
                    onclick="copyText(<?= htmlspecialchars(json_encode((!empty($addr['name']) ? $addr['name'] . "\n" : '') . $addr['address'] . (!empty($addr['state']) ? "\n" . $addr['state'] : '')), ENT_QUOTES) ?>, this)"
                    class="shrink-0 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-pink-500 hover:bg-pink-50 rounded-xl transition-colors"
                    title="Salin alamat">
                    <i class="fa-solid fa-copy text-sm"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Orders -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-700 text-sm">
                <i class="fa-solid fa-bag-shopping mr-2 text-pink-400"></i>Senarai Pesanan
            </h3>
            <span class="text-xs bg-pink-100 text-pink-700 font-semibold px-2 py-0.5 rounded-full"><?= count($orders) ?></span>
        </div>
        <?php if (empty($orders)): ?>
            <div class="px-5 py-4 text-sm text-gray-400">Tiada pesanan.</div>
        <?php else: ?>
        <div class="divide-y divide-gray-100">
            <?php foreach ($orders as $order):
                $statusColor = ['pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
                $statusLabel = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
                $cls = $statusColor[$order['status']] ?? 'bg-gray-100 text-gray-600';
                $lbl = $statusLabel[$order['status']] ?? $order['status'];
            ?>
            <a href="/admin/orders/<?= $order['id'] ?>"
               class="block px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-pink-500 text-sm font-mono"><?= e($order['order_number']) ?></span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $cls ?>"><?= $lbl ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
                    <p class="font-bold text-gray-800 text-sm">RM<?= number_format($order['total'], 2) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(function () {
        var icon = btn.querySelector('i');
        var orig = icon.className;
        icon.className = 'fa-solid fa-check text-sm';
        btn.classList.add('text-green-500');
        setTimeout(function () {
            icon.className = orig;
            btn.classList.remove('text-green-500');
        }, 1500);
    });
}
</script>
