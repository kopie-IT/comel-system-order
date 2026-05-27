<!DOCTYPE html>
<html lang="ms" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <title>Admin - <?= e(app_name()) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body { font-family: "Segoe UI", sans-serif; }
        #mobile-drawer { transform: translateX(-100%); transition: transform 0.25s ease; }
        #mobile-drawer.open { transform: translateX(0); }
        #drawer-overlay { opacity: 0; pointer-events: none; transition: opacity 0.25s ease; }
        #drawer-overlay.open { opacity: 1; pointer-events: auto; }
        /* Markdown rendering */
        .ai-response table { width: 100%; border-collapse: collapse; margin: 6px 0; }
        .ai-response th { background: #f3f4f6; padding: 5px 8px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; font-size: 11px; }
        .ai-response td { padding: 5px 8px; border: 1px solid #e5e7eb; font-size: 11px; }
        .ai-response tr:nth-child(even) td { background: #f9fafb; }
        .ai-response code { background: #f3f4f6; padding: 1px 4px; border-radius: 3px; font-family: monospace; font-size: 11px; }
        .ai-response pre { background: #1f2937; color: #f9fafb; padding: 8px 10px; border-radius: 8px; overflow-x: auto; margin: 6px 0; }
        .ai-response pre code { background: none; color: inherit; padding: 0; }
        .ai-response ul { padding-left: 14px; margin: 4px 0; list-style-type: disc; }
        .ai-response ol { padding-left: 14px; margin: 4px 0; list-style-type: decimal; }
        .ai-response li { margin: 2px 0; }
        .ai-response p { margin: 3px 0; }
        .ai-response p:first-child { margin-top: 0; }
        .ai-response p:last-child { margin-bottom: 0; }
        .ai-response strong { font-weight: 700; }
        .ai-response em { font-style: italic; }
        .ai-response h1 { font-size: 15px; font-weight: 700; margin: 6px 0 3px; }
        .ai-response h2 { font-size: 13px; font-weight: 700; margin: 5px 0 3px; }
        .ai-response h3 { font-size: 12px; font-weight: 700; margin: 4px 0 2px; }
        .ai-response img { max-width: 100%; border-radius: 8px; margin: 4px 0; display: block; }
        .ai-response a { color: #8b5cf6; text-decoration: underline; }
        .ai-response blockquote { border-left: 3px solid #d1d5db; padding-left: 8px; color: #6b7280; margin: 4px 0; }
        .ai-response hr { border: none; border-top: 1px solid #e5e7eb; margin: 6px 0; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Mobile drawer overlay -->
<div id="drawer-overlay" class="fixed inset-0 bg-black/40 z-40 md:hidden" onclick="closeDrawer()"></div>

<!-- Mobile drawer -->
<div id="mobile-drawer" class="fixed top-0 left-0 h-full w-64 bg-white shadow-xl z-50 flex flex-col md:hidden">
    <div class="px-6 py-5 border-b flex items-center justify-between">
        <a href="/admin/dashboard" class="flex items-center gap-2">
            <span class="text-2xl">&#x1F37C;</span>
            <span class="font-bold text-pink-600"><?= e(app_name()) ?></span>
        </a>
        <button onclick="closeDrawer()" class="text-gray-400 hover:text-gray-600 p-1">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        <?php
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        function navLinkMobile(string $href, string $icon, string $label, string $current): string {
            $active = str_starts_with($current, $href) && $href !== '/admin/dashboard'
                ? true
                : ($current === '/admin/dashboard' && $href === '/admin/dashboard');
            $cls = $active
                ? 'flex items-center gap-3 px-4 py-2.5 rounded-xl bg-pink-50 text-pink-600 font-semibold text-sm'
                : 'flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-600 hover:bg-gray-50 text-sm';
            return '<a href="' . $href . '" class="' . $cls . '"><i class="fa-solid ' . $icon . ' w-4"></i>' . $label . '</a>';
        }
        echo navLinkMobile('/admin/dashboard',  'fa-gauge',        'Dashboard',       $currentUri);
        echo navLinkMobile('/admin/orders',     'fa-bag-shopping', 'Order',           $currentUri);
        echo navLinkMobile('/admin/products',   'fa-box',          '+Produk',         $currentUri);
        echo navLinkMobile('/admin/customers',  'fa-users',        'Pelanggan',       $currentUri);
        echo navLinkMobile('/admin/whatsapp',   'fa-brands fa-whatsapp', 'WhatsApp Mesej', $currentUri);
        echo navLinkMobile('/admin/ai',         'fa-robot',        'AI Assistant',    $currentUri);
        if (($_SESSION['admin_role'] ?? '') === 'superadmin') {
            echo navLinkMobile('/admin/users',  'fa-users',        'Pengguna',        $currentUri);
        }
        echo navLinkMobile('/admin/backup',     'fa-hard-drive',   'Backup',          $currentUri);
        echo navLinkMobile('/admin/settings',   'fa-gear',         'Tetapan',         $currentUri);
        ?>
    </nav>
    <div class="px-4 py-3 border-t border-b">
        <a href="https://www.jtexpress.my" target="_blank" rel="noopener noreferrer"
           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-600 hover:bg-yellow-50 hover:text-yellow-600 text-sm">
            <i class="fa-solid fa-truck w-4 text-yellow-500"></i> J&T Express
        </a>
        <a href="https://www.jtexpress.my/trajectoryQuery" target="_blank" rel="noopener noreferrer"
           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-600 hover:bg-yellow-50 hover:text-yellow-600 text-sm">
            <i class="fa-solid fa-magnifying-glass w-4 text-yellow-500"></i> Semak Tracking J&T
        </a>
    </div>
    <div class="px-4 py-4">
        <a href="/admin/logout" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-500 hover:bg-red-50 hover:text-red-500 text-sm">
            <i class="fa-solid fa-right-from-bracket w-4"></i> Log Keluar
        </a>
    </div>
</div>

<!-- Sidebar + Content wrapper -->
<div class="flex min-h-screen">

    <!-- Desktop Sidebar -->
    <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
        <div class="px-6 py-5 border-b">
            <a href="/admin/dashboard" class="flex items-center gap-2">
                <span class="text-2xl">&#x1F37C;</span>
                <span class="font-bold text-pink-600"><?= e(app_name()) ?></span>
            </a>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-1">
            <?php
            function navLink(string $href, string $icon, string $label, string $current): string {
                $active = str_starts_with($current, $href) && $href !== '/admin/dashboard'
                    ? true
                    : ($current === '/admin/dashboard' && $href === '/admin/dashboard');
                $cls = $active
                    ? 'flex items-center gap-3 px-4 py-2.5 rounded-xl bg-pink-50 text-pink-600 font-semibold text-sm'
                    : 'flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-600 hover:bg-gray-50 text-sm';
                return '<a href="' . $href . '" class="' . $cls . '"><i class="fa-solid ' . $icon . ' w-4"></i>' . $label . '</a>';
            }
            echo navLink('/admin/dashboard',  'fa-gauge',        'Dashboard',    $currentUri);
            echo navLink('/admin/orders',     'fa-bag-shopping', 'Order',        $currentUri);
            echo navLink('/admin/products',   'fa-box',          '+Produk',      $currentUri);
            echo navLink('/admin/categories', 'fa-tags',         'Kategori',     $currentUri);
            echo navLink('/admin/customers',  'fa-users',        'Pelanggan',    $currentUri);
            echo navLink('/admin/whatsapp',   'fa-brands fa-whatsapp', 'WhatsApp', $currentUri);
            echo navLink('/admin/ai',         'fa-robot',        'AI Assistant', $currentUri);
            if (($_SESSION['admin_role'] ?? '') === 'superadmin') {
                echo navLink('/admin/users',  'fa-users',        'Pengguna',     $currentUri);
            }
            echo navLink('/admin/backup',     'fa-hard-drive',   'Backup',       $currentUri);
            echo navLink('/admin/settings',   'fa-gear',         'Tetapan',      $currentUri);
            ?>
        </nav>
        <!-- External courier link -->
        <div class="px-4 py-3 border-t border-b">
            <a href="https://www.jtexpress.my" target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-600 hover:bg-yellow-50 hover:text-yellow-600 text-sm">
                <i class="fa-solid fa-truck w-4 text-yellow-500"></i> J&T Express
            </a>
            <a href="https://www.jtexpress.my/trajectoryQuery" target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-600 hover:bg-yellow-50 hover:text-yellow-600 text-sm">
                <i class="fa-solid fa-magnifying-glass w-4 text-yellow-500"></i> Semak Tracking J&T
            </a>
        </div>
        <div class="px-4 py-4 border-t">
            <a href="/admin/logout" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-500 hover:bg-red-50 hover:text-red-500 text-sm">
                <i class="fa-solid fa-right-from-bracket w-4"></i> Log Keluar
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar -->
        <header class="bg-white shadow-sm px-4 md:px-6 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <!-- Hamburger (mobile only) -->
                <button onclick="openDrawer()" class="md:hidden text-gray-500 hover:text-pink-500 p-1">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="font-semibold text-gray-700 text-base md:text-lg truncate">
                    <?= $pageTitle ?? 'Admin Panel' ?>
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="/admin/ai" class="hidden sm:inline-flex items-center gap-1.5 bg-purple-100 hover:bg-purple-200 text-purple-600 text-xs font-semibold px-3 py-1.5 rounded-full transition-colors">
                    <i class="fa-solid fa-robot text-xs"></i> AI
                </a>
                <span class="text-sm text-gray-400 hidden sm:block">
                    <i class="fa-solid fa-user-shield mr-1"></i>
                    <?= e($_SESSION['admin_username'] ?? 'Admin') ?>
                    <?php if (($_SESSION['admin_role'] ?? '') === 'superadmin'): ?>
                    <span class="ml-1 text-xs bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded-full font-semibold">Super</span>
                    <?php endif; ?>
                </span>
            </div>
        </header>

        <!-- Flash messages -->
        <?php $success = flash('success'); $error = flash('error'); ?>
        <div class="px-4 md:px-6 pt-4">
        <?php if ($success): ?>
            <div class="flash-msg bg-green-100 border border-green-300 text-green-800 rounded-xl px-4 py-3 text-sm mb-4 flex items-center justify-between">
                <span><i class="fa-solid fa-circle-check mr-2"></i><?= e($success) ?></span>
                <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800 ml-3"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="flash-msg bg-red-100 border border-red-300 text-red-800 rounded-xl px-4 py-3 text-sm mb-4 flex items-center justify-between">
                <span><i class="fa-solid fa-circle-exclamation mr-2"></i><?= e($error) ?></span>
                <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 ml-3"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        </div>

        <!-- Page content -->
        <main class="flex-1 px-4 md:px-6 py-2 pb-20 md:pb-10">
            <?php require $viewFile; ?>
        </main>
    </div>
</div>

<!-- Admin mobile bottom nav (hidden on desktop) -->
<nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-t border-gray-100 z-40 md:hidden" style="padding-bottom:env(safe-area-inset-bottom);-webkit-transform:translateZ(0);transform:translateZ(0)">
    <div class="flex items-center justify-around px-1 py-2">

        <!-- Customer -->
        <a href="/admin/customers" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center <?= str_starts_with($currentUri, '/admin/customers') ? 'scale-110' : '' ?>"
                 style="background:linear-gradient(145deg,#60a5fa,#2563eb);box-shadow:0 4px 12px rgba(37,99,235,0.4),inset 0 1px 0 rgba(255,255,255,0.3)">
                <i class="fa-solid fa-users text-white" style="font-size:15px;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25))"></i>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentUri, '/admin/customers') ? 'text-blue-500' : 'text-gray-400' ?>">Pelanggan</span>
        </a>

        <!-- Order -->
        <a href="/admin/orders" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center <?= str_starts_with($currentUri, '/admin/orders') ? 'scale-110' : '' ?>"
                 style="background:linear-gradient(145deg,#f472b6,#db2777);box-shadow:0 4px 12px rgba(219,39,119,0.4),inset 0 1px 0 rgba(255,255,255,0.3)">
                <i class="fa-solid fa-bag-shopping text-white" style="font-size:16px;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25))"></i>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentUri, '/admin/orders') ? 'text-pink-500' : 'text-gray-400' ?>">Order</span>
        </a>

        <!-- Home -->
        <a href="/admin/dashboard" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center <?= $currentUri === '/admin/dashboard' ? 'scale-110' : '' ?>"
                 style="background:linear-gradient(145deg,#fb923c,#ea580c);box-shadow:0 4px 12px rgba(234,88,12,0.4),inset 0 1px 0 rgba(255,255,255,0.3)">
                <i class="fa-solid fa-gauge text-white" style="font-size:16px;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25))"></i>
            </div>
            <span class="text-[10px] font-bold <?= $currentUri === '/admin/dashboard' ? 'text-orange-500' : 'text-gray-400' ?>">Home</span>
        </a>

        <!-- Produk -->
        <a href="/admin/products" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center <?= str_starts_with($currentUri, '/admin/products') ? 'scale-110' : '' ?>"
                 style="background:linear-gradient(145deg,#4ade80,#16a34a);box-shadow:0 4px 12px rgba(22,163,74,0.4),inset 0 1px 0 rgba(255,255,255,0.3)">
                <i class="fa-solid fa-box text-white" style="font-size:16px;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25))"></i>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentUri, '/admin/products') ? 'text-green-500' : 'text-gray-400' ?>">Produk</span>
        </a>

    </div>
</nav>

<script>
function openDrawer() {
    document.getElementById('mobile-drawer').classList.add('open');
    document.getElementById('drawer-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDrawer() {
    document.getElementById('mobile-drawer').classList.remove('open');
    document.getElementById('drawer-overlay').classList.remove('open');
    document.body.style.overflow = '';
}
</script>

<!-- Confirmation modal -->
<div id="confirm-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.55)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
            </div>
            <h3 id="confirm-modal-title" class="font-bold text-gray-800 text-base"></h3>
        </div>
        <p id="confirm-modal-message" class="text-sm text-gray-500 mb-5 pl-1"></p>
        <div class="flex gap-3">
            <button type="button" onclick="closeConfirmModal()"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl text-sm transition-colors">
                Batal
            </button>
            <button type="button" id="confirm-modal-btn"
                class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                Padam
            </button>
        </div>
    </div>
</div>

<!-- Alert modal -->
<div id="alert-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.55)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-info text-amber-500"></i>
            </div>
            <h3 id="alert-modal-title" class="font-bold text-gray-800 text-base">Perhatian</h3>
        </div>
        <p id="alert-modal-message" class="text-sm text-gray-500 mb-5 pl-1"></p>
        <button type="button" onclick="closeAlertModal()"
            class="w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
            OK
        </button>
    </div>
</div>

<script>
function showConfirmModal(title, message, onConfirm) {
    document.getElementById('confirm-modal-title').textContent = title;
    document.getElementById('confirm-modal-message').textContent = message;
    document.getElementById('confirm-modal-btn').onclick = function () {
        closeConfirmModal();
        onConfirm();
    };
    var m = document.getElementById('confirm-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeConfirmModal() {
    var m = document.getElementById('confirm-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
function showAlertModal(message, title) {
    document.getElementById('alert-modal-title').textContent = title || 'Perhatian';
    document.getElementById('alert-modal-message').textContent = message;
    var m = document.getElementById('alert-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeAlertModal() {
    var m = document.getElementById('alert-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
// Auto-dismiss flash messages after 4s
document.querySelectorAll('.flash-msg').forEach(function (el) {
    setTimeout(function () {
        el.style.transition = 'opacity 0.4s, transform 0.4s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-6px)';
        setTimeout(function () { el.remove(); }, 400);
    }, 4000);
});
// Event delegation for delete buttons using data-* attributes
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-confirm-form]');
    if (!btn) return;
    e.preventDefault();
    var formId  = btn.getAttribute('data-confirm-form');
    var title   = btn.getAttribute('data-confirm-title') || 'Padam';
    var message = btn.getAttribute('data-confirm-message') || 'Adakah anda pasti?';
    showConfirmModal(title, message, function () {
        var form = document.getElementById(formId);
        if (form) form.submit();
    });
});
</script>

</body>
</html>
