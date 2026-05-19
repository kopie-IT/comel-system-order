<?php $pageTitle = 'Senarai Pesanan'; ?>
<div class="py-2">
    <h2 class="text-lg font-bold text-gray-700 mb-5">Senarai Pesanan</h2>

    <!-- Status filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
        <?php
        $statuses = ['' => 'Semua', 'pending' => 'Tertunggak', 'confirmed' => 'Disahkan', 'completed' => 'Selesai', 'cancelled' => 'Dibatal'];
        foreach ($statuses as $val => $label):
            $active = $status === $val;
        ?>
        <a href="/admin/orders<?= $val ? '?status=' . $val : '' ?>"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors
               <?= $active ? 'bg-pink-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if (empty($orders)): ?>
            <div class="text-center py-10 text-gray-400 text-sm">Tiada pesanan.</div>
        <?php else: ?>
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
                        $statusMap = [
                            'pending'   => 'bg-yellow-100 text-yellow-700',
                            'confirmed' => 'bg-blue-100 text-blue-700',
                            'completed' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                        $statusLabel = ['pending'=>'Tertunggak','confirmed'=>'Disahkan','completed'=>'Selesai','cancelled'=>'Dibatal'];
                        $cls = $statusMap[$order['status']] ?? 'bg-gray-100 text-gray-600';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-pink-500"><?= e($order['order_number']) ?></td>
                        <td class="px-5 py-3 text-gray-700"><?= e($order['customer_name']) ?></td>
                        <td class="px-5 py-3 text-gray-500"><?= e($order['customer_phone']) ?></td>
                        <td class="px-5 py-3 font-semibold">RM<?= number_format($order['total'], 2) ?></td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold <?= $cls ?>">
                                <?= $statusLabel[$order['status']] ?? $order['status'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-400"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                        <td class="px-5 py-3 text-right">
                            <a href="/admin/orders/<?= $order['id'] ?>"
                               class="text-blue-500 hover:text-blue-700 text-xs font-semibold">
                                <i class="fa-solid fa-eye"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
