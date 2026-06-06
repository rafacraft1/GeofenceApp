<?php

/**
 * @var array<int, array<string, mixed>> $daftarIzin
 * @var string $search
 * @var string|null $status
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
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Persetujuan Izin & Sakit</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola pengajuan tidak masuk sekolah dari siswa.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row gap-4 items-center">
    <div class="flex flex-col sm:flex-row gap-3 w-full max-w-2xl">
        <div class="relative w-full sm:w-2/3">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" id="live-search" value="<?= esc($search ?? '') ?>" placeholder="Cari nama siswa..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>

        <div class="w-full sm:w-1/3">
            <select id="filter-status" class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer font-medium">
                <option value="">Semua Status</option>
                <option value="Pending" <?= ($status === 'Pending') ? 'selected' : '' ?>>Menunggu (Pending)</option>
                <option value="Approved" <?= ($status === 'Approved') ? 'selected' : '' ?>>Disetujui</option>
                <option value="Rejected" <?= ($status === 'Rejected') ? 'selected' : '' ?>>Ditolak</option>
            </select>
        </div>
    </div>
</div>

<div id="data-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col relative">

    <div id="loading-overlay" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 hidden items-center justify-center">
        <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="overflow-x-auto flex-1 min-h-[300px]">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                    <th class="px-6 py-4">Informasi Siswa</th>
                    <th class="px-6 py-4">Detail Pengajuan</th>
                    <th class="px-6 py-4 text-center">Bukti</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($daftarIzin)) : ?>
                    <?php foreach ($daftarIzin as $izin): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs shadow-inner border border-indigo-100 shrink-0">
                                        <?= esc(strtoupper(substr((string) ($izin['nama_siswa'] ?? ''), 0, 1))) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800"><?= esc((string) $izin['nama_siswa']) ?></div>
                                        <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                            <?= esc((string) $izin['nis']) ?> • <?= esc((string) ($izin['nama_kelas'] ?? '-')) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-700 mb-1">
                                    <span class="text-blue-600"><?= esc((string) $izin['jenis']) ?></span>
                                    <span class="text-gray-400 font-normal mx-1">&bull;</span>
                                    <?= date('d M', strtotime((string) $izin['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime((string) $izin['tanggal_selesai'])) ?>
                                </div>
                                <div class="text-xs text-gray-500 line-clamp-2 italic">
                                    "<?= esc((string) $izin['alasan']) ?>"
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (!empty($izin['bukti_foto'])): ?>
                                    <a href="<?= base_url('uploads/izin/' . (string) $izin['bukti_foto']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg border border-blue-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Lihat
                                    </a>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-400 italic">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                $statusLabel = (string) $izin['status'];
                                $badgeClass = match ($statusLabel) {
                                    'Approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    default    => 'bg-amber-50 text-amber-700 border-amber-200', // Pending
                                };
                                ?>
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold border uppercase tracking-wide <?= $badgeClass ?>">
                                    <?= esc($statusLabel) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ((string) $izin['status'] === 'Pending'): ?>
                                    <div class="flex justify-end gap-2">
                                        <form action="<?= base_url('admin/izin/approve/' . (string) $izin['id_izin']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-lg transition-colors" data-text="Setujui izin ini? Absensi akan otomatis diisi untuk rentang tanggal pengajuan." data-btn="Ya, Setujui" title="Approve">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </form>

                                        <form action="<?= base_url('admin/izin/reject/' . (string) $izin['id_izin']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition-colors" data-text="Tolak pengajuan izin ini?" data-btn="Ya, Tolak" title="Reject">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] font-semibold text-gray-400">Telah Diproses</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center flex-col items-center justify-center">
                            <div class="mb-3 text-gray-300"><i class="fas fa-folder-open text-4xl"></i></div>
                            <span class="text-gray-500 font-medium"><?= !empty($search) || !empty($status) ? 'Data pengajuan tidak ditemukan.' : 'Belum ada pengajuan izin.' ?></span>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ==========================================
    // LOGIKA AJAX "HTML OVER THE WIRE" (SPA UX)
    // ==========================================
    const dataContainer = document.getElementById('data-container');
    const searchInput = document.getElementById('live-search');
    const filterStatus = document.getElementById('filter-status');

    function fetchIzinData(url) {
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
            url.searchParams.delete('page_izin');

            fetchIzinData(url.toString());
        }, 400);
    });

    // B. Event: Filter Status
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            const url = new window.URL(window.location.href);
            if (this.value) {
                url.searchParams.set('status', this.value);
            } else {
                url.searchParams.delete('status');
            }
            url.searchParams.delete('page_izin');
            fetchIzinData(url.toString());
        });
    }

    // C. Event Delegation: Pagination
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-wrapper a');
        if (link) {
            e.preventDefault();
            fetchIzinData(link.href);
        }
    });
</script>
<?= $this->endSection() ?>