<?php

/**
 * @var string $title
 * @var string $search
 * @var string|null $kelas_aktif
 * @var string|null $tipe_aktif
 * @var array<int, array<string, mixed>> $log_data
 * @var array<int, array<string, string>> $list_kelas
 * @var array<int, array<string, string>> $list_tipe
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 * @var string $pager_links
 * @var bool $is_wali_kelas
 */

// Logika Fungsi Sortable UI
$currentSort = $_GET['sort'] ?? 'waktu';
$currentDir  = strtoupper($_GET['dir'] ?? 'DESC');

$buildSortUrl = function ($column) use ($currentSort, $currentDir) {
    $get = $_GET;
    $get['sort'] = $column;
    $get['dir']  = ($currentSort === $column && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    unset($get['page_fraud']);
    return '?' . http_build_query($get);
};

$renderSortIcon = function ($column) use ($currentSort, $currentDir) {
    if ($currentSort !== $column) {
        return '<svg class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>';
    }
    if ($currentDir === 'ASC') {
        return '<svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path></svg>';
    }
    return '<svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>';
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
        height: 38px;
        min-width: 38px;
        padding: 0 0.85rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        background-color: #fff;
        color: #4b5563;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    .pagination li a:hover {
        background-color: #fef2f2;
        color: #b91c1c;
        border-color: #fca5a5;
        transform: translateY(-1px);
    }

    .pagination li.active span {
        background-color: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
        box-shadow: 0 4px 10px -2px rgba(239, 68, 68, 0.3);
    }

    .pagination li.disabled span {
        color: #94a3b8;
        background-color: #f1f5f9;
        cursor: not-allowed;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-shield-alt text-red-500"></i>
            Log Pelanggaran Keamanan
        </h2>
        <p class="text-sm text-gray-500 mt-1">Pantau riwayat deteksi Fake GPS dan manipulasi waktu dari aplikasi siswa.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5 mb-6 flex flex-col md:flex-row gap-4 items-center relative overflow-hidden">
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-orange-500"></div>

    <div class="flex flex-col sm:flex-row gap-3 w-full">
        <div class="relative w-full sm:flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" id="live-search" value="<?= esc($search ?? '') ?>" placeholder="Cari NIS / Nama Pelanggar..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-red-500 transition-all">
        </div>

        <?php if (!$is_wali_kelas): ?>
            <div class="w-full sm:w-1/4 shrink-0">
                <select id="filter-kelas" class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-sm outline-none focus:ring-2 focus:ring-red-500 transition-all bg-gray-50 cursor-pointer font-medium">
                    <option value="">Semua Kelas</option>
                    <?php if (!empty($list_kelas)): ?>
                        <?php foreach ($list_kelas as $k): ?>
                            <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelas_aktif === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                                <?= esc((string) $k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="w-full sm:w-1/4 shrink-0">
            <select id="filter-tipe" class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-sm outline-none focus:ring-2 focus:ring-red-500 transition-all bg-gray-50 cursor-pointer font-medium text-red-600">
                <option value="" class="text-gray-800">Semua Anomali</option>
                <?php if (!empty($list_tipe)): ?>
                    <?php foreach ($list_tipe as $t): ?>
                        <?php if (!empty($t['tipe_fraud'])): ?>
                            <option value="<?= esc((string) $t['tipe_fraud']) ?>" <?= ((string) $tipe_aktif === (string) $t['tipe_fraud']) ? 'selected' : '' ?>>
                                <?= esc((string) $t['tipe_fraud']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
</div>

<div id="data-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">

    <div id="loading-overlay" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 hidden items-center justify-center">
        <div class="w-8 h-8 border-4 border-red-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="overflow-x-auto min-h-[300px]">
        <table class="w-full text-left" id="fraud-table">
            <thead class="bg-red-50/30">
                <tr class="text-gray-500 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100 select-none">
                    <th class="px-6 py-4 w-12 text-center">No</th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortUrl('waktu') ?>" class="group flex items-center gap-2 hover:text-red-600 transition-colors" title="Urutkan berdasarkan waktu">
                            Waktu Kejadian
                            <?= $renderSortIcon('waktu') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortUrl('nama_siswa') ?>" class="group flex items-center gap-2 hover:text-red-600 transition-colors" title="Urutkan berdasarkan nama">
                            Identitas Siswa
                            <?= $renderSortIcon('nama_siswa') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortUrl('tipe') ?>" class="group flex items-center gap-2 hover:text-red-600 transition-colors" title="Urutkan berdasarkan tipe anomali">
                            Tipe Pelanggaran
                            <?= $renderSortIcon('tipe') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">Titik Koordinat Palsu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($log_data)) : ?>
                    <?php
                    $no = ($page - 1) * $perPage + 1;
                    foreach ($log_data as $log): ?>
                        <tr class="hover:bg-red-50/30 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium text-center"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800">
                                    <?= date('d M Y', strtotime((string) $log['created_at'])) ?>
                                </div>
                                <div class="text-[11px] font-mono text-gray-500 mt-0.5">
                                    <i class="far fa-clock mr-1"></i> <?= date('H:i:s', strtotime((string) $log['created_at'])) ?> WIB
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-[10px] shrink-0 border border-red-200">
                                        <?= esc(strtoupper(substr((string) ($log['nama_siswa'] ?? ''), 0, 1))) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800"><?= esc((string) $log['nama_siswa']) ?></div>
                                        <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                            <?= esc((string) $log['nis']) ?> • <?= esc((string) ($log['nama_kelas'] ?? '-')) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $tipe = (string) $log['tipe_fraud'];
                                $bgBadge = strpos(strtolower($tipe), 'fake') !== false ? 'bg-red-100 text-red-700 border-red-200' : 'bg-orange-100 text-orange-700 border-orange-200';
                                ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[10px] font-black border uppercase tracking-wide <?= $bgBadge ?>">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?= esc($tipe) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($log['lat_fraud']) && !empty($log['long_fraud'])): ?>
                                    <div class="text-xs font-mono text-gray-600 bg-gray-50 px-2 py-1.5 rounded border border-gray-200 inline-block mb-1.5 shadow-sm">
                                        <?= esc((string) $log['lat_fraud']) ?>, <?= esc((string) $log['long_fraud']) ?>
                                    </div>
                                    <br>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= esc((string) $log['lat_fraud']) ?>,<?= esc((string) $log['long_fraud']) ?>" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-blue-600 hover:text-blue-800 hover:underline font-bold bg-blue-50 px-2 py-1 rounded border border-blue-100 transition-colors">
                                        <i class="fas fa-map-marked-alt"></i> Lihat Peta
                                    </a>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-400 italic">Tidak ada koordinat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center flex-col items-center justify-center">
                            <div class="mb-3 text-emerald-300"><i class="fas fa-shield-check text-5xl"></i></div>
                            <span class="text-gray-500 font-medium block">
                                <?= !empty($search) || !empty($tipe_aktif) ? 'Tidak ditemukan data yang sesuai filter pencarian.' : 'Aman Terkendali. Belum ada catatan pelanggaran keamanan.' ?>
                            </span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="p-5 bg-gray-50/30 flex flex-col md:flex-row justify-between items-center gap-4 border-t border-gray-100">
        <div class="text-sm text-gray-500 font-medium">
            <?php
            $page = $page ?? 1;
            $perPage = $perPage ?? 15;
            $total_data = $total_data ?? 0;
            $start = $total_data > 0 ? (($page - 1) * $perPage) + 1 : 0;
            $end = min($page * $perPage, $total_data);
            ?>
            Menampilkan <span class="font-bold text-gray-800"><?= (int) $start ?></span> - <span class="font-bold text-gray-800"><?= (int) $end ?></span> dari <span class="font-bold text-gray-800"><?= (int) $total_data ?></span> log
        </div>

        <?php if (!empty($pager_links)): ?>
            <div class="pagination-wrapper"><?= $pager_links ?></div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const dataContainer = document.getElementById('data-container');
    const searchInput = document.getElementById('live-search');
    const filterKelas = document.getElementById('filter-kelas');
    const filterTipe = document.getElementById('filter-tipe');

    function fetchFraudData(url) {
        window.history.pushState({}, '', url);

        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.replace('hidden', 'flex');

        fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new window.DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newContainer = doc.querySelector('#data-container');
                if (newContainer) {
                    dataContainer.innerHTML = newContainer.innerHTML;
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                if (overlay) overlay.classList.replace('flex', 'hidden');
            });
    }

    let searchTimer;
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimer);
            const keyword = e.target.value.trim();

            searchTimer = setTimeout(() => {
                const url = new window.URL(window.location.href);
                if (keyword) {
                    url.searchParams.set('search', keyword);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('page_fraud');

                fetchFraudData(url.toString());
            }, 400);
        });
    }

    if (filterKelas) {
        filterKelas.addEventListener('change', function() {
            const url = new window.URL(window.location.href);
            if (this.value) {
                url.searchParams.set('kelas_id', this.value);
            } else {
                url.searchParams.delete('kelas_id');
            }
            url.searchParams.delete('page_fraud');
            fetchFraudData(url.toString());
        });
    }

    if (filterTipe) {
        filterTipe.addEventListener('change', function() {
            const url = new window.URL(window.location.href);
            if (this.value) {
                url.searchParams.set('tipe_fraud', this.value);
            } else {
                url.searchParams.delete('tipe_fraud');
            }
            url.searchParams.delete('page_fraud');
            fetchFraudData(url.toString());
        });
    }

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-wrapper a, thead th a');
        if (link) {
            e.preventDefault();
            fetchFraudData(link.href);
        }
    });
</script>
<?= $this->endSection() ?>