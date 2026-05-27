<?php $pageTitle = 'AI Assistant'; ?>
<div class="py-2 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <!-- Solid back button -->
            <?php if ($order): ?>
            <a href="/admin/orders/<?= $order['id'] ?>"
               class="inline-flex items-center gap-2 bg-purple-500 hover:bg-purple-600 active:bg-purple-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <?php else: ?>
            <a href="/admin/dashboard"
               class="inline-flex items-center gap-2 bg-purple-500 hover:bg-purple-600 active:bg-purple-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <?php endif; ?>
            <div>
                <h2 class="text-lg font-bold text-gray-800">AI Assistant</h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    <?php if ($order): ?>
                        Konteks: Pesanan <strong class="text-purple-600"><?= e($order['order_number']) ?></strong>
                        — <?= e($order['customer_name']) ?>
                    <?php else: ?>
                        Tanya soalan umum atau pilih pesanan.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Order context card -->
    <?php if ($order): ?>
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4 mb-4 text-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <p class="text-xs text-purple-400 mb-0.5">No. Pesanan</p>
                <p class="font-bold text-purple-700"><?= e($order['order_number']) ?></p>
            </div>
            <div>
                <p class="text-xs text-purple-400 mb-0.5">Pelanggan</p>
                <p class="font-semibold text-gray-700"><?= e($order['customer_name']) ?></p>
            </div>
            <div>
                <p class="text-xs text-purple-400 mb-0.5">Status</p>
                <?php
                $statusLabel = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
                $statusColor = ['pending'=>'text-yellow-600','confirmed'=>'text-blue-600','completed'=>'text-green-600','cancelled'=>'text-red-600'];
                ?>
                <p class="font-semibold <?= $statusColor[$order['status']] ?? 'text-gray-600' ?>">
                    <?= $statusLabel[$order['status']] ?? $order['status'] ?>
                </p>
            </div>
            <div>
                <p class="text-xs text-purple-400 mb-0.5">Jumlah</p>
                <p class="font-bold text-pink-600">RM<?= number_format($order['total'], 2) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Chat window -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col" style="height: calc(100vh - 300px); min-height: 420px;">

        <!-- Messages -->
        <div id="ai-chat-messages" class="flex-1 overflow-y-auto px-4 md:px-5 py-4 space-y-4 bg-gray-50">
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-robot text-purple-500 text-sm"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-none px-4 py-3 text-sm text-gray-700 shadow-sm max-w-lg">
                    <?php if ($order): ?>
                        Salam! Saya sedia membantu anda dengan pesanan <strong><?= e($order['order_number']) ?></strong>.<br><br>
                        Pelanggan: <strong><?= e($order['customer_name']) ?></strong> (<?= e($order['customer_phone']) ?>)<br>
                        Jumlah: <strong>RM<?= number_format($order['total'], 2) ?></strong><br><br>
                        Anda boleh lampirkan <strong>slip kurier, resit, gambar, atau dokumen</strong> untuk saya analisa.
                    <?php else: ?>
                        Salam! Saya pembantu AI <?= e(app_name()) ?>.<br><br>
                        Anda boleh:<br>
                        • Tanya soalan tentang pesanan<br>
                        • Lampirkan slip kurier atau resit untuk dianalisa<br>
                        • Muat naik gambar produk untuk semakan<br><br>
                        <span class="text-xs text-gray-400">Tip: Buka AI dari halaman pesanan untuk konteks automatik.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick prompts -->
        <div class="px-4 py-2 border-t bg-white flex gap-2 overflow-x-auto shrink-0">
            <?php
            $prompts = $order ? [
                'Cadangkan tindakan seterusnya',
                'Draf mesej WhatsApp ke pelanggan',
                'Ringkaskan pesanan ini',
                'Analisa slip kurier yang dilampirkan',
            ] : [
                'Bagaimana nak proses pesanan baru?',
                'Cara kemaskini status pesanan',
                'Tips pengurusan stok',
                'Analisa dokumen yang dilampirkan',
            ];
            foreach ($prompts as $p):
            ?>
            <button onclick="sendQuickPrompt(this)" data-prompt="<?= e($p) ?>"
                class="shrink-0 text-xs bg-purple-50 hover:bg-purple-100 text-purple-600 font-semibold px-3 py-1.5 rounded-full transition-colors whitespace-nowrap">
                <?= e($p) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- File preview -->
        <div id="file-preview-bar" class="hidden px-4 py-2 border-t bg-yellow-50 flex items-center gap-3 shrink-0">
            <div id="file-preview-thumb" class="shrink-0"></div>
            <div class="flex-1 min-w-0">
                <p id="file-preview-name" class="text-xs font-semibold text-gray-700 truncate"></p>
                <p id="file-preview-size" class="text-xs text-gray-400"></p>
            </div>
            <button onclick="clearFilePreview()" class="shrink-0 text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Input -->
        <div class="px-4 py-3 border-t bg-white flex gap-2 shrink-0 items-end">
            <!-- Hidden file input -->
            <input type="file" id="ai-file-input"
                accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,text/plain,text/csv"
                class="hidden" onchange="handleFileSelect(this)">

            <!-- Attach button -->
            <button onclick="document.getElementById('ai-file-input').click()"
                id="ai-attach-btn"
                title="Lampirkan fail (imej, PDF, teks)"
                class="shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-purple-100 text-gray-400 hover:text-purple-500 transition-colors">
                <i class="fa-solid fa-paperclip"></i>
            </button>

            <textarea id="ai-chat-input" rows="1"
                placeholder="Taip soalan atau lampirkan fail..."
                class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-purple-400 resize-none overflow-hidden"
                style="min-height:42px; max-height:120px;"
                oninput="autoResize(this)"
                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendAiMessage();}"></textarea>

            <button onclick="sendAiMessage()" id="ai-send-btn"
                class="shrink-0 w-10 h-10 bg-purple-500 hover:bg-purple-600 text-white font-bold rounded-xl text-sm transition-colors flex items-center justify-center">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <p class="text-xs text-gray-400 text-center mt-2">
        <i class="fa-solid fa-paperclip mr-1"></i>Sokong: Imej (JPEG/PNG/WebP), PDF, Teks, CSV — maks 10MB
    </p>
</div>

<script>
const ORDER_ID = <?= $order ? $order['id'] : 0 ?>;

function sendQuickPrompt(btn) {
    document.getElementById('ai-chat-input').value = btn.dataset.prompt;
    sendAiMessage();
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;

    const bar      = document.getElementById('file-preview-bar');
    const nameEl   = document.getElementById('file-preview-name');
    const sizeEl   = document.getElementById('file-preview-size');
    const thumbEl  = document.getElementById('file-preview-thumb');
    const attachBtn = document.getElementById('ai-attach-btn');

    nameEl.textContent = file.name;
    sizeEl.textContent = formatBytes(file.size);
    attachBtn.classList.add('border-purple-400', 'text-purple-500');

    // Show image thumbnail if image
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            thumbEl.innerHTML = `<img src="${e.target.result}" class="w-10 h-10 rounded-lg object-cover border border-gray-200">`;
        };
        reader.readAsDataURL(file);
    } else {
        const icon = file.type === 'application/pdf' ? 'fa-file-pdf text-red-400' : 'fa-file-lines text-blue-400';
        thumbEl.innerHTML = `<div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center"><i class="fa-solid ${icon} text-lg"></i></div>`;
    }

    bar.classList.remove('hidden');
}

function clearFilePreview() {
    document.getElementById('ai-file-input').value = '';
    document.getElementById('file-preview-bar').classList.add('hidden');
    document.getElementById('file-preview-thumb').innerHTML = '';
    document.getElementById('ai-attach-btn').classList.remove('border-purple-400', 'text-purple-500');
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function sendAiMessage() {
    const input    = document.getElementById('ai-chat-input');
    const fileInput = document.getElementById('ai-file-input');
    const msg      = input.value.trim();
    const file     = fileInput.files[0];

    if (!msg && !file) return;

    // Show user message in chat
    if (msg && file) {
        appendMessage('user', msg, file.name, file.type);
    } else if (msg) {
        appendMessage('user', msg);
    } else {
        appendMessage('user', '', file.name, file.type);
    }

    input.value = '';
    input.style.height = 'auto';
    clearFilePreview();

    const btn = document.getElementById('ai-send-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    const typingId = appendTyping();

    const formData = new FormData();
    formData.append('message', msg);
    formData.append('order_id', ORDER_ID);
    if (file) formData.append('file', file);

    fetch('/admin/ai/chat', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        removeTyping(typingId);
        if (data.reply) appendMessage('ai', data.reply);
        if (data.action) {
            appendActionResult(data.action);
            const orderActions = ['update_order_status','update_order_tracking','update_order_address'];
            if (data.action.success && orderActions.includes(data.action.type)) {
                setTimeout(() => location.reload(), 2500);
            }
        }
        if (!data.reply && !data.action) appendMessage('ai', data.error || 'Tiada respons.');
    })
    .catch(() => {
        removeTyping(typingId);
        appendMessage('ai', 'Ralat sambungan. Sila cuba lagi.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
    });
}

function appendMessage(role, text, fileName = null, fileType = null) {
    const container = document.getElementById('ai-chat-messages');
    const isUser    = role === 'user';
    const div       = document.createElement('div');
    div.className   = 'flex gap-3' + (isUser ? ' justify-end' : '');

    let fileHtml = '';
    if (fileName) {
        if (fileType && fileType.startsWith('image/')) {
            fileHtml = `<div class="mt-1 text-xs bg-white/20 rounded-lg px-2 py-1"><i class="fa-solid fa-image mr-1"></i>${escHtml(fileName)}</div>`;
        } else {
            const icon = fileType === 'application/pdf' ? 'fa-file-pdf' : 'fa-file-lines';
            fileHtml = `<div class="mt-1 text-xs bg-white/20 rounded-lg px-2 py-1"><i class="fa-solid ${icon} mr-1"></i>${escHtml(fileName)}</div>`;
        }
    }

    div.innerHTML = isUser
        ? `<div class="bg-purple-500 rounded-2xl rounded-tr-none px-4 py-3 text-sm text-white max-w-lg">${text ? escHtml(text) : ''}${fileHtml}</div>`
        : `<div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0 mt-1">
               <i class="fa-solid fa-robot text-purple-500 text-sm"></i>
           </div>
           <div class="bg-white rounded-2xl rounded-tl-none px-4 py-3 text-sm text-gray-700 shadow-sm max-w-2xl overflow-x-auto"><div class="ai-response">${typeof marked !== 'undefined' ? marked.parse(text) : escHtml(text)}</div></div>`;

    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function appendActionResult(action) {
    const container = document.getElementById('ai-chat-messages');
    const div = document.createElement('div');
    div.className = 'flex justify-center my-1';
    div.innerHTML = `<div class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-semibold border ${action.success ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'}">
        <i class="fa-solid ${action.success ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
        ${escHtml(action.message)}
    </div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function appendTyping() {
    const container = document.getElementById('ai-chat-messages');
    const id        = 'typing-' + Date.now();
    const div       = document.createElement('div');
    div.id          = id;
    div.className   = 'flex gap-3';
    div.innerHTML   = `<div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                           <i class="fa-solid fa-robot text-purple-500 text-sm"></i>
                       </div>
                       <div class="bg-white rounded-2xl rounded-tl-none px-4 py-3 text-sm text-gray-400 shadow-sm">
                           <i class="fa-solid fa-ellipsis fa-beat"></i> Sedang memproses...
                       </div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    return id;
}

function removeTyping(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
