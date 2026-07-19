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
<div class="mb-8 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Profil 360 Siswa</h2>
        <p class="text-sm text-gray-500 mt-1 font-medium">Detail informasi, statistik presensi, dan log aktivitas pelanggaran.</p>
    </div>
    <a href="<?= base_url('admin/siswa') ?>" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-xl font-bold shadow-sm hover:bg-gray-50 hover:text-blue-600 transition-all text-sm group">
        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
        Kembali ke Direktori
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- KOLOM KIRI: Profil Identitas & Ringkasan -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col h-fit relative overflow-hidden group">
        <!-- Dekorasi Background -->
        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-blue-600 to-indigo-600 z-0 transition-transform duration-500 group-hover:scale-105"></div>
        
        <div class="p-7 pt-16 flex flex-col items-center text-center relative z-10">
            <div class="w-28 h-28 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-black text-4xl shadow-xl overflow-hidden border-4 border-white ring-4 ring-blue-50/50 shrink-0 mb-5 relative group/avatar">
                <?php if (!empty($siswa['foto_profil'])): ?>
                    <img src="<?= base_url('uploads/siswa/' . (string) $siswa['foto_profil']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover/avatar:scale-110">
                <?php else: ?>
                    <?= esc(strtoupper(substr((string) ($siswa['nama_siswa'] ?? ''), 0, 1))) ?>
                <?php endif; ?>
            </div>

            <h3 class="text-xl font-black text-gray-800 tracking-tight"><?= esc((string) ($siswa['nama_siswa'] ?? '')) ?></h3>
            <div class="flex items-center justify-center gap-2 mt-2 mb-6">
                <span class="bg-gray-100 text-gray-600 font-bold px-3 py-1 rounded-full text-xs shadow-sm border border-gray-200"><i class="fas fa-id-card mr-1 text-gray-400"></i> <?= esc((string) ($siswa['nis'] ?? '')) ?></span>
                <span class="bg-indigo-50 text-indigo-600 font-bold px-3 py-1 rounded-full text-xs shadow-sm border border-indigo-100"><i class="fas fa-chalkboard-teacher mr-1 text-indigo-400"></i> <?= esc((string) ($siswa['nama_kelas'] ?? 'Belum ada kelas')) ?></span>
            </div>

            <div class="w-full bg-gray-50 rounded-2xl border border-gray-100 p-5 mb-8 text-left space-y-4">
                <div>
                    <div class="flex items-center gap-1.5 text-[10px] text-gray-500 font-black uppercase tracking-widest mb-1.5">
                        <i class="fas fa-mobile-alt text-gray-400"></i> ID Perangkat (Device)
                    </div>
                    <?php if (!empty($siswa['device_id'])): ?>
                        <div class="flex items-center gap-2">
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-mono font-bold border border-emerald-200 break-all shadow-sm">
                                <i class="fas fa-fingerprint mr-1 opacity-50"></i> <?= esc((string) $siswa['device_id']) ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <span class="text-gray-400 italic text-xs font-medium"><i class="fas fa-exclamation-circle mr-1"></i> Belum tertaut ke perangkat</span>
                    <?php endif; ?>
                </div>
                <div class="border-t border-gray-200/60 pt-4">
                    <div class="flex items-center gap-1.5 text-[10px] text-gray-500 font-black uppercase tracking-widest mb-1.5">
                        <i class="fas fa-sign-in-alt text-gray-400"></i> Login Terakhir
                    </div>
                    <div class="text-xs text-gray-800 font-bold bg-white px-3 py-2 rounded-lg border border-gray-200 shadow-sm inline-block">
                        <?= !empty($siswa['last_login']) ? '<i class="fas fa-clock text-blue-500 mr-1.5"></i>' . date('d M Y, H:i', strtotime((string) $siswa['last_login'])) . ' WIB' : '<span class="text-gray-400 italic">Belum pernah login</span>' ?>
                    </div>
                </div>
            </div>

            <!-- Statistik -->
            <div class="w-full grid grid-cols-2 gap-3 mt-auto">
                <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 flex flex-col items-center relative overflow-hidden group/stat hover:bg-emerald-100 transition-colors">
                    <i class="fas fa-check-circle absolute -right-3 -bottom-3 text-5xl text-emerald-500 opacity-10 group-hover/stat:scale-110 group-hover/stat:opacity-20 transition-all"></i>
                    <div class="text-[10px] text-emerald-600 font-black uppercase tracking-widest mb-1 relative z-10">Total Hadir</div>
                    <div class="text-3xl font-black text-emerald-700 relative z-10"><?= (int) ($stats['hadir'] ?? 0) ?></div>
                </div>
                <div class="bg-rose-50 p-4 rounded-2xl border border-rose-100 flex flex-col items-center relative overflow-hidden group/stat hover:bg-rose-100 transition-colors">
                    <i class="fas fa-times-circle absolute -right-3 -bottom-3 text-5xl text-rose-500 opacity-10 group-hover/stat:scale-110 group-hover/stat:opacity-20 transition-all"></i>
                    <div class="text-[10px] text-rose-600 font-black uppercase tracking-widest mb-1 relative z-10">Total Alpa</div>
                    <div class="text-3xl font-black text-rose-700 relative z-10"><?= (int) ($stats['alpa'] ?? 0) ?></div>
                </div>
                <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100 flex flex-col items-center relative overflow-hidden group/stat hover:bg-amber-100 transition-colors">
                    <i class="fas fa-user-clock absolute -right-3 -bottom-3 text-5xl text-amber-500 opacity-10 group-hover/stat:scale-110 group-hover/stat:opacity-20 transition-all"></i>
                    <div class="text-[10px] text-amber-600 font-black uppercase tracking-widest mb-1 relative z-10">Terlambat</div>
                    <div class="text-3xl font-black text-amber-700 relative z-10"><?= (int) ($stats['terlambat'] ?? 0) ?></div>
                </div>
                <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-100 flex flex-col items-center relative overflow-hidden group/stat hover:bg-indigo-100 transition-colors">
                    <i class="fas fa-notes-medical absolute -right-3 -bottom-3 text-5xl text-indigo-500 opacity-10 group-hover/stat:scale-110 group-hover/stat:opacity-20 transition-all"></i>
                    <div class="text-[10px] text-indigo-600 font-black uppercase tracking-widest mb-1 relative z-10">Izin/Sakit</div>
                    <div class="text-3xl font-black text-indigo-700 relative z-10"><?= ((int) ($stats['izin'] ?? 0)) + ((int) ($stats['sakit'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: Tabel Riwayat & Pelanggaran -->
    <div class="lg:col-span-2 space-y-8">

        <!-- RIWAYAT PRESENSI -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shadow-sm">
                        <i class="fas fa-calendar-check text-sm"></i>
                    </div>
                    <h4 class="font-black text-gray-800 text-sm uppercase tracking-wider">Riwayat Presensi</h4>
                </div>

                <form action="<?= base_url('admin/siswa/detail/' . esc((string)$siswa['id_siswa'])) ?>" method="GET" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <div class="flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-400 transition-all w-full md:w-auto">
                        <input type="date" name="start_date" value="<?= esc((string)($start_date ?? '')) ?>" class="text-xs px-3 py-2 outline-none text-gray-600 bg-transparent w-full">
                        <span class="text-gray-300 text-[10px] font-black uppercase bg-gray-50 px-2 h-full flex items-center border-x border-gray-100">s/d</span>
                        <input type="date" name="end_date" value="<?= esc((string)($end_date ?? '')) ?>" class="text-xs px-3 py-2 outline-none text-gray-600 bg-transparent w-full">
                    </div>
                    
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md shadow-blue-500/20 active:scale-95 flex items-center gap-1.5 flex-1 md:flex-none justify-center">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    <?php if (!empty($start_date) || !empty($end_date)): ?>
                        <a href="<?= base_url('admin/siswa/detail/' . esc((string)$siswa['id_siswa'])) ?>" class="bg-gray-100 text-gray-500 px-3 py-2 rounded-xl text-xs font-bold hover:bg-gray-200 hover:text-red-600 transition-colors flex items-center justify-center" title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-white border-b border-gray-100">
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Jam (Masuk &mdash; Pulang)</th>
                            <th class="px-6 py-4 text-center">Bukti Selfie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (!empty($absensi)): ?>
                            <?php foreach ($absensi as $ab): ?>
                                <tr class="hover:bg-blue-50/20 transition-colors group">
                                    <td class="px-6 py-4 font-bold text-gray-700">
                                        <i class="far fa-calendar-alt text-gray-300 mr-1.5"></i> <?= date('d M Y', strtotime((string) ($ab['tanggal'] ?? ''))) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                            $status = (string) ($ab['status'] ?? '');
                                            $statusColor = 'bg-gray-100 text-gray-600 border-gray-200';
                                            $icon = 'fa-info-circle';
                                            if ($status === 'Hadir' || $status === 'Dispensasi') { $statusColor = 'bg-emerald-100 text-emerald-700 border-emerald-200'; $icon = 'fa-check-circle'; }
                                            elseif ($status === 'Terlambat') { $statusColor = 'bg-amber-100 text-amber-700 border-amber-200'; $icon = 'fa-user-clock'; }
                                            elseif ($status === 'Izin' || $status === 'Sakit') { $statusColor = 'bg-indigo-100 text-indigo-700 border-indigo-200'; $icon = 'fa-notes-medical'; }
                                            elseif ($status === 'Alpa') { $statusColor = 'bg-rose-100 text-rose-700 border-rose-200'; $icon = 'fa-times-circle'; }
                                        ?>
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border shadow-sm flex items-center w-max gap-1 <?= $statusColor ?>">
                                            <i class="fas <?= $icon ?>"></i> <?= esc($status) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 font-mono text-xs text-right font-semibold">
                                        <span class="bg-gray-100 px-2 py-1 rounded border border-gray-200 text-gray-700"><?= esc((string) ($ab['jam_masuk'] ?? '--:--')) ?></span> 
                                        <span class="mx-1 text-gray-300">&mdash;</span> 
                                        <span class="bg-gray-100 px-2 py-1 rounded border border-gray-200 text-gray-700"><?= esc((string) ($ab['jam_pulang'] ?? '--:--')) ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if (!empty($ab['foto_masuk']) || !empty($ab['foto_pulang'])): ?>
                                            <button type="button" onclick="openFotoModal('<?= esc((string)($ab['foto_masuk'] ?? '')) ?>', '<?= esc((string)($ab['foto_pulang'] ?? '')) ?>')" class="bg-white text-blue-600 hover:bg-gradient-to-r hover:from-blue-600 hover:to-indigo-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 mx-auto border border-blue-200 hover:border-transparent shadow-sm group/btn active:scale-95">
                                                <i class="fas fa-camera text-blue-400 group-hover/btn:text-white transition-colors"></i>
                                                Cek Foto
                                            </button>
                                        <?php else: ?>
                                            <span class="text-[10px] text-gray-400 italic font-bold uppercase tracking-wider bg-gray-50 px-2 py-1 rounded">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="far fa-folder-open text-4xl mb-3 opacity-50"></i>
                                        <span class="italic text-sm font-medium">Data presensi tidak ditemukan pada rentang tanggal tersebut.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($total_data)): ?>
                <div class="p-6 bg-gray-50/50 flex flex-col md:flex-row justify-between items-center gap-4 border-t border-gray-100">
                    <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">
                        <?php
                        $safePage    = max(1, (int)($page ?? 1));
                        $safePerPage = max(1, (int)($perPage ?? 10));
                        $safeTotal   = max(0, (int)($total_data ?? 0));
                        $start = $safeTotal > 0 ? (($safePage - 1) * $safePerPage) + 1 : 0;
                        $end   = min($safePage * $safePerPage, $safeTotal);
                        ?>
                        <i class="fas fa-database mr-1 opacity-50"></i> Data <span class="text-blue-600 mx-1"><?= $start ?> &mdash; <?= $end ?></span> dari <span class="text-gray-800 mx-1"><?= $safeTotal ?></span>
                    </div>

                    <div class="w-full md:w-auto flex justify-center">
                        <?= $pager_links ?? '' ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIWAYAT FRAUD / PELANGGARAN -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <!-- Header merah atas -->
            <div class="h-1.5 w-full bg-gradient-to-r from-rose-500 to-red-600"></div>
            
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shadow-sm">
                        <i class="fas fa-shield-virus text-sm"></i>
                    </div>
                    <h4 class="font-black text-rose-700 text-sm uppercase tracking-wider">Log Investigasi (Fraud)</h4>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <tbody class="divide-y divide-gray-50">
                        <?php if (!empty($logFraud)): ?>
                            <?php foreach ($logFraud as $log): ?>
                                <tr class="hover:bg-rose-50/30 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-700 w-1/3">
                                        <i class="far fa-clock text-rose-300 mr-2"></i> <?= date('d M Y, H:i', strtotime((string) ($log['created_at'] ?? ''))) ?> WIB
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-rose-50 text-rose-600 text-[10px] font-black px-3 py-1.5 rounded-lg shadow-sm border border-rose-200 uppercase tracking-wider flex items-center w-max gap-1.5">
                                            <i class="fas fa-exclamation-triangle"></i> <?= esc((string) ($log['tipe_fraud'] ?? 'Peringatan Keamanan')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center text-emerald-500">
                                        <i class="fas fa-shield-check text-4xl mb-2 opacity-50"></i>
                                        <span class="italic text-sm font-bold">Status Bersih. Tidak ada indikasi kecurangan (Fraud).</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FOTO PRESENSI -->
<div id="fotoModal" class="fixed inset-0 z-[9999] hidden bg-slate-900/80 backdrop-blur-sm items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="fotoModalContent">
        
        <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-black text-gray-800 flex items-center gap-2.5 uppercase tracking-wider text-sm">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shadow-sm">
                    <i class="fas fa-camera-retro"></i>
                </div>
                Pemeriksaan Bukti Visual
            </h3>
            <button type="button" onclick="closeFotoModal()" class="text-gray-400 hover:text-rose-500 hover:rotate-90 transition-all bg-white hover:bg-rose-50 rounded-full p-2 border border-gray-200 hover:border-rose-200 shadow-sm focus:outline-none">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-white relative">
            <!-- Col 1: Masuk -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm">Foto Datang</span>
                    <span class="text-xs text-gray-400 font-bold" id="label-masuk-waktu"></span>
                </div>
                <div class="w-full aspect-[3/4] bg-gray-50 rounded-2xl overflow-hidden border-2 border-dashed border-gray-200 flex items-center justify-center relative group">
                    <img id="imgMasuk" src="" alt="Foto Masuk" class="w-full h-full object-cover hidden cursor-zoom-in transition-transform duration-500 group-hover:scale-105" onclick="window.open(this.src, '_blank')" title="Klik untuk resolusi penuh">
                    <div id="noImgMasuk" class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-image text-3xl mb-2 opacity-30"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Aset Tidak Tersedia</span>
                    </div>
                </div>
            </div>

            <!-- Col 2: Pulang -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm">Foto Pulang</span>
                    <span class="text-xs text-gray-400 font-bold" id="label-pulang-waktu"></span>
                </div>
                <div class="w-full aspect-[3/4] bg-gray-50 rounded-2xl overflow-hidden border-2 border-dashed border-gray-200 flex items-center justify-center relative group">
                    <img id="imgPulang" src="" alt="Foto Pulang" class="w-full h-full object-cover hidden cursor-zoom-in transition-transform duration-500 group-hover:scale-105" onclick="window.open(this.src, '_blank')" title="Klik untuk resolusi penuh">
                    <div id="noImgPulang" class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-image text-3xl mb-2 opacity-30"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Aset Tidak Tersedia</span>
                    </div>
                </div>
            </div>
            
            <div class="col-span-1 md:col-span-2 text-center mt-2">
                <p class="text-[10px] text-gray-400 font-bold"><i class="fas fa-lightbulb text-amber-400 mr-1"></i> Klik pada gambar untuk melihat dalam ukuran dan resolusi aslinya (Tab Baru).</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const baseUploadUrl = '<?= base_url('uploads/absensi/') ?>';

    const modal = document.getElementById('fotoModal');
    const modalContent = document.getElementById('fotoModalContent');
    const imgMasuk = document.getElementById('imgMasuk');
    const noImgMasuk = document.getElementById('noImgMasuk');
    const imgPulang = document.getElementById('imgPulang');
    const noImgPulang = document.getElementById('noImgPulang');

    function openFotoModal(fotoMasuk, fotoPulang) {
        // Masuk
        if (fotoMasuk && fotoMasuk.trim() !== '') {
            imgMasuk.src = baseUploadUrl + fotoMasuk;
            imgMasuk.classList.remove('hidden');
            noImgMasuk.classList.add('hidden');
        } else {
            imgMasuk.classList.add('hidden');
            noImgMasuk.classList.remove('hidden');
        }

        // Pulang
        if (fotoPulang && fotoPulang.trim() !== '') {
            imgPulang.src = baseUploadUrl + fotoPulang;
            imgPulang.classList.remove('hidden');
            noImgPulang.classList.add('hidden');
        } else {
            imgPulang.classList.add('hidden');
            noImgPulang.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Timeout minimal agar transisi CSS berjalan
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 20);
    }

    function closeFotoModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Hapus cache image src agar tidak berkedip ketika membuka modal lain
            imgMasuk.src = '';
            imgPulang.src = '';
        }, 300); // 300ms sesuaikan durasi class transition
    }

    // Klik di area backdrop hitam
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeFotoModal();
        }
    });

    // Tekan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeFotoModal();
        }
    });
</script>
<?= $this->endSection() ?>