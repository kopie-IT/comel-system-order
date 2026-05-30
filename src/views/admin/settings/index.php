<?php $pageTitle = 'Tetapan'; ?>
<div class="py-2 max-w-lg">
    <h2 class="text-lg font-bold text-gray-700 mb-5">Tetapan Sistem</h2>

    <form method="POST" action="/admin/settings" enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5 mb-6">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- App name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fa-solid fa-store text-pink-500 mr-1"></i> Nama Aplikasi
            </label>
            <input type="text" name="app_name"
                   value="<?= e($settings['app_name'] ?? 'Comel Baby Store') ?>"
                   placeholder="Contoh: Comel Baby Store"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            <p class="text-xs text-gray-400 mt-1">Nama ini akan dipaparkan pada tajuk halaman, header, footer dan mesej WhatsApp.</p>
        </div>

        <!-- Base URL -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fa-solid fa-globe text-blue-500 mr-1"></i> Base URL
            </label>
            <input type="text" name="base_url"
                   value="<?= e($settings['base_url'] ?? '') ?>"
                   placeholder="Contoh: https://yourdomain.com"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            <p class="text-xs text-gray-400 mt-1">URL asas sistem untuk pautan dalam mesej WhatsApp. Kosongkan untuk auto-detect dari server.</p>
        </div>

        <!-- WhatsApp number -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> Nombor WhatsApp Admin
            </label>
            <input type="text" name="whatsapp_number"
                   value="<?= e($settings['whatsapp_number'] ?? '') ?>"
                   placeholder="Contoh: 60123456789"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
            <p class="text-xs text-gray-400 mt-1">Format antarabangsa tanpa '+'. Contoh: 60123456789</p>
        </div>

        <!-- QR code upload -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fa-solid fa-qrcode text-gray-500 mr-1"></i> Gambar QR Code Bayaran
            </label>
            <?php if (!empty($settings['qr_code_image'])): ?>
                <div class="mb-3">
                    <p class="text-xs text-gray-400 mb-1">QR Code semasa:</p>
                    <img src="<?= e($settings['qr_code_image']) ?>" alt="QR Code"
                         class="w-40 h-40 object-contain border rounded-xl bg-gray-50 p-2">
                </div>
            <?php endif; ?>
            <input type="file" name="qr_code_image" accept="image/jpeg,image/png"
                class="block text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0
                       file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-600 hover:file:bg-pink-100">
            <p class="text-xs text-gray-400 mt-1">JPEG atau PNG sahaja (WhatsApp tidak menerima WebP untuk QR). Maks 2MB. Kosongkan jika tidak mahu tukar.</p>
        </div>

        <!-- Favicon upload -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fa-solid fa-star text-yellow-500 mr-1"></i> Favicon (Ikon Website)
            </label>
            <?php if (file_exists('favicon.ico')): ?>
                <div class="mb-3">
                    <p class="text-xs text-gray-400 mb-1">Favicon semasa:</p>
                    <img src="/favicon.ico" alt="Favicon"
                         class="w-16 h-16 object-contain border rounded-lg bg-gray-50 p-2">
                </div>
            <?php endif; ?>
            <input type="file" name="favicon" accept="image/x-icon,image/png,image/jpeg,image/svg+xml"
                class="block text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0
                       file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-600 hover:file:bg-yellow-100">
            <p class="text-xs text-gray-400 mt-1">Fail .ico, PNG, JPEG atau SVG sahaja. Disyorkan: 16x16, 32x32 atau 48x48 pixel. Aplikasi akan menukar ke format .ico secara automatik.</p>
            <p class="text-xs text-gray-400 mt-1">Nota: Untuk hasil terbaik, gunakan alat dalam talian untuk menukar imej kepada format .ico seperti <a href="https://favicon.io/" target="_blank" class="text-blue-500 hover:underline">favicon.io</a></p>
        </div>

        <!-- Payment instructions -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fa-solid fa-file-lines text-gray-500 mr-1"></i> Arahan Pembayaran
            </label>
            <textarea name="payment_instructions" rows="4"
                placeholder="Masukkan arahan pembayaran untuk pelanggan..."
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 resize-none"><?= e($settings['payment_instructions'] ?? '') ?></textarea>
        </div>

        <!-- Postage fee -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fa-solid fa-truck text-gray-500 mr-1"></i> Caj Postaj Mengikut Zon (RM)
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="border-2 border-gray-200 rounded-xl p-3 bg-gray-50">
                    <p class="text-xs font-bold text-gray-600 mb-1">
                        <i class="fa-solid fa-map text-blue-500 mr-1"></i> Semenanjung Malaysia
                    </p>
                    <p class="text-xs text-gray-400 mb-2">Johor, Kedah, Kelantan, Melaka, N. Sembilan, Pahang, Perak, Perlis, P. Pinang, Selangor, Terengganu, KL, Putrajaya</p>
                    <input type="number" name="postage_fee" step="0.01" min="0"
                           value="<?= e($settings['postage_fee'] ?? '7.00') ?>"
                           placeholder="7.00"
                           class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 bg-white">
                </div>
                <div class="border-2 border-orange-200 rounded-xl p-3 bg-orange-50">
                    <p class="text-xs font-bold text-orange-700 mb-1">
                        <i class="fa-solid fa-mountain-sun text-orange-500 mr-1"></i> Sabah / Sarawak / Labuan
                    </p>
                    <p class="text-xs text-orange-500 mb-2">Caj postaj zon timur (biasanya lebih tinggi disebabkan jarak)</p>
                    <input type="number" name="postage_fee_east" step="0.01" min="0"
                           value="<?= e($settings['postage_fee_east'] ?? '12.00') ?>"
                           placeholder="12.00"
                           class="w-full border-2 border-orange-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 bg-white">
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Caj postaj akan dikira secara automatik berdasarkan negeri yang dipilih oleh pelanggan semasa checkout.</p>
        </div>

        <!-- Notification method selector -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fa-solid fa-bell text-gray-500 mr-1"></i> Kaedah Notifikasi Pesanan
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="notification_method" value="wa_link" class="sr-only notif-radio"
                           <?= ($settings['notification_method'] ?? 'wa_link') === 'wa_link' ? 'checked' : '' ?>>
                    <div class="notif-opt border-2 rounded-xl p-3 text-center transition-all
                        <?= ($settings['notification_method'] ?? 'wa_link') === 'wa_link' ? 'border-green-400 bg-green-50' : 'border-gray-200' ?>">
                        <i class="fa-brands fa-whatsapp text-green-500 text-xl block mb-1"></i>
                        <p class="text-xs font-semibold text-gray-700">WhatsApp Biasa</p>
                        <p class="text-xs text-gray-400 mt-0.5">Pautan wa.me manual</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="notification_method" value="wawp" class="sr-only notif-radio"
                           <?= ($settings['notification_method'] ?? 'wa_link') === 'wawp' ? 'checked' : '' ?>>
                    <div class="notif-opt border-2 rounded-xl p-3 text-center transition-all
                        <?= ($settings['notification_method'] ?? 'wa_link') === 'wawp' ? 'border-blue-400 bg-blue-50' : 'border-gray-200' ?>">
                        <i class="fa-solid fa-robot text-blue-500 text-xl block mb-1"></i>
                        <p class="text-xs font-semibold text-gray-700">WAWP API</p>
                        <p class="text-xs text-gray-400 mt-0.5">Auto hantar mesej</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- WAWP settings (shown only when WAWP selected) -->
        <div id="wawp-settings" class="space-y-4 <?= ($settings['notification_method'] ?? 'wa_link') !== 'wawp' ? 'hidden' : '' ?>">

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fa-solid fa-link text-gray-500 mr-1"></i> WAWP API Endpoint
            </label>
            <div class="flex gap-2 items-start">
                <input type="text" name="wawp_api_endpoint" id="wawp-endpoint"
                       value="<?= e($settings['wawp_api_endpoint'] ?? '') ?>"
                       placeholder="https://api.wawp.net/v2/send/text"
                       class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                <button type="button"
                    onclick="document.getElementById('wawp-endpoint').value='https://api.wawp.net/v2/send/text'"
                    class="shrink-0 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold px-3 py-3 rounded-xl transition-colors whitespace-nowrap">
                    Isi Default
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-1">Endpoint wawp.net untuk menghantar mesej teks. Klik <strong>Isi Default</strong> untuk menggunakan URL standard.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fa-solid fa-key text-gray-500 mr-1"></i> WAWP API Key
                </label>
                <input type="password" name="wawp_api_key"
                       value="<?= e($settings['wawp_api_key'] ?? '') ?>"
                       placeholder="API Key wawp.net"
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 font-mono">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fa-solid fa-user-check text-gray-500 mr-1"></i> WAWP Sender ID
                </label>
                <input type="text" name="wawp_sender_id"
                       value="<?= e($settings['wawp_sender_id'] ?? '') ?>"
                       placeholder="Sender ID atau instance"
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400">
                <p class="text-xs text-gray-400 mt-1">Opsional. Hanya jika penyedia memerlukan ID penghantar.</p>
            </div>
        </div>

        <!-- Test WAWP connection -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-gray-700 mb-2"><i class="fa-solid fa-flask text-blue-500 mr-1"></i> Uji Sambungan WAWP</p>
            <p class="text-xs text-gray-500 mb-3">Hantar mesej ujian ke nombor WhatsApp admin untuk mengesahkan konfigurasi berfungsi. Pastikan tetapan disimpan dahulu.</p>
            <button type="button" id="test-wawp-btn" onclick="testWawp()"
                class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <i class="fa-solid fa-paper-plane" id="test-wawp-icon"></i> Hantar Mesej Ujian
            </button>
            <div id="test-wawp-result" class="hidden mt-3 text-sm rounded-xl px-4 py-3"></div>
        </div>

        </div><!-- end wawp-settings -->

        <button type="submit"
            class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded-xl transition-colors text-sm">
            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Tetapan
        </button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fa-brands fa-whatsapp text-green-500"></i> WhatsApp Blast (WAWP)
        </h3>
        <p class="text-xs text-gray-500 mb-4">
            Hantar mesej blast ke semua pelanggan yang pernah membuat pesanan. Sistem juga akan menghantar ringkasan kepada nombor admin WhatsApp yang dikonfigurasi.
        </p>
        <form method="POST" action="/admin/settings/blast" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mesej WhatsApp</label>
                <textarea name="blast_message" rows="5"
                    placeholder="Masukkan mesej blast untuk pelanggan anda..."
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-pink-400 resize-none"></textarea>
            </div>
            <button type="submit"
                class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                <i class="fa-solid fa-paper-plane mr-2"></i> Hantar Blast WhatsApp
            </button>
        </form>
    </div>

    <!-- Auto-cancel Settings -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="font-bold text-gray-700 mb-1 flex items-center gap-2">
            <i class="fa-solid fa-clock text-red-500"></i> Auto-Batalkan Pesanan
        </h3>
        <p class="text-xs text-gray-400 mb-4">Batalkan pesanan "Tertunggak" secara automatik selepas tempoh masa yang ditetapkan jika tiada pembayaran.</p>
        <form method="POST" action="/admin/settings/auto-cancel" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="auto_cancel_enabled" value="1" id="auto-cancel-toggle"
                           class="sr-only peer"
                           <?= ($settings['auto_cancel_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                </label>
                <span class="text-sm font-semibold text-gray-700">Aktifkan Auto-Batalkan</span>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tempoh Masa (jam)</label>
                <input type="number" name="auto_cancel_hours" min="1" max="72"
                       value="<?= e($settings['auto_cancel_hours'] ?? '2') ?>"
                       class="w-32 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400">
                <p class="text-xs text-gray-400 mt-1">Pesanan akan dibatalkan selepas <strong><?= e($settings['auto_cancel_hours'] ?? '2') ?></strong> jam jika tiada pembayaran. Default: 2 jam.</p>
            </div>
            <button type="submit"
                class="bg-pink-500 hover:bg-pink-600 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan
            </button>
        </form>
    </div>

    <!-- AI Assistant Settings -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-700 mb-1 flex items-center gap-2">
            <i class="fa-solid fa-robot text-purple-500"></i> Tetapan AI Assistant
        </h3>
        <p class="text-xs text-gray-400 mb-4">Konfigurasi model AI untuk pembantu pesanan. Sokong OpenAI, Google Gemini, OpenRouter dan mana-mana API yang serasi dengan OpenAI.</p>

        <!-- Provider presets -->
        <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-2">Preset Penyedia</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="applyPreset('openai')"
                    class="preset-btn bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-xl px-3 py-2 text-xs font-semibold text-gray-600 transition-colors text-center">
                    <i class="fa-solid fa-brain text-green-500 block text-lg mb-1"></i> OpenAI
                </button>
                <button type="button" onclick="applyPreset('google')"
                    class="preset-btn bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-xl px-3 py-2 text-xs font-semibold text-gray-600 transition-colors text-center">
                    <i class="fa-brands fa-google text-blue-500 block text-lg mb-1"></i> Google Gemini
                </button>
                <button type="button" onclick="applyPreset('openrouter')"
                    class="preset-btn bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-xl px-3 py-2 text-xs font-semibold text-gray-600 transition-colors text-center">
                    <i class="fa-solid fa-route text-orange-500 block text-lg mb-1"></i> OpenRouter
                </button>
            </div>
        </div>

        <form method="POST" action="/admin/settings/ai" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Base URL</label>
                <input type="text" name="ai_base_url" id="ai_base_url"
                       value="<?= e($settings['ai_base_url'] ?? 'https://api.openai.com/v1') ?>"
                       placeholder="https://api.openai.com/v1"
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-purple-400 font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">API Key</label>
                <input type="password" name="ai_api_key" id="ai_api_key"
                       value="<?= e($settings['ai_api_key'] ?? '') ?>"
                       placeholder="sk-..."
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-purple-400 font-mono">
                <p class="text-xs text-gray-400 mt-1">API key disimpan dalam pangkalan data. Pastikan server anda selamat.</p>
            </div>

            <!-- Model field with fetch button -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold text-gray-600">Model</label>
                    <button type="button" onclick="fetchModels()"
                        id="fetch-models-btn"
                        class="inline-flex items-center gap-1.5 text-xs bg-purple-100 hover:bg-purple-200 text-purple-600 font-semibold px-3 py-1 rounded-full transition-colors">
                        <i class="fa-solid fa-rotate" id="fetch-icon"></i> Ambil Senarai Model
                    </button>
                </div>

                <!-- Manual text input -->
                <input type="text" name="ai_model" id="ai_model"
                       value="<?= e($settings['ai_model'] ?? 'gpt-4o') ?>"
                       placeholder="gpt-4o"
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-purple-400 font-mono">

                <!-- Model dropdown (hidden until fetched) -->
                <div id="model-dropdown-wrap" class="hidden mt-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs text-gray-500">
                            <i class="fa-solid fa-list mr-1"></i>
                            <span id="model-count-display"></span>
                        </span>
                        <span class="text-xs text-gray-400">Klik untuk pilih</span>
                    </div>
                    <!-- Search filter -->
                    <div class="relative mb-1.5">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        <input type="text" id="model-search"
                               placeholder="Cari model..."
                               oninput="filterModels(this.value)"
                               class="w-full border border-gray-200 rounded-lg pl-8 pr-3 py-2 text-xs focus:outline-none focus:border-purple-400 bg-gray-50">
                    </div>
                    <!-- Model list -->
                    <div id="model-list"
                         class="border-2 border-purple-200 rounded-xl overflow-y-auto bg-white divide-y divide-gray-100"
                         style="max-height: 200px;"></div>
                </div>

                <!-- Status message -->
                <p class="text-xs mt-1" id="model-hint">
                    <?= e($settings['ai_model'] ?? 'gpt-4o') !== '' ? '' : 'Masukkan Base URL dan API Key, kemudian klik Ambil Senarai Model.' ?>
                </p>
                <p id="fetch-error" class="hidden text-xs text-red-500 mt-1"></p>
            </div>

            <button type="submit"
                class="w-full bg-purple-500 hover:bg-purple-600 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Tetapan AI
            </button>
        </form>
    </div>
</div>

<script>
const presets = {
    openai: {
        url:   'https://api.openai.com/v1',
        model: 'gpt-4o',
        hint:  'Model berbayar. Contoh: gpt-4o, gpt-4o-mini, gpt-3.5-turbo'
    },
    google: {
        url:   'https://generativelanguage.googleapis.com/v1beta/openai',
        model: 'gemini-2.0-flash',
        hint:  '✅ Tier percuma tersedia. Model percuma: gemini-2.0-flash, gemini-2.0-flash-lite, gemini-1.5-flash, gemini-1.5-flash-8b'
    },
    openrouter: {
        url:   'https://openrouter.ai/api/v1',
        model: 'google/gemini-2.0-flash-exp:free',
        hint:  '✅ Model percuma tersedia (tambah :free). Contoh: google/gemini-2.0-flash-exp:free, meta-llama/llama-3.1-8b-instruct:free'
    }
};

let allModels = []; // Stores full fetched list for filtering

function applyPreset(provider) {
    const p = presets[provider];
    document.getElementById('ai_base_url').value = p.url;
    document.getElementById('ai_model').value    = p.model;
    document.getElementById('model-hint').textContent = p.hint;
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('border-purple-400', 'bg-purple-50'));
    event.currentTarget.classList.add('border-purple-400', 'bg-purple-50');
    // Reset model list when switching preset
    allModels = [];
    document.getElementById('model-dropdown-wrap').classList.add('hidden');
    document.getElementById('fetch-error').classList.add('hidden');
    document.getElementById('model-search').value = '';
}

function fetchModels() {
    const baseUrl = document.getElementById('ai_base_url').value.trim();
    const apiKey  = document.getElementById('ai_api_key').value.trim();
    const btn     = document.getElementById('fetch-models-btn');
    const icon    = document.getElementById('fetch-icon');
    const errEl   = document.getElementById('fetch-error');
    const wrap    = document.getElementById('model-dropdown-wrap');
    const hint    = document.getElementById('model-hint');

    if (!baseUrl) { showFetchError('Sila masukkan Base URL terlebih dahulu.'); return; }
    if (!apiKey)  { showFetchError('Sila masukkan API Key terlebih dahulu.'); return; }

    btn.disabled = true;
    icon.className = 'fa-solid fa-spinner fa-spin';
    errEl.classList.add('hidden');
    wrap.classList.add('hidden');
    hint.textContent = 'Sedang mengambil senarai model...';
    document.getElementById('model-search').value = '';

    const fd = new FormData();
    fd.append('base_url', baseUrl);
    fd.append('api_key',  apiKey);

    fetch('/admin/ai/models', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showFetchError(data.error); hint.textContent = ''; return; }

        const models = data.models || [];
        if (models.length === 0) { showFetchError('Tiada model dijumpai dari penyedia ini.'); hint.textContent = ''; return; }

        allModels = models;
        renderModelList(models);
        wrap.classList.remove('hidden');
        hint.textContent = models.length + ' model tersedia. Cari atau pilih dari senarai.';
    })
    .catch(() => { showFetchError('Ralat sambungan. Semak Base URL dan API Key.'); hint.textContent = ''; })
    .finally(() => { btn.disabled = false; icon.className = 'fa-solid fa-rotate'; });
}

function filterModels(query) {
    const q        = query.trim().toLowerCase();
    const filtered = q === '' ? allModels : allModels.filter(m => m.toLowerCase().includes(q));
    renderModelList(filtered);

    const countEl = document.getElementById('model-count-display');
    if (q) {
        countEl.textContent = filtered.length + ' / ' + allModels.length + ' model';
    } else {
        countEl.textContent = allModels.length + ' model dijumpai';
    }
}

function renderModelList(models) {
    const list         = document.getElementById('model-list');
    const currentModel = document.getElementById('ai_model').value.trim();
    const countEl      = document.getElementById('model-count-display');

    list.innerHTML = '';

    if (models.length === 0) {
        list.innerHTML = '<div class="px-4 py-4 text-xs text-gray-400 text-center"><i class="fa-solid fa-magnifying-glass mr-1"></i>Tiada model sepadan.</div>';
        return;
    }

    countEl.textContent = models.length + ' model dijumpai';

    models.forEach(modelId => {
        const isSelected = modelId === currentModel;
        const div        = document.createElement('div');
        div.className    = 'px-4 py-2.5 text-xs font-mono cursor-pointer hover:bg-purple-50 flex items-center justify-between gap-2 transition-colors'
            + (isSelected ? ' bg-purple-50 text-purple-700 font-semibold' : ' text-gray-700');

        // Highlight matching search text
        const q       = document.getElementById('model-search').value.trim().toLowerCase();
        let labelHtml = escHtml(modelId);
        if (q && modelId.toLowerCase().includes(q)) {
            const idx  = modelId.toLowerCase().indexOf(q);
            labelHtml  = escHtml(modelId.slice(0, idx))
                + '<mark class="bg-yellow-200 text-gray-800 rounded px-0.5">' + escHtml(modelId.slice(idx, idx + q.length)) + '</mark>'
                + escHtml(modelId.slice(idx + q.length));
        }

        div.innerHTML = `<span class="truncate">${labelHtml}</span>`
            + (isSelected ? '<i class="fa-solid fa-check text-purple-500 shrink-0"></i>' : '');
        div.onclick = () => selectModel(modelId);
        list.appendChild(div);
    });
}

function selectModel(modelId) {
    document.getElementById('ai_model').value = modelId;
    document.getElementById('model-hint').textContent = '✓ Model dipilih: ' + modelId;
    // Re-render to update highlight + checkmark
    const q        = document.getElementById('model-search').value.trim().toLowerCase();
    const filtered = q === '' ? allModels : allModels.filter(m => m.toLowerCase().includes(q));
    renderModelList(filtered);
}

function showFetchError(msg) {
    const el = document.getElementById('fetch-error');
    el.textContent = '⚠ ' + msg;
    el.classList.remove('hidden');
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function testWawp() {
    var btn    = document.getElementById('test-wawp-btn');
    var icon   = document.getElementById('test-wawp-icon');
    var result = document.getElementById('test-wawp-result');

    btn.disabled   = true;
    icon.className = 'fa-solid fa-spinner fa-spin';
    result.className = 'hidden mt-3 text-sm rounded-xl px-4 py-3';
    result.textContent = '';

    fetch('/admin/settings/test-wawp', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            result.classList.remove('hidden');
            if (data.success) {
                result.className = 'mt-3 text-sm rounded-xl px-4 py-3 bg-green-50 border border-green-200 text-green-700';
                result.innerHTML = '<i class="fa-solid fa-circle-check mr-2"></i>' + data.message;
            } else {
                result.className = 'mt-3 text-sm rounded-xl px-4 py-3 bg-red-50 border border-red-200 text-red-700';
                result.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-2"></i>' + data.message;
            }
        })
        .catch(function() {
            result.classList.remove('hidden');
            result.className = 'mt-3 text-sm rounded-xl px-4 py-3 bg-red-50 border border-red-200 text-red-700';
            result.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-2"></i>Ralat sambungan. Sila cuba lagi.';
        })
        .finally(function() {
            btn.disabled   = false;
            icon.className = 'fa-solid fa-paper-plane';
        });
}

// Notification method toggle
document.querySelectorAll('.notif-radio').forEach(function (radio) {
    radio.addEventListener('change', function () {
        var wawpSection = document.getElementById('wawp-settings');
        wawpSection.classList.toggle('hidden', radio.value !== 'wawp');
        document.querySelectorAll('.notif-opt').forEach(function (opt) {
            opt.classList.remove('border-green-400', 'bg-green-50', 'border-blue-400', 'bg-blue-50');
            opt.classList.add('border-gray-200');
        });
        var sel = radio.nextElementSibling;
        sel.classList.remove('border-gray-200');
        if (radio.value === 'wawp') {
            sel.classList.add('border-blue-400', 'bg-blue-50');
        } else {
            sel.classList.add('border-green-400', 'bg-green-50');
        }
    });
});
</script>
