<?php

/**
 * Deklarasi variabel untuk memuaskan strict type Intelephense
 * @var array<int, array<string, mixed>> $roles
 * @var array<int, array<string, mixed>> $menus
 * @var array<int, array<int, bool>> $akses
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
    <div class="p-5 border-b flex flex-col md:flex-row justify-between md:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Matriks Hak Akses (RBAC)</h3>
            <p class="text-xs text-gray-500">Centang modul yang diizinkan untuk setiap Role.</p>
        </div>
        <a href="<?= base_url('admin/user') ?>" class="text-sm font-bold text-gray-500 hover:text-blue-600 transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="<?= base_url('admin/user/hak-akses/save') ?>" method="POST" id="formHakAkses">
        <?= csrf_field() ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-800 text-white font-bold uppercase text-[10px] tracking-widest">
                        <th class="px-6 py-4 border-r border-slate-700">Nama Modul / Menu</th>
                        <?php foreach ($roles as $role): ?>
                            <th class="px-6 py-4 text-center border-r border-slate-700"><?= esc((string)$role['nama_role']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($menus as $menu): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-semibold text-gray-700 border-r border-gray-100 flex items-center gap-3">
                                <i class="<?= esc((string)$menu['icon']) ?> text-gray-400 w-5 text-center text-lg"></i>
                                <?= esc((string)$menu['nama_menu']) ?>
                            </td>
                            <?php foreach ($roles as $role): ?>
                                <?php
                                // PERBAIKAN: Menggunakan $akses dan mengecek nilai id_menu menggunakan in_array
                                $isChecked = (isset($akses[$role['id_role']]) && in_array($menu['id_menu'], $akses[$role['id_role']])) ? 'checked' : '';
                                
                                // Proteksi Admin agar tidak terkunci (Wajib akses Dashboard & Manajemen User)
                                $isLocked = ($role['id_role'] == 1 && in_array($menu['id_menu'], [1, 9])) ? 'disabled' : '';
                                ?>
                                <td class="px-6 py-3 text-center border-r border-gray-100 bg-gray-50/20">
                                    <input type="checkbox" name="permissions[<?= $role['id_role'] ?>][]" value="<?= $menu['id_menu'] ?>" <?= $isChecked ?> <?= $isLocked ?> class="w-5 h-5 accent-blue-600 cursor-pointer shadow-sm border-gray-300 rounded">
                                    <?php if ($isLocked): ?>
                                        <input type="hidden" name="permissions[<?= $role['id_role'] ?>][]" value="<?= $menu['id_menu'] ?>">
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-6 bg-gray-50 border-t flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-10 py-3 rounded-xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 active:scale-95 transition-all btn-submit flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Perubahan Hak Akses
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('formHakAkses').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>