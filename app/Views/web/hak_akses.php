<?php

/**
 * @var string $title
 * @var array<int, array{id_role: string|int, nama_role: string}> $roles
 * @var array<int, array{id_menu: string|int, nama_menu: string, icon: string}> $menus
 * @var array<int, array<int, bool>> $access
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-user-shield text-indigo-600"></i> Matriks Hak Akses (RBAC)
        </h2>
        <p class="text-sm text-gray-500 mt-1">Centang modul yang diizinkan untuk setiap Role pada sistem.</p>
    </div>
    <a href="<?= base_url('admin/user') ?>" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
        <i class="fas fa-arrow-left"></i> Kembali ke User
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col relative">
    <form action="<?= base_url('admin/user/hak-akses/save') ?>" method="POST" id="formHakAkses">
        <?= csrf_field() ?>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-800 shadow-sm">
                    <tr class="text-white font-bold uppercase text-[10px] tracking-widest">
                        <th class="px-6 py-4 border-r border-slate-700 w-1/3">Nama Modul / Menu</th>
                        <?php foreach ($roles as $role): ?>
                            <th class="px-6 py-4 text-center border-r border-slate-700">
                                <?= esc((string)$role['nama_role']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($menus as $menu): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-700 border-r border-gray-100 flex items-center gap-3">
                                <i class="<?= esc((string)$menu['icon']) ?> text-indigo-400 w-5 text-center text-lg"></i>
                                <?= esc((string)$menu['nama_menu']) ?>
                            </td>
                            <?php foreach ($roles as $role): ?>
                                <?php
                                $isChecked = isset($access[$role['id_role']][$menu['id_menu']]) ? 'checked' : '';

                                // Proteksi UI Admin Utama (Role 1): Wajib punya akses ke Dashboard (1) & User (9)
                                $isLocked = ($role['id_role'] == 1 && in_array($menu['id_menu'], [1, 9])) ? 'disabled' : '';
                                $bgClass = $isLocked ? 'bg-gray-100/50' : '';
                                ?>
                                <td class="px-6 py-3 text-center border-r border-gray-100 <?= $bgClass ?> relative group">
                                    <label class="absolute inset-0 flex items-center justify-center cursor-pointer w-full h-full">
                                        <input type="checkbox" name="permissions[<?= $role['id_role'] ?>][]" value="<?= $menu['id_menu'] ?>" <?= $isChecked ?> <?= $isLocked ?> class="w-5 h-5 accent-indigo-600 cursor-pointer shadow-sm border-gray-300 rounded transition-all focus:ring-indigo-500 hover:scale-110">
                                    </label>

                                    <?php if ($isLocked): ?>
                                        <input type="hidden" name="permissions[<?= $role['id_role'] ?>][]" value="<?= $menu['id_menu'] ?>">
                                        <div class="absolute inset-0 hidden group-hover:flex items-center justify-center bg-white/80 backdrop-blur-[1px] text-[9px] font-bold text-red-500 uppercase tracking-wider z-10 pointer-events-none">
                                            Akses Wajib
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="p-5 bg-gray-50 border-t border-gray-200 flex justify-end shrink-0 z-20">
            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold shadow-md shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all btn-submit flex items-center justify-center gap-2 min-w-[200px]">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('formHakAkses').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const btn = form.querySelector('.btn-submit');
        const originalContent = btn.innerHTML;

        btn.classList.add('btn-loading');
        btn.innerHTML = 'Menyimpan...';
        btn.setAttribute('disabled', 'true');

        fetch(form.action, {
                method: 'POST',
                body: new window.FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.classList.remove('btn-loading');
                btn.innerHTML = originalContent;
                btn.removeAttribute('disabled');

                if (data.status === 'success') {
                    toastr.success(data.message, 'Berhasil Diperbarui!');
                } else {
                    toastr.error(data.message, 'Gagal!');
                }
            })
            .catch(error => {
                console.error('AJAX Submit Error:', error);
                btn.classList.remove('btn-loading');
                btn.innerHTML = originalContent;
                btn.removeAttribute('disabled');
                toastr.error('Terjadi kesalahan jaringan.', 'Koneksi Terputus');
            });
    });
</script>
<?= $this->endSection() ?>