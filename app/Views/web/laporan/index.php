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
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Rekapitulasi Laporan</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau rentang kehadiran bulanan siswa secara komprehensif.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 relative overflow-hidden">
    <div class="absolute right-0 top-0 w-32 h-32 bg-blue-50 rounded-bl-full opacity-50 z-0"></div>
    <form action="<?= base_url('admin/laporan') ?>" method="GET" class="relative z-10 grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="filterForm">

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Kelas</label>
            <?php if (session()->get('is_wali_kelas')): ?>
                <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full border-gray-200 rounded-xl p-2.5 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none" readonly>
                <input type="hidden" name="kelas" value="<?= session()->get('kelas_id') ?>">
            <?php else: ?>
                <select name="kelas" onchange="this.form.submit()" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelasId === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                            <?= esc((string) $k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Mulai Bulan</label>
            <select name="bulan_mulai" onchange="this.form.submit()" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                <?php
                $namaBulan = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
                foreach ($namaBulan as $num => $name): ?>
                    <option value="<?= esc((string) $num) ?>" <?= ($bulanMulai === (string) $num) ? 'selected' : '' ?>><?= esc((string) $name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Sampai Bulan</label>
            <select name="bulan_selesai" onchange="this.form.submit()" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                <?php foreach ($namaBulan as $num => $name): ?>
                    <option value="<?= esc((string) $num) ?>" <?= ($bulanSelesai === (string) $num) ? 'selected' : '' ?>><?= esc((string) $name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tahun</label>
            <select name="tahun" onchange="this.form.submit()" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                <?php $tahunSekarang = (int) date('Y');
                for ($i = $tahunSekarang; $i >= $tahunSekarang - 3; $i--): ?>
                    <option value="<?= esc((string) $i) ?>" <?= ($tahun === (string) $i) ? 'selected' : '' ?>><?= esc((string) $i) ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="flex gap-2">
            <a href="<?= base_url('admin/laporan/export?kelas=' . $kelasId . '&bulan_mulai=' . $bulanMulai . '&bulan_selesai=' . $bulanSelesai . '&tahun=' . $tahun) ?>" onclick="showExportLoading(this)" class="w-full bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-emerald-700 transition-all h-[42px] flex items-center justify-center gap-2 relative overflow-hidden group">
                <span class="flex items-center gap-2 btn-content">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </span>
                <span class="absolute inset-0 items-center justify-center bg-emerald-700 hidden btn-loader">
                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative max-h-[600px] overflow-y-auto custom-scrollbar">
    <table class="w-full text-left" id="dataTable">
        <thead class="sticky top-0 bg-gray-50/95 backdrop-blur shadow-sm z-10 text-gray-500 text-[10px] font-bold uppercase tracking-wider">
            <tr>
                <th class="px-6 py-4">Siswa & Kelas</th>
                <th class="px-4 py-4 text-center">Kehadiran</th>
                <th class="px-3 py-4 text-center">Hadir</th>
                <th class="px-3 py-4 text-center">Dispensasi</th>
                <th class="px-3 py-4 text-center">Telat</th>
                <th class="px-3 py-4 text-center">Sakit</th>
                <th class="px-3 py-4 text-center">Izin</th>
                <th class="px-3 py-4 text-center">Alpa</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($rekapData as $row):
                $pct = (int) $row['Persentase'];
                $barColor = $pct >= 85 ? 'bg-emerald-500' : ($pct >= 60 ? 'bg-amber-400' : 'bg-red-500');
            ?>
                <tr class="hover:bg-blue-50/30 transition-colors data-row">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-800 search-target"><?= esc((string) $row['nama_siswa']) ?></div>
                        <div class="text-[10px] text-gray-500 font-medium search-target"><?= esc((string) $row['nis']) ?> • <?= esc((string) $row['nama_kelas']) ?></div>
                    </td>
                    <td class="px-4 py-4 w-48">
                        <div class="flex justify-between text-[10px] font-bold mb-1">
                            <span><?= $pct ?>%</span>
                            <span class="text-gray-400"><?= $row['TotalHari'] ?> Hari Aktif</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="<?= $barColor ?> h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                        </div>
                    </td>
                    <td class="px-3 py-4 text-center"><span class="text-emerald-700 font-bold text-xs"><?= (int) $row['Hadir'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-teal-700 font-bold text-xs"><?= (int) $row['Dispensasi'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-amber-700 font-bold text-xs"><?= (int) $row['Terlambat'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-blue-700 font-bold text-xs"><?= (int) $row['Sakit'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-indigo-700 font-bold text-xs"><?= (int) $row['Izin'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-red-700 font-bold text-xs"><?= (int) $row['Alpa'] ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function showExportLoading(btn) {
        btn.classList.add('pointer-events-none');
        btn.querySelector('.btn-content').classList.add('invisible');
        let loader = btn.querySelector('.btn-loader');
        loader.classList.remove('hidden');
        loader.classList.add('flex');
        setTimeout(() => {
            btn.classList.remove('pointer-events-none');
            btn.querySelector('.btn-content').classList.remove('invisible');
            loader.classList.remove('flex');
            loader.classList.add('hidden');
        }, 3000);
    }
</script>
<?= $this->endSection() ?>