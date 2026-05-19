<?php $pageTitle = 'Log Masuk Admin'; ?>
<div class="w-full max-w-sm mx-auto px-4">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-6">
            <span class="text-4xl">🍼</span>
            <h1 class="text-xl font-bold text-pink-600 mt-2">Comel Admin</h1>
            <p class="text-gray-400 text-sm mt-1">Log masuk untuk meneruskan</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-4">
            <i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/login" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                <input type="text" name="username" required autofocus
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            </div>
            <button type="submit"
                class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded-xl transition-colors">
                Log Masuk
            </button>
        </form>
    </div>
</div>
