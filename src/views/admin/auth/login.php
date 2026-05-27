<?php $pageTitle = 'Log Masuk Admin'; ?>
<div class="w-full max-w-sm mx-auto px-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-7">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-900/50">
                <i class="fa-solid fa-store text-white text-2xl"></i>
            </div>
            <h1 class="text-xl font-bold text-white"><?= e(app_name()) ?></h1>
            <p class="text-slate-400 text-sm mt-1">Log masuk untuk meneruskan</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="bg-red-900/40 border border-red-700 text-red-300 rounded-xl px-4 py-3 text-sm mb-4">
            <i class="fa-solid fa-circle-exclamation mr-1"></i><?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/login" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Username</label>
                <input type="text" name="username" required autofocus
                    class="w-full bg-slate-900 border-2 border-slate-600 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Password</label>
                <input type="password" name="password" required
                    class="w-full bg-slate-900 border-2 border-slate-600 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-3 rounded-xl transition-colors mt-2">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Log Masuk
            </button>
        </form>
    </div>
    <p class="text-center text-slate-600 text-xs mt-4">&copy; <?= date('Y') ?> <?= e(app_name()) ?></p>
</div>
