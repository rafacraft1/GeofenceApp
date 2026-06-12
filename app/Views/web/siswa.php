<?php

/**
 * @var array<int, array<string, mixed>> $list_kelas
 * @var string|null $kelas_aktif
 * @var string|null $search_aktif
 * @var string|null $sort_aktif
 * @var string $sort_col
 * @var string $sort_dir
 * @var array<int, array<string, mixed>> $siswa
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 * @var string $pager_links
 * @var bool $is_wali_kelas
 */

$buildSortLink = function ($column) use ($search_aktif, $kelas_aktif, $sort_col, $sort_dir) {
    $newDir = ($sort_col === $column && $sort_dir === 'asc') ? 'desc' : 'asc';
    $url = base_url('admin/siswa') . "?sort={$column}-{$newDir}";
    if ($search_aktif) $url .= "&search=" . urlencode($search_aktif);
    if ($kelas_aktif) $url .= "&kelas=" . urlencode($kelas_aktif);
    return $url;
};

$getSortIcon = function ($column) use ($sort_col, $sort_dir) {
    if ($sort_col !== $column) {
        return '<svg class="w-3.5 h-3.5 text-gray-300 opacity-50 ml-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>';
    }
    if ($sort_dir === 'asc') {
        return '<svg class="w-3.5 h-3.5 text-blue-600 ml-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>';
    }
    return '<svg class="w-3.5 h-3.5 text-blue-600 ml-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';
};
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<style>
    .modal-active {
        overflow: hidden;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @keyframes indeterminate {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(200%);
        }
    }

    .animate-indeterminate {
        animation: indeterminate 1.5s infinite linear;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Daftar Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola data siswa, foto, dan pantau perangkat.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <form action="<?= base_url('admin/siswa') ?>" method="GET" class="w-full md:flex-1 max-w-2xl">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" name="search" value="<?= esc((string)($search_aktif ?? '')) ?>" placeholder="Cari NIS / Nama Siswa..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-10 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
        <?php if (!empty($kelas_aktif)): ?>
            <input type="hidden" name="kelas" value="<?= esc((string)$kelas_aktif) ?>">
        <?php endif; ?>
    </form>

    <div class="flex w-full md:w-auto gap-2 items-center overflow-x-auto pb-1 md:pb-0 scrollbar-hide">
        <a href="<?= base_url('admin/siswa/export' . (!empty($kelas_aktif) ? '?kelas=' . esc((string)$kelas_aktif) : '')) ?>" class="flex items-center justify-center gap-1.5 bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 shadow-md whitespace-nowrap transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg> Ekspor
        </a>
        <button onclick="openImportModal()" class="flex items-center justify-center gap-1.5 bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 shadow-md whitespace-nowrap transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg> Impor
        </button>
        <button onclick="toggleFormTambah()" class="flex items-center justify-center gap-1.5 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md whitespace-nowrap transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg> Tambah
        </button>
    </div>
</div>

<div id="form-tambah" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6 hidden transition-all">
    <div class="mb-4 border-b border-gray-100 pb-3">
        <h4 class="text-md font-bold text-gray-800">Tambah Siswa Baru</h4>
    </div>
    <form action="<?= base_url('admin/siswa/store') ?>" method="POST" enctype="multipart/form-data" id="formSiswa" class="grid grid-cols-1 md:grid-cols-4 gap-5 items-start">
        <?= csrf_field() ?>
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">NIS</label>
            <input type="text" name="nis" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Contoh: 2026001">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Lengkap</label>
            <input type="text" name="nama_siswa" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Nama siswa">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Kelas</label>
            <?php if ($is_wali_kelas): ?>
                <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none" readonly>
                <input type="hidden" name="kelas_id" value="<?= esc((string) session()->get('kelas_id')) ?>">
            <?php else: ?>
                <select name="kelas_id" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                    <option value="" disabled selected>-- Pilih Kelas --</option>
                    <?php if (!empty($list_kelas)) : ?>
                        <?php foreach ($list_kelas as $k): ?>
                            <option value="<?= (string) $k['id_kelas'] ?>"><?= esc((string) $k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Foto (Opsional)</label>
            <input type="file" name="foto" accept="image/*" class="w-full border border-gray-200 rounded-xl p-2 bg-gray-50 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
        <div class="md:col-span-4 flex justify-end gap-3 pt-2">
            <button type="button" onclick="toggleFormTambah()" class="text-sm font-semibold text-gray-500 px-4 py-2 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-md hover:bg-blue-700 btn-submit transition-all">Simpan Data</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-24">
    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap" id="siswa-table">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-t border-b border-gray-100">
                    <th class="px-4 py-4 w-12 text-center">
                        <input type="checkbox" id="chk-all" onchange="toggleAllSiswa(this)" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            <a href="<?= $buildSortLink('nama_siswa') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600" title="Urutkan berdasarkan Nama">
                                Siswa <?= $getSortIcon('nama_siswa') ?>
                            </a>
                            <span class="text-gray-200">|</span>
                            <a href="<?= $buildSortLink('nis') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600" title="Urutkan berdasarkan NIS">
                                NIS <?= $getSortIcon('nis') ?>
                            </a>
                        </div>
                    </th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortLink('device_id') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600">
                            Status HP <?= $getSortIcon('device_id') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4 text-center">
                        <div class="flex justify-center">
                            <a href="<?= $buildSortLink('fraud_count') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600">
                                Keamanan <?= $getSortIcon('fraud_count') ?>
                            </a>
                        </div>
                    </th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($siswa)) : ?>
                    <?php foreach ($siswa as $s): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" value="<?= esc((string) $s['id_siswa']) ?>" onchange="toggleSiswa(this)" class="chk-siswa w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shadow-inner overflow-hidden border border-gray-200 shrink-0">
                                        <?php if (!empty($s['foto_profil'])): ?>
                                            <img src="<?= base_url('uploads/siswa/' . (string) $s['foto_profil']) ?>" alt="Foto" loading="lazy" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?= esc(strtoupper(substr((string) ($s['nama_siswa'] ?? ''), 0, 1))) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800"><?= esc((string) $s['nama_siswa']) ?></div>
                                        <div class="text-[11px] text-gray-500 font-medium mt-1">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded inline-block"><?= esc((string) $s['nis']) ?> • <?= esc((string) ($s['nama_kelas'] ?? 'Belum ada kelas')) ?></span>
                                            <?php if (!empty($s['nama_zona'])): ?>
                                                <span class="bg-amber-50 text-amber-600 border border-amber-100 px-2 py-0.5 rounded inline-block ml-1">📍 <?= esc((string) $s['nama_zona']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($s['device_id'])): ?>
                                    <div class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-2 py-1.5 rounded-lg text-[10px] font-bold w-fit border border-emerald-100">
                                        <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>TERIKAT
                                    </div>
                                <?php else: ?>
                                    <div class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-1.5 rounded-lg w-fit border border-gray-200">BELUM TERIKAT</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] font-bold <?= (!empty($s['is_blocked'])) ? 'text-red-500' : 'text-gray-600' ?>"><?= (int) ($s['fraud_count'] ?? 0) ?>/3 Fraud</span>
                                    <div class="w-16 h-1 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full <?= (!empty($s['is_blocked'])) ? 'bg-red-500' : 'bg-blue-500' ?>" style="width: <?= (((int)($s['fraud_count'] ?? 0)) / 3) * 100 ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="<?= base_url('admin/siswa/detail/' . (string) $s['id_siswa']) ?>" class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-lg transition-colors" title="Profil 360">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>

                                    <button onclick='openEditModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8") ?>)' class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-lg transition-colors" title="Edit Data">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 111.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <form action="<?= base_url('admin/siswa/delete/' . (string) $s['id_siswa']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn-confirm p-2 text-slate-600 bg-slate-50 hover:bg-red-100 hover:text-red-600 border border-slate-200 rounded-lg transition-colors" data-text="Data siswa beserta foto akan dihapus permanen. Lanjutkan?" data-btn="Ya, Hapus Permanen" title="Hapus Siswa">
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
                        <td colspan="5" class="py-10 text-center text-gray-400 italic">Data siswa tidak ditemukan.</td>
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
            Menampilkan <?= $start ?> - <?= $end ?> dari <?= $safeTotal ?> data
        </div>

        <div class="w-full flex justify-center lg:justify-end">
            <?= $pager_links ?? '' ?>
        </div>
    </div>
</div>

<div id="bulk-action-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.5)] z-50 flex items-center gap-4 transition-all duration-300 translate-y-24 opacity-0 pointer-events-none">
    <div class="flex items-center gap-2">
        <span class="flex items-center justify-center w-6 h-6 bg-blue-500 rounded-full text-xs font-bold" id="bulk-count">0</span>
        <span class="text-sm font-semibold whitespace-nowrap">Terpilih</span>
    </div>
    <div class="w-px h-5 bg-slate-700"></div>
    <button onclick="clearSelection()" class="text-xs font-medium text-slate-400 hover:text-white transition-colors">Batal</button>
    <button onclick="submitBulkDelete()" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-xl text-sm font-bold shadow-lg shadow-red-500/30 transition-all flex items-center gap-2 whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
        </svg> Hapus
    </button>
</div>

<form id="form-bulk-delete" action="<?= base_url('admin/siswa/deleteBulk') ?>" method="POST" class="hidden">
    <?= csrf_field() ?>
    <div id="hidden-ids-container"></div>
</form>

<div id="modal-edit" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-lg p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Edit Data Siswa</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="form-edit-action" method="POST" enctype="multipart/form-data" class="space-y-5">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">NIS</label>
                <input type="text" id="edit-nis" name="nis" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                <p id="edit-pwd-hint" class="text-[10px] text-amber-500 mt-1.5 ml-1 font-bold hidden">
                    <svg class="w-3 h-3 inline-block mr-0.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    NIS berubah! Password siswa otomatis direset ke NIS baru.
                </p>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Lengkap</label>
                <input type="text" id="edit-nama" name="nama_siswa" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Kelas</label>
                <?php if ($is_wali_kelas): ?>
                    <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none" readonly>
                    <input type="hidden" id="edit-kelas" name="kelas_id" value="<?= esc((string) session()->get('kelas_id')) ?>">
                <?php else: ?>
                    <select id="edit-kelas" name="kelas_id" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                        <?php if (!empty($list_kelas)) : ?>
                            <?php foreach ($list_kelas as $k): ?>
                                <option value="<?= (string) $k['id_kelas'] ?>"><?= esc((string) $k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Ganti Foto (Kosongkan jika tidak mengubah)</label>
                <input type="file" id="edit-foto" name="foto" accept="image/*" class="w-full border border-gray-200 rounded-xl p-2 bg-gray-50 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-400 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btn-submit-edit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 btn-submit transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-import" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeImportModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-md p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Import Data</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="<?= base_url('admin/siswa/import') ?>" method="POST" enctype="multipart/form-data" id="formImport" class="space-y-6">
            <?= csrf_field() ?>
            <div class="p-6 border-2 border-dashed border-slate-300 rounded-2xl text-center bg-slate-50 hover:bg-slate-100 cursor-pointer transition-colors">
                <input type="file" name="file_excel" id="file_excel" class="hidden" required accept=".xlsx">
                <label for="file_excel" class="cursor-pointer block w-full h-full">
                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <p class="text-sm font-medium text-slate-600" id="file-name-preview">Klik pilih file Excel (.xlsx)</p>
                </label>
            </div>

            <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl">
                <p class="text-[11px] text-amber-700 leading-relaxed font-medium text-center">Gunakan format template agar tidak error.</p>
                <?php if ($is_wali_kelas): ?>
                    <p class="text-[11px] text-red-600 leading-relaxed font-bold text-center mt-1">Anda hanya dapat mengimpor data khusus untuk Kelas <?= esc((string) session()->get('nama_kelas')) ?>.</p>
                <?php endif; ?>
                <a href="<?= base_url('admin/siswa/downloadTemplate') ?>" class="block text-center text-amber-900 font-bold text-xs mt-2 underline">Unduh Template</a>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 btn-submit transition-all">Mulai Import</button>
        </form>
    </div>
</div>

<div id="loading-overlay" class="fixed inset-0 z-[100] hidden items-center justify-center transition-opacity duration-300 opacity-0">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>
    <div class="bg-white p-8 rounded-[2rem] shadow-2xl z-10 flex flex-col items-center max-w-sm w-full mx-4 text-center transform scale-100">
        <div class="relative w-20 h-20 mb-6">
            <svg class="animate-spin absolute inset-0 w-full h-full text-blue-100" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8"></circle>
            </svg>
            <svg class="animate-spin absolute inset-0 w-full h-full text-blue-600" viewBox="0 0 100 100" style="animation-direction: reverse; animation-duration: 1.5s;">
                <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" stroke-dasharray="80 200" stroke-linecap="round"></circle>
            </svg>
            <div class="absolute inset-0 flex items-center justify-center text-blue-600">
                <svg class="w-8 h-8 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
            </div>
        </div>
        <h4 class="text-lg font-black text-gray-800 mb-2">Memproses Import...</h4>
        <p class="text-xs text-gray-500 font-medium leading-relaxed mb-5">Sistem sedang membaca dan memvalidasi file Excel Anda. Mohon jangan tutup atau refresh halaman ini.</p>
        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden relative">
            <div class="bg-blue-600 h-full rounded-full absolute left-0 top-0 w-1/2 animate-indeterminate"></div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('file_excel').addEventListener('change', function(e) {
        document.getElementById('file-name-preview').textContent = e.target.files[0] ? e.target.files[0].name : "Klik pilih file Excel (.xlsx)";
    });

    function toggleFormTambah() {
        document.getElementById('form-tambah').classList.toggle('hidden');
    }

    let initialEditState = {};

    function openEditModal(data) {
        document.getElementById('edit-nis').value = data.nis;
        document.getElementById('edit-nama').value = data.nama_siswa;

        const editKelas = document.getElementById('edit-kelas');
        if (editKelas) editKelas.value = data.kelas_id;

        document.getElementById('edit-foto').value = "";
        document.getElementById('form-edit-action').action = '<?= base_url("admin/siswa/update/") ?>' + data.id_siswa;

        initialEditState = {
            nis: data.nis,
            nama: data.nama_siswa,
            kelas: editKelas ? data.kelas_id : '',
            foto: ""
        };

        document.getElementById('edit-pwd-hint').classList.add('hidden');
        document.getElementById('btn-submit-edit').disabled = true;

        document.getElementById('modal-edit').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function checkEditChanges() {
        const currentNis = document.getElementById('edit-nis').value;
        const currentNama = document.getElementById('edit-nama').value;
        const editKelas = document.getElementById('edit-kelas');
        const currentKelas = editKelas ? editKelas.value : '';
        const currentFoto = document.getElementById('edit-foto').value;

        if (currentNis !== initialEditState.nis && currentNis !== '') {
            document.getElementById('edit-pwd-hint').classList.remove('hidden');
        } else {
            document.getElementById('edit-pwd-hint').classList.add('hidden');
        }

        const isChanged = (
            currentNis !== initialEditState.nis ||
            currentNama !== initialEditState.nama ||
            (editKelas && currentKelas != initialEditState.kelas) ||
            currentFoto !== initialEditState.foto
        );

        document.getElementById('btn-submit-edit').disabled = !isChanged;
    }

    document.getElementById('edit-nis').addEventListener('input', checkEditChanges);
    document.getElementById('edit-nama').addEventListener('input', checkEditChanges);

    const editKelasInput = document.getElementById('edit-kelas');
    if (editKelasInput && editKelasInput.tagName === 'SELECT') {
        editKelasInput.addEventListener('change', checkEditChanges);
    }

    document.getElementById('edit-foto').addEventListener('change', checkEditChanges);

    function closeEditModal() {
        document.getElementById('modal-edit').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    function openImportModal() {
        document.getElementById('formImport').reset();
        document.getElementById('file_excel').value = '';
        document.getElementById('file-name-preview').textContent = "Klik pilih file Excel (.xlsx)";

        document.getElementById('modal-import').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function closeImportModal() {
        document.getElementById('modal-import').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    let selectedSiswa = JSON.parse(sessionStorage.getItem('selectedSiswa')) || [];

    function updateCheckboxesState() {
        let allChecked = true;
        let hasSiswaOnPage = false;

        document.querySelectorAll('.chk-siswa').forEach(chk => {
            hasSiswaOnPage = true;
            if (selectedSiswa.includes(chk.value)) {
                chk.checked = true;
            } else {
                chk.checked = false;
                allChecked = false;
            }
        });

        const chkAll = document.getElementById('chk-all');
        if (chkAll) {
            chkAll.checked = hasSiswaOnPage && allChecked;
        }

        updateBulkActionBar();
    }

    function toggleSiswa(cb) {
        if (cb.checked) {
            if (!selectedSiswa.includes(cb.value)) {
                selectedSiswa.push(cb.value);
            }
        } else {
            selectedSiswa = selectedSiswa.filter(id => id !== cb.value);
        }
        saveSelection();
    }

    function toggleAllSiswa(cb) {
        document.querySelectorAll('.chk-siswa').forEach(chk => {
            chk.checked = cb.checked;
            if (cb.checked) {
                if (!selectedSiswa.includes(chk.value)) {
                    selectedSiswa.push(chk.value);
                }
            } else {
                selectedSiswa = selectedSiswa.filter(id => id !== chk.value);
            }
        });
        saveSelection();
    }

    function saveSelection() {
        sessionStorage.setItem('selectedSiswa', JSON.stringify(selectedSiswa));
        updateCheckboxesState();
    }

    function clearSelection() {
        selectedSiswa = [];
        saveSelection();
    }

    function updateBulkActionBar() {
        const bar = document.getElementById('bulk-action-bar');
        const count = document.getElementById('bulk-count');

        if (selectedSiswa.length > 0) {
            count.innerText = selectedSiswa.length;
            bar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
            bar.classList.add('translate-y-0', 'opacity-100');
        } else {
            bar.classList.remove('translate-y-0', 'opacity-100');
            bar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
        }
    }

    function submitBulkDelete() {
        if (selectedSiswa.length === 0) return;

        if (confirm(`Peringatan! Anda akan menghapus secara permanen ${selectedSiswa.length} data siswa terpilih. Lanjutkan?`)) {
            const container = document.getElementById('hidden-ids-container');
            container.innerHTML = '';

            selectedSiswa.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                container.appendChild(input);
            });

            sessionStorage.removeItem('selectedSiswa');
            document.getElementById('form-bulk-delete').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateCheckboxesState();
    });

    document.querySelectorAll('#formSiswa, #form-edit-action, #formImport').forEach(function(form) {
        form.addEventListener('submit', function() {
            if (this.id === 'formImport') {
                closeImportModal();
                const loadingOverlay = document.getElementById('loading-overlay');
                loadingOverlay.classList.remove('hidden');
                loadingOverlay.classList.add('flex');
                setTimeout(() => {
                    loadingOverlay.classList.remove('opacity-0');
                }, 20);
            } else {
                const btn = this.querySelector('.btn-submit');
                if (btn) {
                    btn.classList.add('btn-loading');
                    btn.setAttribute('disabled', 'true');
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>