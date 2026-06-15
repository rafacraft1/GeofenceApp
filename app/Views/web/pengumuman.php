<?php

/**
 * @var string $title
 * @var array<int, array<string, mixed>> $pengumuman
 * @var string|null $pager_links
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Buat Broadcast Baru</h3>
            <p class="text-xs text-gray-500 mb-6">Pesan akan langsung muncul di aplikasi siswa.</p>

            <form action="<?= base_url('admin/pengumuman/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4" id="formPengumuman">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tipe Pengumuman</label>
                    <select name="tipe" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer bg-gray-50">
                        <option value="Info">ℹ️ Informasi Umum</option>
                        <option value="Penting">⚠️ Sangat Penting</option>
                        <option value="Libur">🏖️ Info Libur / Event</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Judul / Subjek</label>
                    <input type="text" name="judul" required placeholder="Contoh: Info Libur Idul Fitri" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium text-gray-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Isi Pesan Detail</label>
                    <textarea name="isi" required rows="5" placeholder="Tuliskan pesan lengkap di sini..." class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-700 resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Lampiran (Opsional)</label>
                    <input type="file" name="gambar" id="inputFile" accept="image/jpeg,image/png,image/jpg,application/pdf" class="w-full border border-gray-200 rounded-xl p-2 text-sm focus:border-blue-500 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[10px] text-gray-400 mt-1 font-medium leading-relaxed">
                        Format: JPG, PNG, PDF.<br>
                        - <strong class="text-gray-500">Gambar:</strong> Akan otomatis dikompres sebelum dikirim.<br>
                        - <strong class="text-gray-500">PDF:</strong> Maksimal ukuran 2 MB.
                    </p>

                    <div id="imagePreviewContainer" class="hidden mt-3 rounded-xl overflow-hidden border border-gray-200 shadow-sm relative bg-gray-50">
                        <img id="imagePreview" src="" alt="Preview Lampiran" class="w-full h-auto object-cover max-h-48">
                        <div class="absolute top-2 right-2 bg-black/60 text-white text-[9px] px-2 py-1 rounded backdrop-blur-sm shadow-sm flex items-center gap-1">
                            <i class="fas fa-compress-arrows-alt"></i> <span id="imageSizeInfo"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 text-white py-3.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 active:scale-95 transition-all btn-submit flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim Broadcast Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-black text-gray-800">Riwayat Pengumuman</h2>
            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">Data Log</span>
        </div>

        <div class="space-y-4 h-[calc(100vh-220px)] overflow-y-auto pr-2" id="riwayatContainer">
            <?php if (empty($pengumuman)): ?>
                <div class="bg-white border-2 border-dashed border-gray-200 rounded-2xl p-10 flex flex-col items-center justify-center text-center">
                    <i class="fas fa-bullhorn text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-gray-500 font-bold">Belum Ada Pengumuman</h3>
                    <p class="text-xs text-gray-400 mt-1">Broadcast yang Anda kirim akan muncul di sini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pengumuman as $p): ?>
                    <?php
                    $tipeConfig = [
                        'Info'    => ['color' => 'blue',   'icon' => 'fa-info-circle'],
                        'Penting' => ['color' => 'red',    'icon' => 'fa-exclamation-triangle'],
                        'Libur'   => ['color' => 'emerald', 'icon' => 'fa-calendar-day']
                    ];
                    $t = $tipeConfig[$p['tipe']] ?? $tipeConfig['Info'];
                    ?>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative group hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-<?= $t['color'] ?>-50 flex items-center justify-center text-<?= $t['color'] ?>-500 border border-<?= $t['color'] ?>-100">
                                <i class="fas <?= $t['icon'] ?> text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0 pr-10">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-<?= $t['color'] ?>-100 text-<?= $t['color'] ?>-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                                        <?= esc((string) $p['tipe']) ?>
                                    </span>
                                    <span class="text-xs font-semibold text-gray-400">
                                        <i class="far fa-clock mr-1"></i> <?= date('d M Y, H:i', strtotime((string) $p['created_at'])) ?>
                                    </span>
                                </div>
                                <h4 class="text-base font-bold text-gray-800 mb-2 truncate"><?= esc((string) $p['judul']) ?></h4>

                                <?php if (!empty($p['gambar'])): ?>
                                    <?php $ext = strtolower(pathinfo((string)$p['gambar'], PATHINFO_EXTENSION)); ?>
                                    <?php if ($ext === 'pdf'): ?>
                                        <div class="mb-4">
                                            <a href="<?= base_url('uploads/pengumuman/' . esc((string) $p['gambar'])) ?>" target="_blank" class="inline-flex items-center gap-3 bg-red-50 text-red-600 px-5 py-3 rounded-xl border border-red-100 hover:bg-red-100 transition-colors text-sm font-bold w-full max-w-sm shadow-sm group/pdf">
                                                <i class="fas fa-file-pdf text-2xl group-hover/pdf:scale-110 transition-transform"></i>
                                                <div class="flex flex-col text-left">
                                                    <span>Lihat Dokumen PDF</span>
                                                    <span class="text-[10px] font-medium text-red-400">Klik untuk membuka di tab baru</span>
                                                </div>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-4 overflow-hidden rounded-xl border border-gray-100 bg-gray-50">
                                            <img src="<?= base_url('uploads/pengumuman/' . esc((string) $p['gambar'])) ?>" alt="Lampiran Pengumuman" class="w-full max-h-64 object-cover hover:opacity-90 transition-opacity cursor-pointer" onclick="window.open(this.src, '_blank')">
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap"><?= esc((string) $p['isi']) ?></p>
                            </div>
                        </div>

                        <form action="<?= base_url('admin/pengumuman/delete/' . (string) $p['id_pengumuman']) ?>" method="POST" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <?= csrf_field() ?>
                            <button type="button" class="btn-confirm p-2 bg-white text-gray-400 hover:text-red-500 hover:bg-red-50 border border-gray-200 rounded-lg shadow-sm" data-text="Tarik pengumuman ini agar tidak terlihat lagi oleh siswa?" data-btn="Ya, Tarik" title="Tarik / Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (isset($pager_links)) : ?>
            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-center">
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

    const MAX_PDF_SIZE = 2 * 1024 * 1024; // Maksimal 2 MB

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];

        // Sembunyikan preview setiap kali file diganti/dihapus
        previewContainer.classList.add('hidden');
        previewImg.src = '';

        if (!file) return;

        if (file.type === 'application/pdf') {
            if (file.size > MAX_PDF_SIZE) {
                toastr.error('Ukuran file PDF tidak boleh lebih dari 2 MB.', 'File Terlalu Besar');
                fileInput.value = '';
            }
            return;
        }

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

                        // --- TAMPILKAN PREVIEW UI ---
                        previewImg.src = URL.createObjectURL(blob);
                        previewContainer.classList.remove('hidden');
                        sizeInfo.innerText = (compressedFile.size / 1024).toFixed(1) + ' KB';

                        console.log(`Kompresi berhasil. Ukuran awal: ${(file.size / 1024).toFixed(2)} KB | Ukuran baru: ${(compressedFile.size / 1024).toFixed(2)} KB`);

                    }, 'image/jpeg', 0.7);
                }
            };

            reader.readAsDataURL(file);
        }
    });

    form.addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>