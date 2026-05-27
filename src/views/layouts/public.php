<?php
$cartCount = 0;
foreach ($_SESSION['cart'] ?? [] as $item) {
    $cartCount += $item['quantity'];
}
$justAdded = $_SESSION['cart_just_added'] ?? null;
unset($_SESSION['cart_just_added']);
?>
<!DOCTYPE html>
<html lang="ms" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <title><?= e(app_name()) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: "Segoe UI", sans-serif; }

        /* === TV Switch-On animation for "added to cart" modal === */
        @keyframes tv-on {
            0%   { transform: scale(0.001, 0.005); filter: brightness(2.5) blur(0.5px); opacity: 0; }
            18%  { transform: scale(1, 0.005);     filter: brightness(2.5) blur(0.5px); opacity: 1; }
            45%  { transform: scale(1, 1.04);      filter: brightness(1.8) blur(0); opacity: 1; }
            70%  { transform: scale(1, 0.98);      filter: brightness(1.1); }
            100% { transform: scale(1, 1);         filter: brightness(1); }
        }
        @keyframes tv-flash {
            0%, 100% { opacity: 0; }
            5%       { opacity: 1; }
            15%      { opacity: 0.4; }
            25%      { opacity: 0; }
        }
        @keyframes tv-scanlines {
            0%   { transform: translateY(-100%); opacity: 1; }
            100% { transform: translateY(100%);  opacity: 0; }
        }
        .tv-modal-backdrop { opacity: 0; transition: opacity 0.25s ease; }
        .tv-modal-backdrop.is-on { opacity: 1; }
        .tv-screen {
            transform-origin: center center;
            animation: tv-on 0.65s cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
            position: relative; overflow: hidden;
        }
        .tv-screen::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.55) 0%, rgba(255,255,255,0) 35%, rgba(255,255,255,0) 65%, rgba(255,255,255,0.18) 100%);
            pointer-events: none;
            animation: tv-flash 0.65s ease-out forwards;
        }
        .tv-screen::after {
            content: ''; position: absolute; left: 0; right: 0; height: 35%;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.55) 50%, rgba(255,255,255,0) 100%);
            pointer-events: none;
            animation: tv-scanlines 0.65s linear forwards;
        }
        .tv-screen-glow {
            box-shadow:
                0 0 0 1px rgba(99,102,241,0.2),
                0 25px 50px -12px rgba(99,102,241,0.35),
                0 0 80px rgba(99,102,241,0.2);
        }
        @media (prefers-reduced-motion: reduce) {
            .tv-screen { animation: none; }
            .tv-screen::before, .tv-screen::after { animation: none; opacity: 0; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<!-- Header -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                <i class="fa-solid fa-store text-white text-sm"></i>
            </div>
            <span class="font-bold text-slate-800 text-base"><?= e(app_name()) ?></span>
        </a>
        <div class="flex items-center gap-2">
            <a href="/cart" class="relative p-2 text-slate-500 hover:text-indigo-600 transition-colors">
                <i class="fa-solid fa-cart-shopping text-xl"></i>
                <?php if ($cartCount > 0): ?>
                <span class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                    <?= $cartCount ?>
                </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<!-- Flash messages -->
<?php $success = flash('success'); $error = flash('error'); ?>
<?php if ($success): ?>
<div class="max-w-lg mx-auto px-4 pt-3">
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">
        <i class="fa-solid fa-circle-check mr-2 text-emerald-500"></i><?= e($success) ?>
    </div>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="max-w-lg mx-auto px-4 pt-3">
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2 text-red-500"></i><?= e($error) ?>
    </div>
</div>
<?php endif; ?>

<!-- Main content -->
<main class="max-w-lg mx-auto px-4 py-4 pb-4">
    <?php require $viewFile; ?>
</main>

<!-- Footer -->
<footer class="text-center text-xs text-slate-400 py-2 pb-20">
    &copy; <?= date('Y') ?> <?= e(app_name()) ?>
    <br>
    <a href="/admin/login" class="text-slate-300 hover:text-slate-400 text-[10px] mt-0.5 inline-block transition-colors">Admin</a>
</footer>

<!-- Bottom navigation bar -->
<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$waNumber    = (new Setting())->get('whatsapp_number', '601234567890');
$waLink      = 'https://wa.me/' . preg_replace('/\D/', '', $waNumber);
$waBottomMsg = rawurlencode('Salam! Saya ada pertanyaan tentang produk ' . app_name() . '.');
?>
<nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-t border-slate-200 z-40" style="padding-bottom:env(safe-area-inset-bottom);-webkit-transform:translateZ(0);transform:translateZ(0)">
    <div class="max-w-lg mx-auto flex items-center justify-around px-2 py-2">

        <!-- Home -->
        <a href="/" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="w-11 h-11 rounded-2xl flex items-center justify-center transition-colors <?= $currentPath === '/' ? 'bg-indigo-600' : 'bg-slate-100' ?>">
                <i class="fa-solid fa-house <?= $currentPath === '/' ? 'text-white' : 'text-slate-500' ?>" style="font-size:18px"></i>
            </div>
            <span class="text-[10px] font-bold <?= $currentPath === '/' ? 'text-indigo-600' : 'text-slate-400' ?>">Home</span>
        </a>

        <!-- My Order -->
        <a href="/order/view" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="w-11 h-11 rounded-2xl flex items-center justify-center transition-colors <?= str_starts_with($currentPath, '/order/view') ? 'bg-violet-600' : 'bg-slate-100' ?>">
                <i class="fa-solid fa-receipt <?= str_starts_with($currentPath, '/order/view') ? 'text-white' : 'text-slate-500' ?>" style="font-size:18px"></i>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentPath, '/order/view') ? 'text-violet-600' : 'text-slate-400' ?>">My Order</span>
        </a>

        <!-- Tanya Kami -->
        <a href="<?= $waLink ?>?text=<?= $waBottomMsg ?>" target="_blank" rel="noopener noreferrer"
           class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-emerald-500">
                <i class="fa-brands fa-whatsapp text-white" style="font-size:20px"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400">Tanya Kami</span>
        </a>

        <!-- Cart -->
        <a href="/cart" class="flex flex-col items-center gap-1 active:scale-95 transition-all">
            <div class="w-11 h-11 rounded-2xl flex items-center justify-center relative transition-colors <?= str_starts_with($currentPath, '/cart') ? 'bg-amber-500' : 'bg-slate-100' ?>">
                <i class="fa-solid fa-cart-shopping <?= str_starts_with($currentPath, '/cart') ? 'text-white' : 'text-slate-500' ?>" style="font-size:18px"></i>
                <?php if ($cartCount > 0): ?>
                <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] rounded-full w-4 h-4 flex items-center justify-center font-bold border-2 border-white leading-none">
                    <?= $cartCount > 9 ? '9+' : $cartCount ?>
                </span>
                <?php endif; ?>
            </div>
            <span class="text-[10px] font-bold <?= str_starts_with($currentPath, '/cart') ? 'text-amber-500' : 'text-slate-400' ?>">Cart</span>
        </a>

    </div>
</nav>

<?php if ($justAdded): ?>
<?php
    $jaImage    = $justAdded['image'] ?? '';
    $jaName     = $justAdded['product_name'] ?? '';
    $jaQty      = (int)($justAdded['quantity'] ?? 1);
    $jaPrice    = (float)($justAdded['unit_price'] ?? 0);
    $jaSize     = $justAdded['size_label']    ?? '';
    $jaVariant  = $justAdded['variant_label'] ?? '';
    $jaCatId    = (int)($justAdded['category_id'] ?? 0);
?>
<!-- Cart-added modal: TV switch-on animation -->
<div id="cart-added-modal"
     class="tv-modal-backdrop fixed inset-0 z-[100] hidden items-center justify-center bg-black/70 backdrop-blur-sm px-4">
    <div class="tv-screen tv-screen-glow w-full max-w-sm bg-white rounded-3xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-indigo-600 to-violet-600 text-white px-5 pt-5 pb-4 text-center">
            <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-full flex items-center justify-center mx-auto mb-2 ring-2 ring-white/30">
                <i class="fa-solid fa-circle-check text-white text-2xl"></i>
            </div>
            <h3 class="text-base font-bold">Berjaya Ditambah ke Troli!</h3>
            <p class="text-xs text-white/80 mt-0.5">Apa anda mahu buat seterusnya?</p>
        </div>

        <!-- Product info -->
        <div class="p-5">
            <div class="flex items-center gap-3 bg-slate-50 rounded-2xl p-3 border border-slate-100">
                <?php if (!empty($jaImage)): ?>
                <img src="<?= e($jaImage) ?>" alt=""
                     class="w-16 h-16 rounded-xl object-cover border border-white shadow-sm shrink-0">
                <?php else: ?>
                <div class="w-16 h-16 rounded-xl bg-white flex items-center justify-center shrink-0 border border-slate-100">
                    <i class="fa-solid fa-box text-slate-300 text-xl"></i>
                </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 text-sm truncate"><?= e($jaName) ?></p>
                    <?php if ($jaSize !== '' || $jaVariant !== ''): ?>
                    <p class="text-xs text-slate-500 mt-0.5 truncate">
                        <?php if ($jaSize !== ''): ?><?= e($jaSize) ?><?php endif; ?>
                        <?php if ($jaSize !== '' && $jaVariant !== ''): ?> · <?php endif; ?>
                        <?php if ($jaVariant !== ''): ?><?= e($jaVariant) ?><?php endif; ?>
                    </p>
                    <?php endif; ?>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-slate-500">Kuantiti: <strong class="text-slate-700">x<?= $jaQty ?></strong></span>
                        <span class="text-sm font-bold text-indigo-600">RM<?= number_format($jaPrice * $jaQty, 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="grid grid-cols-1 gap-2 mt-4">
                <a href="/checkout"
                   class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-3 rounded-xl text-center text-sm transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-credit-card"></i> Teruskan ke Pembayaran
                </a>
                <a href="<?= $jaCatId > 0 ? '/category/' . $jaCatId : '/' ?>"
                   id="cart-modal-continue"
                   class="w-full bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-semibold py-3 rounded-xl text-center text-sm transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-bag-shopping"></i> Teruskan Membeli-belah
                </a>
                <button type="button" id="cart-modal-close"
                    class="w-full text-slate-400 hover:text-slate-600 text-xs py-1.5 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var modal     = document.getElementById('cart-added-modal');
    var closeBtn  = document.getElementById('cart-modal-close');
    var continueA = document.getElementById('cart-modal-continue');
    if (!modal) return;
    function openModal() {
        modal.classList.remove('hidden'); modal.classList.add('flex');
        requestAnimationFrame(function () { modal.classList.add('is-on'); });
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.classList.remove('is-on');
        setTimeout(function () {
            modal.classList.add('hidden'); modal.classList.remove('flex');
            document.body.style.overflow = '';
        }, 250);
    }
    if (closeBtn)  closeBtn.addEventListener('click', closeModal);
    if (continueA) {
        continueA.addEventListener('click', function (e) {
            try {
                var target = new URL(continueA.href, window.location.origin);
                if (target.pathname === window.location.pathname) { e.preventDefault(); closeModal(); }
            } catch (_) {}
        });
    }
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
    requestAnimationFrame(openModal);
})();
</script>
<?php endif; ?>

</body>
</html>
