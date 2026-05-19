<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Comel Baby Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body { font-family: "Segoe UI", sans-serif; }</style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Sidebar + Content wrapper -->
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
        <div class="px-6 py-5 border-b">
            <a href="/admin/dashboard" class="flex items-center gap-2">
                <span class="text-2xl">🍼</span>
                <span class="font-bold text-pink-600">Comel Admin</span>
            </a>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-1">
            <?php
            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            function navLink(string $href, string $icon, string $label, string $current): string {
                $active = str_starts_with($current, $href) && $href !== '/admin/dashboard'
                    ? true
                    : ($current === '/admin/dashboard' && $href === '/admin/dashboard');
                $cls = $active
                    ? 'flex items-center gap-3 px-4 py-2.5 rounded-xl bg-pink-50 text-pink-600 font-semibold text-sm'
                    : 'flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-600 hover:bg-gray-50 text-sm';
                return '<a href="' . $href . '" class="' . $cls . '"><i class="fa-solid ' . $icon . ' w-4"></i>' . $label . '</a>';
            }
            echo navLink('/admin/dashboard',  'fa-gauge',        'Dashboard',  $currentUri);
            echo navLink('/admin/orders',     'fa-bag-shopping', 'Pesanan',    $currentUri);
            echo navLink('/admin/products',   'fa-box',          'Produk',     $currentUri);
            echo navLink('/admin/categories', 'fa-tags',         'Kategori',   $currentUri);
            echo navLink('/admin/settings',   'fa-gear',         'Tetapan',    $currentUri);
            ?>
        </nav>
        <div class="px-4 py-4 border-t">
            <a href="/admin/logout" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-500 hover:bg-red-50 hover:text-red-500 text-sm">
                <i class="fa-solid fa-right-from-bracket w-4"></i> Log Keluar
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar -->
        <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
            <h1 class="font-semibold text-gray-700 text-lg">
                <?= $pageTitle ?? 'Admin Panel' ?>
            </h1>
            <span class="text-sm text-gray-400">
                <i class="fa-solid fa-user-shield mr-1"></i>
                <?= e($_SESSION['admin_username'] ?? 'Admin') ?>
            </span>
        </header>

        <!-- Flash messages -->
        <?php $success = flash('success'); $error = flash('error'); ?>
        <div class="px-6 pt-4">
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-300 text-green-800 rounded-xl px-4 py-3 text-sm mb-4">
                <i class="fa-solid fa-circle-check mr-2"></i><?= e($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-300 text-red-800 rounded-xl px-4 py-3 text-sm mb-4">
                <i class="fa-solid fa-circle-exclamation mr-2"></i><?= e($error) ?>
            </div>
        <?php endif; ?>
        </div>

        <!-- Page content -->
        <main class="flex-1 px-6 py-2 pb-10">
            <?php require $viewFile; ?>
        </main>
    </div>
</div>

</body>
</html>
