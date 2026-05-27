<?php $pageTitle = 'Dashboard'; ?>
<div class="py-2">
    <!-- Stats cards: 2-col on mobile (Pesanan + Tertunggak side by side), 3-col on desktop -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-pink-100 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bag-shopping text-pink-500 text-lg md:text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Jumlah Pesanan</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['total_orders'] ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clock text-yellow-500 text-lg md:text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['pending_orders'] ?></p>
                </div>
            </div>
        </div>

        <div class="col-span-2 md:col-span-1 bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-money-bill-wave text-green-500 text-lg md:text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Jumlah Hasil</p>
                    <p class="text-2xl font-bold text-gray-800">RM<?= number_format($stats['total_revenue'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" action="/admin/dashboard" class="mb-4">
        <div class="relative">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
            <input type="text" name="search" value="<?= e($search) ?>"
                placeholder="Cari nama, telefon atau alamat..."
                class="w-full border-2 border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 bg-white">
        </div>
    </form>

    <!-- Active orders -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-700">
                Pesanan Aktif
                <?php if ($search !== ''): ?>
                <span class="ml-2 text-xs bg-pink-100 text-pink-600 font-semibold px-2 py-0.5 rounded-full"><?= count($orders) ?> hasil</span>
                <?php endif; ?>
            </h3>
            <a href="/admin/orders" class="text-pink-500 text-sm hover:underline">Lihat Semua</a>
        </div>
        <?php if (empty($orders)): ?>
            <div class="text-center py-10 text-gray-400 text-sm">
                <?= $search !== '' ? 'Tiada hasil carian.' : 'Tiada pesanan aktif.' ?>
            </div>
        <?php else:
        // Group orders by date
        $grouped  = [];
        $today    = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        foreach ($orders as $o) {
            $grouped[date('Y-m-d', strtotime($o['created_at']))][] = $o;
        }
        $statusMap = [
            'pending'   => ['bg-yellow-100 text-yellow-700', 'Pending'],
            'confirmed' => ['bg-blue-100 text-blue-700',     'Confirmed'],
            'completed' => ['bg-green-100 text-green-700',   'Completed'],
            'cancelled' => ['bg-red-100 text-red-700',       'Cancelled'],
        ];
        ?>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Order ID</th>
                        <th class="px-5 py-3 text-left">Pelanggan</th>
                        <th class="px-5 py-3 text-left">Jumlah</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Masa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped as $date => $orders):
                        $dl = $date === $today ? 'Hari Ini' : ($date === $yesterday ? 'Semalam' : date('d M Y', strtotime($date)));
                    ?>
                    <tr>
                        <td colspan="5" class="px-5 py-2 bg-pink-50 border-y border-pink-100">
                            <span class="text-xs font-bold text-pink-500 uppercase tracking-wide">
                                <i class="fa-solid fa-calendar-day mr-1.5"></i><?= $dl ?>
                            </span>
                        </td>
                    </tr>
                    <?php foreach ($orders as $order):
                        [$cls, $label] = $statusMap[$order['status']] ?? ['bg-gray-100 text-gray-600', $order['status']];
                    ?>
                    <tr class="hover:bg-gray-50 border-b border-gray-100 cursor-pointer" onclick="window.location='/admin/orders/<?= $order['id'] ?>'">
                        <td class="px-5 py-3">
                            <span class="text-pink-500 font-medium"><?= e($order['order_number']) ?></span>
                        </td>
                        <td class="px-5 py-3 text-gray-600"><?= e($order['customer_name']) ?></td>
                        <td class="px-5 py-3 font-semibold">RM<?= number_format($order['total'], 2) ?></td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold <?= $cls ?>"><?= $label ?></span>
                        </td>
                        <td class="px-5 py-3 text-gray-400"><?= date('h:i A', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden">
            <?php foreach ($grouped as $date => $orders):
                $dl = $date === $today ? 'Hari Ini' : ($date === $yesterday ? 'Semalam' : date('d M Y', strtotime($date)));
            ?>
            <div class="px-4 py-2 bg-pink-50 border-b border-pink-100">
                <span class="text-xs font-bold text-pink-500 uppercase tracking-wide">
                    <i class="fa-solid fa-calendar-day mr-1.5"></i><?= $dl ?>
                </span>
            </div>
            <?php foreach ($orders as $order):
                [$cls, $label] = $statusMap[$order['status']] ?? ['bg-gray-100 text-gray-600', $order['status']];
            ?>
            <a href="/admin/orders/<?= $order['id'] ?>"
               class="block px-4 py-3 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-pink-500 text-sm font-mono"><?= e($order['order_number']) ?></span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $cls ?>"><?= $label ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-700 font-medium"><?= e($order['customer_name']) ?></p>
                        <p class="text-xs text-gray-400"><?= date('h:i A', strtotime($order['created_at'])) ?></p>
                    </div>
                    <p class="font-bold text-gray-800 text-sm">RM<?= number_format($order['total'], 2) ?></p>
                </div>
            </a>
            <?php endforeach; endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</div>
