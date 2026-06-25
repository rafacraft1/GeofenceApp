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
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-gray-400 italic">Data presensi tidak ditemukan.</td>
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
<?= $this->endSection() ?>