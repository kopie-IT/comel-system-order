<?php $pageTitle = $editing ? 'Edit Pengguna' : 'Tambah Pengguna'; ?>
<div class="py-2 max-w-md">
    <a href="/admin/users" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h2 class="text-lg font-bold text-gray-700 mb-5"><?= $editing ? 'Edit Pengguna' : 'Tambah Pengguna Baru' ?></h2>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <?php foreach ($errors as $err): ?>
        <p class="text-red-600 text-sm"><i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= $editing ? '/admin/users/edit/' . $user['id'] : '/admin/users/add' ?>"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
            <input type="text" name="username"
                   value="<?= e($old['username'] ?? $user['username'] ?? '') ?>"
                   required placeholder="Contoh: admin2"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Password <?= $editing ? '<span class="text-gray-400 font-normal">(kosongkan jika tidak mahu tukar)</span>' : '<span class="text-pink-500">*</span>' ?>
            </label>
            <input type="password" name="password"
                   <?= $editing ? '' : 'required' ?>
                   placeholder="<?= $editing ? 'Kosongkan jika tidak mahu tukar' : 'Minimum 6 aksara' ?>"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
            <select name="role"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                <?php
                $currentRole = $old['role'] ?? $user['role'] ?? 'admin';
                $roles = ['admin' => 'Admin', 'superadmin' => 'Super Admin'];
                foreach ($roles as $val => $label):
                ?>
                <option value="<?= $val ?>" <?= $currentRole === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-gray-400 mt-1">
                <strong>Admin</strong> — akses penuh ke semua pesanan, produk, tetapan.<br>
                <strong>Super Admin</strong> — tambahan akses pengurusan pengguna.
            </p>
        </div>

        <button type="submit"
            class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded-xl transition-colors text-sm">
            <i class="fa-solid fa-floppy-disk mr-2"></i> <?= $editing ? 'Simpan Perubahan' : 'Tambah Pengguna' ?>
        </button>
    </form>
</div>
