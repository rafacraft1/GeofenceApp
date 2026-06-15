<?php

/**
 * @var string $title
 * @var array<int, array<string, mixed>> $daftar_kelas
 * @var array<int, array<string, mixed>> $list_guru
 * @var array<int, array<string, mixed>> $list_zona
 * @var string|null $search_aktif
 * @var string|null $pager_links
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    .modal-active {
        overflow: hidden;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Kelas</h2>
        <p class="text-sm text-gray-500 mt-1">Atur data kelas, penetapan wali kelas, dan lokasi zona PKL massal.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <form action="<?= base_url('admin/kelas') ?>" method="GET" class="w-full md:flex-1 max-w-md">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" name="search" value="<?= esc((string)($search_aktif ?? '')) ?>" placeholder="Cari Nama Kelas..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-10 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
    </form>

    <div class="flex w-full md:w-auto gap-2 items-center">
        <button onclick="toggleFormTambah()" class="w-full md:w-auto flex items-center justify-center gap-1.5 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg> Tambah Kelas
        </button>
    </div>
</div>

<div id="form-tambah" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6 hidden transition-all">
    <div class="mb-4 border-b border-gray-100 pb-3">
        <h4 class="text-md font-bold text-gray-800">Tambah Kelas Baru</h4>
    </div>
    <form action="<?= base_url('admin/kelas/store') ?>" method="POST" id="formKelas" class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">
        <?= csrf_field() ?>
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Kelas</label>
            <input type="text" name="nama_kelas" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Contoh: XII RPL 1">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Wali Kelas (Opsional)</label>
            <select name="wali_kelas_id" class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                <option value="">-- Belum ada Wali Kelas --</option>
                <?php if (!empty($list_guru)) : ?>
                    <?php foreach ($list_guru as $g): ?>
                        <option value="<?= (string) $g['id_user'] ?>"><?= esc((string) $g['nama_lengkap']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Zona Absensi (Massal)</label>
            <select name="zona_id" class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-amber-500 transition-all cursor-pointer text-gray-700">
                <option value="">🏫 Zona Default Sekolah (Pusat)</option>
                <?php if (!empty($list_zona)) : ?>
                    <?php foreach ($list_zona as $z): ?>
                        <option value="<?= (string) $z['id_zona'] ?>">📍 <?= esc((string) $z['nama_zona']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <p class="text-[9px] text-gray-400 mt-1 font-medium">Ubah ini jika 1 kelas full sedang Kunjungan/PKL di tempat yang sama.</p>
        </div>

        <div class="md:col-span-3 flex justify-end gap-3 pt-2">
            <button type="button" onclick="toggleFormTambah()" class="text-sm font-semibold text-gray-500 px-4 py-2 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-md hover:bg-blue-700 btn-submit transition-all">Simpan Kelas</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-12">
    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-t border-b border-gray-100">
                    <th class="px-6 py-4 w-16 text-center">No</th>
                    <th class="px-6 py-4">Informasi Kelas</th>
                    <th class="px-6 py-4">Wali Kelas</th>
                    <th class="px-6 py-4 text-center">Jumlah Siswa</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($daftar_kelas)) : ?>
                    <?php
                    $safePage    = max(1, (int)($page ?? 1));
                    $safePerPage = max(1, (int)($perPage ?? 10));
                    $no = (($safePage - 1) * $safePerPage) + 1;
                    ?>
                    <?php foreach ($daftar_kelas as $k): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 text-center text-sm font-semibold text-gray-500"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $k['nama_kelas']) ?></div>
                                <div class="mt-1">
                                    <?php if (!empty($k['nama_zona'])): ?>
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-600 border border-amber-100 px-2 py-0.5 rounded text-[10px] font-bold">
                                            📍 PKL / Kunjungan: <?= esc((string) $k['nama_zona']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 border border-gray-200 px-2 py-0.5 rounded text-[10px] font-bold">
                                            🏫 Zona Pusat Sekolah
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($k['nama_wali'])): ?>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">
                                            <?= esc(strtoupper(substr((string) $k['nama_wali'], 0, 1))) ?>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700"><?= esc((string) $k['nama_wali']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] font-medium text-amber-500 bg-amber-50 px-2 py-1 rounded-lg border border-amber-100">Belum Ditentukan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php $jml = (int) ($k['jumlah_siswa'] ?? 0); ?>
                                <span class="text-sm font-black <?= $jml > 0 ? 'text-blue-600' : 'text-gray-400' ?>">
                                    <?= $jml ?> Siswa
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick='openEditModal(<?= htmlspecialchars(json_encode($k), ENT_QUOTES, "UTF-8") ?>)' class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-lg transition-colors" title="Edit Kelas">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 111.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <form action="<?= base_url('admin/kelas/delete/' . (string) $k['id_kelas']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn-confirm p-2 text-slate-600 bg-slate-50 hover:bg-red-100 hover:text-red-600 border border-slate-200 rounded-lg transition-colors" data-text="Hapus kelas <?= esc((string) $k['nama_kelas']) ?>? Pastikan kelas sudah kosong (0 siswa)." data-btn="Ya, Hapus" title="Hapus Kelas">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-400 italic">Belum ada data kelas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="p-6 bg-gray-50/30 flex flex-col lg:flex-row justify-between items-center gap-6 border-t border-gray-100">
        <div class="text-sm text-gray-500 font-semibold whitespace-nowrap">
            <?php
            $safePage    = max(1, (int)($page ?? 1));
            $safePerPage = max(1, (int)($perPage ?? 10));
            $safeTotal   = max(0, (int)($total_data ?? 0));
            $start = $safeTotal > 0 ? (($safePage - 1) * $safePerPage) + 1 : 0;
            $end   = min($safePage * $safePerPage, $safeTotal);
            ?>
            Menampilkan <?= $start ?> - <?= $end ?> dari <?= $safeTotal ?> kelas
        </div>

        <div class="w-full flex justify-center lg:justify-end">
            <?= $pager_links ?? '' ?>
        </div>
    </div>
</div>

<div id="modal-edit" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-md p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Edit Data Kelas</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="form-edit-action" method="POST" class="space-y-5">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Kelas</label>
                <input type="text" id="edit-nama" name="nama_kelas" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Wali Kelas (Opsional)</label>
                <select id="edit-wali" name="wali_kelas_id" class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                    <option value="">-- Belum ada Wali Kelas --</option>
                    <?php if (!empty($list_guru)) : ?>
                        <?php foreach ($list_guru as $g): ?>
                            <option value="<?= (string) $g['id_user'] ?>"><?= esc((string) $g['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Zona Absensi (Massal)</label>
                <select id="edit-zona" name="zona_id" class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-amber-500 transition-all cursor-pointer text-gray-700">
                    <option value="">🏫 Zona Default Sekolah (Pusat)</option>
                    <?php if (!empty($list_zona)) : ?>
                        <?php foreach ($list_zona as $z): ?>
                            <option value="<?= (string) $z['id_zona'] ?>">📍 <?= esc((string) $z['nama_zona']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-400 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btn-submit-edit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-md hover:bg-blue-700 btn-submit transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function toggleFormTambah() {
        document.getElementById('form-tambah').classList.toggle('hidden');
    }

    let initialEditState = {};

    function openEditModal(data) {
        document.getElementById('edit-nama').value = data.nama_kelas;
        document.getElementById('edit-wali').value = data.wali_kelas_id || "";
        document.getElementById('edit-zona').value = data.zona_id || ""; // Tangkap Data Zona

        document.getElementById('form-edit-action').action = '<?= base_url("admin/kelas/update/") ?>' + data.id_kelas;

        initialEditState = {
            nama: data.nama_kelas,
            wali: data.wali_kelas_id || "",
            zona: data.zona_id || ""
        };

        document.getElementById('btn-submit-edit').disabled = true;

        document.getElementById('modal-edit').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function checkEditChanges() {
        const currentNama = document.getElementById('edit-nama').value;
        const currentWali = document.getElementById('edit-wali').value;
        const currentZona = document.getElementById('edit-zona').value;

        const isChanged = (
            currentNama !== initialEditState.nama ||
            currentWali !== initialEditState.wali ||
            currentZona !== initialEditState.zona
        );

        document.getElementById('btn-submit-edit').disabled = !isChanged;
    }

    document.getElementById('edit-nama').addEventListener('input', checkEditChanges);
    document.getElementById('edit-wali').addEventListener('change', checkEditChanges);
    document.getElementById('edit-zona').addEventListener('change', checkEditChanges);

    function closeEditModal() {
        document.getElementById('modal-edit').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    document.querySelectorAll('#formKelas, #form-edit-action').forEach(function(form) {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('.btn-submit');
            if (btn) {
                btn.classList.add('btn-loading');
                btn.setAttribute('disabled', 'true');
            }
        });
    });
</script>
<?= $this->endSection() ?>