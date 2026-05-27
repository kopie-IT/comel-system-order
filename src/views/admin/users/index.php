<?php $pageTitle = 'Pengurusan Pengguna'; ?>
<div class="py-2">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-700">Pengurusan Pengguna</h2>
        <a href="/admin/users/add"
           class="inline-flex items-center gap-2 bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <i class="fa-solid fa-plus"></i> Tambah Pengguna
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if (empty($users)): ?>
            <div class="text-center py-10 text-gray-400 text-sm">Tiada pengguna.</div>
        <?php else: ?>
        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Username</th>
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Tarikh Daftar</th>
                        <th class="px-5 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-semibold text-gray-800">
                            <i class="fa-solid fa-user-shield mr-2 <?= $user['role'] === 'superadmin' ? 'text-purple-500' : 'text-gray-400' ?>"></i>
                            <?= e($user['username']) ?>
                            <?php if ((int)$user['id'] === (int)($_SESSION['admin_id'] ?? 0)): ?>
                            <span class="ml-1 text-xs bg-green-100 text-green-600 px-1.5 py-0.5 rounded-full">Anda</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold <?= $user['role'] === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' ?>">
                                <?= $user['role'] === 'superadmin' ? 'Super Admin' : 'Admin' ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-400"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        <td class="px-5 py-3 text-right flex items-center justify-end gap-2">
                            <a href="/admin/users/edit/<?= $user['id'] ?>"
                               class="text-blue-500 hover:text-blue-700 text-xs font-semibold">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            <?php if ((int)$user['id'] !== (int)($_SESSION['admin_id'] ?? 0)): ?>
                            <form method="POST" action="/admin/users/delete/<?= $user['id'] ?>" id="del-user-<?= $user['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="button"
                                    data-confirm-form="del-user-<?= $user['id'] ?>"
                                    data-confirm-title="Padam Pengguna"
                                    data-confirm-message="Adakah anda pasti mahu memadam pengguna ini?"
                                    class="text-red-400 hover:text-red-600 text-xs font-semibold">
                                    <i class="fa-solid fa-trash"></i> Padam
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Mobile cards -->
        <div class="md:hidden divide-y divide-gray-100">
            <?php foreach ($users as $user): ?>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-user-shield <?= $user['role'] === 'superadmin' ? 'text-purple-500' : 'text-gray-400' ?>"></i>
                        <span class="font-semibold text-gray-800 text-sm"><?= e($user['username']) ?></span>
                        <?php if ((int)$user['id'] === (int)($_SESSION['admin_id'] ?? 0)): ?>
                        <span class="text-xs bg-green-100 text-green-600 px-1.5 py-0.5 rounded-full">Anda</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full <?= $user['role'] === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $user['role'] === 'superadmin' ? 'Super Admin' : 'Admin' ?>
                    </span>
                </div>
                <div class="flex gap-2">
                    <a href="/admin/users/edit/<?= $user['id'] ?>"
                       class="text-xs bg-blue-50 text-blue-500 font-semibold px-3 py-1.5 rounded-lg">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <?php if ((int)$user['id'] !== (int)($_SESSION['admin_id'] ?? 0)): ?>
                    <form method="POST" action="/admin/users/delete/<?= $user['id'] ?>" id="del-user-m-<?= $user['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="button"
                            data-confirm-form="del-user-m-<?= $user['id'] ?>"
                            data-confirm-title="Padam Pengguna"
                            data-confirm-message="Adakah anda pasti mahu memadam pengguna ini?"
                            class="text-xs bg-red-50 text-red-400 font-semibold px-3 py-1.5 rounded-lg">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
