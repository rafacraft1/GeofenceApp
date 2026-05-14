<?php

/**
 * Deklarasi variabel untuk memuaskan strict type Intelephense
 * @var array<int, array<string, mixed>> $users
 * @var array<int, array<string, mixed>> $roles
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Manajemen User</h1>
        <p class="text-sm text-gray-500 font-medium">Kelola akun Administrator dan Guru Wali Kelas.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="<?= base_url('admin/user/hak-akses') ?>" class="flex items-center justify-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95 whitespace-nowrap">
            <i class="fas fa-user-shield"></i> Hak Akses Role
        </a>
        <button onclick="openModal()" class="flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95 whitespace-nowrap">
            <i class="fas fa-plus"></i> Tambah User
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
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
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm font-medium">
                            <i class="fas fa-folder-open text-3xl mb-3 block text-gray-300"></i>
                            Belum ada data user.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?= esc((string) $u['nama_lengkap']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-600 font-medium"><?= esc((string) $u['username']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($u['role_id'] == 1): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">
                                        <i class="fas fa-crown text-[10px]"></i> <?= esc((string) $u['nama_role']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                        <i class="fas fa-chalkboard-teacher text-[10px]"></i> <?= esc((string) $u['nama_role']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <button onclick="editUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)" class="p-2.5 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 rounded-xl transition-colors shadow-sm" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="<?= base_url('admin/user/reset/' . $u['id_user']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-2.5 bg-slate-50 text-slate-600 hover:bg-slate-200 hover:text-slate-800 rounded-xl transition-colors shadow-sm btn-confirm" data-text="Reset password akun ini menjadi default (123456)?" data-btn="Ya, Reset" title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </form>

                                    <form action="<?= base_url('admin/user/delete/' . $u['id_user']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-2.5 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 rounded-xl transition-colors shadow-sm btn-confirm" data-text="Hapus akun ini secara permanen?" data-btn="Ya, Hapus" title="Hapus Akun">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
                    <select name="role_id" id="role_id" required class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all appearance-none bg-white font-medium text-gray-800">
                        <option value="">-- Pilih Hak Akses --</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id_role'] ?>"><?= esc((string) $r['nama_role']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 text-blue-800 p-3 rounded-lg text-xs font-medium items-start gap-2 hidden" id="passwordHint">
                <i class="fas fa-info-circle mt-0.5"></i>
                <p>Password default untuk akun baru adalah: <b>123456</b></p>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all btn-submit flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const modal = document.getElementById('userModal');
    const modalContent = document.getElementById('modalContent');
    const form = document.getElementById('formUser');
    const title = document.getElementById('modalTitle');
    const pwdHint = document.getElementById('passwordHint');

    function openModal() {
        form.reset();
        document.getElementById('id_user').value = '';
        title.innerHTML = '<i class="fas fa-user-plus text-blue-600"></i> Tambah User Baru';

        // FIX TAILWIND CSS: Penambahan flex secara dinamis
        pwdHint.classList.remove('hidden');
        pwdHint.classList.add('flex');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 20);
    }

    function editUser(user) {
        form.reset();
        document.getElementById('id_user').value = user.id_user;
        document.getElementById('nama_lengkap').value = user.nama_lengkap;
        document.getElementById('username').value = user.username;
        document.getElementById('role_id').value = user.role_id;

        title.innerHTML = '<i class="fas fa-user-edit text-amber-500"></i> Edit Data User';

        // FIX TAILWIND CSS: Penghapusan flex agar tidak bertabrakan dengan hidden
        pwdHint.classList.add('hidden');
        pwdHint.classList.remove('flex');

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
        }, 300);
    }

    form.addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>