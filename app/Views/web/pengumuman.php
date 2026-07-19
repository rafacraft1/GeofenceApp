<?php

/**
 * @var string $title
 * @var array<int, array<string, mixed>> $pengumuman
 * @var string|null $pager_links
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    /* CSS Spinner Loading untuk Tombol Submit */
    .btn-loading {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }
    .btn-loading::after {
        content: '';
        position: absolute;
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
        left: calc(50% - 0.625rem);
        top: calc(50% - 0.625rem);
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- KOLOM KIRI: FORM BROADCAST -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-bullhorn text-sm"></i>
                </div>
                Buat Broadcast
            </h3>
            <p class="text-[11px] font-medium text-gray-500 mb-6 pl-10">Pesan akan dikirim via notifikasi *Push* (FCM) ke semua HP siswa yang terdaftar.</p>

            <form action="<?= base_url('admin/pengumuman/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4" id="formPengumuman">
                <?= csrf_field() ?>

                <!-- Input Tipe -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Tipe Pesan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-tag"></i>
                        </div>
                        <select name="tipe" required class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-10 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer bg-gray-50 hover:bg-white text-gray-700 appearance-none font-medium">
                            <option value="Info">ℹ️ Informasi Umum</option>
                            <option value="Penting">⚠️ Sangat Penting (Urgent)</option>
                            <option value="Libur">🏖️ Info Libur / Acara</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Input Judul -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Subjek / Judul</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-heading"></i>
                        </div>
                        <input type="text" name="judul" required placeholder="Contoh: Rapat Wali Murid" class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-gray-800 bg-gray-50 hover:bg-white placeholder-gray-400">
                    </div>
                </div>

                <!-- Input Isi Pesan -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Detail Pesan</label>
                    <div class="relative">
                        <div class="absolute top-3.5 left-0 pl-3.5 flex items-start pointer-events-none text-gray-400">
                            <i class="fas fa-align-left mt-0.5"></i>
                        </div>
                        <textarea name="isi" required rows="5" placeholder="Ketik rincian pengumuman yang jelas di sini..." class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-700 resize-none bg-gray-50 hover:bg-white placeholder-gray-400 custom-scrollbar"></textarea>
                    </div>
                </div>

                <!-- Input Lampiran -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Lampiran (Opsional)</label>
                    <div class="relative bg-gray-50 border border-gray-200 border-dashed rounded-xl p-2 hover:border-blue-400 hover:bg-blue-50/30 transition-colors group">
                        <input type="file" name="gambar" id="inputFile" accept="image/jpeg,image/png,image/jpg,application/pdf" class="w-full text-sm outline-none transition-all file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-600 hover:file:text-white cursor-pointer file:cursor-pointer text-gray-500">
                        <p class="text-[9px] text-gray-400 mt-2 px-1 leading-relaxed">
                            Mendukung <strong class="text-gray-500">JPG/PNG</strong> (Otomatis dikompres) & <strong class="text-gray-500">PDF</strong> (Maks 2MB).
                        </p>
                    </div>

                    <!-- UI Preview Lampiran Gambar -->
                    <div id="imagePreviewContainer" class="hidden mt-3 rounded-2xl overflow-hidden border border-gray-100 shadow-sm relative group bg-black">
                        <img id="imagePreview" src="" alt="Preview Lampiran" class="w-full h-auto object-cover max-h-48 opacity-90 group-hover:opacity-100 transition-opacity">
                        <div class="absolute top-2 right-2 bg-gray-900/80 text-white text-[10px] font-bold px-2.5 py-1 rounded-md backdrop-blur-md flex items-center gap-1.5 border border-white/10 shadow-lg">
                            <i class="fas fa-compress-arrows-alt text-emerald-400"></i> <span id="imageSizeInfo"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-indigo-700 hover:-translate-y-0.5 active:scale-[0.98] transition-all btn-submit flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Siarkan Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KOLOM KANAN: RIWAYAT / LOG -->
    <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-6 pl-2 lg:pl-0">
            <h2 class="text-xl font-black text-gray-800 tracking-tight"><i class="fas fa-history text-gray-400 mr-2"></i> Log Broadcast</h2>
            <span class="bg-indigo-50 border border-indigo-100 text-indigo-700 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm">
                <?= count($pengumuman) ?> Pesan
            </span>
        </div>

        <div class="space-y-4 h-[calc(100vh-190px)] overflow-y-auto pr-2 custom-scrollbar pb-10" id="riwayatContainer">
            <?php if (empty($pengumuman)): ?>
                <div class="bg-gray-50/50 border-2 border-dashed border-gray-200 rounded-3xl p-12 flex flex-col items-center justify-center text-center h-64">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-gray-300 shadow-sm mb-4">
                        <i class="fas fa-bullhorn text-4xl"></i>
                    </div>
                    <h3 class="text-gray-600 font-bold text-lg mb-1">Riwayat Kosong</h3>
                    <p class="text-xs text-gray-400 max-w-sm leading-relaxed">Broadcast yang Anda kirimkan ke siswa akan tercatat dan ditampilkan di halaman ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pengumuman as $p): ?>
                    <?php
                    // Konfigurasi visual berdasarkan tipe
                    $tipeConfig = [
                        'Info'    => ['color' => 'blue',   'icon' => 'fa-info-circle', 'bg' => 'bg-gradient-to-br from-white to-blue-50/30'],
                        'Penting' => ['color' => 'red',    'icon' => 'fa-exclamation-triangle', 'bg' => 'bg-gradient-to-br from-white to-red-50/30'],
                        'Libur'   => ['color' => 'emerald', 'icon' => 'fa-calendar-day', 'bg' => 'bg-gradient-to-br from-white to-emerald-50/30']
                    ];
                    $t = $tipeConfig[$p['tipe']] ?? $tipeConfig['Info'];
                    ?>

                    <div class="rounded-2xl shadow-sm border border-gray-100 <?= $t['bg'] ?> p-5 relative group hover:shadow-md hover:border-<?= $t['color'] ?>-200 transition-all flex flex-col sm:flex-row gap-4">
                        
                        <!-- Avatar Ikon -->
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-<?= $t['color'] ?>-100 flex items-center justify-center text-<?= $t['color'] ?>-600 shadow-inner">
                            <i class="fas <?= $t['icon'] ?> text-xl"></i>
                        </div>

                        <!-- Konten Pesan -->
                        <div class="flex-1 min-w-0 pr-0 sm:pr-12">
                            <div class="flex items-center gap-2.5 mb-1.5 flex-wrap">
                                <span class="bg-<?= $t['color'] ?>-50 border border-<?= $t['color'] ?>-200 text-<?= $t['color'] ?>-700 px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest shadow-sm">
                                    <?= esc((string) $p['tipe']) ?>
                                </span>
                                <span class="text-[10px] font-bold text-gray-400 flex items-center gap-1">
                                    <i class="far fa-clock"></i> <?= date('d M Y • H:i', strtotime((string) $p['created_at'])) ?>
                                </span>
                            </div>
                            <h4 class="text-base font-bold text-gray-800 mb-2.5 break-words leading-snug group-hover:text-blue-700 transition-colors"><?= esc((string) $p['judul']) ?></h4>

                            <!-- Lampiran PDF / Gambar -->
                            <?php if (!empty($p['gambar'])): ?>
                                <?php $ext = strtolower(pathinfo((string)$p['gambar'], PATHINFO_EXTENSION)); ?>
                                <?php if ($ext === 'pdf'): ?>
                                    <div class="mb-4">
                                        <a href="<?= base_url('uploads/pengumuman/' . esc((string) $p['gambar'])) ?>" target="_blank" class="inline-flex items-center gap-3.5 bg-red-50 text-red-600 px-5 py-3 rounded-xl border border-red-100 hover:bg-red-500 hover:text-white transition-all text-sm font-bold w-full sm:w-auto shadow-sm hover:shadow-red-500/30 group/pdf">
                                            <i class="fas fa-file-pdf text-2xl group-hover/pdf:-translate-y-0.5 transition-transform"></i>
                                            <div class="flex flex-col text-left">
                                                <span>Lampiran PDF</span>
                                                <span class="text-[9px] font-semibold text-red-400 group-hover/pdf:text-red-200 uppercase tracking-widest">Buka Dokumen &rarr;</span>
                                            </div>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-4 rounded-xl border border-gray-200 bg-gray-50 overflow-hidden shadow-sm inline-block max-w-full sm:max-w-sm">
                                        <img src="<?= base_url('uploads/pengumuman/' . esc((string) $p['gambar'])) ?>" alt="Lampiran Pengumuman" class="w-full max-h-56 object-cover hover:opacity-90 transition-opacity cursor-pointer zoom-img" onclick="window.open(this.src, '_blank')">
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap font-medium"><?= esc((string) $p['isi']) ?></p>
                        </div>

                        <!-- Tombol Hapus: Fixed UX Mobile -->
                        <form action="<?= base_url('admin/pengumuman/delete/' . (string) $p['id_pengumuman']) ?>" method="POST" class="absolute top-4 right-4">
                            <?= csrf_field() ?>
                            <!-- Terlihat sedikit pudar secara default (opacity-40), tidak butuh hover khusus mobile -->
                            <button type="button" class="btn-confirm p-2.5 bg-white text-gray-400 opacity-60 hover:opacity-100 hover:text-red-500 hover:bg-red-50 border border-gray-200 hover:border-red-200 rounded-xl shadow-sm transition-all focus:opacity-100" data-text="Tarik pengumuman ini agar terhapus dari log dan aplikasi siswa?" data-btn="Ya, Tarik Pesan" title="Tarik / Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (isset($pager_links)) : ?>
            <div class="mt-2 pt-4 border-t border-gray-100 flex justify-center">
                <?= $pager_links ?>
            </div>
        <?php endif; ?>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const form = document.getElementById('formPengumuman');
    const fileInput = document.getElementById('inputFile');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');
    const sizeInfo = document.getElementById('imageSizeInfo');

    const MAX_PDF_SIZE = 2 * 1024 * 1024; // Maks 2 MB

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];

        // Reset UI
        previewContainer.classList.add('hidden');
        previewImg.src = '';

        if (!file) return;

        // Validasi PDF
        if (file.type === 'application/pdf') {
            if (file.size > MAX_PDF_SIZE) {
                if(typeof toastr !== 'undefined') toastr.error('Ukuran file PDF tidak boleh lebih dari 2 MB.', 'File Terlalu Besar');
                else alert('Ukuran file PDF tidak boleh lebih dari 2 MB.');
                fileInput.value = '';
            }
            return;
        }

        // Kompresi Gambar
        if (file.type.startsWith('image/')) {
            const reader = new window.FileReader();

            reader.onload = function(event) {
                const img = new window.Image();
                img.src = event.target.result;

                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > MAX_WIDTH) {
                        height = Math.round((height * MAX_WIDTH) / width);
                        width = MAX_WIDTH;
                    }

                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function(blob) {
                        if (!blob) return;

                        const compressedFile = new window.File([blob], file.name.replace(/\.[^/.]+$/, ".jpg"), {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });

                        const dataTransfer = new window.DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        fileInput.files = dataTransfer.files;

                        // UI Preview
                        previewImg.src = URL.createObjectURL(blob);
                        previewContainer.classList.remove('hidden');
                        sizeInfo.innerText = (compressedFile.size / 1024).toFixed(1) + ' KB';

                        console.log(`Kompresi image sukses: ${(file.size / 1024).toFixed(1)} KB -> ${(compressedFile.size / 1024).toFixed(1)} KB`);

                    }, 'image/jpeg', 0.7);
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Loading State
    form.addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim Broadcast...`;
            btn.classList.add('btn-loading', 'cursor-not-allowed', 'opacity-90');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>