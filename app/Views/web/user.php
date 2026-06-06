<?php

/**
 * @var string $title
 * @var array<int, array{id_user: string|int, nama_lengkap: string, username: string, role_id: string|int, nama_role: string}> $users
 * @var array<int, array{id_role: string|int, nama_role: string}> $roles
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

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Manajemen User</h1>
        <p class="text-sm text-gray-500 font-medium mt-1">Kelola akun Administrator dan Guru Wali Kelas.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="<?= base_url('admin/user/hak-akses') ?>" class="flex items-center justify-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all active:scale-95 whitespace-nowrap">
            <i class="fas fa-user-shield"></i> Hak Akses (RBAC)
        </a>
        <button onclick="openModal()" class="flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-blue-700 transition-all active:scale-95 whitespace-nowrap">
            <i class="fas fa-plus"></i> Tambah User
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
    <div class="relative w-full md:w-1/2 lg:w-1/3">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input type="text" id="live-search" value="<?= esc($search ?? '') ?>" placeholder="Cari Nama atau Username..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
    </div>
</div>

<div id="data-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">

    <div id="loading-overlay" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 hidden items-center justify-center">
        <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="overflow-x-auto min-h-[300px]">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-100">
                <tr class="text-gray-500 font-bold uppercase text-[10px] tracking-widest">
                    <th class="px-6 py-4">Nama Lengkap</th>
                    <th class="px-6 py-4">Username</th>
                    <th class="px-6 py-4">Role Akses</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-sm font-medium">
                            <i class="fas fa-folder-open text-4xl mb-3 block text-gray-300"></i>
                            <?= !empty($search) ? 'Tidak ada user yang cocok dengan pencarian.' : 'Belum ada data user.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 flex items-center gap-2 group-hover:text-blue-600 transition-colors">
                                    <?= esc((string) $u['nama_lengkap']) ?>
                                    <?php if ((int)$u['id_user'] === 1): ?>
                                        <i class="fas fa-check-circle text-blue-500" title="Super Administrator"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-600 font-medium"><?= esc((string) $u['username']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($u['role_id'] == 1): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide">
                                        <i class="fas fa-crown"></i> <?= esc((string) $u['nama_role']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">
                                        <i class="fas fa-chalkboard-teacher"></i> <?= esc((string) $u['nama_role']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <button onclick='editUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, "UTF-8") ?>)' class="p-2 text-amber-600 bg-amber-50 hover:bg-amber-100 border border-amber-100 rounded-lg transition-colors" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <form action="<?= base_url('admin/user/reset/' . $u['id_user']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-confirm p-2 text-slate-600 bg-slate-50 hover:bg-slate-200 border border-slate-200 rounded-lg transition-colors" data-text="Reset password akun ini menjadi default (123456)?" data-btn="Ya, Reset" title="Reset Password">
                                            <i class="fas fa-key w-4 text-center"></i>
                                        </button>
                                    </form>

                                    <?php if ((int)$u['id_user'] === 1): ?>
                                        <button type="button" disabled class="p-2 text-gray-300 bg-gray-50 border border-gray-100 rounded-lg cursor-not-allowed" title="Administrator Utama (Dilindungi)">
                                            <i class="fas fa-shield-alt w-4 text-center"></i>
                                        </button>
                                    <?php else: ?>
                                        <form action="<?= base_url('admin/user/delete/' . $u['id_user']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-confirm p-2 text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition-colors" data-text="Hapus akun ini secara permanen?" data-btn="Ya, Hapus" title="Hapus Akun">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
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
            Menampilkan <span class="font-bold text-gray-800"><?= (int) $start ?></span> - <span class="font-bold text-gray-800"><?= (int) $end ?></span> dari <span class="font-bold text-gray-800"><?= (int) $total_data ?></span> user
        </div>
        <?php if (!empty($pager_links)): ?>
            <div class="pagination-wrapper"><?= $pager_links ?></div>
        <?php endif; ?>
    </div>
</div>

<div id="userModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="modalContent">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2" id="modalTitle">
                <i class="fas fa-user-plus text-blue-600"></i> Tambah User Baru
            </h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="<?= base_url('admin/user/store') ?>" method="POST" class="p-6 space-y-5" id="formUser">
            <?= csrf_field() ?>
            <input type="hidden" name="id_user" id="id_user">

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" required placeholder="Masukkan nama lengkap..." class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium text-gray-800 placeholder-gray-400">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" id="username" required placeholder="Gunakan huruf kecil tanpa spasi..." class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium text-gray-800 placeholder-gray-400">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Role Akses</label>
                <div class="relative">
                    <select name="role_id" id="role_id" required class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all appearance-none bg-white font-medium text-gray-800 cursor-pointer">
                        <option value="">-- Pilih Hak Akses --</option>
                        <?php foreach ($roles as $r): ?>
                            <?php $isAdmin = ((int)$r['id_role'] === 1); ?>
                            <option value="<?= esc((string) $r['id_role']) ?>" id="opt-role-<?= $r['id_role'] ?>" <?= $isAdmin ? 'class="hidden" disabled style="display:none;"' : '' ?>>
                                <?= esc((string) $r['nama_role']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <p id="roleWarning" class="text-[10px] text-red-500 font-bold mt-1.5 hidden ml-1">
                    <i class="fas fa-lock"></i> Role Administrator Utama dilindungi sistem.
                </p>
            </div>

            <div class="bg-blue-50 text-blue-800 p-3 rounded-lg text-xs font-medium items-start gap-2 hidden" id="passwordHint">
                <i class="fas fa-info-circle mt-0.5"></i>
                <p>Password default untuk akun baru adalah: <b>123456</b></p>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors text-sm">Batal</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-md shadow-blue-200 transition-all btn-submit flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // State Variables for Modal
    const modal = document.getElementById('userModal');
    const modalContent = document.getElementById('modalContent');
    const form = document.getElementById('formUser');
    const title = document.getElementById('modalTitle');
    const pwdHint = document.getElementById('passwordHint');
    const roleSelect = document.getElementById('role_id');
    const roleWarning = document.getElementById('roleWarning');

    function openModal() {
        form.reset();
        document.getElementById('id_user').value = '';
        title.innerHTML = '<i class="fas fa-user-plus text-blue-600"></i> Tambah User Baru';

        pwdHint.classList.remove('hidden');
        pwdHint.classList.add('flex');

        // Pastikan Opsi Admin hilang saat tambah user baru
        const adminOption = document.getElementById('opt-role-1');
        if (adminOption) {
            adminOption.classList.add('hidden');
            adminOption.disabled = true;
            adminOption.style.display = 'none';
        }

        roleSelect.removeAttribute('disabled');
        roleSelect.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
        roleWarning.classList.add('hidden');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 20);
    }

    window.editUser = function(user) {
        form.reset();
        document.getElementById('id_user').value = user.id_user;
        document.getElementById('nama_lengkap').value = user.nama_lengkap;
        document.getElementById('username').value = user.username;
        document.getElementById('role_id').value = user.role_id;

        title.innerHTML = '<i class="fas fa-user-edit text-amber-500"></i> Edit Data User';

        pwdHint.classList.add('hidden');
        pwdHint.classList.remove('flex');

        const adminOption = document.getElementById('opt-role-1');

        if (parseInt(user.id_user) === 1) {
            // Tampilkan kembali dropdown option untuk dirender saat melihat data Admin 1
            if (adminOption) {
                adminOption.classList.remove('hidden');
                adminOption.disabled = false;
                adminOption.style.display = 'block';
            }
            roleSelect.setAttribute('disabled', 'true');
            roleSelect.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
            roleWarning.classList.remove('hidden');
        } else {
            // Sembunyikan dan disabled opsi Admin untuk user biasa
            if (adminOption) {
                adminOption.classList.add('hidden');
                adminOption.disabled = true;
                adminOption.style.display = 'none';
            }
            roleSelect.removeAttribute('disabled');
            roleSelect.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
            roleWarning.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 20);
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            roleSelect.removeAttribute('disabled');
        }, 300);
    }

    form.addEventListener('submit', function() {
        roleSelect.removeAttribute('disabled');
        const btn = this.querySelector('.btn-submit');
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

    function fetchUserData(url) {
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
                if (keyword) url.searchParams.set('search', keyword);
                else url.searchParams.delete('search');
                url.searchParams.delete('page_user');

                fetchUserData(url.toString());
            }, 400);
        });
    }

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-wrapper a');
        if (link) {
            e.preventDefault();
            fetchUserData(link.href);
        }
    });
</script>
<?= $this->endSection() ?>