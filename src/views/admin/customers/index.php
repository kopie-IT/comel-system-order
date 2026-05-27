<?php $pageTitle = 'Pelanggan'; ?>
<div class="py-2">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-700">Senarai Pelanggan</h2>
    </div>

    <form method="GET" action="/admin/customers" class="flex gap-2 mb-4">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama atau telefon..."
            class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400">
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-sm font-semibold">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    <?php if (empty($customers)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 text-center py-10 text-gray-400 text-sm">
            Tiada pelanggan dijumpai.
        </div>
    <?php else: ?>

    <!-- Desktop table -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Nama</th>
                    <th class="px-5 py-3 text-left">Telefon</th>
                    <th class="px-5 py-3 text-center">Pesanan</th>
                    <th class="px-5 py-3 text-center">Alamat</th>
                    <th class="px-5 py-3 text-left">Pesanan Terakhir</th>
                    <th class="px-5 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($customers as $c): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-semibold text-gray-800"><?= e($c['name']) ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= e($c['phone']) ?></td>
                    <td class="px-5 py-3 text-center">
                        <span class="bg-pink-100 text-pink-700 text-xs font-semibold px-2 py-0.5 rounded-full"><?= $c['order_count'] ?></span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full"><?= $c['address_count'] ?></span>
                    </td>
                    <td class="px-5 py-3 text-gray-400"><?= $c['last_order_at'] ? date('d/m/Y', strtotime($c['last_order_at'])) : '-' ?></td>
                    <td class="px-5 py-3 text-right">
                        <a href="/admin/customers/<?= $c['id'] ?>" class="text-blue-500 hover:text-blue-700 text-xs font-semibold">
                            <i class="fa-solid fa-eye"></i> Lihat
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile cards -->
    <div class="md:hidden space-y-3">
        <?php foreach ($customers as $c): ?>
        <a href="/admin/customers/<?= $c['id'] ?>"
           class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 active:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="font-semibold text-gray-800 text-sm"><?= e($c['name']) ?></p>
                    <p class="text-xs text-gray-400"><?= e($c['phone']) ?></p>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-300 text-xs mt-1"></i>
            </div>
            <div class="flex gap-2">
                <span class="text-xs bg-pink-100 text-pink-700 font-semibold px-2 py-0.5 rounded-full"><?= $c['order_count'] ?> pesanan</span>
                <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full"><?= $c['address_count'] ?> alamat</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>
