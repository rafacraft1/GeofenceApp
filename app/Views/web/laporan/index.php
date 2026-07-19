<?php

/**
 * @var array<int, array<string, mixed>> $listKelas
 * @var string $bulanMulai
 * @var string $bulanSelesai
 * @var string $tahun
 * @var string|int $kelasId
 * @var array<int, array<string, mixed>> $rekapData
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Rekapitulasi Laporan</h2>
        <p class="text-sm text-gray-500 mt-1 font-medium">Pantau analitik kehadiran bulanan siswa secara komprehensif.</p>
    </div>
</div>

<!-- FILTER CARD -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-8 relative overflow-hidden">
    <!-- Dekorasi Background -->
    <div class="absolute right-0 top-0 w-48 h-48 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-full opacity-60 pointer-events-none"></div>
    <div class="absolute left-0 bottom-0 w-24 h-24 bg-gradient-to-tr from-indigo-50 to-transparent rounded-tr-full opacity-60 pointer-events-none"></div>
    
    <form action="<?= base_url('admin/laporan') ?>" method="GET" class="relative z-10 grid grid-cols-1 md:grid-cols-5 gap-5 items-end" id="filterForm">

        <!-- Filter Kelas -->
        <div>
            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Pilih Kelas</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-school"></i>
                </div>
                <?php if (session()->get('is_wali_kelas')): ?>
                    <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-3 text-sm bg-gray-50 text-gray-500 cursor-not-allowed outline-none font-bold" readonly>
                    <input type="hidden" name="kelas" value="<?= esc((string) session()->get('kelas_id')) ?>">
                <?php else: ?>
                    <select name="kelas" class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-8 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white hover:bg-gray-50 cursor-pointer font-bold text-gray-700 appearance-none shadow-sm">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($listKelas as $k): ?>
                            <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string)$kelasId === (string)$k['id_kelas']) ? 'selected' : '' ?>>
                                <?= esc((string) $k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter Bulan Mulai -->
        <div>
            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Bulan Mulai</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="far fa-calendar-alt"></i>
                </div>
                <select name="bulan_mulai" class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-8 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white hover:bg-gray-50 cursor-pointer font-bold text-gray-700 appearance-none shadow-sm">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= sprintf('%02d', $m) ?>" <?= ($bulanMulai === sprintf('%02d', $m)) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>
        </div>

        <!-- Filter Bulan Selesai -->
        <div>
            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Bulan Selesai</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="far fa-calendar-check"></i>
                </div>
                <select name="bulan_selesai" class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-8 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white hover:bg-gray-50 cursor-pointer font-bold text-gray-700 appearance-none shadow-sm">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= sprintf('%02d', $m) ?>" <?= ($bulanSelesai === sprintf('%02d', $m)) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>
        </div>

        <!-- Filter Tahun -->
        <div>
            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Tahun</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-history"></i>
                </div>
                <select name="tahun" class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-8 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white hover:bg-gray-50 cursor-pointer font-bold text-gray-700 appearance-none shadow-sm">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-md shadow-blue-500/20 text-sm active:scale-95">
                <i class="fas fa-search mr-1.5"></i> Tampilkan
            </button>
            <a href="<?= base_url('admin/laporan/export') ?>?kelas=<?= esc((string)$kelasId) ?>&bulan_mulai=<?= esc($bulanMulai) ?>&bulan_selesai=<?= esc($bulanSelesai) ?>&tahun=<?= esc($tahun) ?>" onclick="showExportLoading(this)" class="flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-bold py-3 rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all shadow-md shadow-emerald-500/20 text-sm text-center flex items-center justify-center relative overflow-hidden group active:scale-95" title="Export Excel">
                <span class="btn-content flex items-center gap-1.5"><i class="fas fa-file-excel"></i> Ekspor</span>
                <span class="btn-loader hidden absolute inset-0 items-center justify-center bg-emerald-700">
                    <i class="fas fa-spinner fa-spin text-lg"></i>
                </span>
            </a>
        </div>
    </form>
</div>

<!-- TABEL REKAP -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
    <div class="p-5 border-b border-gray-50 bg-gray-50/30 flex items-center justify-between">
        <h3 class="text-sm font-black text-gray-700 uppercase tracking-wider"><i class="fas fa-chart-line mr-2 text-blue-500"></i> Hasil Analitik Kehadiran</h3>
        <div class="text-[10px] font-bold text-gray-400 bg-white border border-gray-100 px-3 py-1.5 rounded-lg shadow-sm">
            Total Data: <?= count($rekapData) ?> Siswa
        </div>
    </div>

    <div class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left whitespace-nowrap min-w-max">
            <thead>
                <tr class="bg-white text-gray-400 text-[10px] font-black uppercase tracking-widest border-b border-gray-100">
                    <th class="px-6 py-4 text-center w-12">No</th>
                    <th class="px-4 py-4">Siswa & Kelas</th>
                    <th class="px-5 py-4 w-48">Indeks Kehadiran (%)</th>
                    <th class="px-2 py-4 text-center">Hadir</th>
                    <th class="px-2 py-4 text-center">Dispen</th>
                    <th class="px-2 py-4 text-center">Telat</th>
                    <th class="px-2 py-4 text-center">Sakit</th>
                    <th class="px-2 py-4 text-center">Izin</th>
                    <th class="px-2 py-4 text-center">Alpa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $no = 1; foreach ($rekapData as $row): ?>
                    <?php
                        // Logika Inisial & Warna Avatar
                        $namaSiswa = trim((string) $row['nama_siswa']);
                        $words = explode(' ', $namaSiswa);
                        $inisial = strtoupper(substr($words[0], 0, 1));
                        if (count($words) > 1) {
                            $inisial .= strtoupper(substr(end($words), 0, 1));
                        } else {
                            $inisial .= strtoupper(substr($namaSiswa, 1, 1) ?: '');
                        }
                        
                        $colors = ['bg-blue-100 text-blue-600', 'bg-emerald-100 text-emerald-600', 'bg-purple-100 text-purple-600', 'bg-amber-100 text-amber-600', 'bg-rose-100 text-rose-600', 'bg-cyan-100 text-cyan-600', 'bg-indigo-100 text-indigo-600'];
                        $colorIndex = abs(crc32($namaSiswa)) % count($colors);
                        $avatarClass = $colors[$colorIndex];
                        
                        // Logika Persentase
                        $pct = (float) $row['Persentase'];
                        $barColor = $pct >= 85 ? 'bg-emerald-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-red-500');
                        $textColor = $pct >= 85 ? 'text-emerald-600' : ($pct >= 70 ? 'text-amber-600' : 'text-red-600');
                    ?>
                    <tr class="hover:bg-blue-50/20 transition-colors group">
                        <td class="px-6 py-4 text-sm text-gray-400 font-bold text-center"><?= $no++ ?></td>
                        
                        <!-- Kolom Nama + Avatar -->
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm <?= $avatarClass ?> shadow-sm">
                                    <?= esc($inisial) ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 group-hover:text-blue-700 transition-colors"><?= esc($namaSiswa) ?></div>
                                    <div class="text-[10px] text-gray-500 font-bold bg-gray-50 border border-gray-100 px-2 py-0.5 rounded flex items-center gap-1.5 w-max mt-1 tracking-wider shadow-sm">
                                        <i class="far fa-id-badge text-gray-400"></i> <?= esc((string) $row['nis']) ?> 
                                        <span class="text-gray-300">|</span> 
                                        <i class="fas fa-school text-gray-400"></i> <?= esc((string) ($row['nama_kelas'] ?? '-')) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom Indeks Persentase (BARU DIMUNCULKAN) -->
                        <td class="px-5 py-4 w-48 align-middle">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-black <?= $textColor ?>"><?= number_format($pct, 1) ?>%</span>
                                <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest bg-gray-50 px-1.5 rounded border border-gray-100"><?= (int)$row['TotalHari'] ?> Hari</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden shadow-inner">
                                <div class="<?= $barColor ?> h-full rounded-full transition-all duration-1000 ease-out relative" style="width: 0%" data-width="<?= $pct ?>%">
                                    <div class="absolute inset-0 bg-white/20 w-full h-full"></div>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom Angka (Pill Badges) -->
                        <td class="px-2 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 font-black text-xs border border-emerald-100 shadow-sm"><?= (int) $row['Hadir'] ?></span>
                        </td>
                        <td class="px-2 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-50 text-cyan-700 font-black text-xs border border-cyan-100 shadow-sm"><?= (int) $row['Dispensasi'] ?></span>
                        </td>
                        <td class="px-2 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-700 font-black text-xs border border-amber-100 shadow-sm"><?= (int) $row['Terlambat'] ?></span>
                        </td>
                        <td class="px-2 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-black text-xs border border-blue-100 shadow-sm"><?= (int) $row['Sakit'] ?></span>
                        </td>
                        <td class="px-2 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 font-black text-xs border border-indigo-100 shadow-sm"><?= (int) $row['Izin'] ?></span>
                        </td>
                        <td class="px-2 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-700 font-black text-xs border border-red-100 shadow-sm"><?= (int) $row['Alpa'] ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <!-- EMPTY STATE (Dipercantik) -->
                <?php if (empty($rekapData)): ?>
                    <tr>
                        <td colspan="9" class="py-20 text-center">
                            <div class="w-24 h-24 bg-gray-50 border border-gray-100 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                <i class="fas fa-folder-open text-4xl"></i>
                            </div>
                            <h3 class="text-gray-600 font-bold text-lg mb-1">Tidak Ada Data Rekap</h3>
                            <p class="text-xs text-gray-400 max-w-sm mx-auto leading-relaxed">Sistem tidak menemukan data absensi pada periode atau kelas yang Anda pilih. Silakan sesuaikan filter di atas.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Animasi Progress Bar saat halaman diload
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(() => {
            const bars = document.querySelectorAll('[data-width]');
            bars.forEach(bar => {
                bar.style.width = bar.getAttribute('data-width');
            });
        }, 300);
    });

    // Animasi Loading Tombol Export
    function showExportLoading(btn) {
        btn.classList.add('pointer-events-none');
        btn.querySelector('.btn-content').classList.add('invisible');
        let loader = btn.querySelector('.btn-loader');
        loader.classList.remove('hidden');
        loader.classList.add('flex');
        
        // Asumsi export memakan waktu, reset tombol setelah 3.5 detik
        setTimeout(() => {
            btn.classList.remove('pointer-events-none');
            btn.querySelector('.btn-content').classList.remove('invisible');
            loader.classList.add('hidden');
            loader.classList.remove('flex');
        }, 3500);
    }
</script>
<?= $this->endSection() ?>