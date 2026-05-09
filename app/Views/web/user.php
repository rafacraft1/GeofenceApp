<?php

/**
 * @var array<int, array<string, mixed>> $listRoles
 * @var array<int, array<string, mixed>> $listUsers
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun akses untuk Administrator dan Guru (Wali Kelas).</p>
        </div>

        <div class="relative w-full md:w-72">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" id="searchInput" onkeyup="searchTable()" class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all shadow-sm" placeholder="Cari nama atau username...">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit border-t-4 border-t-blue-600 transition-all duration-300" id="form-container">
            <h2 class="text-lg font-bold text-gray-800 mb-1" id="form-title">Tambah Pengguna Baru</h2>
            <p class="text-xs text-gray-500 mb-5" id="form-subtitle">Buat akun untuk memberi akses ke dalam sistem.</p>

            <form action="<?= base_url('admin/user/store') ?>" method="POST" id="form-user">
                <?= csrf_field() ?>
                <input type="hidden" name="id_user" id="id_user">

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm" placeholder="Cth: Budi Santoso, S.Pd" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Peran (Role)</label>
                    <select name="role_id" id="role_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer text-sm" required>
                        <option value="" disabled selected>-- Pilih Hak Akses --</option>
                        <?php foreach ($listRoles as $role): ?>
                            <option value="<?= esc((string) $role['id_role']) ?>"><?= esc((string) $role['nama_role']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[10px] text-red-500 font-bold mt-1.5 hidden" id="role-hint">🔒 Hak akses level Admin dikunci sistem.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Username</label>
                    <input type="text" name="username" id="username" autocomplete="off" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm lowercase" placeholder="Ketik username" required>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" autocomplete="new-password" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm pr-10" placeholder="Ketik password">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600 focus:outline-none transition-colors">
                            <svg id="eye-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0a10.05 10.05 0 015.188-1.583c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0l-3.29-3.29" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-[10px] text-amber-600 font-bold mt-1.5 hidden" id="password-hint">* Kosongkan field ini jika tidak ingin mengubah password lama.</p>
                </div>

                <div class="flex gap-2">
                    <button type="reset" onclick="resetForm()" id="btn-cancel" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2.5 px-4 rounded-lg transition duration-200 hidden text-sm">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-200 shadow-md hover:shadow-lg text-sm">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full bg-white text-left">
                    <thead class="bg-gray-50/80 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="py-4 px-6 border-b border-gray-100">Identitas Pengguna</th>
                            <th class="py-4 px-6 border-b border-gray-100 text-center">Hak Akses</th>
                            <th class="py-4 px-6 border-b border-gray-100 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm divide-y divide-gray-50">
                        <?php foreach ($listUsers as $u) : ?>
                            <tr class="hover:bg-blue-50/30 transition-colors group data-row">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-xs font-bold text-gray-500 shrink-0">
                                            <?= esc(strtoupper(substr((string) $u['nama_lengkap'], 0, 1))) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800 search-target"><?= esc((string) $u['nama_lengkap']) ?></div>
                                            <div class="text-[11px] text-gray-500 font-mono mt-0.5 search-target">@<?= esc((string) $u['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php
                                    $namaRole = strtolower((string) $u['nama_role']);
                                    if ($namaRole === 'superadmin') {
                                        $roleStyle = 'bg-purple-100 text-purple-700 border-purple-200';
                                    } elseif ($namaRole === 'admin') {
                                        $roleStyle = 'bg-blue-100 text-blue-700 border-blue-200';
                                    } else {
                                        $roleStyle = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                    }
                                    ?>
                                    <span class="inline-block px-2.5 py-1 rounded text-[9px] font-bold tracking-wide uppercase border <?= $roleStyle ?>">
                                        <?= esc((string) $u['nama_role']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">

                                        <button type="button"
                                            onclick="fillForm('<?= esc((string) $u['id_user']) ?>', '<?= esc((string) $u['nama_lengkap'], 'js') ?>', '<?= esc((string) $u['username'], 'js') ?>', '<?= esc((string) $u['role_id']) ?>', '<?= esc((string) $u['nama_role'], 'js') ?>')"
                                            class="text-blue-500 hover:text-blue-700 transition-transform hover:scale-110 bg-blue-50 p-1.5 rounded-lg border border-blue-100" title="Edit Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <?php if ($namaRole === 'guru'): ?>
                                            <form action="<?= base_url('admin/user/reset/' . (string) $u['id_user']) ?>" method="POST" id="form-reset-<?= (string) $u['id_user'] ?>" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="konfirmasiResetPassword('<?= (string) $u['id_user'] ?>', '<?= esc((string) $u['nama_lengkap'], 'js') ?>')"
                                                    class="text-amber-500 hover:text-amber-700 transition-transform hover:scale-110 bg-amber-50 p-1.5 rounded-lg border border-amber-100" title="Reset Password (guru1234)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ((int)$u['id_user'] !== 1): ?>
                                            <form action="<?= base_url('admin/user/delete/' . (string) $u['id_user']) ?>" method="POST" id="form-delete-<?= (string) $u['id_user'] ?>" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="konfirmasiHapusUser('<?= (string) $u['id_user'] ?>', '<?= esc((string) $u['nama_lengkap'], 'js') ?>')" class="text-red-500 hover:text-red-700 transition-transform hover:scale-110 bg-red-50 p-1.5 rounded-lg border border-red-100" title="Hapus Akun">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="text-gray-300 bg-gray-50 p-1.5 rounded-lg border border-gray-100 cursor-not-allowed" title="Sistem Core (Tidak bisa dihapus)">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($listUsers)): ?>
                            <tr>
                                <td colspan="3" class="py-16 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-60">
                                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <p class="text-sm font-bold text-gray-600">Belum Ada Pengguna</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function searchTable() {
        let input = document.getElementById("searchInput");
        let filter = input.value.toUpperCase();
        let rows = document.querySelectorAll(".data-row");

        rows.forEach(row => {
            let targets = row.querySelectorAll(".search-target");
            let match = false;

            targets.forEach(t => {
                if (t.innerText.toUpperCase().indexOf(filter) > -1) {
                    match = true;
                }
            });

            row.style.display = match ? "" : "none";
        });
    }

    function togglePassword() {
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        } else {
            passInput.type = 'password';
            eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0a10.05 10.05 0 015.188-1.583c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0l-3.29-3.29" />`;
        }
    }

    function fillForm(id, nama, username, roleId, namaRole) {
        document.getElementById('id_user').value = id;
        document.getElementById('nama_lengkap').value = nama;
        document.getElementById('username').value = username;

        let roleSelect = document.getElementById('role_id');
        let optionExists = Array.from(roleSelect.options).some(opt => opt.value === roleId);

        if (!optionExists) {
            let newOption = new window.Option(namaRole, roleId);
            newOption.classList.add('dynamic-role');
            roleSelect.add(newOption);
        }

        roleSelect.value = roleId;

        let roleLowerCase = namaRole ? namaRole.toLowerCase() : '';
        if (roleLowerCase === 'admin' || roleLowerCase === 'superadmin') {
            roleSelect.disabled = true;
            roleSelect.classList.replace('bg-gray-50', 'bg-gray-200');
            roleSelect.classList.add('cursor-not-allowed');
            document.getElementById('role-hint').classList.remove('hidden');
        } else {
            roleSelect.disabled = false;
            roleSelect.classList.replace('bg-gray-200', 'bg-gray-50');
            roleSelect.classList.remove('cursor-not-allowed');
            document.getElementById('role-hint').classList.add('hidden');
        }

        document.getElementById('password').required = false;
        document.getElementById('password').value = '';
        document.getElementById('password-hint').classList.remove('hidden');

        document.getElementById('form-title').innerText = 'Edit Pengguna';
        document.getElementById('form-subtitle').innerText = 'Mengubah akses akun untuk ' + nama;

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Perubahan';
        btnSubmit.classList.replace('bg-blue-600', 'bg-amber-500');
        btnSubmit.classList.replace('hover:bg-blue-700', 'hover:bg-amber-600');

        document.getElementById('btn-cancel').classList.remove('hidden');
        document.getElementById('form-container').classList.replace('border-t-blue-600', 'border-t-amber-500');

        document.getElementById('nama_lengkap').focus();
        document.getElementById('form-user').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetForm() {
        document.getElementById('id_user').value = '';
        document.getElementById('password').required = true;
        document.getElementById('password-hint').classList.add('hidden');

        let roleSelect = document.getElementById('role_id');
        let dynamicOptions = roleSelect.querySelectorAll('.dynamic-role');
        dynamicOptions.forEach(opt => opt.remove());

        roleSelect.value = '';
        roleSelect.disabled = false;
        roleSelect.classList.replace('bg-gray-200', 'bg-gray-50');
        roleSelect.classList.remove('cursor-not-allowed');
        document.getElementById('role-hint').classList.add('hidden');

        document.getElementById('form-title').innerText = 'Tambah Pengguna Baru';
        document.getElementById('form-subtitle').innerText = 'Buat akun untuk memberi akses ke dalam sistem.';

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Akun';
        btnSubmit.classList.replace('bg-amber-500', 'bg-blue-600');
        btnSubmit.classList.replace('hover:bg-amber-600', 'hover:bg-blue-700');

        document.getElementById('btn-cancel').classList.add('hidden');
        document.getElementById('form-container').classList.replace('border-t-amber-500', 'border-t-blue-600');
    }

    function konfirmasiHapusUser(id, namaUser) {
        Swal.fire({
            title: 'Hapus Akun?',
            html: "<p class='text-sm text-gray-600 mt-2'>Anda yakin ingin mencabut hak akses dan menghapus permanen akun <b>" + namaUser + "</b>?</p>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Akun',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                document.getElementById('form-delete-' + id).submit();
            }
        });
    }

    function konfirmasiResetPassword(id, namaUser) {
        Swal.fire({
            title: 'Reset Password?',
            html: `<p class='text-sm text-gray-600 mt-2'>Password <b>${namaUser}</b> akan dikembalikan ke default: <span class="font-mono font-bold text-blue-600">guru1234</span></p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Reset Sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                document.getElementById('form-reset-' + id).submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>