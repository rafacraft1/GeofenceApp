<?php

/**
 * @var string $title
 * @var array<int, array{id_libur: string|int, tanggal: string, keterangan: string}> $daftar_libur
 * @var string $search
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
        height: 34px;
        min-width: 34px;
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

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-umbrella-beach text-red-500"></i> Manajemen Hari Libur
        </h2>
        <p class="text-sm text-gray-500 mt-1">Atur tanggal merah, libur nasional, dan kalender akademik sekolah.</p>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24 border-t-4 border-t-red-500">
            <h3 class="text-lg font-bold text-gray-800 mb-1">Tambah Hari Libur</h3>
            <p class="text-xs text-gray-500 mb-5">Sistem absen akan otomatis dihentikan pada tanggal ini.</p>

            <form action="<?= base_url('admin/libur/store') ?>" method="POST" id="formLibur">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Pilih Tanggal</label>
                    <input type="date" name="tanggal" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white outline-none transition-all text-sm font-bold text-gray-700">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Keterangan Libur</label>
                    <textarea name="keterangan" rows="3" required placeholder="Misal: Libur Semester Genap" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white outline-none transition-all resize-none text-sm font-medium text-gray-800"></textarea>
                </div>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-red-200 transition-all flex justify-center items-center gap-2 active:scale-95 btn-submit-action text-sm">
                    <i class="fas fa-calendar-plus"></i> Simpan Tanggal Libur
                </button>
            </form>
        </div>
    </div>

    <div class="w-full lg:w-2/3 flex flex-col">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="live-search" value="<?= esc($search ?? '') ?>" placeholder="Cari keterangan atau tanggal (contoh: 2026-05)..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-red-500 transition-all">
            </div>
        </div>

        <div id="data-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative flex-1 flex flex-col">

            <div id="loading-overlay" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 hidden items-center justify-center">
                <div class="w-8 h-8 border-4 border-red-500 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-xs font-bold text-gray-500 uppercase tracking-widest border-b border-gray-100">
                            <th class="px-6 py-4 w-16 text-center">No</th>
                            <th class="px-6 py-4">Tanggal Pelaksanaan</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($daftar_libur)) : ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center flex-col items-center justify-center">
                                    <div class="mb-3 text-gray-300"><i class="fas fa-calendar-times text-4xl"></i></div>
                                    <span class="text-gray-500 font-medium text-sm">
                                        <?= !empty($search) ? 'Tidak ditemukan data libur sesuai pencarian.' : 'Belum ada hari libur yang didaftarkan.' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php
                            $no = ($page - 1) * $perPage + 1;
                            foreach ($daftar_libur as $libur) :
                                $date = new DateTime((string) $libur['tanggal']);
                                $tanggalIndo = $date->format('d M Y');
                                $isPassed = (new DateTime())->setTime(0, 0, 0) > $date;
                            ?>
                                <tr class="hover:bg-red-50/30 transition-colors group <?= $isPassed ? 'opacity-60 grayscale-[30%]' : '' ?>">
                                    <td class="px-6 py-4 text-sm text-gray-500 font-medium text-center"><?= $no++ ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-<?= $isPassed ? 'gray' : 'red' ?>-100 flex items-center justify-center text-<?= $isPassed ? 'gray' : 'red' ?>-600 font-black text-sm border border-<?= $isPassed ? 'gray' : 'red' ?>-200 shrink-0">
                                                <?= $date->format('d') ?>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-800"><?= $tanggalIndo ?></div>
                                                <div class="text-[10px] font-medium text-gray-500 uppercase"><?= $date->format('l') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-700"><?= esc((string) $libur['keterangan']) ?></div>
                                        <?php if ($isPassed): ?>
                                            <span class="inline-block mt-1 text-[9px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Telah Berlalu</span>
                                        <?php else: ?>
                                            <span class="inline-block mt-1 text-[9px] bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded border border-emerald-200 font-bold uppercase tracking-wider animate-pulse">Akan Datang</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="<?= base_url('admin/libur/delete/' . (string) $libur['id_libur']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="p-2 text-red-500 bg-red-50 hover:bg-red-100 border border-red-100 hover:text-red-700 rounded-lg transition-colors btn-confirm shadow-sm" data-text="Hapus tanggal merah ini secara permanen?" data-btn="Ya, Hapus" title="Hapus Libur">
                                                <i class="fas fa-trash-alt w-4 text-center"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-gray-50/50 flex flex-col md:flex-row justify-between items-center gap-4 border-t border-gray-100">
                <div class="text-xs text-gray-500 font-medium">
                    <?php
                    $page = $page ?? 1;
                    $perPage = $perPage ?? 10;
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
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('formLibur').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit-action');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'true');
        }
    });

    // ==========================================
    // LOGIKA AJAX "HTML OVER THE WIRE" (SPA UX)
    // ==========================================
    const dataContainer = document.getElementById('data-container');
    const searchInput = document.getElementById('live-search');

    function fetchLiburData(url) {
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
                url.searchParams.delete('page_libur');

                fetchLiburData(url.toString());
            }, 400);
        });
    }

    // Event Delegation: Pagination
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-wrapper a');
        if (link) {
            e.preventDefault();
            fetchLiburData(link.href);
        }
    });
</script>
<?= $this->endSection() ?>