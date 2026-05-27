<?php $pageTitle = 'Backup Sistem'; ?>
<div class="py-2 max-w-2xl">
    <h2 class="text-lg font-bold text-gray-800 mb-1">Backup Sistem</h2>
    <p class="text-sm text-gray-400 mb-6">Muat turun backup pangkalan data atau fail upload. Simpan backup di tempat yang selamat.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- DB Backup -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-database text-blue-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-base">Backup Pangkalan Data</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Export semua jadual & data sebagai fail .sql</p>
                </div>
            </div>
            <ul class="text-xs text-gray-500 space-y-1 pl-1">
                <li><i class="fa-solid fa-circle-check text-green-400 mr-1"></i> Semua jadual (orders, products, customers, dll)</li>
                <li><i class="fa-solid fa-circle-check text-green-400 mr-1"></i> Semua data & tetapan</li>
                <li><i class="fa-solid fa-circle-check text-green-400 mr-1"></i> Boleh import semula di phpMyAdmin</li>
            </ul>
            <form method="POST" action="/admin/backup/db" class="mt-auto">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white font-bold py-2.5 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-download"></i> Muat Turun SQL
                </button>
            </form>
        </div>

        <!-- Files Backup -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-folder-open text-orange-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-base">Backup Fail Upload</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Zip semua gambar produk, slip kurier & QR</p>
                </div>
            </div>
            <ul class="text-xs text-gray-500 space-y-1 pl-1">
                <li><i class="fa-solid fa-circle-check text-green-400 mr-1"></i> Gambar produk</li>
                <li><i class="fa-solid fa-circle-check text-green-400 mr-1"></i> Slip kurier yang diupload</li>
                <li><i class="fa-solid fa-circle-check text-green-400 mr-1"></i> Imej QR code pembayaran</li>
            </ul>
            <form method="POST" action="/admin/backup/files" class="mt-auto">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold py-2.5 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-zipper"></i> Muat Turun ZIP
                </button>
            </form>
        </div>

    </div>

    <!-- Info box -->
    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex gap-3">
        <i class="fa-solid fa-circle-info text-amber-500 mt-0.5 shrink-0"></i>
        <div class="text-xs text-amber-700 space-y-1">
            <p class="font-semibold">Panduan Backup</p>
            <p>Lakukan backup secara berkala, terutama sebelum membuat sebarang perubahan besar pada sistem.</p>
            <p>Untuk memulihkan DB: buka phpMyAdmin → pilih database → klik <strong>Import</strong> → pilih fail .sql yang dimuat turun.</p>
            <p>Untuk memulihkan fail: ekstrak ZIP ke folder <code class="bg-amber-100 px-1 rounded">public/uploads/</code> di pelayan.</p>
        </div>
    </div>
</div>
