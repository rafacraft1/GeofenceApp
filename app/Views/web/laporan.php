<?php

/**
 * @var array<int, array<string, mixed>> $listKelas
 * @var string $bulanMulai
 * @var string $bulanSelesai
 * @var string $tahun
 * @var string|int $kelasId
 * @var string $search
 * @var array<int, array<string, mixed>> $rekapData
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 * @var string $pager_links
 */
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
        transition: all 0.2s;
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
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Rekapitulasi Laporan</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau rentang kehadiran bulanan siswa secara komprehensif.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 relative overflow-hidden">
    <div class="absolute right-0 top-0 w-32 h-32 bg-blue-50 rounded-bl-full opacity-50 z-0"></div>
    <div class="relative z-10 flex flex-col lg:flex-row gap-4 items-end">

        <div class="w-full lg:flex-1">
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pencarian Siswa</label>
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="live-search" value="<?= esc($search ?? '') ?>" placeholder="Ketik Nama atau NIS..." class="w-full border-gray-200 rounded-xl py-2.5 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 w-full lg:w-auto">
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Kelas</label>
                <?php if (session()->get('is_wali_kelas')): ?>
                    <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full border-gray-200 rounded-xl p-2.5 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none" readonly>
                    <input type="hidden" id="filter-kelas" value="<?= session()->get('kelas_id') ?>">
                <?php else: ?>
                    <select id="filter-kelas" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                        <option value="">-- Semua --</option>
                        <?php foreach ($listKelas as $k): ?>
                            <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelasId === (string) $k['id_kelas']) ? 'selected' : '' ?>><?= esc((string) $k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Mulai</label>
                <select id="filter-bulan-mulai" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                    <?php
                    $namaBulan = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
                    foreach ($namaBulan as $num => $name): ?>
                        <option value="<?= esc((string) $num) ?>" <?= ($bulanMulai === (string) $num) ? 'selected' : '' ?>><?= esc((string) $name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Sampai</label>
                <select id="filter-bulan-selesai" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                    <?php foreach ($namaBulan as $num => $name): ?>
                        <option value="<?= esc((string) $num) ?>" <?= ($bulanSelesai === (string) $num) ? 'selected' : '' ?>><?= esc((string) $name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tahun</label>
                <select id="filter-tahun" class="w-full border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                    <?php $tahunSekarang = (int) date('Y');
                    for ($i = $tahunSekarang; $i >= $tahunSekarang - 3; $i--): ?>
                        <option value="<?= esc((string) $i) ?>" <?= ($tahun === (string) $i) ? 'selected' : '' ?>><?= esc((string) $i) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="w-full lg:w-auto shrink-0">
            <a href="<?= base_url('admin/laporan/export?kelas=' . $kelasId . '&bulan_mulai=' . $bulanMulai . '&bulan_selesai=' . $bulanSelesai . '&tahun=' . $tahun . '&search=' . $search) ?>" id="btn-export" onclick="showExportLoading(this)" class="w-full lg:w-32 bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-emerald-700 transition-all h-[42px] flex items-center justify-center gap-2 relative overflow-hidden group">
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
    </div>
</div>

<div id="data-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">

    <div id="loading-overlay" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 hidden items-center justify-center">
        <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="overflow-x-auto min-h-[300px]">
        <table class="w-full text-left" id="dataTable">
            <thead class="bg-gray-50/95 backdrop-blur shadow-sm z-10 text-gray-500 text-[10px] font-bold uppercase tracking-wider border-b border-gray-100">
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
                <?php if (!empty($rekapData)) : ?>
                    <?php foreach ($rekapData as $row):
                        $pct = (int) $row['Persentase'];
                        $barColor = $pct >= 85 ? 'bg-emerald-500' : ($pct >= 60 ? 'bg-amber-400' : 'bg-red-500');
                    ?>
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $row['nama_siswa']) ?></div>
                                <div class="text-[10px] text-gray-500 font-medium"><?= esc((string) $row['nis']) ?> • <?= esc((string) $row['nama_kelas']) ?></div>
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
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-500 font-medium">
                            <i class="fas fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                            Tidak ada data rekapitulasi untuk parameter yang dipilih.
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
            Menampilkan <span class="font-bold text-gray-800"><?= (int) $start ?></span> - <span class="font-bold text-gray-800"><?= (int) $end ?></span> dari <span class="font-bold text-gray-800"><?= (int) $total_data ?></span> siswa
        </div>

        <?php if (!empty($pager_links)): ?>
            <div class="pagination-wrapper"><?= $pager_links ?></div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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

    // ==========================================
    // LOGIKA AJAX "HTML OVER THE WIRE" (SPA UX)
    // ==========================================
    const dataContainer = document.getElementById('data-container');
    const searchInput = document.getElementById('live-search');
    const filterKelas = document.getElementById('filter-kelas');
    const filterMulai = document.getElementById('filter-bulan-mulai');
    const filterSelesai = document.getElementById('filter-bulan-selesai');
    const filterTahun = document.getElementById('filter-tahun');
    const btnExport = document.getElementById('btn-export');

    function fetchLaporanData(url) {
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

                // Update URL Tombol Export
                const urlObj = new window.URL(url);
                const queryParams = urlObj.search;
                btnExport.href = '<?= base_url('admin/laporan/export') ?>' + queryParams;
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
                if (keyword) url.searchParams.set('search', keyword);
                else url.searchParams.delete('search');
                url.searchParams.delete('page_laporan');
                fetchLaporanData(url.toString());
            }, 400);
        });
    }

    [filterKelas, filterMulai, filterSelesai, filterTahun].forEach(element => {
        if (element) {
            element.addEventListener('change', function() {
                const url = new window.URL(window.location.href);

                // Mapping ID element ke parameter URL
                let paramName = '';
                if (this.id === 'filter-kelas') paramName = 'kelas';
                if (this.id === 'filter-bulan-mulai') paramName = 'bulan_mulai';
                if (this.id === 'filter-bulan-selesai') paramName = 'bulan_selesai';
                if (this.id === 'filter-tahun') paramName = 'tahun';

                if (this.value) url.searchParams.set(paramName, this.value);
                else url.searchParams.delete(paramName);

                url.searchParams.delete('page_laporan');
                fetchLaporanData(url.toString());
            });
        }
    });

    // Event Delegation: Pagination
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-wrapper a');
        if (link) {
            e.preventDefault();
            fetchLaporanData(link.href);
        }
    });
</script>
<?= $this->endSection() ?>