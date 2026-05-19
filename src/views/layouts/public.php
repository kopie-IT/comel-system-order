<?php
$cartCount = 0;
foreach ($_SESSION['cart'] ?? [] as $item) {
    $cartCount += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comel Baby Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; }
        .btn-primary { @apply bg-pink-500 hover:bg-pink-600 text-white font-semibold py-3 px-6 rounded-xl transition-colors duration-200; }
        .card { @apply bg-white rounded-2xl shadow-sm border border-gray-100; }
    </style>
</head>
<body class="bg-rose-50 min-h-screen">

<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2">
            <span class="text-2xl">🍼</span>
            <span class="font-bold text-pink-600 text-lg">Comel Baby Store</span>
        </a>
        <a href="/cart" class="relative p-2">
            <i class="fa-solid fa-cart-shopping text-pink-500 text-xl"></i>
            <?php if ($cartCount > 0): ?>
            <span class="absolute -top-1 -right-1 bg-pink-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                <?= $cartCount ?>
            </span>
            <?php endif; ?>
        </a>
    </div>
</header>

<!-- Flash messages -->
<?php $success = flash('success'); $error = flash('error'); ?>
<?php if ($success): ?>
<div class="max-w-lg mx-auto px-4 pt-3">
    <div class="bg-green-100 border border-green-300 text-green-800 rounded-xl px-4 py-3 text-sm">
        <i class="fa-solid fa-circle-check mr-2"></i><?= e($success) ?>
    </div>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="max-w-lg mx-auto px-4 pt-3">
    <div class="bg-red-100 border border-red-300 text-red-800 rounded-xl px-4 py-3 text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2"></i><?= e($error) ?>
    </div>
</div>
<?php endif; ?>

<!-- Main content -->
<main class="max-w-lg mx-auto px-4 py-4 pb-20">
    <?php require $viewFile; ?>
</main>

<!-- Footer -->
<footer class="text-center text-xs text-gray-400 py-6">
    &copy; <?= date('Y') ?> Comel Baby Store
</footer>

</body>
</html>
