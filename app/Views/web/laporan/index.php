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
                <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none" readonly>
                <input type="hidden" name="kelas" value="<?= esc((string) session()->get('kelas_id')) ?>">
            <?php else: ?>
                <select name="kelas" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string)$kelasId === (string)$k['id_kelas']) ? 'selected' : '' ?>>
                            <?= esc((string) $k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Bulan Mulai</label>
            <select name="bulan_mulai" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= sprintf('%02d', $m) ?>" <?= ($bulanMulai === sprintf('%02d', $m)) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Bulan Selesai</label>
            <select name="bulan_selesai" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= sprintf('%02d', $m) ?>" <?= ($bulanSelesai === sprintf('%02d', $m)) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tahun</label>
            <select name="tahun" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors shadow-md text-sm">Tampilkan</button>
            <a href="<?= base_url('admin/laporan/export') ?>?kelas=<?= esc((string)$kelasId) ?>&bulan_mulai=<?= esc($bulanMulai) ?>&bulan_selesai=<?= esc($bulanSelesai) ?>&tahun=<?= esc($tahun) ?>" onclick="showExportLoading(this)" class="flex-1 bg-emerald-600 text-white font-bold py-3 rounded-xl hover:bg-emerald-700 transition-colors shadow-md text-sm text-center flex items-center justify-center relative overflow-hidden group">
                <span class="btn-content flex items-center gap-1.5"><i class="fas fa-file-excel"></i> Ekspor</span>
                <span class="btn-loader hidden absolute inset-0 items-center justify-center bg-emerald-700">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left whitespace-nowrap">
        <thead>
            <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                <th class="px-6 py-4">No</th>
                <th class="px-6 py-4">Siswa</th>
                <th class="px-3 py-4 text-center">Hadir</th>
                <th class="px-3 py-4 text-center">Dispen</th>
                <th class="px-3 py-4 text-center">Telat</th>
                <th class="px-3 py-4 text-center">Sakit</th>
                <th class="px-3 py-4 text-center">Izin</th>
                <th class="px-3 py-4 text-center">Alpa</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $no = 1;
            foreach ($rekapData as $row): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-800"><?= esc((string) $row['nama_siswa']) ?></div>
                        <div class="text-[11px] text-gray-500 font-medium bg-gray-100 px-2 py-0.5 rounded inline-block mt-1"><?= esc((string) $row['nis']) ?> • <?= esc((string) ($row['nama_kelas'] ?? '-')) ?></div>
                    </td>
                    <td class="px-3 py-4 text-center"><span class="text-emerald-700 font-bold text-xs"><?= (int) $row['Hadir'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-teal-700 font-bold text-xs"><?= (int) $row['Dispensasi'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-amber-700 font-bold text-xs"><?= (int) $row['Terlambat'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-blue-700 font-bold text-xs"><?= (int) $row['Sakit'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-indigo-700 font-bold text-xs"><?= (int) $row['Izin'] ?></span></td>
                    <td class="px-3 py-4 text-center"><span class="text-red-700 font-bold text-xs"><?= (int) $row['Alpa'] ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rekapData)): ?>
                <tr>
                    <td colspan="8" class="py-12 text-center text-gray-400 italic">Data rekapitulasi tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
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
            loader.classList.add('hidden');
            loader.classList.remove('flex');
        }, 3000);
    }
</script>
<?= $this->endSection() ?>