<?php $pageTitle = 'Dashboard'; ?>
<div class="py-2">
    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-bag-shopping text-pink-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Jumlah Pesanan</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['total_orders'] ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-money-bill-wave text-green-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Jumlah Hasil</p>
                    <p class="text-2xl font-bold text-gray-800">RM<?= number_format($stats['total_revenue'], 2) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-clock text-yellow-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Pesanan Tertunggak</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['pending_orders'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent orders -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-700">Pesanan Terkini</h3>
            <a href="/admin/orders" class="text-pink-500 text-sm hover:underline">Lihat Semua</a>
        </div>
        <?php if (empty($stats['recent_orders'])): ?>
            <div class="text-center py-10 text-gray-400 text-sm">Tiada pesanan lagi.</div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">No. Pesanan</th>
                        <th class="px-5 py-3 text-left">Pelanggan</th>
                        <th class="px-5 py-3 text-left">Jumlah</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Tarikh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($stats['recent_orders'] as $order): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="/admin/orders/<?= $order['id'] ?>" class="text-pink-500 hover:underline font-medium">
                                <?= e($order['order_number']) ?>
                            </a>
                        </td>
                        <td class="px-5 py-3 text-gray-600"><?= e($order['customer_name']) ?></td>
                        <td class="px-5 py-3 font-semibold">RM<?= number_format($order['total'], 2) ?></td>
                        <td class="px-5 py-3">
                            <?php
                            $statusMap = [
                                'pending'   => ['bg-yellow-100 text-yellow-700', 'Tertunggak'],
                                'confirmed' => ['bg-blue-100 text-blue-700',   'Disahkan'],
                                'completed' => ['bg-green-100 text-green-700', 'Selesai'],
                                'cancelled' => ['bg-red-100 text-red-700',     'Dibatal'],
                            ];
                            [$cls, $label] = $statusMap[$order['status']] ?? ['bg-gray-100 text-gray-600', $order['status']];
                            ?>
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold <?= $cls ?>"><?= $label ?></span>
                        </td>
                        <td class="px-5 py-3 text-gray-400"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
