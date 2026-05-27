<?php $pageTitle = 'Senarai Pesanan'; ?>
<div class="py-2">
    <div class="flex items-center justify-between mb-4 gap-2">
        <h2 class="text-lg font-bold text-gray-700">Senarai Pesanan</h2>
        <a href="/admin/orders/pending-items"
           class="inline-flex items-center gap-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-semibold px-3 py-1.5 rounded-full transition-colors">
            <i class="fa-solid fa-clipboard-list text-xs"></i> Barang Yang Diperlukan
        </a>
    </div>

    <!-- Status filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
        <?php
        $statuses = ['' => 'Active', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'all' => 'All'];
        foreach ($statuses as $val => $label):
            $active = $status === $val;
        ?>
        <a href="/admin/orders<?= $val ? '?status=' . $val : '' ?>"
           class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors
               <?= $active ? 'bg-pink-500 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-600' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 text-center py-10 text-gray-400 text-sm">
            Tiada pesanan.
        </div>
    <?php else: ?>

    <!-- Desktop table -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">No. Pesanan</th>
                        <th class="px-5 py-3 text-left">Pelanggan</th>
                        <th class="px-5 py-3 text-left">Telefon</th>
                        <th class="px-5 py-3 text-left">Jumlah</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Tarikh</th>
                        <th class="px-5 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($orders as $order):
                        $statusColor = ['pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
                        $statusLabel = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
                        $cls = $statusColor[$order['status']] ?? 'bg-gray-100 text-gray-600';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-pink-500"><?= e($order['order_number']) ?></td>
                        <td class="px-5 py-3 text-gray-700">
                            <a href="/order/track?phone=<?= urlencode($order['customer_phone']) ?>" target="_blank"
                               class="hover:text-pink-500 hover:underline"><?= e($order['customer_name']) ?></a>
                        </td>
                        <td class="px-5 py-3 text-gray-500"><?= e($order['customer_phone']) ?></td>
                        <td class="px-5 py-3 font-semibold">RM<?= number_format($order['total'], 2) ?></td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold <?= $cls ?>">
                                <?= $statusLabel[$order['status']] ?? $order['status'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-400"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="/admin/orders/<?= $order['id'] ?>"
                                   class="text-blue-500 hover:text-blue-700 text-xs font-semibold">
                                    <i class="fa-solid fa-eye"></i> Lihat
                                </a>
                                <form id="del-order-<?= $order['id'] ?>" method="POST" action="/admin/orders/<?= $order['id'] ?>/delete">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                </form>
                                <button type="button"
                                    data-confirm-form="del-order-<?= $order['id'] ?>"
                                    data-confirm-title="Padam Pesanan"
                                    data-confirm-message="Padam pesanan <?= e($order['order_number']) ?>? Tindakan ini tidak boleh dibatalkan."
                                    class="text-red-400 hover:text-red-600 text-xs font-semibold">
                                    <i class="fa-solid fa-trash"></i> Padam
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile cards -->
    <div class="md:hidden space-y-3">
        <?php foreach ($orders as $order):
            $statusColor = ['pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
            $statusLabel = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
            $cls = $statusColor[$order['status']] ?? 'bg-gray-100 text-gray-600';
        ?>
        <a href="/admin/orders/<?= $order['id'] ?>"
           class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 active:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="font-bold text-pink-500 text-sm"><?= e($order['order_number']) ?></p>
                    <p class="text-xs text-gray-400"><?= date('d/m/Y h:i A', strtotime($order['created_at'])) ?></p>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $cls ?>">
                    <?= $statusLabel[$order['status']] ?? $order['status'] ?>
                </span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-700"><?= e($order['customer_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= e($order['customer_phone']) ?></p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-800 text-sm">RM<?= number_format($order['total'], 2) ?></p>
                    <p class="text-xs text-gray-400 mt-0.5"><i class="fa-solid fa-chevron-right text-xs"></i></p>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>
