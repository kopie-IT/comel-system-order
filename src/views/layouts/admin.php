<!DOCTYPE html>
<html lang="ms" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
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
        .ai-response th { background: #f1f5f9; padding: 5px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; font-size: 11px; }
        .ai-response td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 11px; }
        .ai-response tr:nth-child(even) td { background: #f8fafc; }
        .ai-response code { background: #f1f5f9; padding: 1px 4px; border-radius: 3px; font-family: monospace; font-size: 11px; }
        .ai-response pre { background: #0f172a; color: #e2e8f0; padding: 8px 10px; border-radius: 8px; overflow-x: auto; margin: 6px 0; }
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
        .ai-response a { color: #6366f1; text-decoration: underline; }
        .ai-response blockquote { border-left: 3px solid #cbd5e1; padding-left: 8px; color: #64748b; margin: 4px 0; }
        .ai-response hr { border: none; border-top: 1px solid #e2e8f0; margin: 6px 0; }
        /* Sidebar active state */
        .nav-active { background: rgba(99,102,241,0.12); color: #6366f1; font-weight: 600; }
        .nav-active i { color: #6366f1; }
        /* Header enhancements */
        .top-bar {
            background: linear-gradient(120deg, #ffffff, #f8fafc);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
        }
        /* AI button enhancements */
        .ai-button {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            box-shadow: 0 2px 4px -1px rgba(99, 102, 241, 0.3), 0 4px 6px -1px rgba(99, 102, 241, 0.15);
            transition: all 0.3s ease;
        }
        .ai-button:hover {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4), 0 8px 10px -1px rgba(99, 102, 241, 0.2);
            transform: translateY(-1px);
        }
        /* User profile enhancements */
        .user-profile {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        /* Flash message enhancements */
        .flash-msg {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .flash-msg.success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #a7f3d0;
        }
        .flash-msg.error {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">

<!-- Mobile drawer overlay -->
<div id="drawer-overlay" class="fixed inset-0 bg-black/50 z-40 md:hidden" onclick="closeDrawer()"></div>

<!-- Mobile drawer -->
<div id="mobile-drawer" class="fixed top-0 left-0 h-full w-64 bg-slate-900 shadow-2xl z-50 flex flex-col md:hidden">
    <div class="px-6 py-5 border-b border-slate-700 flex items-center justify-between">
        <a href="/admin/dashboard" class="flex items-center gap-2">
            <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-store text-white text-base"></i>
            </div>
            <span class="font-bold text-white text-sm"><?= e(app_name()) ?></span>
        </a>
        <button onclick="closeDrawer()" class="text-slate-400 hover:text-white p-1 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <?php
            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            function navLinkMobile(string $href, string $icon, string $label, string $current): string {
                $active = str_starts_with($current, $href) && $href !== '/admin/dashboard'
                    ? true
                    : ($current === '/admin/dashboard' && $href === '/admin/dashboard');
                $cls = $active
                    ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600/20 to-indigo-600/10 text-indigo-400 font-bold text-sm border-l-4 border-indigo-500'
                    : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white text-sm transition-all duration-200 hover:pl-4';
                return '<a href="' . $href . '" class="' . $cls . '"><i class="fa-solid ' . $icon . ' w-4 text-center"></i>' . $label . '</a>';
            }
            echo navLinkMobile('/admin/dashboard',  'fa-gauge',        'Dashboard',       $currentUri);
            echo navLinkMobile('/admin/orders',     'fa-bag-shopping', 'Pesanan',         $currentUri);
            echo navLinkMobile('/admin/products',   'fa-box',          'Produk',          $currentUri);
            echo navLinkMobile('/admin/customers',  'fa-users',        'Pelanggan',       $currentUri);
            echo navLinkMobile('/admin/whatsapp',   'fa-brands fa-whatsapp', 'WhatsApp', $currentUri);
            echo navLinkMobile('/admin/ai',         'fa-robot',        'AI Assistant',    $currentUri);
            echo navLinkMobile('/admin/backup',     'fa-hard-drive',   'Backup',          $currentUri);
            if (($_SESSION['admin_role'] ?? '') === 'superadmin') {
                echo navLinkMobile('/admin/users',  'fa-user-shield',  'Pengguna',        $currentUri);
            }
            echo navLinkMobile('/admin/settings',   'fa-gear',         'Tetapan',         $currentUri);
            ?>
        </nav>
    <div class="px-3 py-3 border-t border-slate-700 space-y-0.5">
        <a href="https://www.jtexpress.my/trajectoryQuery" target="_blank" rel="noopener noreferrer"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-amber-400 text-sm transition-colors">
            <i class="fa-solid fa-truck w-4 text-center text-amber-500"></i> Semak J&T
        </a>
        <a href="/admin/logout"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-red-900/30 hover:text-red-400 text-sm transition-colors">
            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Log Keluar
        </a>
    </div>
</div>

<!-- Sidebar + Content wrapper -->
<div class="flex min-h-screen">

    <!-- Desktop Sidebar -->
    <aside class="w-60 bg-slate-900 shadow-xl flex-shrink-0 hidden md:flex flex-col">
        <div class="px-5 py-5 border-b border-slate-700/60">
        <a href="/admin/dashboard" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-store text-white text-base"></i>
            </div>
            <div>
                <p class="font-bold text-white text-sm leading-tight"><?= e(app_name()) ?></p>
                <p class="text-slate-400 text-[10px]">Admin Panel</p>
            </div>
        </a>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <?php
            function navLink(string $href, string $icon, string $label, string $current): string {
                $active = str_starts_with($current, $href) && $href !== '/admin/dashboard'
                    ? true
                    : ($current === '/admin/dashboard' && $href === '/admin/dashboard');
                $cls = $active
                    ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600/20 to-indigo-600/10 text-indigo-400 font-bold text-sm border-l-4 border-indigo-500'
                    : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white text-sm transition-all duration-200 hover:pl-4';
                return '<a href="' . $href . '" class="' . $cls . '"><i class="fa-solid ' . $icon . ' w-4 text-center"></i>' . $label . '</a>';
            }
            echo navLink('/admin/dashboard',  'fa-gauge',        'Dashboard',    $currentUri);
            echo navLink('/admin/orders',     'fa-bag-shopping', 'Pesanan',      $currentUri);
            echo navLink('/admin/products',   'fa-box',          'Produk',       $currentUri);
            echo navLink('/admin/categories', 'fa-tags',         'Kategori',     $currentUri);
            echo navLink('/admin/customers',  'fa-users',        'Pelanggan',    $currentUri);
            echo navLink('/admin/whatsapp',   'fa-brands fa-whatsapp', 'WhatsApp', $currentUri);
            echo navLink('/admin/ai',         'fa-robot',        'AI Assistant', $currentUri);
            echo navLink('/admin/backup',     'fa-hard-drive',   'Backup',       $currentUri);
            if (($_SESSION['admin_role'] ?? '') === 'superadmin') {
                echo navLink('/admin/users',  'fa-user-shield',  'Pengguna',     $currentUri);
            }
            echo navLink('/admin/settings',   'fa-gear',         'Tetapan',      $currentUri);
            ?>
        </nav>
        <div class="px-3 py-3 border-t border-slate-700/60 space-y-0.5">
            <a href="https://www.jtexpress.my/trajectoryQuery" target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-amber-400 text-sm transition-colors">
                <i class="fa-solid fa-truck w-4 text-center text-amber-500"></i> Semak J&T
            </a>
            <a href="/admin/logout"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-red-900/30 hover:text-red-400 text-sm transition-colors">
                <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Log Keluar
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar -->
        <header class="top-bar px-4 md:px-6 py-3.5 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button onclick="openDrawer()" class="md:hidden text-slate-500 hover:text-slate-800 p-1 transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="font-bold text-slate-800 text-base md:text-lg truncate">
                    <?= $pageTitle ?? 'Admin Panel' ?>
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="/admin/ai" class="ai-button hidden sm:inline-flex items-center gap-1.5 text-white text-xs font-bold px-4 py-2 rounded-full">
                    <i class="fa-solid fa-robot text-xs"></i> AI
                </a>
                <div class="hidden sm:flex items-center gap-2 user-profile rounded-full px-4 py-2">
                    <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-user text-white text-xs"></i>
                    </div>
                    <span class="text-sm text-slate-700 font-bold">
                        <?= e($_SESSION['admin_username'] ?? 'Admin') ?>
                    </span>
                    <?php if (($_SESSION['admin_role'] ?? '') === 'superadmin'): ?>
                    <span class="text-[10px] bg-gradient-to-r from-purple-100 to-indigo-100 text-indigo-700 px-2 py-1 rounded-full font-bold">Super</span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <?php $success = flash('success'); $error = flash('error'); ?>
        <div class="px-4 md:px-6 pt-4">
        <?php if ($success): ?>
            <div class="flash-msg success px-5 py-4 text-sm mb-4 flex items-start justify-between">
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center mr-3 flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    </div>
                    <span class="text-emerald-800 font-medium"><?= e($success) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 ml-3 mt-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="flash-msg error px-5 py-4 text-sm mb-4 flex items-start justify-between">
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center mr-3 flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    </div>
                    <span class="text-red-800 font-medium"><?= e($error) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 ml-3 mt-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        </div>

        <!-- Page content -->
        <main class="flex-1 px-4 md:px-6 py-2 pb-20 md:pb-10">
            <?php require $viewFile; ?>
        </main>
    </div>
</div>

<!-- Admin mobile bottom nav -->
<nav class="fixed bottom-0 left-0 right-0 bg-slate-900/95 backdrop-blur-sm border-t border-slate-700 z-40 md:hidden" style="padding-bottom:env(safe-area-inset-bottom)">
    <div class="flex items-center justify-around px-1 py-2">
        <style>
            .bottom-nav-icon {
                position: relative;
                transform-style: preserve-3d;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            
            .bottom-nav-icon:hover {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
            }
            
            .bottom-nav-icon::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                border-radius: inherit;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .bottom-nav-icon:hover::before {
                opacity: 1;
            }
            
            .bottom-nav-icon.active {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1); }
                50% { box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.3), 0 8px 10px -2px rgba(0, 0, 0, 0.15); }
                100% { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1); }
            }
        </style>

        <a href="/admin/customers" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="bottom-nav-icon w-12 h-12 rounded-2xl flex items-center justify-center <?= str_starts_with($currentUri, '/admin/customers') ? 'bg-gradient-to-br from-blue-500 to-blue-600 active' : 'bg-gradient-to-br from-slate-700 to-slate-800' ?>">
                <i class="fa-solid fa-users text-white" style="font-size:18px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"></i>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentUri, '/admin/customers') ? 'text-blue-300' : 'text-slate-400' ?>">Pelanggan</span>
        </a>

        <a href="/admin/orders" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="bottom-nav-icon w-12 h-12 rounded-2xl flex items-center justify-center <?= str_starts_with($currentUri, '/admin/orders') ? 'bg-gradient-to-br from-indigo-500 to-purple-600 active' : 'bg-gradient-to-br from-slate-700 to-slate-800' ?>">
                <i class="fa-solid fa-bag-shopping text-white" style="font-size:18px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"></i>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentUri, '/admin/orders') ? 'text-indigo-300' : 'text-slate-400' ?>">Pesanan</span>
        </a>

        <a href="/admin/dashboard" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="bottom-nav-icon w-12 h-12 rounded-2xl flex items-center justify-center <?= $currentUri === '/admin/dashboard' ? 'bg-gradient-to-br from-cyan-500 to-teal-500 active' : 'bg-gradient-to-br from-slate-700 to-slate-800' ?>">
                <i class="fa-solid fa-gauge text-white" style="font-size:18px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"></i>
            </div>
            <span class="text-[10px] font-bold <?= $currentUri === '/admin/dashboard' ? 'text-cyan-300' : 'text-slate-400' ?>">Home</span>
        </a>

        <a href="/admin/products" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="bottom-nav-icon w-12 h-12 rounded-2xl flex items-center justify-center <?= str_starts_with($currentUri, '/admin/products') ? 'bg-gradient-to-br from-emerald-500 to-green-600 active' : 'bg-gradient-to-br from-slate-700 to-slate-800' ?>">
                <i class="fa-solid fa-box text-white" style="font-size:18px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"></i>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentUri, '/admin/products') ? 'text-emerald-300' : 'text-slate-400' ?>">Produk</span>
        </a>

        <a href="/admin/settings" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="bottom-nav-icon w-12 h-12 rounded-2xl flex items-center justify-center <?= str_starts_with($currentUri, '/admin/settings') ? 'bg-gradient-to-br from-slate-400 to-slate-600 active' : 'bg-gradient-to-br from-slate-700 to-slate-800' ?>">
                <i class="fa-solid fa-gear text-white" style="font-size:18px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"></i>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentUri, '/admin/settings') ? 'text-slate-200' : 'text-slate-400' ?>">Tetapan</span>
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
<div id="confirm-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.7)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
            </div>
            <h3 id="confirm-modal-title" class="font-bold text-slate-800 text-base"></h3>
        </div>
        <p id="confirm-modal-message" class="text-sm text-slate-500 mb-5 pl-1"></p>
        <div class="flex gap-3">
            <button type="button" onclick="closeConfirmModal()"
                class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-xl text-sm transition-colors">
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
<div id="alert-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.7)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-info text-amber-500"></i>
            </div>
            <h3 id="alert-modal-title" class="font-bold text-slate-800 text-base">Perhatian</h3>
        </div>
        <p id="alert-modal-message" class="text-sm text-slate-500 mb-5 pl-1"></p>
        <button type="button" onclick="closeAlertModal()"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
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
