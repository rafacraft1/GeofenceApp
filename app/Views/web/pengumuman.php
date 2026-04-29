<?php

/**
 * @var object{id: int|string, judul: string, isi: string, tipe: string, created_at: string}[] $pengumuman
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Kolom Kiri: Form Buat Pengumuman -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Buat Broadcast Baru</h3>
            <p class="text-xs text-gray-500 mb-6">Pesan akan langsung muncul di aplikasi siswa.</p>

            <form action="/admin/pengumuman/store" method="POST" class="space-y-4" id="formPengumuman">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tipe Pengumuman</label>
                    <select name="tipe" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer bg-gray-50">
                        <option value="Info">ℹ️ Informasi Umum</option>
                        <option value="Penting">⚠️ Sangat Penting</option>
                        <option value="Libur">🏖️ Info Libur / Event</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Judul</label>
                    <input type="text" name="judul" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Contoh: Libur Nasional">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Isi Pesan</label>
                    <textarea name="isi" required rows="5" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none" placeholder="Ketik detail pengumuman di sini..."></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 btn-submit transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 transform -rotate-45 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Kirim Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kolom Kanan: Riwayat Pengumuman -->
    <div class="lg:col-span-2 space-y-4">
        <h3 class="text-lg font-bold text-gray-800 mb-4 px-1">Riwayat Broadcast</h3>

        <?php if (empty($pengumuman)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <p class="text-gray-500 font-medium">Belum ada pengumuman yang disiarkan.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pengumuman as $p): ?>
                <?php
                // Tentukan warna berdasarkan tipe
                $bg_color = 'bg-blue-50';
                $text_color = 'text-blue-600';
                $icon = 'ℹ️';
                if ($p->tipe == 'Penting') {
                    $bg_color = 'bg-red-50';
                    $text_color = 'text-red-600';
                    $icon = '⚠️';
                }
                if ($p->tipe == 'Libur') {
                    $bg_color = 'bg-emerald-50';
                    $text_color = 'text-emerald-600';
                    $icon = '🏖️';
                }
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex gap-4 hover:shadow-md transition-shadow relative group">
                    <div class="w-12 h-12 rounded-full <?= $bg_color ?> <?= $text_color ?> flex items-center justify-center text-xl shrink-0">
                        <?= $icon ?>
                    </div>
                    <div class="flex-1 pb-1">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-gray-800 text-base"><?= esc($p->judul) ?></h4>
                            <span class="text-[10px] text-gray-400 font-semibold whitespace-nowrap"><?= date('d M Y, H:i', strtotime($p->created_at)) ?></span>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold <?= $bg_color ?> <?= $text_color ?> mb-2">Kategori: <?= esc($p->tipe) ?></span>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap"><?= esc($p->isi) ?></p>
                    </div>

                    <!-- Tombol Hapus (Muncul saat di-hover) -->
                    <form action="/admin/pengumuman/delete/<?= esc($p->id) ?>" method="POST" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="submit" class="btn-confirm p-2 bg-white text-gray-400 hover:text-red-500 hover:bg-red-50 border border-gray-200 rounded-lg shadow-sm" data-text="Pengumuman ini akan ditarik dari aplikasi siswa." title="Tarik / Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    $('#formPengumuman').on('submit', function() {
        $(this).find('.btn-submit').addClass('btn-loading');
    });
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>