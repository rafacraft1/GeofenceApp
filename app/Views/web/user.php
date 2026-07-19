<?php

/**
 * @var array<int, array<string, mixed>> $users
 * @var array<int, array<string, mixed>> $roles
 * @var array<string, int> $summary
 * @var array<int, array<string, mixed>> $listKelas
 */

// Helper: relative time
$timeAgo = function (string $datetime): string {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') return '-';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Baru saja';
    if ($diff < 3600)   return floor($diff / 60) . ' mnt lalu';
    if ($diff < 86400)  return floor($diff / 3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
};
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    .modal-active { overflow: hidden; }
    #modal-user { transition: opacity 0.25s ease; }
    #modal-content { transition: transform 0.25s ease; }
</style>

<!-- ===== PAGE HEADER ===== -->
<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Manajemen User</h1>
        <p class="text-sm text-gray-500 font-medium mt-1">Kelola akun Administrator dan Guru Wali Kelas.</p>
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

<!-- ===== FITUR 4: SUMMARY COUNTER BAR ===== -->
<?php
$totalUsers = count($users);
$roleColors = [
    'Superadmin'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600',  'border' => 'border-indigo-100',  'icon' => 'fa-shield-alt'],
    'Admin'       => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600',    'border' => 'border-blue-100',    'icon' => 'fa-user-cog'],
    'Guru'        => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'icon' => 'fa-chalkboard-teacher'],
    'Wali Kelas'  => ['bg' => 'bg-teal-50',   'text' => 'text-teal-600',    'border' => 'border-teal-100',    'icon' => 'fa-user-tie'],
    '_default'    => ['bg' => 'bg-gray-50',    'text' => 'text-gray-600',    'border' => 'border-gray-100',    'icon' => 'fa-user'],
];
?>
<div class="grid grid-cols-2 md:grid-cols-<?= min(4, count($summary) + 1) ?> gap-3 mb-6">
    <!-- Total -->
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex items-center gap-3 col-span-<?= count($summary) > 0 ? '1' : '2' ?>">
        <div class="w-9 h-9 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center shrink-0">
            <i class="fas fa-users text-sm"></i>
        </div>
        <div>
            <p class="text-xl font-black text-gray-800 leading-none"><?= $totalUsers ?></p>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-0.5">Total User</p>
        </div>
    </div>
    <!-- Per Role -->
    <?php foreach ($summary as $roleName => $count):
        $rc = $roleColors[$roleName] ?? $roleColors['_default'];
    ?>
    <div class="bg-white rounded-xl p-4 border <?= $rc['border'] ?> shadow-sm flex items-center gap-3 transition-all hover:-translate-y-0.5 hover:shadow-md">
        <div class="w-9 h-9 rounded-full <?= $rc['bg'] ?> <?= $rc['text'] ?> flex items-center justify-center shrink-0">
            <i class="fas <?= $rc['icon'] ?> text-sm"></i>
        </div>
        <div>
            <p class="text-xl font-black text-gray-800 leading-none"><?= $count ?></p>
            <p class="text-[10px] font-bold <?= $rc['text'] ?> uppercase tracking-wider mt-0.5"><?= esc($roleName) ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ===== FITUR 1: SEARCH BAR ===== -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5 flex items-center gap-3">
    <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
        </div>
        <input type="text" id="user-search" placeholder="Cari nama, username, atau kelas..."
               class="w-full border border-gray-200 rounded-xl py-2.5 pl-10 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
    </div>
    <span id="search-count" class="text-xs font-bold text-gray-400 whitespace-nowrap hidden">
        <span id="search-count-val">0</span> ditemukan
    </span>
</div>

<!-- ===== DATA TABLE ===== -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left" id="user-table">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    <th class="px-6 py-5">No</th>
                    <th class="px-6 py-5">Identitas User</th>
                    <th class="px-6 py-5">Role &amp; Kelas</th> <!-- FITUR 3 & 6 -->
                    <th class="px-6 py-5">Bergabung</th> <!-- FITUR 5 -->
                    <th class="px-6 py-5 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="user-tbody">
                <?php if (!empty($users)): ?>
                    <?php $no = 1; foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors user-row group"
                            data-search="<?= strtolower(esc((string) $user['nama_lengkap']) . ' ' . esc((string) $user['username']) . ' ' . esc((string) ($user['wali_kelas_nama'] ?? ''))) ?>">

                            <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>

                            <!-- Identitas User -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <!-- Avatar dengan foto atau initial -->
                                    <div class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-gray-100 shadow-sm shrink-0">
                                        <?php if (!empty($user['foto'])): ?>
                                            <img src="<?= base_url('uploads/profiles/' . (string) $user['foto']) ?>" alt="Foto" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?php
                                            // FITUR 6: Warna avatar dari warna_badge role
                                            $avatarBg = 'bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600';
                                            $wb = (string)($user['warna_badge'] ?? 'gray');
                                            $bgMap = [
                                                'indigo'  => 'bg-gradient-to-br from-indigo-100 to-indigo-200 text-indigo-700',
                                                'blue'    => 'bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700',
                                                'emerald' => 'bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-700',
                                                'teal'    => 'bg-gradient-to-br from-teal-100 to-teal-200 text-teal-700',
                                                'amber'   => 'bg-gradient-to-br from-amber-100 to-amber-200 text-amber-700',
                                                'red'     => 'bg-gradient-to-br from-red-100 to-red-200 text-red-700',
                                                'gray'    => 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-700',
                                            ];
                                            $avatarBg = $bgMap[$wb] ?? $bgMap['gray'];
                                            ?>
                                            <div class="w-full h-full <?= $avatarBg ?> flex items-center justify-center font-black text-lg">
                                                <?= esc(mb_strtoupper(mb_substr((string) $user['nama_lengkap'], 0, 1))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-gray-800 truncate"><?= esc((string) $user['nama_lengkap']) ?></div>
                                        <div class="text-[11px] font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded mt-1 inline-block">
                                            @<?= esc((string) $user['username']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- FITUR 3 + 6: Role badge dinamis & info kelas wali -->
                            <td class="px-6 py-4">
                                <?php
                                // FITUR 6: Badge warna dinamis dari warna_badge di tabel roles
                                $warnaBadge = (string)($user['warna_badge'] ?? 'gray');
                                $badgeClassMap = [
                                    'indigo'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'blue'    => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'teal'    => 'bg-teal-50 text-teal-700 border-teal-200',
                                    'amber'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'red'     => 'bg-red-50 text-red-700 border-red-200',
                                    'gray'    => 'bg-gray-50 text-gray-700 border-gray-200',
                                ];
                                $badgeClass = $badgeClassMap[$warnaBadge] ?? $badgeClassMap['gray'];
                                ?>
                                <span class="px-3 py-1.5 rounded-lg text-xs font-bold border <?= $badgeClass ?>">
                                    <?= esc((string) ($user['nama_role'] ?? 'User')) ?>
                                </span>

                                <!-- FITUR 3: Info Wali Kelas -->
                                <?php if (!empty($user['wali_kelas_nama'])): ?>
                                    <div class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-teal-600">
                                        <i class="fas fa-chalkboard text-[9px]"></i>
                                        <?= esc((string) $user['wali_kelas_nama']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- FITUR 5: Tanggal bergabung relatif -->
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-gray-600" title="<?= !empty($user['created_at']) ? date('d M Y H:i', strtotime((string) $user['created_at'])) : '-' ?>">
                                    <?= $timeAgo((string) ($user['created_at'] ?? '')) ?>
                                </div>
                            </td>

                            <!-- Tindakan -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <!-- Reset Password -->
                                    <form action="<?= base_url('admin/user/reset/' . (string) $user['id_user']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="button"
                                                class="btn-confirm p-2.5 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors border border-amber-100"
                                                data-text="Password akan direset menjadi default: 123456"
                                                data-btn="Ya, Reset"
                                                title="Reset Password">
                                            <i class="fas fa-key text-sm"></i>
                                        </button>
                                    </form>

                                    <!-- Edit -->
                                    <button onclick='openModal(<?= htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8") ?>)'
                                            class="p-2.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors border border-blue-100"
                                            title="Edit Data">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>

                                    <!-- Hapus (dilindungi) -->
                                    <?php if ($user['id_user'] != 1 && $user['id_user'] != session()->get('id_user')): ?>
                                        <form action="<?= base_url('admin/user/delete/' . (string) $user['id_user']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button"
                                                    class="btn-confirm p-2.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors border border-red-100"
                                                    data-text="Hapus akun &quot;<?= esc((string) $user['nama_lengkap']) ?>&quot; secara permanen? Foto profil juga akan dihapus."
                                                    data-btn="Ya, Hapus Akun"
                                                    title="Hapus User">
                                                <i class="fas fa-trash-alt text-sm"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button disabled class="p-2.5 text-gray-300 bg-gray-50 rounded-xl border border-gray-100 cursor-not-allowed" title="Tidak dapat dihapus">
                                            <i class="fas fa-lock text-sm"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-14 text-center text-gray-400 font-medium">
                            <i class="fas fa-users text-3xl text-gray-200 mb-3 block"></i>
                            Belum ada data user.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Empty state saat search tidak ditemukan -->
    <div id="search-empty" class="hidden py-14 text-center text-gray-400 font-medium border-t border-gray-100">
        <i class="fas fa-search text-3xl text-gray-200 mb-3 block"></i>
        Tidak ada user yang cocok dengan pencarian.
    </div>
</div>

<!-- ===== MODAL TAMBAH / EDIT USER (FITUR 2, 3, 7) ===== -->
<div id="modal-user" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 opacity-0">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white rounded-[2rem] shadow-2xl z-10 w-full max-w-lg relative transform scale-95 max-h-[90vh] flex flex-col" id="modal-content">

        <!-- Header Modal -->
        <div class="flex justify-between items-center px-8 pt-8 pb-4 border-b border-gray-100 shrink-0">
            <h3 class="text-xl font-black text-gray-800 tracking-tight" id="modal-title">Tambah User Baru</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Body Modal (scrollable) -->
        <div class="overflow-y-auto flex-1 px-8 py-5 space-y-5">
            <form action="<?= base_url('admin/user/store') ?>" method="POST" id="form-user" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id_user" id="id_user">

                <!-- FITUR 7: Preview + Upload Foto -->
                <div class="flex flex-col items-center gap-3 mb-2">
                    <div class="relative group cursor-pointer" onclick="document.getElementById('foto-input').click()">
                        <div id="foto-preview-container" class="w-20 h-20 rounded-2xl overflow-hidden border-4 border-gray-100 shadow-md bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400">
                            <img id="foto-preview-img" src="" class="w-full h-full object-cover hidden" alt="Preview Foto">
                            <div id="foto-preview-placeholder">
                                <i class="fas fa-user text-2xl"></i>
                            </div>
                        </div>
                        <div class="absolute inset-0 rounded-2xl bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-camera text-white text-lg"></i>
                        </div>
                    </div>
                    <input type="file" name="foto" id="foto-input" accept="image/jpg,image/jpeg,image/png" class="hidden" onchange="previewFoto(this)">
                    <p class="text-[10px] text-gray-400 font-bold text-center">Klik gambar untuk ganti foto<br>JPG/PNG, maks 2MB</p>
                </div>

                <div class="space-y-4">
                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" required
                               class="w-full border-2 border-gray-100 rounded-xl p-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-gray-700 bg-gray-50 focus:bg-white"
                               placeholder="Masukkan nama lengkap">
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Username</label>
                        <input type="text" name="username" id="username" required
                               class="w-full border-2 border-gray-100 rounded-xl p-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-gray-700 bg-gray-50 focus:bg-white"
                               placeholder="Tanpa spasi, contoh: guru_budi">
                    </div>

                    <!-- Role -->
                    <div id="role-container">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Role / Hak Akses</label>
                        <select name="role_id" id="role_id" required
                                class="w-full border-2 border-gray-100 rounded-xl p-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-gray-700 bg-gray-50 focus:bg-white cursor-pointer appearance-none">
                            <option value="" disabled selected>-- Pilih Role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= esc((string) $r['id_role']) ?>"><?= esc((string) $r['nama_role']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p id="role-warning" class="text-[10px] text-amber-600 font-bold mt-2 hidden bg-amber-50 p-2 rounded-lg border border-amber-100">
                            <i class="fas fa-info-circle"></i> Role SuperAdmin tidak dapat diubah.
                        </p>
                    </div>

                    <!-- FITUR 2: Password Field dengan Generate -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider" id="password-label">Password Baru</label>
                            <button type="button" onclick="generatePassword()" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                <i class="fas fa-dice text-[9px]"></i> Generate Acak
                            </button>
                        </div>
                        <div class="relative">
                            <input type="text" name="password" id="password"
                                   class="w-full border-2 border-gray-100 rounded-xl p-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium font-mono text-gray-700 bg-gray-50 focus:bg-white pr-10"
                                   placeholder="Kosongkan = default 123456"
                                   autocomplete="new-password">
                            <!-- Toggle show/hide password -->
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-gray-600">
                                <i id="eye-icon" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        <p id="password-hint" class="text-[10px] text-gray-400 mt-1.5">Jika kosong, password default <code class="bg-gray-100 px-1 rounded font-mono">123456</code> akan digunakan.</p>
                        <p id="password-hint-edit" class="text-[10px] text-blue-600 mt-1.5 hidden font-bold"><i class="fas fa-info-circle mr-1"></i>Kosongkan jika tidak ingin mengubah password.</p>
                    </div>
                </div>

            </form>
        </div>

        <!-- Footer Modal -->
        <div class="px-8 py-5 border-t border-gray-100 flex justify-end gap-3 shrink-0">
            <button type="button" onclick="closeModal()" class="px-6 py-2.5 text-sm font-bold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
            <button type="button" onclick="submitForm()" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 btn-submit transition-all active:scale-95">Simpan Data</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ================================================================
    // FITUR 1: CLIENT-SIDE SEARCH
    // ================================================================
    const searchInput   = document.getElementById('user-search');
    const rows          = document.querySelectorAll('.user-row');
    const searchCount   = document.getElementById('search-count');
    const searchCountVal = document.getElementById('search-count-val');
    const searchEmpty   = document.getElementById('search-empty');

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;

        rows.forEach(row => {
            const text = (row.dataset.search || '').toLowerCase();
            const match = q === '' || text.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (q !== '') {
            searchCountVal.textContent = visible;
            searchCount.classList.remove('hidden');
        } else {
            searchCount.classList.add('hidden');
        }

        searchEmpty.classList.toggle('hidden', visible > 0 || q === '');
    });

    // ================================================================
    // MODAL TAMBAH / EDIT
    // ================================================================
    const modal        = document.getElementById('modal-user');
    const modalContent = document.getElementById('modal-content');
    const form         = document.getElementById('form-user');
    const roleSelect   = document.getElementById('role_id');
    const roleWarning  = document.getElementById('role-warning');

    function openModal(data = null) {
        // Reset form
        form.reset();
        document.getElementById('id_user').value   = '';
        document.getElementById('foto-preview-img').src = '';
        document.getElementById('foto-preview-img').classList.add('hidden');
        document.getElementById('foto-preview-placeholder').classList.remove('hidden');

        if (data) {
            // Mode EDIT
            document.getElementById('id_user').value       = data.id_user;
            document.getElementById('nama_lengkap').value  = data.nama_lengkap;
            document.getElementById('username').value      = data.username;
            document.getElementById('modal-title').innerText = 'Edit Data User';

            // Set foto preview jika ada
            if (data.foto) {
                const imgEl = document.getElementById('foto-preview-img');
                imgEl.src = '<?= base_url('uploads/profiles/') ?>' + data.foto;
                imgEl.classList.remove('hidden');
                document.getElementById('foto-preview-placeholder').classList.add('hidden');
            }

            // Username readonly saat edit
            document.getElementById('username').setAttribute('readonly', 'true');
            document.getElementById('username').classList.add('bg-gray-100');

            roleSelect.value = data.role_id;

            // Proteksi Superadmin
            if (data.id_user == 1) {
                roleSelect.setAttribute('disabled', 'true');
                roleSelect.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                roleWarning.classList.remove('hidden');
            } else {
                roleSelect.removeAttribute('disabled');
                roleSelect.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                roleWarning.classList.add('hidden');
            }

            // Password hint mode edit
            document.getElementById('password-label').textContent     = 'Password Baru (Opsional)';
            document.getElementById('password-hint').classList.add('hidden');
            document.getElementById('password-hint-edit').classList.remove('hidden');
            document.getElementById('password').placeholder           = 'Kosongkan untuk tidak mengubah password';

        } else {
            // Mode TAMBAH
            document.getElementById('modal-title').innerText = 'Tambah User Baru';
            document.getElementById('username').removeAttribute('readonly');
            document.getElementById('username').classList.remove('bg-gray-100');

            roleSelect.removeAttribute('disabled');
            roleSelect.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
            roleWarning.classList.add('hidden');

            document.getElementById('password-label').textContent     = 'Password';
            document.getElementById('password-hint').classList.remove('hidden');
            document.getElementById('password-hint-edit').classList.add('hidden');
            document.getElementById('password').placeholder           = 'Kosongkan = default 123456';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('modal-active');

        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        });
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('modal-active');
            roleSelect.removeAttribute('disabled');
        }, 280);
    }

    function submitForm() {
        roleSelect.removeAttribute('disabled');
        const btn = document.querySelector('.btn-submit');
        if (btn) {
            btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>Menyimpan...';
            btn.disabled = true;
        }
        form.submit();
    }

    // ESC to close modal
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // ================================================================
    // FITUR 2: PASSWORD UTILITIES
    // ================================================================
    function generatePassword() {
        const chars   = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#!';
        let pass = '';
        for (let i = 0; i < 10; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        const inp = document.getElementById('password');
        inp.value = pass;
        inp.type  = 'text'; // Tampilkan agar admin bisa lihat
        document.getElementById('eye-icon').className = 'fas fa-eye-slash text-sm';
    }

    function togglePasswordVisibility() {
        const inp  = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (inp.type === 'password') {
            inp.type  = 'text';
            icon.className = 'fas fa-eye-slash text-sm';
        } else {
            inp.type  = 'password';
            icon.className = 'fas fa-eye text-sm';
        }
    }

    // ================================================================
    // FITUR 7: FOTO PREVIEW
    // ================================================================
    function previewFoto(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById('foto-preview-img');
            img.src = e.target.result;
            img.classList.remove('hidden');
            document.getElementById('foto-preview-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
</script>
<?= $this->endSection() ?>