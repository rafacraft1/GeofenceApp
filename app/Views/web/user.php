<?php

/**
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
            <i class="fas fa-plus"></i> Tambah User Baru
        </button>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    <th class="px-6 py-5">No</th>
                    <th class="px-6 py-5">Identitas User</th>
                    <th class="px-6 py-5">Role Akses</th>
                    <th class="px-6 py-5 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($users)): ?>
                    <?php $no = 1;
                    foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center font-black text-lg shadow-inner overflow-hidden border border-blue-200 shrink-0">
                                        <?php if (!empty($user['foto'])): ?>
                                            <img src="<?= base_url('uploads/profiles/' . (string) $user['foto']) ?>" alt="Foto" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?= esc(substr((string) $user['nama_lengkap'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800"><?= esc((string) $user['nama_lengkap']) ?></div>
                                        <div class="text-[11px] font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded mt-1 inline-block">@<?= esc((string) $user['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $badgeTheme = ($user['role_id'] == 1)
                                    ? 'bg-indigo-50 text-indigo-700 border-indigo-200'
                                    : 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                ?>
                                <span class="px-3 py-1.5 rounded-lg text-xs font-bold border <?= $badgeTheme ?>">
                                    <?= esc((string) ($user['nama_role'] ?? 'User')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <form action="<?= base_url('admin/user/reset/' . (string) $user['id_user']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn-confirm p-2.5 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors border border-amber-100" data-text="Password akan direset menjadi default: 123456" data-btn="Ya, Reset" title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </form>

                                    <button onclick='openModal(<?= json_encode($user) ?>)' class="p-2.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors border border-blue-100" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <?php if ($user['id_user'] != 1 && $user['id_user'] != session()->get('id_user')): ?>
                                        <form action="<?= base_url('admin/user/delete/' . (string) $user['id_user']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors border border-red-100" data-text="Hapus user ini secara permanen?" data-btn="Ya, Hapus" title="Hapus User">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button disabled class="p-2.5 text-gray-400 bg-gray-50 rounded-xl border border-gray-100 cursor-not-allowed opacity-50" title="Tidak dapat dihapus">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="py-12 text-center text-gray-400 font-medium">Belum ada data user.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-user" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white rounded-[2rem] shadow-2xl z-10 w-full max-w-lg p-8 relative transform scale-95 transition-transform duration-300" id="modal-content">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-800 tracking-tight" id="modal-title">Tambah User Baru</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form action="<?= base_url('admin/user/store') ?>" method="POST" id="form-user" class="space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="id_user" id="id_user">

            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" required class="w-full border-2 border-gray-100 rounded-xl p-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-gray-700 bg-gray-50 focus:bg-white" placeholder="Masukkan nama lengkap">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" id="username" required class="w-full border-2 border-gray-100 rounded-xl p-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-gray-700 bg-gray-50 focus:bg-white" placeholder="Tanpa spasi, contoh: guru_budi">
            </div>

            <div id="role-container">
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Role / Hak Akses</label>
                <select name="role_id" id="role_id" required class="w-full border-2 border-gray-100 rounded-xl p-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-gray-700 bg-gray-50 focus:bg-white cursor-pointer appearance-none">
                    <option value="" disabled selected>-- Pilih Role --</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= esc((string) $r['id_role']) ?>"><?= esc((string) $r['nama_role']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p id="role-warning" class="text-[10px] text-amber-600 font-bold mt-2 hidden bg-amber-50 p-2 rounded-lg border border-amber-100"><i class="fas fa-info-circle"></i> Role SuperAdmin tidak dapat diubah.</p>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-6 py-3 text-sm font-bold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 btn-submit transition-all active:scale-95">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const modal = document.getElementById('modal-user');
    const modalContent = document.getElementById('modal-content');
    const form = document.getElementById('form-user');
    const roleSelect = document.getElementById('role_id');
    const roleWarning = document.getElementById('role-warning');

    function openModal(data = null) {
        if (data) {
            document.getElementById('id_user').value = data.id_user;
            document.getElementById('nama_lengkap').value = data.nama_lengkap;
            document.getElementById('username').value = data.username;
            document.getElementById('username').setAttribute('readonly', 'true');
            document.getElementById('username').classList.add('bg-gray-100');
            document.getElementById('modal-title').innerText = 'Edit Data User';

            roleSelect.value = data.role_id;

            if (data.id_user == 1) {
                roleSelect.setAttribute('disabled', 'true');
                roleSelect.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                roleWarning.classList.remove('hidden');
            } else {
                roleSelect.removeAttribute('disabled');
                roleSelect.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                roleWarning.classList.add('hidden');
            }
        } else {
            form.reset();
            document.getElementById('id_user').value = '';
            document.getElementById('username').removeAttribute('readonly');
            document.getElementById('username').classList.remove('bg-gray-100');
            document.getElementById('modal-title').innerText = 'Tambah User Baru';

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
</script>
<?= $this->endSection() ?>