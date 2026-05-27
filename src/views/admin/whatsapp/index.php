<?php $pageTitle = 'Template & Log WhatsApp'; ?>

<div class="py-2">

    <!-- Page header -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-700">
            <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> Template & Log WhatsApp
        </h2>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-paper-plane text-green-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Berjaya Dihantar</p>
                <p class="text-xl font-bold text-gray-800"><?= number_format($stats['sent']) ?></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-xmark text-red-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Gagal</p>
                <p class="text-xl font-bold text-gray-800"><?= number_format($stats['failed']) ?></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 col-span-2 md:col-span-1">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-layer-group text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Jumlah Template</p>
                <p class="text-xl font-bold text-gray-800"><?= count($templates) ?></p>
            </div>
        </div>
    </div>

    <!-- Templates section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
        <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-file-lines text-pink-400"></i> Template Mesej
        </h3>
        <p class="text-xs text-gray-400 mb-4">Edit teks mesej yang dihantar secara automatik kepada pelanggan. Gunakan placeholder yang disenaraikan dalam setiap template.</p>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Template</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Slug</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Dikemaskini</th>
                        <th class="py-2 px-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($templates as $tpl): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-3">
                            <p class="font-semibold text-gray-800"><?= e($tpl['name']) ?></p>
                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-2 max-w-xs"><?= e(mb_substr($tpl['body'], 0, 80)) ?>...</p>
                        </td>
                        <td class="py-3 px-3">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded-lg"><?= e($tpl['slug']) ?></span>
                        </td>
                        <td class="py-3 px-3 text-xs text-gray-400">
                            <?= date('d M Y, H:i', strtotime($tpl['updated_at'])) ?>
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="/admin/whatsapp/edit/<?= $tpl['id'] ?>"
                               class="inline-flex items-center gap-1.5 bg-pink-500 hover:bg-pink-600 text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-colors">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden space-y-3">
            <?php foreach ($templates as $tpl): ?>
            <div class="border border-gray-100 rounded-xl p-4">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm"><?= e($tpl['name']) ?></p>
                        <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-0.5 rounded-lg mt-1 inline-block"><?= e($tpl['slug']) ?></span>
                    </div>
                    <a href="/admin/whatsapp/edit/<?= $tpl['id'] ?>"
                       class="shrink-0 inline-flex items-center gap-1.5 bg-pink-500 hover:bg-pink-600 text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-colors">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                </div>
                <p class="text-xs text-gray-400 line-clamp-2"><?= e(mb_substr($tpl['body'], 0, 100)) ?>...</p>
                <p class="text-xs text-gray-300 mt-2"><?= date('d M Y, H:i', strtotime($tpl['updated_at'])) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Blast section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
        <h3 class="font-bold text-gray-700 mb-1 flex items-center gap-2">
            <i class="fa-solid fa-bullhorn text-green-500"></i> Hantar Blast WhatsApp
        </h3>
        <p class="text-xs text-gray-400 mb-4">Hantar mesej kepada semua pelanggan yang pernah membuat pesanan.</p>

        <form method="POST" action="/admin/whatsapp/blast" id="blast-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="use_template" id="use_template_input" value="0">

            <!-- Toggle: template vs custom -->
            <div class="flex gap-2 mb-3">
                <button type="button" id="btn-use-template"
                    onclick="setBlastMode('template')"
                    class="text-xs font-semibold px-3 py-1.5 rounded-xl border-2 border-green-400 bg-green-50 text-green-700 transition-colors">
                    <i class="fa-solid fa-file-lines mr-1"></i> Guna Template Blast
                </button>
                <button type="button" id="btn-use-custom"
                    onclick="setBlastMode('custom')"
                    class="text-xs font-semibold px-3 py-1.5 rounded-xl border-2 border-gray-200 text-gray-500 transition-colors">
                    <i class="fa-solid fa-pen mr-1"></i> Tulis Sendiri
                </button>
            </div>

            <!-- Template preview -->
            <div id="blast-template-preview" class="mb-3">
                <?php
                $blastTpl = null;
                foreach ($templates as $t) {
                    if ($t['slug'] === 'blast') { $blastTpl = $t; break; }
                }
                ?>
                <?php if ($blastTpl): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-sm text-gray-700 whitespace-pre-wrap font-mono text-xs leading-relaxed"><?= e($blastTpl['body']) ?></div>
                <p class="text-xs text-gray-400 mt-1">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Ini adalah teks dari template "Blast Promosi". <a href="/admin/whatsapp/edit/<?= $blastTpl['id'] ?>" class="text-pink-500 underline">Edit template</a> untuk ubah kandungan.
                </p>
                <?php else: ?>
                <p class="text-xs text-red-400">Template blast tidak dijumpai.</p>
                <?php endif; ?>
            </div>

            <!-- Custom textarea -->
            <div id="blast-custom-area" class="mb-3 hidden">
                <textarea name="blast_message" id="blast_message" rows="5"
                    placeholder="Taip mesej blast anda di sini..."
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-pink-400 focus:outline-none resize-none font-mono"></textarea>
            </div>

            <button type="button"
                onclick="confirmBlast()"
                class="w-full md:w-auto bg-green-500 hover:bg-green-600 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                <i class="fa-brands fa-whatsapp"></i> Hantar Blast Sekarang
            </button>
        </form>
    </div>

    <!-- Log section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <h3 class="font-bold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-blue-400"></i> Log Mesej Dihantar
            </h3>
            <!-- Search -->
            <form method="GET" action="/admin/whatsapp" class="flex gap-2">
                <input type="text" name="search" value="<?= e($search) ?>"
                    placeholder="Cari nombor / template..."
                    class="border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:border-pink-400 focus:outline-none w-48">
                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <?php if ($search !== ''): ?>
                <a href="/admin/whatsapp" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($logData['rows'])): ?>
        <div class="text-center py-10 text-gray-400">
            <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>
            <p class="text-sm">Tiada log mesej<?= $search !== '' ? ' untuk carian "' . e($search) . '"' : '' ?>.</p>
        </div>
        <?php else: ?>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Masa</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Penerima</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Template</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Mesej</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($logData['rows'] as $log): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-3 text-xs text-gray-400 whitespace-nowrap">
                            <?= date('d M Y', strtotime($log['sent_at'])) ?><br>
                            <span class="text-gray-300"><?= date('H:i:s', strtotime($log['sent_at'])) ?></span>
                        </td>
                        <td class="py-3 px-3 font-mono text-xs text-gray-700"><?= e($log['recipient']) ?></td>
                        <td class="py-3 px-3">
                            <?php if ($log['template_slug'] !== ''): ?>
                            <span class="bg-blue-50 text-blue-600 text-xs font-mono px-2 py-0.5 rounded-lg"><?= e($log['template_slug']) ?></span>
                            <?php else: ?>
                            <span class="text-gray-300 text-xs">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3 text-xs text-gray-600 max-w-xs">
                            <span class="line-clamp-2"><?= e(mb_substr($log['message'], 0, 120)) ?><?= mb_strlen($log['message']) > 120 ? '...' : '' ?></span>
                        </td>
                        <td class="py-3 px-3">
                            <?php if ($log['status'] === 'sent'): ?>
                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle-check text-[10px]"></i> Berjaya
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-600 text-xs font-semibold px-2 py-0.5 rounded-full" title="<?= e($log['error']) ?>">
                                <i class="fa-solid fa-circle-xmark text-[10px]"></i> Gagal
                            </span>
                            <?php if ($log['error'] !== ''): ?>
                            <p class="text-[10px] text-red-400 mt-0.5"><?= e(mb_substr($log['error'], 0, 60)) ?></p>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden space-y-3">
            <?php foreach ($logData['rows'] as $log): ?>
            <div class="border border-gray-100 rounded-xl p-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-mono text-xs text-gray-700"><?= e($log['recipient']) ?></span>
                    <?php if ($log['status'] === 'sent'): ?>
                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Berjaya
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center gap-1 bg-red-100 text-red-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-circle-xmark text-[10px]"></i> Gagal
                    </span>
                    <?php endif; ?>
                </div>
                <?php if ($log['template_slug'] !== ''): ?>
                <span class="bg-blue-50 text-blue-600 text-xs font-mono px-2 py-0.5 rounded-lg inline-block mb-1"><?= e($log['template_slug']) ?></span>
                <?php endif; ?>
                <p class="text-xs text-gray-500 line-clamp-2"><?= e(mb_substr($log['message'], 0, 100)) ?>...</p>
                <p class="text-xs text-gray-300 mt-1"><?= date('d M Y, H:i', strtotime($log['sent_at'])) ?></p>
                <?php if ($log['status'] === 'failed' && $log['error'] !== ''): ?>
                <p class="text-[10px] text-red-400 mt-1"><?= e(mb_substr($log['error'], 0, 80)) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($logData['totalPages'] > 1): ?>
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400">
                Halaman <?= $logData['page'] ?> / <?= $logData['totalPages'] ?>
                &nbsp;&bull;&nbsp; <?= number_format($logData['total']) ?> rekod
            </p>
            <div class="flex gap-1">
                <?php if ($logData['page'] > 1): ?>
                <a href="?page=<?= $logData['page'] - 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                   class="px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-colors">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                <?php endif; ?>
                <?php
                $start = max(1, $logData['page'] - 2);
                $end   = min($logData['totalPages'], $logData['page'] + 2);
                for ($p = $start; $p <= $end; $p++):
                ?>
                <a href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                   class="px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors <?= $p === $logData['page'] ? 'bg-pink-500 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-600' ?>">
                    <?= $p ?>
                </a>
                <?php endfor; ?>
                <?php if ($logData['page'] < $logData['totalPages']): ?>
                <a href="?page=<?= $logData['page'] + 1 ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                   class="px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-colors">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<!-- Blast confirm modal -->
<div id="blast-confirm-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.55)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                <i class="fa-brands fa-whatsapp text-green-500 text-lg"></i>
            </div>
            <h3 class="font-bold text-gray-800 text-base">Hantar Blast?</h3>
        </div>
        <p class="text-sm text-gray-500 mb-5 pl-1">Mesej akan dihantar kepada semua pelanggan yang pernah membuat pesanan. Tindakan ini tidak boleh dibatalkan.</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeBlastModal()"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl text-sm transition-colors">
                Batal
            </button>
            <button type="button" onclick="submitBlast()"
                class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                <i class="fa-brands fa-whatsapp"></i> Ya, Hantar
            </button>
        </div>
    </div>
</div>

<script>
var blastMode = 'template';

function setBlastMode(mode) {
    blastMode = mode;
    document.getElementById('use_template_input').value = mode === 'template' ? '1' : '0';
    if (mode === 'template') {
        document.getElementById('blast-template-preview').classList.remove('hidden');
        document.getElementById('blast-custom-area').classList.add('hidden');
        document.getElementById('btn-use-template').classList.add('border-green-400', 'bg-green-50', 'text-green-700');
        document.getElementById('btn-use-template').classList.remove('border-gray-200', 'text-gray-500');
        document.getElementById('btn-use-custom').classList.remove('border-green-400', 'bg-green-50', 'text-green-700');
        document.getElementById('btn-use-custom').classList.add('border-gray-200', 'text-gray-500');
    } else {
        document.getElementById('blast-template-preview').classList.add('hidden');
        document.getElementById('blast-custom-area').classList.remove('hidden');
        document.getElementById('btn-use-custom').classList.add('border-green-400', 'bg-green-50', 'text-green-700');
        document.getElementById('btn-use-custom').classList.remove('border-gray-200', 'text-gray-500');
        document.getElementById('btn-use-template').classList.remove('border-green-400', 'bg-green-50', 'text-green-700');
        document.getElementById('btn-use-template').classList.add('border-gray-200', 'text-gray-500');
    }
}

function confirmBlast() {
    if (blastMode === 'custom') {
        var msg = document.getElementById('blast_message').value.trim();
        if (msg === '') {
            showAlertModal('Sila taip mesej blast terlebih dahulu.', 'Mesej Kosong');
            return;
        }
    }
    var m = document.getElementById('blast-confirm-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}

function closeBlastModal() {
    var m = document.getElementById('blast-confirm-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}

function submitBlast() {
    closeBlastModal();
    // Show loading overlay
    var btn = document.querySelector('#blast-form button[type="button"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghantar...'; }
    document.getElementById('blast-form').submit();
}

// Init: set template mode active
setBlastMode('template');
</script>
