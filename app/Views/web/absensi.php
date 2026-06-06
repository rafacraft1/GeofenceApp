<?php

/**
 * @var string $title
 * @var string $tanggal
 * @var string|int|null $kelas_aktif
 * @var string $search
 * @var array<int, array<string, string|null>> $absensi
 * @var array<int, array<string, string|null>> $siswa
 * @var array<int, array<string, string|null>> $list_kelas
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
    unset($get['page_absensi']);
    return '?' . http_build_query($get);
};

$renderSortIcon = function ($column) use ($currentSort, $currentDir) {
    if ($currentSort !== $column) {
        return '<svg class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>';
    }
    if ($currentDir === 'ASC') {
        return '<svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path></svg>';
    }
    return '<svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>';
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

    .modal-active {
        overflow: hidden;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Data Absensi Harian</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau kehadiran, keterlambatan, dan input absen manual.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto md:flex-1 max-w-3xl">

        <div class="w-full sm:w-auto shrink-0">
            <input type="date" id="filter-tanggal" value="<?= esc((string) $tanggal) ?>" class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer bg-gray-50 font-medium">
        </div>

        <div class="w-full sm:w-1/4 shrink-0">
            <?php if ($is_wali_kelas): ?>
                <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none font-medium" readonly>
            <?php else: ?>
                <select id="filter-kelas" class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer font-medium">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($list_kelas as $k): ?>
                        <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelas_aktif === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                            <?= esc((string) $k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <div class="relative w-full flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" id="live-search" value="<?= esc($search ?? '') ?>" placeholder="Cari nama siswa..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
    </div>

    <button onclick="openManualModal()" class="w-full md:w-auto shrink-0 flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md transition-all active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Input Manual
    </button>
</div>

<div id="data-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">

    <div id="loading-overlay" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 hidden items-center justify-center">
        <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="overflow-x-auto min-h-[300px]">
        <table class="w-full text-left" id="absensi-table">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100 select-none">
                    <th class="px-6 py-4 w-12 text-center">No</th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortUrl('nama_siswa') ?>" class="group flex items-center gap-2 hover:text-blue-600 transition-colors" title="Urutkan berdasarkan nama">
                            Identitas Siswa
                            <?= $renderSortIcon('nama_siswa') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortUrl('waktu') ?>" class="group flex items-center gap-2 hover:text-blue-600 transition-colors" title="Urutkan berdasarkan waktu">
                            Waktu Presensi
                            <?= $renderSortIcon('waktu') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4 text-center">
                        <a href="<?= $buildSortUrl('status') ?>" class="group flex justify-center items-center gap-2 hover:text-blue-600 transition-colors" title="Urutkan berdasarkan status">
                            Status
                            <?= $renderSortIcon('status') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($absensi)) : ?>
                    <?php
                    $no = ($page - 1) * $perPage + 1;
                    foreach ($absensi as $ab): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium text-center"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $ab['nama_siswa']) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium mt-1">
                                    <?= esc((string) $ab['nis']) ?> • <?= esc((string) ($ab['nama_kelas'] ?? '-')) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-mono text-gray-700 font-semibold">
                                    M: <?= esc((string) ($ab['jam_masuk'] ?? '--:--:--')) ?>
                                </div>
                                <div class="text-xs font-mono text-gray-500 mt-1">
                                    P: <?= esc((string) ($ab['jam_pulang'] ?? '--:--:--')) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                $status = (string) $ab['status'];
                                $badgeColor = match ($status) {
                                    'Hadir', 'Dispensasi' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'Terlambat'           => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Sakit', 'Izin'       => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'Alpa', 'Manipulasi'  => 'bg-red-100 text-red-700 border-red-200',
                                    default               => 'bg-gray-100 text-gray-700 border-gray-200'
                                };
                                ?>
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold border uppercase tracking-wide <?= $badgeColor ?>">
                                    <?= esc($status) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500"><?= esc((string) ($ab['keterangan'] ?? '-')) ?></span>
                                <?php if (!empty($ab['is_fake_gps'])): ?>
                                    <div class="mt-1 text-[10px] font-bold text-red-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Fake GPS
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center flex-col items-center justify-center">
                            <div class="mb-3 text-gray-300"><i class="fas fa-folder-open text-4xl"></i></div>
                            <span class="text-gray-500 font-medium"><?= !empty($search) ? 'Pencarian tidak ditemukan.' : 'Belum ada data absensi untuk tanggal ini.' ?></span>
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
            Menampilkan <span class="font-bold text-gray-800"><?= (int) $start ?></span> - <span class="font-bold text-gray-800"><?= (int) $end ?></span> dari <span class="font-bold text-gray-800"><?= (int) $total_data ?></span> data
        </div>

        <?php if (!empty($pager_links)): ?>
            <div class="pagination-wrapper"><?= $pager_links ?></div>
        <?php endif; ?>
    </div>
</div>

<div id="modal-manual" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeManualModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-lg p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Input Absensi Manual</h3>
            <button onclick="closeManualModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="<?= base_url('admin/absensi/inputManual') ?>" method="POST" id="form-manual" class="space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="tanggal" value="<?= esc((string) $tanggal) ?>" id="input-modal-tanggal">

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Siswa</label>
                <select name="siswa_id" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                    <option value="" disabled selected>-- Cari / Pilih Siswa --</option>
                    <?php foreach ($siswa as $s): ?>
                        <option value="<?= esc((string) $s['id_siswa']) ?>">
                            <?= esc((string) $s['nama_siswa']) ?> (<?= esc((string) ($s['nama_kelas'] ?? '-')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Status Kehadiran</label>
                <select name="status" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                    <option value="Hadir">Hadir</option>
                    <option value="Terlambat">Terlambat</option>
                    <option value="Dispensasi">Dispensasi</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Catatan tambahan...">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeManualModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-400">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 btn-submit transition-all">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function openManualModal() {
        // Sinkronisasi tanggal modal dengan tanggal filter aktif saat ini
        const activeDate = document.getElementById('filter-tanggal').value;
        document.getElementById('input-modal-tanggal').value = activeDate;

        document.getElementById('modal-manual').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function closeManualModal() {
        document.getElementById('modal-manual').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    document.getElementById('form-manual').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        btn.classList.add('btn-loading');
        btn.setAttribute('disabled', 'true');
    });

    // ==========================================
    // LOGIKA AJAX "HTML OVER THE WIRE" (SPA UX)
    // ==========================================
    const dataContainer = document.getElementById('data-container');
    const searchInput = document.getElementById('live-search');
    const filterTanggal = document.getElementById('filter-tanggal');
    const filterKelas = document.getElementById('filter-kelas');

    function fetchAbsensiData(url) {
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

    // A. Event: Live Search (Debounce)
    let searchTimer;
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
            url.searchParams.delete('page_absensi');

            fetchAbsensiData(url.toString());
        }, 400);
    });

    // B. Event: Ubah Tanggal
    filterTanggal.addEventListener('change', function() {
        const url = new window.URL(window.location.href);
        url.searchParams.set('tanggal', this.value);
        url.searchParams.delete('page_absensi');
        fetchAbsensiData(url.toString());
    });

    // C. Event: Ubah Kelas
    if (filterKelas) {
        filterKelas.addEventListener('change', function() {
            const url = new window.URL(window.location.href);
            if (this.value) {
                url.searchParams.set('kelas_id', this.value);
            } else {
                url.searchParams.delete('kelas_id');
            }
            url.searchParams.delete('page_absensi');
            fetchAbsensiData(url.toString());
        });
    }

    // D. Event Delegation: Pagination & Sortable
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-wrapper a, thead th a');
        if (link) {
            e.preventDefault();
            fetchAbsensiData(link.href);
        }
    });
</script>
<?= $this->endSection() ?>