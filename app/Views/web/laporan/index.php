<?php

/**
 * @var array<int, array<string, mixed>> $listKelas
 * @var string $bulan
 * @var string $tahun
 * @var string $kelasId
 * @var array<int, array<string, mixed>> $rekapData
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Rekapitulasi Laporan</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau dan unduh rekap kehadiran bulanan siswa.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <form action="<?= base_url('admin/laporan') ?>" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="w-full md:w-auto flex-1">
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Kelas</label>
            <select name="kelas" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                <option value="">-- Semua Kelas --</option>
                <?php foreach ($listKelas as $k): ?>
                    <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ($kelasId === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                        <?= esc((string) $k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="w-full md:w-48">
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Bulan</label>
            <select name="bulan" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                <?php
                $namaBulan = [
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember'
                ];
                foreach ($namaBulan as $num => $name): ?>
                    <option value="<?= esc((string) $num) ?>" <?= ($bulan === (string) $num) ? 'selected' : '' ?>>
                        <?= esc((string) $name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="w-full md:w-32">
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tahun</label>
            <select name="tahun" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                <?php
                $tahunSekarang = (int) date('Y');
                for ($i = $tahunSekarang; $i >= $tahunSekarang - 3; $i--): ?>
                    <option value="<?= esc((string) $i) ?>" <?= ($tahun === (string) $i) ? 'selected' : '' ?>>
                        <?= esc((string) $i) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="w-full md:w-auto flex gap-2">
            <button type="submit" class="flex-1 md:flex-none bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-blue-700 transition-all h-[42px] flex items-center justify-center">
                Tampilkan
            </button>
            <a href="<?= base_url('admin/laporan/export?kelas=' . $kelasId . '&bulan=' . $bulan . '&tahun=' . $tahun) ?>" class="flex-1 md:flex-none bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-emerald-700 transition-all h-[42px] flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                    <th class="px-6 py-4">No</th>
                    <th class="px-6 py-4">Informasi Siswa</th>
                    <th class="px-4 py-4 text-center">Hadir</th>
                    <th class="px-4 py-4 text-center">Terlambat</th>
                    <th class="px-4 py-4 text-center">Sakit</th>
                    <th class="px-4 py-4 text-center">Izin</th>
                    <th class="px-4 py-4 text-center">Alpa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($rekapData)) : ?>
                    <?php $no = 1;
                    foreach ($rekapData as $row): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $row['nama_siswa']) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium mt-1">
                                    <?= esc((string) $row['nis']) ?> • <?= esc((string) ($row['nama_kelas'] ?? '-')) ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-block w-8 py-1 rounded bg-emerald-50 text-emerald-700 font-bold text-xs border border-emerald-100"><?= (int) $row['Hadir'] ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-block w-8 py-1 rounded bg-amber-50 text-amber-700 font-bold text-xs border border-amber-100"><?= (int) $row['Terlambat'] ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-block w-8 py-1 rounded bg-blue-50 text-blue-700 font-bold text-xs border border-blue-100"><?= (int) $row['Sakit'] ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-block w-8 py-1 rounded bg-indigo-50 text-indigo-700 font-bold text-xs border border-indigo-100"><?= (int) $row['Izin'] ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-block w-8 py-1 rounded bg-red-50 text-red-700 font-bold text-xs border border-red-100"><?= (int) $row['Alpa'] ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-500">Belum ada data absensi pada periode ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>