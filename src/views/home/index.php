<?php
$pageTitle = app_name();
$waNumber  = (new Setting())->get('whatsapp_number', '601234567890');
$waLink    = 'https://wa.me/' . preg_replace('/\D/', '', $waNumber);
?>
<div class="py-4">

    <!-- Centered heading -->
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-1">Selamat Datang!</h2>
        <p class="text-gray-500 text-sm">Pilih kategori untuk mula membeli-belah.</p>
    </div>

    <?php if (empty($categories)): ?>
        <div class="card p-8 text-center text-gray-400">
            <i class="fa-solid fa-box-open text-4xl mb-3 block"></i>
            <p>Tiada kategori tersedia.</p>
        </div>
    <?php else: ?>

    <!-- Categories container -->
    <div class="card p-4 mb-4">
        <div class="grid grid-cols-2 gap-3">
            <?php foreach ($categories as $cat): ?>
            <a href="/category/<?= $cat['id'] ?>"
               class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow active:scale-95 flex flex-col">
                <!-- Rotating product images -->
                <div class="aspect-square bg-gray-100 relative category-gallery overflow-hidden"
                     data-images="<?= htmlspecialchars(json_encode($cat['preview_images'] ?? []), ENT_QUOTES) ?>">
                    <?php if (!empty($cat['preview_images'])): ?>
                        <img src="<?= e($cat['preview_images'][0]) ?>" alt="<?= e($cat['name']) ?>"
                             class="w-full h-full object-cover" style="transition:opacity 0.4s ease;">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center
                            <?= $cat['type'] === 'pakaian' ? 'bg-pink-100' : 'bg-blue-100' ?>">
                            <i class="fa-solid <?= $cat['type'] === 'pakaian' ? 'fa-shirt text-pink-500' : 'fa-baby text-blue-500' ?> text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Category name -->
                <div class="p-3 text-center">
                    <span class="font-semibold text-gray-700 text-sm"><?= e($cat['name']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Semak Pesanan button (hidden for now) -->
    <?php /* ?>
    <a href="/order/view"
       class="block w-full bg-pink-500 hover:bg-pink-600 active:bg-pink-700 text-white font-bold py-3.5 rounded-xl text-center transition-colors mb-4">
        <i class="fa-solid fa-receipt mr-2"></i> Semak Pesanan Saya
    </a>
    <?php */ ?>

    <?php endif; ?>

    <!-- Social media container -->
    <div class="card p-4 mb-3">
        <div class="flex items-center gap-2 mb-1.5">
            <i class="fa-solid fa-star text-yellow-400"></i>
            <p class="text-sm font-semibold text-gray-700">Lihat Ulasan & Produk Terkini</p>
        </div>
        <p class="text-xs text-gray-500 mb-3">
            Ikuti kami di media sosial untuk ulasan pelanggan, gambar produk terkini dan promosi istimewa!
        </p>
        <div class="flex gap-2">
            <a href="https://www.facebook.com/comel.kedai.1" target="_blank" rel="noopener noreferrer"
               class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold py-2.5 rounded-xl transition-colors">
                <i class="fa-brands fa-facebook text-sm"></i> Facebook
            </a>
            <a href="https://www.threads.com/@comelbabycare" target="_blank" rel="noopener noreferrer"
               class="flex-1 flex items-center justify-center gap-2 bg-gray-900 hover:bg-black active:bg-gray-800 text-white text-xs font-semibold py-2.5 rounded-xl transition-colors">
                <i class="fa-brands fa-threads text-sm"></i> Threads
            </a>
        </div>
    </div>

    <!-- WhatsApp container -->
    <a href="<?= $waLink ?>?text=<?= rawurlencode('Salam! Saya ada pertanyaan tentang produk ' . app_name() . '. Boleh bantu saya?') ?>"
       target="_blank" rel="noopener noreferrer"
       class="card p-4 flex items-center gap-3 hover:shadow-md transition-shadow active:scale-95 block">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shrink-0">
            <i class="fa-brands fa-whatsapp text-green-500 text-xl"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-700">Ada soalan? Tanya Kami</p>
            <p class="text-xs text-gray-400">WhatsApp kami untuk pertanyaan produk</p>
        </div>
        <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
    </a>

</div>

<script>
document.querySelectorAll('.category-gallery').forEach(function (wrapper) {
    var images = JSON.parse(wrapper.dataset.images || '[]');
    if (images.length <= 1) return;
    var img = wrapper.querySelector('img');
    if (!img) return;
    var current = 0;
    setInterval(function () {
        img.style.opacity = '0';
        setTimeout(function () {
            current = (current + 1) % images.length;
            img.src = images[current];
            img.style.opacity = '1';
        }, 400);
    }, 2500);
});
</script>
