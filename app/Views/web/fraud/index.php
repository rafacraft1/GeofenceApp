<?php

/**
 * @var string $title
 * @var array<int, array<string, mixed>> $logFraud
 * @var string|null $pager_links
 * @var string $search
 * @var string $date
 * @var string $sort_col
 * @var string $sort_dir
 */

$buildSortUrl = function (string $column) use ($sort_col, $sort_dir, $search, $date) {
    $newDir = ($sort_col === $column && $sort_dir === 'asc') ? 'desc' : 'asc';
    $params = [];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($date)) $params['date'] = $date;
    $params['sort'] = "{$column}-{$newDir}";
    return '?' . http_build_query($params);
};

$sortIcon = function (string $column) use ($sort_col, $sort_dir) {
    if ($sort_col !== $column) return '<i class="fa-solid fa-sort text-gray-300 ml-1"></i>';
    return $sort_dir === 'asc'
        ? '<i class="fa-solid fa-sort-up text-blue-500 ml-1 translate-y-0.5"></i>'
        : '<i class="fa-solid fa-sort-down text-blue-500 ml-1 -translate-y-0.5"></i>';
};
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Log Pelanggaran Keamanan</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau riwayat deteksi Fake GPS dan manipulasi waktu dari perangkat siswa.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="p-4 border-b border-gray-100 bg-gray-50/50">
        <form action="" method="get" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="sort" value="<?= esc($sort_col . '-' . $sort_dir) ?>">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fa-solid fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="<?= esc($search) ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Cari nama siswa atau NIS...">
            </div>
            <div class="relative w-full sm:w-48">
                <input type="date" name="date" value="<?= esc($date) ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg text-sm px-5 py-2.5 transition-colors focus:ring-4 focus:ring-slate-200">
                    Filter Data
                </button>
                <?php if (!empty($search) || !empty($date)): ?>
                    <a href="<?= base_url('admin/log-fraud') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm px-4 py-2.5 transition-colors flex items-center">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/80 text-gray-500 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4 whitespace-nowrap">
                        <a href="<?= $buildSortUrl('created_at') ?>" class="flex items-center group hover:text-blue-600 transition-colors">
                            Waktu & Perangkat <?= $sortIcon('created_at') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4 whitespace-nowrap">
                        <a href="<?= $buildSortUrl('nama_siswa') ?>" class="flex items-center group hover:text-blue-600 transition-colors">
                            Identitas Siswa <?= $sortIcon('nama_siswa') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4 whitespace-nowrap">
                        <a href="<?= $buildSortUrl('tipe_fraud') ?>" class="flex items-center group hover:text-blue-600 transition-colors">
                            Tipe Pelanggaran <?= $sortIcon('tipe_fraud') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4 whitespace-nowrap">Titik Koordinat Forensik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($logFraud)) : ?>
                    <?php foreach ($logFraud as $log): ?>
                        <tr class="hover:bg-red-50/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= date('d M Y', strtotime((string) $log['created_at'])) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium mb-1"><?= date('H:i:s', strtotime((string) $log['created_at'])) ?> WIB</div>
                                <?php if (!empty($log['user_agent'])): ?>
                                    <div class="text-[10px] text-gray-400 max-w-[150px] truncate" title="<?= esc((string) $log['user_agent']) ?>">
                                        <i class="fa-solid fa-mobile-screen-button mr-1"></i> <?= esc((string) $log['user_agent']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $log['nama_siswa']) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium"><?= esc((string) $log['nis']) ?> • <?= esc((string) ($log['nama_kelas'] ?? '-')) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wide border border-red-200">
                                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                                    <?= esc((string) $log['tipe_fraud']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="text-xs font-mono text-gray-600 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200">
                                        <?= esc((string) $log['lat_fraud']) ?>, <?= esc((string) $log['long_fraud']) ?>
                                    </div>
                                    <a href="https://www.google.com/maps?q=<?= esc((string) $log['lat_fraud']) ?>,<?= esc((string) $log['long_fraud']) ?>" target="_blank" class="text-[11px] bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white px-2 py-1 rounded transition-colors font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-map-location-dot"></i> Peta
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-shield-check text-2xl"></i>
                                </div>
                                <h3 class="text-gray-800 font-bold text-lg">Keamanan Terkendali</h3>
                                <p class="text-gray-500 text-sm mt-1">Tidak ditemukan riwayat pelanggaran atau manipulasi sistem sesuai kriteria.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pager_links)) : ?>
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            <?= $pager_links ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>