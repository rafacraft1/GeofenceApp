<?php

/**
 * @var array<string, mixed> $siswa
 * @var array<int, array<string, mixed>> $absensi
 * @var array<int, array<string, mixed>> $logFraud
 * @var array<string, int> $stats
 * @var string|null $start_date
 * @var string|null $end_date
 * @var string|null $pager_absensi
 * @var int $page_absensi
 * @var int $per_page_absensi
 * @var int $total_absensi
 */

// Closure UI Helper untuk Badge Status Presensi
$badgeStatus = function (string $status) {
    return match ($status) {
        'Hadir'     => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'Terlambat' => 'bg-amber-100 text-amber-700 border-amber-200',
        'Sakit'     => 'bg-purple-100 text-purple-700 border-purple-200',
        'Izin'      => 'bg-blue-100 text-blue-700 border-blue-200',
        default     => 'bg-red-100 text-red-700 border-red-200', // Alpa
    };
};
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<style>
    .pagination {
        display: flex;
        flex-wrap: wrap;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 0.35rem;
    }

    .pagination li a,
    .pagination li.active span,
    .pagination li.disabled span {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 34px;
        min-width: 34px;
        padding: 0 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        background-color: #fff;
        color: #4b5563;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    .pagination li a span {
        display: inline;
        padding: 0;
        border: none;
        background: transparent;
        color: inherit;
    }

    .pagination li a:hover {
        background-color: #f8fafc;
        color: #1e293b;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .pagination li.active span {
        background-color: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 4px 10px -2px rgba(37, 99, 235, 0.3);
    }

    .pagination li.disabled span {
        color: #94a3b8;
        background-color: #f1f5f9;
        cursor: not-allowed;
        border-color: #e2e8f0;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Profil 360 Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Detail informasi, statistik, dan riwayat aktivitas keseluruhan.</p>
    </div>
    <a href="<?= base_url('admin/siswa') ?>" class="bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-xl font-semibold hover:bg-gray-50 transition-colors text-sm flex items-center justify-center gap-2 shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center h-fit">
        <div class="w-24 h-24 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-3xl shadow-inner overflow-hidden border-4 border-white ring-2 ring-gray-100 shrink-0 mb-4">
            <?php if (!empty($siswa['foto_profil'])): ?>
                <img src="<?= base_url('uploads/siswa/' . (string) $siswa['foto_profil']) ?>" alt="Foto Profil <?= esc((string) $siswa['nama_siswa']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <?= esc(strtoupper(substr((string) $siswa['nama_siswa'], 0, 1))) ?>
            <?php endif; ?>
        </div>

        <h3 class="text-xl font-bold text-gray-800"><?= esc((string) $siswa['nama_siswa']) ?></h3>
        <p class="text-sm text-gray-500 font-medium mb-6"><?= esc((string) $siswa['nis']) ?> • <?= esc((string) ($siswa['nama_kelas'] ?? 'Belum ada kelas')) ?></p>

        <div class="w-full grid grid-cols-2 gap-3 mt-auto">
            <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100 flex flex-col items-center justify-center">
                <div class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider mb-1">Hadir</div>
                <div class="text-xl font-black text-emerald-700"><?= (int) $stats['hadir'] ?></div>
            </div>
            <div class="bg-amber-50 p-3 rounded-xl border border-amber-100 flex flex-col items-center justify-center">
                <div class="text-[10px] text-amber-600 font-bold uppercase tracking-wider mb-1">Terlambat</div>
                <div class="text-xl font-black text-amber-700"><?= (int) $stats['terlambat'] ?></div>
            </div>
            <div class="bg-indigo-50 p-3 rounded-xl border border-indigo-100 flex flex-col items-center justify-center">
                <div class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider mb-1">Sakit/Izin</div>
                <div class="text-xl font-black text-indigo-700"><?= (int) ($stats['sakit'] + $stats['izin']) ?></div>
            </div>
            <div class="bg-red-50 p-3 rounded-xl border border-red-100 flex flex-col items-center justify-center">
                <div class="text-[10px] text-red-600 font-bold uppercase tracking-wider mb-1">Alpa</div>
                <div class="text-xl font-black text-red-700"><?= (int) $stats['alpa'] ?></div>
            </div>
        </div>

        <?php if (!empty($start_date)): ?>
            <div class="mt-4 text-[10px] text-gray-400 font-medium w-full text-center bg-gray-50 p-2 rounded-lg border border-gray-100">
                Statistik di atas difilter sejak <br><span class="font-bold text-gray-600"><?= date('d M Y', strtotime($start_date)) ?></span> s.d <span class="font-bold text-gray-600"><?= date('d M Y', strtotime($end_date)) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b bg-gray-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h4 class="font-bold text-gray-800 text-sm whitespace-nowrap">Riwayat Presensi</h4>

                <form action="" method="GET" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg p-1.5 shadow-sm flex-1 sm:flex-none">
                        <input type="date" name="start_date" value="<?= esc($start_date ?? '') ?>" required class="px-2 py-1 text-xs outline-none bg-transparent text-gray-600 font-medium cursor-pointer w-full sm:w-auto">
                        <span class="text-gray-300 font-bold">-</span>
                        <input type="date" name="end_date" value="<?= esc($end_date ?? '') ?>" required class="px-2 py-1 text-xs outline-none bg-transparent text-gray-600 font-medium cursor-pointer w-full sm:w-auto">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2.5 rounded-lg text-xs font-bold hover:bg-blue-700 transition-colors shadow-sm whitespace-nowrap">
                        Filter
                    </button>
                    <?php if (!empty($start_date) || !empty($end_date)): ?>
                        <a href="<?= base_url('admin/siswa/detail/' . (string) $siswa['id_siswa']) ?>" class="bg-gray-100 text-gray-600 px-3 py-2.5 rounded-lg text-xs font-bold hover:bg-gray-200 transition-colors border border-gray-200 shadow-sm whitespace-nowrap" title="Hapus Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="overflow-x-auto relative">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/30">
                        <tr class="text-gray-500 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-center">Waktu (M - P)</th>
                            <th class="px-5 py-3 text-center">Bukti Foto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($absensi)): ?>
                            <?php foreach ($absensi as $ab): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 font-medium text-gray-800 whitespace-nowrap">
                                        <?= date('d M Y', strtotime((string) $ab['tanggal'])) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md border <?= $badgeStatus((string) $ab['status']) ?>">
                                            <?= esc((string) $ab['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 font-mono text-xs whitespace-nowrap text-center">
                                        <?= esc((string) ($ab['jam_masuk'] ?? '-')) ?> &mdash; <?= esc((string) ($ab['jam_pulang'] ?? '-')) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <?php if (!empty($ab['foto_masuk'])): ?>
                                                <a href="<?= base_url('uploads/absensi/' . (string) $ab['foto_masuk']) ?>" target="_blank" class="block relative group" title="Lihat Foto Masuk">
                                                    <img src="<?= base_url('uploads/absensi/' . (string) $ab['foto_masuk']) ?>" class="w-8 h-8 rounded-lg object-cover border border-gray-200 shadow-sm group-hover:ring-2 ring-blue-400 transition-all" alt="Foto Masuk">
                                                    <div class="absolute -bottom-1 -right-1 bg-blue-500 text-white text-[8px] font-bold px-1 rounded shadow-sm">M</div>
                                                </a>
                                            <?php else: ?>
                                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-200 border-dashed text-gray-400 text-[10px] relative" title="Tidak ada foto masuk">
                                                    <div class="absolute -bottom-1 -right-1 bg-gray-300 text-white text-[8px] font-bold px-1 rounded">M</div>
                                                    -
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($ab['foto_pulang'])): ?>
                                                <a href="<?= base_url('uploads/absensi/' . (string) $ab['foto_pulang']) ?>" target="_blank" class="block relative group" title="Lihat Foto Pulang">
                                                    <img src="<?= base_url('uploads/absensi/' . (string) $ab['foto_pulang']) ?>" class="w-8 h-8 rounded-lg object-cover border border-gray-200 shadow-sm group-hover:ring-2 ring-amber-400 transition-all" alt="Foto Pulang">
                                                    <div class="absolute -bottom-1 -right-1 bg-amber-500 text-white text-[8px] font-bold px-1 rounded shadow-sm">P</div>
                                                </a>
                                            <?php else: ?>
                                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-200 border-dashed text-gray-400 text-[10px] relative" title="Tidak ada foto pulang">
                                                    <div class="absolute -bottom-1 -right-1 bg-gray-300 text-white text-[8px] font-bold px-1 rounded">P</div>
                                                    -
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-gray-400 flex-col items-center justify-center">
                                    <div class="mb-2"><i class="fas fa-folder-open text-3xl text-gray-300"></i></div>
                                    Tidak ada data presensi pada periode ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-xs text-gray-500 font-medium">
                    <?php
                    $pageAbsensi = $page_absensi ?? 1;
                    $perPageAbsensi = $per_page_absensi ?? 10;
                    $totalAbsensi = $total_absensi ?? 0;

                    $startAbsensi = $totalAbsensi > 0 ? (($pageAbsensi - 1) * $perPageAbsensi) + 1 : 0;
                    $endAbsensi = min($pageAbsensi * $perPageAbsensi, $totalAbsensi);
                    ?>
                    Menampilkan <span class="font-bold text-gray-800"><?= (int) $startAbsensi ?></span> - <span class="font-bold text-gray-800"><?= (int) $endAbsensi ?></span> dari <span class="font-bold text-gray-800"><?= (int) $totalAbsensi ?></span> presensi
                </div>

                <?php if (!empty($pager_absensi)): ?>
                    <div class="pagination-wrapper"><?= $pager_absensi ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col border-t-4 border-t-red-500">
            <div class="p-5 border-b bg-gray-50/50 flex justify-between items-center">
                <h4 class="font-bold text-red-600 text-sm">Riwayat Pelanggaran (Fraud Log)</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($logFraud)): ?>
                            <?php foreach ($logFraud as $log): ?>
                                <tr class="hover:bg-red-50/30 transition-colors">
                                    <td class="px-5 py-4 font-medium text-gray-800 whitespace-nowrap">
                                        <?= date('d M Y H:i', strtotime((string) $log['created_at'])) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2.5 py-1 rounded-md border border-red-200 uppercase">
                                            <?= esc((string) $log['tipe_fraud']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="px-5 py-10 text-center text-gray-400 italic">Bersih. Tidak ada catatan pelanggaran perangkat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>