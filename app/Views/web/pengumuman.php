<?php

/**
 * @var string $title
 * @var array<int, array<string, mixed>> $pengumuman
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Buat Broadcast Baru</h3>
            <p class="text-xs text-gray-500 mb-6">Pesan akan langsung dikirim ke aplikasi siswa.</p>

            <form action="<?= base_url('admin/pengumuman/store') ?>" method="POST" enctype="multipart/form-data" id="formPengumuman">
                <?= csrf_field() ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tipe</label>
                        <select name="tipe" required class="w-full border-gray-200 rounded-xl p-3 text-sm bg-gray-50 cursor-pointer focus:ring-2 focus:ring-blue-500">
                            <option value="Info">ℹ️ Informasi Umum</option>
                            <option value="Penting">⚠️ Sangat Penting</option>
                            <option value="Libur">🏖️ Info Libur / Event</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Judul</label>
                        <input type="text" name="judul" required class="w-full border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Isi Pesan</label>
                        <textarea name="isi" required rows="5" class="w-full border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Lampiran</label>
                        <input type="file" name="gambar" id="inputFile" accept="image/*,.pdf" class="w-full border border-gray-200 rounded-xl p-2 text-sm file:bg-blue-50 file:border-0 file:rounded-full file:px-4 file:py-2">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg btn-submit">Kirim Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-black text-gray-800">Riwayat</h2>
            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold"><?= count($pengumuman) ?> Pesan</span>
        </div>

        <div class="space-y-4 h-[calc(100vh-180px)] overflow-y-auto pr-2" id="riwayatContainer">
            <?php foreach ($pengumuman as $p): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 group hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 border">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-gray-800 truncate"><?= esc((string)$p['judul']) ?></h4>
                            <p class="text-xs text-gray-400 mt-1"><?= date('d M Y, H:i', strtotime((string)$p['created_at'])) ?></p>
                            <p class="text-sm text-gray-600 mt-2 leading-relaxed"><?= esc((string)$p['isi']) ?></p>

                            <?php if (!empty($p['gambar'])): ?>
                                <a href="<?= base_url('uploads/pengumuman/' . $p['gambar']) ?>" target="_blank" class="mt-3 inline-block text-xs font-bold text-blue-600 underline">Lihat Lampiran</a>
                            <?php endif; ?>
                        </div>
                        <form action="<?= base_url('admin/pengumuman/delete/' . $p['id_pengumuman']) ?>" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-red-400 hover:text-red-600 btn-confirm" data-text="Hapus pengumuman ini?"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Script kompresi sudah optimal, pastikan ID inputFile dan formPengumuman sesuai
    // (Gunakan script kompresi yang Anda miliki sebelumnya karena sudah terbukti berjalan)
</script>
<?= $this->endSection() ?>