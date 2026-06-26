<?php

/**
 * @var array<string, mixed> $siswa
 * @var array<int, array<string, mixed>> $absensi
 * @var array<int, array<string, mixed>> $logFraud
 * @var array<string, int> $stats
 * @var string|null $start_date
 * @var string|null $end_date
 * @var string|null $pager_links
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Profil 360 Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Detail informasi, statistik, dan riwayat aktivitas.</p>
    </div>
    <a href="<?= base_url('admin/siswa') ?>" class="bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-xl font-semibold hover:bg-gray-50 transition-colors text-sm flex items-center justify-center gap-2 shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center h-fit">
        <div class="w-24 h-24 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-3xl shadow-inner overflow-hidden border-4 border-white ring-2 ring-gray-100 shrink-0 mb-4">
            <?php if (!empty($siswa['foto_profil'])): ?>
                <img src="<?= base_url('uploads/siswa/' . (string) $siswa['foto_profil']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <?= esc(strtoupper(substr((string) ($siswa['nama_siswa'] ?? ''), 0, 1))) ?>
            <?php endif; ?>
        </div>

        <h3 class="text-xl font-bold text-gray-800"><?= esc((string) ($siswa['nama_siswa'] ?? '')) ?></h3>
        <p class="text-sm text-gray-500 font-medium mb-6"><?= esc((string) ($siswa['nis'] ?? '')) ?> • <?= esc((string) ($siswa['nama_kelas'] ?? 'Belum ada kelas')) ?></p>

        <div class="w-full bg-gray-50 rounded-xl border border-gray-100 p-4 mb-6 text-left space-y-3">
            <div>
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">ID Perangkat (Device ID)</div>
                <?php if (!empty($siswa['device_id'])): ?>
                    <div class="flex items-center gap-2">
                        <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded text-xs font-mono font-semibold border border-green-200 break-all">
                            <?= esc((string) $siswa['device_id']) ?>
                        </span>
                    </div>
                <?php else: ?>
                    <span class="text-gray-400 italic text-xs">Belum ada perangkat tertaut</span>
                <?php endif; ?>
            </div>
            <div class="border-t border-gray-200 pt-3">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Login Terakhir</div>
                <div class="text-xs text-gray-800 font-medium">
                    <?= !empty($siswa['last_login']) ? date('d M Y, H:i', strtotime((string) $siswa['last_login'])) . ' WIB' : '<span class="text-gray-400 italic">Belum pernah login</span>' ?>
                </div>
            </div>
        </div>

        <div class="w-full grid grid-cols-2 gap-3 mt-auto">
            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100 flex flex-col items-center">
                <div class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider mb-1">Total Hadir</div>
                <div class="text-2xl font-black text-emerald-700"><?= (int) ($stats['hadir'] ?? 0) ?></div>
            </div>
            <div class="bg-red-50 p-4 rounded-xl border border-red-100 flex flex-col items-center">
                <div class="text-[10px] text-red-600 font-bold uppercase tracking-wider mb-1">Total Alpa</div>
                <div class="text-2xl font-black text-red-700"><?= (int) ($stats['alpa'] ?? 0) ?></div>
            </div>
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 flex flex-col items-center">
                <div class="text-[10px] text-amber-600 font-bold uppercase tracking-wider mb-1">Terlambat</div>
                <div class="text-2xl font-black text-amber-700"><?= (int) ($stats['terlambat'] ?? 0) ?></div>
            </div>
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex flex-col items-center">
                <div class="text-[10px] text-blue-600 font-bold uppercase tracking-wider mb-1">Izin/Sakit</div>
                <div class="text-2xl font-black text-blue-700"><?= ((int) ($stats['izin'] ?? 0)) + ((int) ($stats['sakit'] ?? 0)) ?></div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h4 class="font-bold text-gray-800 text-sm whitespace-nowrap">Riwayat Presensi</h4>

                <form action="<?= base_url('admin/siswa/detail/' . esc((string)$siswa['id_siswa'])) ?>" method="GET" class="flex items-center gap-2 w-full md:w-auto">
                    <input type="date" name="start_date" value="<?= esc((string)($start_date ?? '')) ?>" class="border border-gray-200 text-xs px-2 py-1.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full md:w-auto text-gray-600">
                    <span class="text-gray-400 text-xs font-bold">s/d</span>
                    <input type="date" name="end_date" value="<?= esc((string)($end_date ?? '')) ?>" class="border border-gray-200 text-xs px-2 py-1.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full md:w-auto text-gray-600">
                    <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-700 transition-colors shadow-sm">Filter</button>

                    <?php if (!empty($start_date) || !empty($end_date)): ?>
                        <a href="<?= base_url('admin/siswa/detail/' . esc((string)$siswa['id_siswa'])) ?>" class="bg-gray-200 text-gray-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-300 transition-colors" title="Reset Filter"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg></a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 font-bold">Tanggal</th>
                            <th class="px-5 py-3 font-bold">Status</th>
                            <th class="px-5 py-3 font-bold text-right">Jam (Masuk - Pulang)</th>
                            <th class="px-5 py-3 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($absensi)): ?>
                            <?php foreach ($absensi as $ab): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 font-medium text-gray-800 whitespace-nowrap">
                                        <?= date('d M Y', strtotime((string) ($ab['tanggal'] ?? ''))) ?>
                                    </td>
                                    <td class="px-5 py-4 font-semibold <?= ((string) ($ab['status'] ?? '') === 'Hadir' || (string) ($ab['status'] ?? '') === 'Dispensasi') ? 'text-emerald-600' : (((string) ($ab['status'] ?? '') === 'Terlambat') ? 'text-amber-600' : 'text-red-600') ?>">
                                        <?= esc((string) ($ab['status'] ?? '')) ?>
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 font-mono text-xs whitespace-nowrap text-right">
                                        <?= esc((string) ($ab['jam_masuk'] ?? '-')) ?> &mdash; <?= esc((string) ($ab['jam_pulang'] ?? '-')) ?>
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <?php if (!empty($ab['foto_masuk']) || !empty($ab['foto_pulang'])): ?>
                                            <button type="button" onclick="openFotoModal('<?= esc((string)($ab['foto_masuk'] ?? '')) ?>', '<?= esc((string)($ab['foto_pulang'] ?? '')) ?>')" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5 mx-auto border border-blue-100 hover:border-blue-600 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Lihat Foto
                                            </button>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400 italic font-medium">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-gray-400 italic">Data presensi tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($total_data)): ?>
                <div class="p-5 bg-gray-50/30 flex flex-col lg:flex-row justify-between items-center gap-4 border-t border-gray-100">
                    <div class="text-sm text-gray-500 font-medium whitespace-nowrap">
                        <?php
                        $safePage    = max(1, (int)($page ?? 1));
                        $safePerPage = max(1, (int)($perPage ?? 10));
                        $safeTotal   = max(0, (int)($total_data ?? 0));
                        $start = $safeTotal > 0 ? (($safePage - 1) * $safePerPage) + 1 : 0;
                        $end   = min($safePage * $safePerPage, $safeTotal);
                        ?>
                        Tampil <span class="font-bold text-gray-800"><?= $start ?></span> - <span class="font-bold text-gray-800"><?= $end ?></span> dari <span class="font-bold text-gray-800"><?= $safeTotal ?></span> data
                    </div>

                    <div class="w-full flex justify-center lg:justify-end">
                        <?= $pager_links ?? '' ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col border-t-4 border-t-red-500">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h4 class="font-bold text-red-600 text-sm">Riwayat Pelanggaran (Fraud Log)</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($logFraud)): ?>
                            <?php foreach ($logFraud as $log): ?>
                                <tr class="hover:bg-red-50/30 transition-colors">
                                    <td class="px-5 py-4 font-medium text-gray-800 whitespace-nowrap">
                                        <?= date('d M Y H:i', strtotime((string) ($log['created_at'] ?? ''))) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded shadow-sm border border-red-200 uppercase">
                                            <?= esc((string) ($log['tipe_fraud'] ?? 'Tidak diketahui')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="px-5 py-10 text-center text-gray-400 italic">Bersih. Tidak ada catatan pelanggaran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="fotoModal" class="fixed inset-0 z-[9999] hidden bg-black/70 backdrop-blur-sm items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="fotoModalContent">
        <div class="flex justify-between items-center p-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Bukti Presensi Siswa
            </h3>
            <button type="button" onclick="closeFotoModal()" class="text-gray-400 hover:text-red-500 transition-colors bg-white hover:bg-red-50 rounded-full p-1.5 border border-transparent hover:border-red-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="space-y-3">
                <div class="text-center">
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">Foto Masuk</span>
                </div>
                <div class="w-full aspect-[3/4] bg-gray-50 rounded-xl overflow-hidden border-2 border-dashed border-gray-200 flex items-center justify-center relative group">
                    <img id="imgMasuk" src="" alt="Foto Masuk" class="w-full h-full object-cover hidden cursor-pointer transition-transform duration-300 group-hover:scale-105" onclick="window.open(this.src, '_blank')" title="Klik untuk memperbesar">
                    <div id="noImgMasuk" class="flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                        <span class="text-xs font-medium italic">Tidak ada foto</span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="text-center">
                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">Foto Pulang</span>
                </div>
                <div class="w-full aspect-[3/4] bg-gray-50 rounded-xl overflow-hidden border-2 border-dashed border-gray-200 flex items-center justify-center relative group">
                    <img id="imgPulang" src="" alt="Foto Pulang" class="w-full h-full object-cover hidden cursor-pointer transition-transform duration-300 group-hover:scale-105" onclick="window.open(this.src, '_blank')" title="Klik untuk memperbesar">
                    <div id="noImgPulang" class="flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                        <span class="text-xs font-medium italic">Tidak ada foto</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Konfigurasi URL base folder upload. Asumsi default direktori public/uploads/absensi/
    const baseUploadUrl = '<?= base_url('uploads/absensi/') ?>';

    // Elemen DOM
    const modal = document.getElementById('fotoModal');
    const modalContent = document.getElementById('fotoModalContent');
    const imgMasuk = document.getElementById('imgMasuk');
    const noImgMasuk = document.getElementById('noImgMasuk');
    const imgPulang = document.getElementById('imgPulang');
    const noImgPulang = document.getElementById('noImgPulang');

    function openFotoModal(fotoMasuk, fotoPulang) {
        // Logika Foto Masuk
        if (fotoMasuk && fotoMasuk.trim() !== '') {
            imgMasuk.src = baseUploadUrl + fotoMasuk;
            imgMasuk.classList.remove('hidden');
            noImgMasuk.classList.add('hidden');
        } else {
            imgMasuk.classList.add('hidden');
            noImgMasuk.classList.remove('hidden');
        }

        // Logika Foto Pulang
        if (fotoPulang && fotoPulang.trim() !== '') {
            imgPulang.src = baseUploadUrl + fotoPulang;
            imgPulang.classList.remove('hidden');
            noImgPulang.classList.add('hidden');
        } else {
            imgPulang.classList.add('hidden');
            noImgPulang.classList.remove('hidden');
        }

        // Animasi buka modal
        modal.classList.remove('hidden');
        modal.classList.add('flex'); // Class flex ditambahkan dinamis melalui JS
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 10);
    }

    function closeFotoModal() {
        // Animasi tutup modal
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex'); // Hapus class flex agar tidak konflik dengan hidden
            // Bersihkan src memori cache browser
            imgMasuk.src = '';
            imgPulang.src = '';
        }, 300);
    }

    // Tutup modal jika klik area luar (backdrop)
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeFotoModal();
        }
    });

    // Tutup modal dengan tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeFotoModal();
        }
    });
</script>
<?= $this->endSection() ?>