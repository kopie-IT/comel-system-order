<?php $pageTitle = 'Label Kurier - ' . e($order['order_number']); ?>
<div class="py-2 max-w-2xl">
    <a href="/admin/orders/<?= $order['id'] ?>" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Pesanan
    </a>
    <h2 class="text-xl font-bold text-gray-800 mb-1">Label Kurier J&T</h2>
    <p class="text-gray-500 text-sm mb-5">Klik butang salin untuk menyalin maklumat ke J&T.</p>

    <!-- Recipient block -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide">
                <i class="fa-solid fa-user mr-2 text-pink-400"></i>Maklumat Penerima
            </h3>
            <button onclick="copyBlock('recipient-block')"
                class="inline-flex items-center gap-1.5 bg-pink-500 hover:bg-pink-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                <i class="fa-solid fa-copy"></i> Salin
            </button>
        </div>
        <div id="recipient-block"
             class="bg-gray-50 rounded-xl p-4 font-mono text-sm text-gray-800 whitespace-pre-wrap leading-relaxed select-all"><?= e(strtoupper($order['customer_name'])) ?>

<?= e($order['customer_phone']) ?>

<?= e(strtoupper($order['address'])) ?></div>
    </div>

    <!-- Full J&T label block -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide">
                <i class="fa-solid fa-box mr-2 text-orange-400"></i>Label Lengkap J&T
            </h3>
            <button onclick="copyBlock('jnt-block')"
                class="inline-flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                <i class="fa-solid fa-copy"></i> Salin
            </button>
        </div>
        <div id="jnt-block"
             class="bg-gray-50 rounded-xl p-4 font-mono text-sm text-gray-800 whitespace-pre-wrap leading-relaxed select-all">NO. PESANAN : <?= e($order['order_number']) ?>

NAMA        : <?= e(strtoupper($order['customer_name'])) ?>

TELEFON     : <?= e($order['customer_phone']) ?>

ALAMAT      : <?= e(strtoupper($order['address'])) ?>

-----------------------------------------
<?php foreach ($items as $item): ?><?= e(strtoupper($item['product_name'])) ?><?= $item['size_label'] ? ' (' . e(strtoupper($item['size_label'])) . ')' : '' ?> x<?= $item['quantity'] ?> = RM<?= number_format($item['subtotal'], 2) ?>
<?php endforeach; ?>-----------------------------------------
JUMLAH      : RM<?= number_format($order['total'], 2) ?>

TARIKH      : <?= date('d/m/Y') ?></div>
    </div>

    <!-- Individual fields -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide mb-4">
            <i class="fa-solid fa-list mr-2 text-blue-400"></i>Salin Satu-Satu
        </h3>
        <div class="space-y-3">
            <?php
            $fields = [
                'Nama'    => strtoupper($order['customer_name']),
                'Telefon' => $order['customer_phone'],
                'Alamat'  => strtoupper($order['address']),
            ];
            foreach ($fields as $label => $value):
            ?>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 w-16 shrink-0"><?= $label ?></span>
                <div class="flex-1 bg-gray-50 rounded-lg px-3 py-2 font-mono text-sm text-gray-800 select-all"><?= e($value) ?></div>
                <button onclick="copyText(this, '<?= addslashes(e($value)) ?>')"
                    class="shrink-0 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-2 rounded-lg transition-colors">
                    <i class="fa-solid fa-copy"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Print button -->
    <button onclick="window.print()"
        class="w-full border-2 border-gray-300 hover:border-gray-400 text-gray-600 font-semibold py-3 rounded-xl transition-colors text-sm">
        <i class="fa-solid fa-print mr-2"></i> Cetak Label
    </button>
</div>

<script>
function copyBlock(id) {
    const el = document.getElementById(id);
    const text = el.innerText;
    navigator.clipboard.writeText(text).then(() => {
        showToast('Disalin!');
    });
}

function copyText(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        btn.classList.add('bg-green-200', 'text-green-700');
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.classList.remove('bg-green-200', 'text-green-700');
        }, 1500);
    });
}

function showToast(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-sm font-semibold px-5 py-2.5 rounded-full shadow-lg z-50 transition-opacity';
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 1500);
}
</script>

<style>
@media print {
    header, nav, aside, footer, button, a { display: none !important; }
    #jnt-block { border: 2px solid #000; padding: 20px; font-size: 14px; }
}
</style>
