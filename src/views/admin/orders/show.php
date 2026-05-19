<?php
$pageTitle = 'Pesanan #' . e($order['order_number']);
$statusMap   = ['pending'=>'Tertunggak','confirmed'=>'Disahkan','completed'=>'Selesai','cancelled'=>'Dibatal'];
$statusColor = [
    'pending'   => 'bg-yellow-100 text-yellow-700',
    'confirmed' => 'bg-blue-100 text-blue-700',
    'completed' => 'bg-green-100 text-green-700',
    'cancelled' => 'bg-red-100 text-red-700',
];
?>
<div class="py-2 max-w-2xl">
    <a href="/admin/orders" class="inline-flex items-center gap-2 text-pink-500 text-sm mb-4">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <!-- Order header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800"><?= e($order['order_number']) ?></h2>
                <p class="text-sm text-gray-400"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
            </div>
            <span class="px-3 py-1 rounded-xl text-sm font-semibold <?= $statusColor[$order['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                <?= $statusMap[$order['status']] ?? $order['status'] ?>
            </span>
        </div>
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
        </div>
    </div>

    <!-- Order items -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-5 py-3 border-b">
            <h3 class="font-semibold text-gray-700 text-sm">Item Pesanan</h3>
        </div>
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
                    <td class="px-5 py-3 font-medium text-gray-800"><?= e($item['product_name']) ?></td>
                    <td class="px-5 py-3 text-gray-500"><?= $item['size_label'] ? e($item['size_label']) : '-' ?></td>
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

    <!-- Update status -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 text-sm mb-3">Kemaskini Status</h3>
        <form method="POST" action="/admin/orders/<?= $order['id'] ?>/status" class="flex gap-3 items-center">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <select name="status"
                class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400">
                <?php foreach ($statusMap as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $order['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"
                class="bg-pink-500 hover:bg-pink-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-colors">
                Simpan
            </button>
        </form>
    </div>
</div>
