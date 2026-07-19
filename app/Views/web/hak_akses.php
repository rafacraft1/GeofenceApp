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
<style>
    /* CSS Spinner Loading untuk Tombol Submit */
    .btn-loading {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }
    .btn-loading::after {
        content: '';
        position: absolute;
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
        left: calc(50% - 0.625rem);
        top: calc(50% - 0.625rem);
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    /* Toggle Switch Styles */
    .toggle-checkbox:checked {
        right: 0;
        border-color: #2563EB; /* Blue-600 */
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #2563EB;
    }
    .toggle-checkbox:checked + .toggle-label:after {
        transform: translateX(100%);
        border-color: white;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Matriks Hak Akses (RBAC)</h2>
        <p class="text-sm text-gray-500 mt-1 font-medium">Atur modul dan menu yang dapat diakses oleh masing-masing tipe pengguna.</p>
    </div>
    <a href="<?= base_url('admin/user') ?>" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-600 px-4 py-2.5 rounded-xl font-bold shadow-sm hover:bg-gray-50 hover:text-blue-600 transition-all text-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Data Pengguna
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
    
    <form action="<?= base_url('admin/user/hak-akses/save') ?>" method="POST" id="formHakAkses">
        <?= csrf_field() ?>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-5 text-xs font-black text-gray-400 uppercase tracking-widest border-r border-gray-100 w-1/4">
                            <i class="fas fa-layer-group mr-1.5"></i> Modul Sistem
                        </th>
                        <?php foreach ($roles as $role): ?>
                            <th class="px-6 py-5 text-center border-r border-gray-100 w-auto">
                                <div class="inline-flex flex-col items-center justify-center">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-1.5 shadow-sm">
                                        <i class="fas fa-user-shield text-xs"></i>
                                    </div>
                                    <span class="text-[11px] font-black text-gray-700 uppercase tracking-wider"><?= esc((string)$role['nama_role']) ?></span>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php 
                    $colors = ['text-blue-500 bg-blue-50', 'text-emerald-500 bg-emerald-50', 'text-amber-500 bg-amber-50', 'text-purple-500 bg-purple-50', 'text-rose-500 bg-rose-50', 'text-cyan-500 bg-cyan-50'];
                    $i = 0;
                    foreach ($menus as $menu): 
                        $iconColor = $colors[$i % count($colors)];
                        $i++;
                    ?>
                        <tr class="hover:bg-blue-50/20 transition-colors group">
                            <!-- Kolom Nama Menu -->
                            <td class="px-6 py-4 border-r border-gray-50">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-sm <?= $iconColor ?> group-hover:scale-110 transition-transform">
                                        <i class="<?= esc((string)$menu['icon']) ?> text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800 text-sm group-hover:text-blue-700 transition-colors"><?= esc((string)$menu['nama_menu']) ?></div>
                                        <div class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">ID Menu: <?= $menu['id_menu'] ?></div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Kolom Checkbox per Role -->
                            <?php foreach ($roles as $role): ?>
                                <?php
                                $isChecked = (isset($akses[$role['id_role']]) && in_array($menu['id_menu'], $akses[$role['id_role']])) ? true : false;
                                
                                // Proteksi Superadmin
                                $isLocked = ($role['id_role'] == 1 && in_array($menu['id_menu'], [1, 9])) ? true : false;
                                ?>
                                <td class="px-6 py-4 text-center border-r border-gray-50 align-middle">
                                    <div class="flex justify-center items-center h-full">
                                        <label class="relative inline-flex items-center <?= $isLocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer group/toggle' ?>">
                                            <input type="checkbox" name="permissions[<?= $role['id_role'] ?>][]" value="<?= $menu['id_menu'] ?>" <?= $isChecked ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?> class="sr-only peer toggle-checkbox">
                                            
                                            <!-- Track -->
                                            <div class="w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 peer-checked:bg-blue-600 transition-colors shadow-inner toggle-label"></div>
                                            
                                            <!-- Dot -->
                                            <div class="absolute left-[2px] top-[2px] bg-white border border-gray-300 rounded-full h-5 w-5 transition-transform peer-checked:translate-x-full peer-checked:border-white shadow-sm flex items-center justify-center">
                                                <!-- Ceklist ikon kecil di dalam dot saat aktif -->
                                                <i class="fas fa-check text-[8px] text-blue-600 opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                            </div>
                                        </label>
                                        
                                        <?php if ($isLocked): ?>
                                            <!-- Hidden input agar nilai tetap terkirim saat form disubmit (karena field disabled diabaikan POST) -->
                                            <input type="hidden" name="permissions[<?= $role['id_role'] ?>][]" value="<?= $menu['id_menu'] ?>">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-gray-50/50 border-t border-gray-100 flex justify-end">
            <button type="submit" class="w-full md:w-auto md:px-12 flex justify-center items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition-all btn-submit">
                <i class="fas fa-save"></i> Terapkan Hak Akses
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
            btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...`;
            btn.classList.add('btn-loading', 'cursor-not-allowed', 'opacity-90');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>