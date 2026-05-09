<?php

/**
 * @var array<int, array<string, mixed>> $listRoles
 * @var array<int, array<string, mixed>> $listUsers
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun akses untuk Admin dan Guru (Wali Kelas).</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6 h-fit border-t-4 border-blue-600 transition-all" id="form-container">
            <h2 class="text-lg font-semibold mb-1 text-gray-700" id="form-title">Tambah Pengguna Baru</h2>
            <p class="text-xs text-gray-500 mb-4" id="form-subtitle">Buat akun untuk memberi akses ke dalam sistem.</p>

            <form action="<?= base_url('admin/user/store') ?>" method="POST" id="form-user">
                <?= csrf_field() ?>
                <input type="hidden" name="id_user" id="id_user">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Contoh: Budi Santoso, S.Pd" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Peran (Role)</label>
                    <select name="role_id" id="role_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer bg-white" required>
                        <option value="" disabled selected>-- Pilih Hak Akses --</option>
                        <?php foreach ($listRoles as $role): ?>
                            <option value="<?= esc((string) $role['id_role']) ?>"><?= esc((string) $role['nama_role']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username (Unik)</label>
                    <input type="text" name="username" id="username" autocomplete="off" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none transition-all bg-gray-50 focus:bg-white" placeholder="Ketik username" required>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" id="password" autocomplete="new-password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Ketik password">
                    <p class="text-[10px] text-amber-600 mt-1 hidden" id="password-hint">Kosongkan jika tidak ingin mengubah password.</p>
                </div>

                <div class="flex gap-2">
                    <button type="reset" onclick="resetForm()" id="btn-cancel" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 px-4 rounded transition duration-200 hidden">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 shadow-lg shadow-blue-200">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold">
                        <tr>
                            <th class="py-4 px-6">Identitas Pengguna</th>
                            <th class="py-4 px-6 text-center">Hak Akses</th>
                            <th class="py-4 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm divide-y divide-gray-100">
                        <?php foreach ($listUsers as $u) : ?>
                            <tr class="hover:bg-blue-50/50 transition-colors group">
                                <td class="py-3 px-6">
                                    <div class="font-bold text-gray-800"><?= esc((string) $u['nama_lengkap']) ?></div>
                                    <div class="text-[11px] text-gray-500 font-mono mt-0.5">@<?= esc((string) $u['username']) ?></div>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <?php
                                    $namaRole = (string) $u['nama_role'];
                                    $roleColor = (strtolower($namaRole) === 'admin') ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700';
                                    ?>
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-bold tracking-wide uppercase <?= $roleColor ?>">
                                        <?= esc($namaRole) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-3">
                                        <button type="button"
                                            onclick="fillForm('<?= esc((string) $u['id_user']) ?>', '<?= esc((string) $u['nama_lengkap'], 'js') ?>', '<?= esc((string) $u['username'], 'js') ?>', '<?= esc((string) $u['role_id']) ?>')"
                                            class="text-blue-500 hover:text-blue-700 transition-transform hover:scale-110 bg-blue-50 p-1.5 rounded-lg border border-blue-100" title="Edit Data">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <form action="<?= base_url('admin/user/delete/' . (string) $u['id_user']) ?>" method="POST" id="form-delete-<?= (string) $u['id_user'] ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" onclick="konfirmasiHapusUser('<?= (string) $u['id_user'] ?>', '<?= esc((string) $u['nama_lengkap'], 'js') ?>')" class="text-red-500 hover:text-red-700 transition-transform hover:scale-110 bg-red-50 p-1.5 rounded-lg border border-red-100" title="Hapus Akun">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($listUsers)): ?>
                            <tr>
                                <td colspan="3" class="py-10 text-center text-gray-400">Belum ada data pengguna yang didaftarkan.</td>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function fillForm(id, nama, username, roleId) {
        document.getElementById('id_user').value = id;
        document.getElementById('nama_lengkap').value = nama;
        document.getElementById('username').value = username;
        document.getElementById('role_id').value = roleId;

        // Form adjustments for Edit Mode
        document.getElementById('password').required = false; // Password opsional saat edit
        document.getElementById('password').value = '';
        document.getElementById('password-hint').classList.remove('hidden');

        document.getElementById('form-title').innerText = 'Edit Pengguna';
        document.getElementById('form-subtitle').innerText = 'Mengubah akses akun untuk ' + nama;

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Perubahan';
        btnSubmit.classList.replace('bg-blue-600', 'bg-amber-500');
        btnSubmit.classList.replace('hover:bg-blue-700', 'hover:bg-amber-600');
        btnSubmit.classList.replace('shadow-blue-200', 'shadow-amber-200');

        document.getElementById('btn-cancel').classList.remove('hidden');
        document.getElementById('form-container').classList.replace('border-blue-600', 'border-amber-500');

        document.getElementById('form-user').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetForm() {
        document.getElementById('id_user').value = '';
        document.getElementById('password').required = true;
        document.getElementById('password-hint').classList.add('hidden');

        document.getElementById('form-title').innerText = 'Tambah Pengguna Baru';
        document.getElementById('form-subtitle').innerText = 'Buat akun untuk memberi akses ke dalam sistem.';

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Akun';
        btnSubmit.classList.replace('bg-amber-500', 'bg-blue-600');
        btnSubmit.classList.replace('hover:bg-amber-600', 'hover:bg-blue-700');
        btnSubmit.classList.replace('shadow-amber-200', 'shadow-blue-200');

        document.getElementById('btn-cancel').classList.add('hidden');
        document.getElementById('form-container').classList.replace('border-amber-500', 'border-blue-600');
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
</script>
<?= $this->endSection() ?>