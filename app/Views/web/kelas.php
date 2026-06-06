<?php

/**
 * @var string $title
 * @var array<int, array{id_kelas: string|int, nama_kelas: string, wali_kelas_id: string|int|null, nama_wali: string|null, jumlah_siswa: string|int}> $kelas
 * @var array<int, array{id_user: string|int, nama_lengkap: string}> $listGuru
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

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Manajemen Kelas</h2>
    <p class="text-sm text-gray-500 mt-1">Kelola data kelas, penugasan wali kelas, dan integrasi siswa.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-fit border-t-4 border-t-blue-600 transition-all sticky top-24" id="form-container">
            <h3 class="text-lg font-bold mb-1 text-gray-800" id="form-title">Tambah Kelas Baru</h3>
            <p class="text-xs text-gray-500 mb-6" id="form-subtitle">Tentukan kelas dan pilih Wali Kelas dari daftar guru.</p>

            <form action="<?= base_url('admin/kelas/store') ?>" method="POST" id="form-kelas">
                <?= csrf_field() ?>
                <input type="hidden" name="id_kelas" id="id_kelas">

                <div class="mb-4">
                    <label for="nama_kelas" class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" autocomplete="off"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all uppercase text-sm font-semibold"
                        placeholder="Contoh: XII RPL 1" required>
                </div>

                <div class="mb-6">
                    <label for="wali_kelas_id" class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Wali Kelas</label>
                    <select name="wali_kelas_id" id="wali_kelas_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer text-sm font-medium">
                        <option value="">-- Tanpa Wali Kelas --</option>
                        <?php foreach ($listGuru as $guru): ?>
                            <option value="<?= esc((string) $guru['id_user']) ?>"><?= esc((string) $guru['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="resetForm()" id="btn-cancel" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2.5 px-4 rounded-xl transition duration-200 hidden text-sm">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl transition duration-200 shadow-md shadow-blue-200 flex items-center justify-center text-sm btn-submit-action">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 flex flex-col">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="live-search" value="<?= esc($search ?? '') ?>" placeholder="Cari Kelas atau Nama Wali..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <div id="data-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative flex-1 flex flex-col">

            <div id="loading-overlay" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 hidden items-center justify-center">
                <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50 text-gray-500 uppercase text-[10px] font-bold tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-4 px-6">Informasi Kelas</th>
                            <th class="py-4 px-6 text-center">Wali Kelas</th>
                            <th class="py-4 px-6 text-center">Jml Siswa</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm divide-y divide-gray-50">
                        <?php if (!empty($kelas)) : ?>
                            <?php foreach ($kelas as $k) : ?>
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-[10px] border border-indigo-100 shrink-0">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <span class="font-black text-gray-800 tracking-tight text-sm"><?= esc((string) $k['nama_kelas']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if (!empty($k['nama_wali'])): ?>
                                            <span class="text-xs font-semibold text-gray-700"><?= esc((string) $k['nama_wali']) ?></span>
                                        <?php else: ?>
                                            <span class="text-[10px] bg-red-50 text-red-500 px-2 py-1 rounded border border-red-100 font-bold uppercase">Kosong</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="bg-blue-50 text-blue-600 text-[10px] font-black px-2 py-1 rounded-md border border-blue-100">
                                            <?= (int) $k['jumlah_siswa'] ?> Orang
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex item-center justify-end gap-2">
                                            <button type="button"
                                                onclick="fillForm('<?= esc((string) $k['id_kelas']) ?>', '<?= esc((string) $k['nama_kelas'], 'js') ?>', '<?= esc((string) ($k['wali_kelas_id'] ?? ''), 'js') ?>')"
                                                class="text-blue-600 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg border border-blue-100 transition-colors" title="Edit Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>

                                            <form action="<?= base_url('admin/kelas/delete/' . (string) $k['id_kelas']) ?>" method="POST" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="button" data-text="Hapus kelas <?= esc((string) $k['nama_kelas'], 'js') ?> beserta seluruh data siswanya?" data-btn="Ya, Hapus Permanen"
                                                    class="btn-confirm text-red-600 bg-red-50 hover:bg-red-100 p-2 rounded-lg border border-red-100 transition-colors" title="Hapus Kelas">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-12 text-center flex-col items-center justify-center">
                                    <div class="mb-3 text-gray-300"><i class="fas fa-chalkboard text-4xl"></i></div>
                                    <span class="text-gray-500 font-medium text-sm">
                                        <?= !empty($search) ? 'Pencarian tidak ditemukan.' : 'Belum ada data kelas.' ?>
                                    </span>
                                </td>
                            </tr>
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
                    Menampilkan <span class="font-bold text-gray-800"><?= (int) $start ?></span> - <span class="font-bold text-gray-800"><?= (int) $end ?></span> dari <span class="font-bold text-gray-800"><?= (int) $total_data ?></span> kelas
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
    // State form interaction
    function fillForm(id, nama, waliId) {
        document.getElementById('id_kelas').value = id;
        document.getElementById('nama_kelas').value = nama;
        document.getElementById('wali_kelas_id').value = waliId;

        document.getElementById('form-title').innerText = 'Edit Kelas';
        document.getElementById('form-subtitle').innerText = 'Mengubah data kelas ' + nama;

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Perubahan';
        btnSubmit.classList.replace('bg-blue-600', 'bg-amber-500');
        btnSubmit.classList.replace('hover:bg-blue-700', 'hover:bg-amber-600');
        btnSubmit.classList.replace('shadow-blue-200', 'shadow-amber-200');

        document.getElementById('btn-cancel').classList.remove('hidden');
        document.getElementById('form-container').classList.replace('border-t-blue-600', 'border-t-amber-500');

        if (window.innerWidth < 1024) {
            document.getElementById('form-container').scrollIntoView({
                behavior: 'smooth'
            });
        }
    }

    function resetForm() {
        document.getElementById('id_kelas').value = '';
        document.getElementById('nama_kelas').value = '';
        document.getElementById('wali_kelas_id').value = '';

        document.getElementById('form-title').innerText = 'Tambah Kelas Baru';
        document.getElementById('form-subtitle').innerText = 'Tentukan kelas dan pilih Wali Kelas dari daftar guru.';

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Data';
        btnSubmit.classList.replace('bg-amber-500', 'bg-blue-600');
        btnSubmit.classList.replace('hover:bg-amber-600', 'hover:bg-blue-700');
        btnSubmit.classList.replace('shadow-amber-200', 'shadow-blue-200');

        document.getElementById('btn-cancel').classList.add('hidden');
        document.getElementById('form-container').classList.replace('border-t-amber-500', 'border-t-blue-600');
    }

    document.getElementById('form-kelas').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit-action');
        btn.classList.add('btn-loading');
        btn.setAttribute('disabled', 'true');
    });

    // ==========================================
    // LOGIKA AJAX "HTML OVER THE WIRE" (SPA UX)
    // ==========================================
    const dataContainer = document.getElementById('data-container');
    const searchInput = document.getElementById('live-search');

    function fetchKelasData(url) {
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
                url.searchParams.delete('page_kelas');

                fetchKelasData(url.toString());
            }, 400);
        });
    }

    // Event Delegation: Pagination
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-wrapper a');
        if (link) {
            e.preventDefault();
            fetchKelasData(link.href);
        }
    });
</script>
<?= $this->endSection() ?>