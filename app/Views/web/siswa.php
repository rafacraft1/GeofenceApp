<?php

/**
 * @var array<int, array<string, mixed>> $list_kelas
 * @var array<int, array<string, mixed>> $list_zona
 * @var string|null $kelas_aktif
 * @var string $nama_kelas_aktif
 * @var array<string, int> $summary
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
    if ($kelas_aktif) $url .= "&kelas=" . urlencode((string)$kelas_aktif);
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
    .modal-active { overflow: hidden; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    @keyframes indeterminate {
        0%   { transform: translateX(-100%); }
        100% { transform: translateX(200%); }
    }
    .animate-indeterminate { animation: indeterminate 1.5s infinite linear; }

    /* FITUR 9: Smooth slide animation form tambah */
    .form-collapsible {
        display: grid;
        grid-template-rows: 0fr;
        opacity: 0;
        transition: grid-template-rows 0.35s ease, opacity 0.3s ease, margin-bottom 0.3s ease;
        margin-bottom: 0;
    }
    .form-collapsible.open {
        grid-template-rows: 1fr;
        opacity: 1;
        margin-bottom: 1.5rem;
    }
    .form-collapsible > .form-inner { overflow: hidden; }
</style>

<!-- ===== PAGE HEADER ===== -->
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Daftar Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola data siswa, foto, pemetaan zona PKL, dan perangkat.</p>
    </div>
</div>

<!-- ===== FITUR 2: SUMMARY STATS BAR ===== -->
<?php
$statItems = [
    ['label' => 'Total Siswa',  'value' => $summary['total'],      'icon' => 'fa-users',       'color' => 'blue'],
    ['label' => 'Terikat HP',   'value' => $summary['terikat_hp'], 'icon' => 'fa-mobile-alt',  'color' => 'emerald'],
    ['label' => 'Terblokir',    'value' => $summary['terblokir'],  'icon' => 'fa-ban',          'color' => 'red'],
    ['label' => 'Belum Foto',   'value' => $summary['belum_foto'], 'icon' => 'fa-image',        'color' => 'amber'],
];
$sc = [
    'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600',    'border' => 'border-blue-100',    'label' => 'text-blue-500'],
    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'label' => 'text-emerald-500'],
    'red'     => ['bg' => 'bg-red-50',     'text' => 'text-red-500',     'border' => 'border-red-100',     'label' => 'text-red-400'],
    'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'border' => 'border-amber-100',   'label' => 'text-amber-500'],
];
?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <?php foreach ($statItems as $si):
        $c = $sc[$si['color']];
    ?>
    <div class="bg-white rounded-xl p-4 border <?= $c['border'] ?> shadow-sm flex items-center gap-3 transition-all hover:-translate-y-0.5 hover:shadow-md group">
        <div class="w-9 h-9 rounded-full <?= $c['bg'] ?> <?= $c['text'] ?> flex items-center justify-center shrink-0 transition-transform group-hover:scale-110">
            <i class="fas <?= $si['icon'] ?> text-sm"></i>
        </div>
        <div>
            <p class="text-xl font-black text-gray-800 leading-none"><?= $si['value'] ?></p>
            <p class="text-[10px] font-bold <?= $c['label'] ?> uppercase tracking-wider mt-0.5"><?= $si['label'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ===== FILTER + TOOLBAR — UNIFIED ONE CARD ===== -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">

    <!-- Baris utama: Search + Kelas + Action Buttons -->
    <form action="<?= base_url('admin/siswa') ?>" method="GET" id="search-form"
          class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

        <!-- Search -->
        <div class="relative flex-1 min-w-0">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" name="search" id="search-input"
                   value="<?= esc((string)($search_aktif ?? '')) ?>"
                   placeholder="Cari NIS atau Nama Siswa..."
                   class="w-full border border-gray-200 rounded-xl py-2.5 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>

        <!-- Filter Kelas (admin only) -->
        <?php if (!$is_wali_kelas): ?>
        <select name="kelas" id="kelas-filter"
                class="shrink-0 w-full sm:w-44 border border-gray-200 rounded-xl py-2.5 px-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
                onchange="this.form.submit()">
            <option value="">🏫 Semua Kelas</option>
            <?php foreach ($list_kelas as $k): ?>
                <option value="<?= (string) $k['id_kelas'] ?>"
                        <?= ((string)($kelas_aktif ?? '') === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                    <?= esc((string) $k['nama_kelas']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>
            <input type="hidden" name="kelas" value="<?= esc((string)($kelas_aktif ?? '')) ?>">
        <?php endif; ?>

        <!-- Preserve sort -->
        <?php if (!empty($sort_aktif) && $sort_aktif !== 'nama_siswa-asc'): ?>
        <input type="hidden" name="sort" value="<?= esc($sort_aktif) ?>">
        <?php endif; ?>

        <!-- Divider (hidden on mobile) -->
        <div class="hidden sm:block w-px h-8 bg-gray-200 shrink-0 self-center"></div>

        <!-- Action buttons -->
        <div class="flex items-center gap-2 shrink-0">
            <a href="<?= base_url('admin/siswa/export' . (!empty($kelas_aktif) ? '?kelas=' . esc((string)$kelas_aktif) : '')) ?>"
               class="flex items-center gap-1.5 border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white px-3 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all"
               title="Ekspor data ke Excel">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span class="hidden sm:inline">Ekspor</span>
            </a>
            <button type="button" onclick="openImportModal()"
                    class="flex items-center gap-1.5 border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white px-3 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all"
                    title="Impor data dari Excel">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <span class="hidden sm:inline">Impor</span>
            </button>
            <button type="button" onclick="toggleFormTambah()" id="btn-toggle-tambah"
                    class="flex items-center gap-1.5 bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-sm whitespace-nowrap transition-all active:scale-95">
                <svg id="btn-icon-plus" class="w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span id="btn-label-tambah">Tambah</span>
            </button>
        </div>
    </form>

    <!-- Active Filter Badge -->
    <?php if (!$is_wali_kelas && !empty($kelas_aktif) && !empty($nama_kelas_aktif)): ?>
    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-50">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Filter:</span>
        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-full text-xs font-bold">
            <i class="fas fa-chalkboard text-[9px]"></i>
            <?= esc($nama_kelas_aktif) ?>
            <a href="<?= base_url('admin/siswa') . (!empty($search_aktif) ? '?search=' . urlencode($search_aktif) : '') ?>"
               class="ml-0.5 text-blue-300 hover:text-red-500 transition-colors" title="Hapus filter kelas">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </span>
        <span class="text-[10px] text-gray-400"><?= $summary['total'] ?> siswa</span>
    </div>
    <?php endif; ?>
</div>

<!-- ===== FITUR 9 & 7: SMOOTH FORM TAMBAH + FOTO PREVIEW ===== -->
<div class="form-collapsible mb-6" id="form-tambah-wrapper">
    <div class="form-inner">
        <div class="bg-white rounded-b-2xl shadow-sm border-x border-b border-gray-100 px-6 py-6">
            <div class="mb-4 border-b border-gray-100 pb-3 flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-plus text-[10px]"></i>
                </div>
                <h4 class="text-md font-bold text-gray-800">Tambah Siswa Baru</h4>
            </div>
            <form action="<?= base_url('admin/siswa/store') ?>" method="POST" enctype="multipart/form-data" id="formSiswa"
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 items-start">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">NIS</label>
                    <input type="text" name="nis" required
                           class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                           placeholder="Contoh: 2026001">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Lengkap</label>
                    <input type="text" name="nama_siswa" required
                           class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                           placeholder="Nama siswa">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Kelas</label>
                    <?php if ($is_wali_kelas): ?>
                        <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>"
                               class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none" readonly>
                        <input type="hidden" name="kelas_id" value="<?= esc((string) session()->get('kelas_id')) ?>">
                    <?php else: ?>
                        <select name="kelas_id" required
                                class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                            <option value="" disabled selected>-- Pilih Kelas --</option>
                            <?php foreach ($list_kelas as $k): ?>
                                <option value="<?= (string) $k['id_kelas'] ?>"
                                        <?= ((string)($kelas_aktif ?? '') === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                                    <?= esc((string) $k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Penempatan PKL</label>
                    <select name="zona_id"
                            class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-amber-500 transition-all cursor-pointer text-gray-700">
                        <option value="">🗺️ Mengikuti Zona Sekolah / Kelas</option>
                        <?php foreach ($list_zona as $z): ?>
                            <option value="<?= (string) $z['id_zona'] ?>">📍 <?= esc((string) $z['nama_zona']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[9px] text-gray-400 mt-1 font-medium">Kosongkan jika siswa belajar di sekolah (Reguler).</p>
                </div>

                <!-- FITUR 7: Foto dengan preview -->
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Foto <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl overflow-hidden border-2 border-gray-200 bg-gray-100 shrink-0 flex items-center justify-center text-gray-400" id="foto-preview-container">
                            <img id="foto-preview-img" src="" class="w-full h-full object-cover hidden" alt="Preview">
                            <i id="foto-preview-icon" class="fas fa-user text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="foto" id="foto-input" accept="image/*"
                                   class="w-full border border-gray-200 rounded-xl p-2 bg-gray-50 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                   onchange="previewTambahFoto(this)">
                            <p class="text-[9px] text-gray-400 mt-1">JPG/PNG maks 2MB</p>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 lg:col-span-3 flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" onclick="toggleFormTambah()" class="text-sm font-semibold text-gray-500 px-4 py-2 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-md hover:bg-blue-700 btn-submit transition-all">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== DATA TABLE ===== -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-24">
    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap" id="siswa-table">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-t border-b border-gray-100">
                    <th class="px-4 py-4 w-12 text-center">
                        <input type="checkbox" id="chk-all" onchange="toggleAllSiswa(this)"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
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
                    <?php foreach ($siswa as $s):
                        $isBlocked  = !empty($s['is_blocked']);
                        $fraudCount = (int) ($s['fraud_count'] ?? 0);
                    ?>
                        <tr class="hover:bg-gray-50/50 transition-colors group <?= $isBlocked ? 'bg-red-50/30' : '' ?>">
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" value="<?= esc((string) $s['id_siswa']) ?>" onchange="toggleSiswa(this)"
                                       class="chk-siswa w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>

                            <!-- Identitas Siswa -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full <?= $isBlocked ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-gray-200' ?> text-blue-600 flex items-center justify-center font-bold text-xs shadow-inner overflow-hidden border shrink-0">
                                        <?php if (!empty($s['foto_profil'])): ?>
                                            <img src="<?= base_url('uploads/siswa/' . (string) $s['foto_profil']) ?>" alt="Foto" loading="lazy" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?= esc(mb_strtoupper(mb_substr((string) ($s['nama_siswa'] ?? ''), 0, 1))) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                            <?= esc((string) $s['nama_siswa']) ?>
                                            <?php if ($isBlocked): ?>
                                                <span class="text-[9px] font-black text-white bg-red-500 px-1.5 py-0.5 rounded-md tracking-wider">BLOCKED</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-[11px] text-gray-500 font-medium mt-1">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded inline-block"><?= esc((string) $s['nis']) ?> • <?= esc((string) ($s['nama_kelas'] ?? 'Belum ada kelas')) ?></span>
                                            <?php if (!empty($s['nama_zona'])): ?>
                                                <span class="bg-amber-50 text-amber-600 border border-amber-100 px-2 py-0.5 rounded inline-block ml-1">📍 PKL: <?= esc((string) $s['nama_zona']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status HP -->
                            <td class="px-6 py-4">
                                <?php if (!empty($s['device_id'])): ?>
                                    <div class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-2 py-1.5 rounded-lg text-[10px] font-bold w-fit border border-emerald-100">
                                        <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>TERIKAT
                                    </div>
                                <?php else: ?>
                                    <div class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-1.5 rounded-lg w-fit border border-gray-200">BELUM TERIKAT</div>
                                <?php endif; ?>
                            </td>

                            <!-- FITUR 4: Kolom Keamanan dengan badge TERBLOKIR terpisah -->
                            <td class="px-6 py-4 text-center">
                                <?php if ($isBlocked): ?>
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-[10px] font-black text-white bg-red-500 px-2 py-0.5 rounded-lg tracking-wide">TERBLOKIR</span>
                                        <span class="text-[9px] text-red-400 font-bold"><?= $fraudCount ?>/3 Fraud</span>
                                    </div>
                                <?php else: ?>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] font-bold <?= $fraudCount >= 2 ? 'text-orange-500' : 'text-gray-600' ?>">
                                            <?= $fraudCount ?>/3 Fraud
                                        </span>
                                        <div class="w-16 h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                            <div class="h-full rounded-full <?= $fraudCount >= 3 ? 'bg-red-500' : ($fraudCount >= 2 ? 'bg-orange-400' : 'bg-blue-400') ?>"
                                                 style="width: <?= ($fraudCount / 3) * 100 ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi: 3 Primary + ⋮ Dropdown -->
                            <td class="px-4 py-4 text-right">
                                <div class="flex justify-end items-center gap-1">

                                    <!-- PRIMARY 1: Profil 360 -->
                                    <a href="<?= base_url('admin/siswa/detail/' . (string) $s['id_siswa']) ?>"
                                       class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-100 rounded-lg transition-all"
                                       title="Profil 360°">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>

                                    <!-- PRIMARY 2: Edit -->
                                    <button onclick='openEditModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8") ?>)'
                                            class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white border border-blue-100 rounded-lg transition-all"
                                            title="Edit Data Siswa">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- PRIMARY 3: Hapus -->
                                    <form action="<?= base_url('admin/siswa/delete/' . (string) $s['id_siswa']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="button"
                                                class="btn-confirm p-2 text-slate-500 bg-slate-50 hover:bg-red-600 hover:text-white border border-slate-200 rounded-lg transition-all"
                                                data-text="Data siswa &quot;<?= esc((string) $s['nama_siswa']) ?>&quot; beserta foto akan dihapus permanen. Lanjutkan?"
                                                data-btn="Ya, Hapus Permanen"
                                                title="Hapus Siswa">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- SECONDARY: ⋮ Dropdown (Blokir/Unblokir + Reset HP) -->
                                    <div class="relative">
                                        <button type="button"
                                                onclick="toggleActionMenu(event, 'amenu-<?= (string) $s['id_siswa'] ?>')"
                                                class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 border border-transparent hover:border-gray-200 rounded-lg transition-all"
                                                title="Aksi lainnya">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 5a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/>
                                            </svg>
                                        </button>

                                        <!-- Dropdown panel -->
                                        <div id="amenu-<?= (string) $s['id_siswa'] ?>"
                                             class="action-menu hidden absolute right-0 bottom-full mb-1.5 bg-white border border-gray-100 rounded-xl shadow-xl py-1.5 w-48 z-30">

                                            <!-- Blokir / Unblokir -->
                                            <?php if ($isBlocked): ?>
                                                <form action="<?= base_url('admin/siswa/unblock/' . (string) $s['id_siswa']) ?>" method="POST">
                                                    <?= csrf_field() ?>
                                                    <button type="button"
                                                            class="btn-confirm w-full text-left px-3.5 py-2.5 text-sm flex items-center gap-2.5 hover:bg-emerald-50 text-emerald-700 rounded-lg mx-auto transition-colors"
                                                            data-text="Buka blokir akun &quot;<?= esc((string) $s['nama_siswa']) ?>&quot;? Fraud count akan di-reset ke 0."
                                                            data-btn="Ya, Unblokir">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                        </svg>
                                                        <span class="font-semibold">Unblokir Siswa</span>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form action="<?= base_url('admin/siswa/block/' . (string) $s['id_siswa']) ?>" method="POST">
                                                    <?= csrf_field() ?>
                                                    <button type="button"
                                                            class="btn-confirm w-full text-left px-3.5 py-2.5 text-sm flex items-center gap-2.5 hover:bg-red-50 text-red-600 rounded-lg transition-colors"
                                                            data-text="Blokir akses login & absensi siswa &quot;<?= esc((string) $s['nama_siswa']) ?>&quot;?"
                                                            data-btn="Ya, Blokir">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                        </svg>
                                                        <span class="font-semibold">Blokir Siswa</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Reset HP -->
                                            <?php if (!empty($s['device_id'])): ?>
                                                <div class="border-t border-gray-50 my-1"></div>
                                                <form action="<?= base_url('admin/siswa/resetDevice/' . (string) $s['id_siswa']) ?>" method="POST">
                                                    <?= csrf_field() ?>
                                                    <button type="button"
                                                            class="btn-confirm w-full text-left px-3.5 py-2.5 text-sm flex items-center gap-2.5 hover:bg-amber-50 text-amber-600 rounded-lg transition-colors"
                                                            data-text="Ikatan perangkat (HP) siswa ini akan dilepas/direset. Lanjutkan?"
                                                            data-btn="Ya, Reset HP">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                                        </svg>
                                                        <span class="font-semibold">Reset HP</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-14 text-center text-gray-400 italic">
                            <i class="fas fa-user-graduate text-3xl text-gray-200 mb-3 block"></i>
                            <?= (!empty($search_aktif) || !empty($kelas_aktif)) ? 'Tidak ada siswa yang cocok dengan filter.' : 'Belum ada data siswa.' ?>
                        </td>
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

<!-- ===== BULK ACTION FLOATING BAR ===== -->
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

<!-- ===== MODAL EDIT ===== -->
<div id="modal-edit" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-lg p-8 relative max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Edit Data Siswa</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="form-edit-action" method="POST" enctype="multipart/form-data" class="space-y-5">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">NIS</label>
                <input type="text" id="edit-nis" name="nis" required
                       class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                <p id="edit-pwd-hint" class="text-[10px] text-amber-500 mt-1.5 ml-1 font-bold hidden">
                    <svg class="w-3 h-3 inline-block mr-0.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    NIS berubah! Password siswa otomatis direset ke NIS baru.
                </p>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Lengkap</label>
                <input type="text" id="edit-nama" name="nama_siswa" required
                       class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Kelas</label>
                <?php if ($is_wali_kelas): ?>
                    <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>"
                           class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none" readonly>
                    <input type="hidden" id="edit-kelas" name="kelas_id" value="<?= esc((string) session()->get('kelas_id')) ?>">
                <?php else: ?>
                    <select id="edit-kelas" name="kelas_id" required
                            class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                        <?php foreach ($list_kelas as $k): ?>
                            <option value="<?= (string) $k['id_kelas'] ?>"><?= esc((string) $k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Penempatan PKL</label>
                <select id="edit-zona" name="zona_id"
                        class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-amber-500 transition-all cursor-pointer text-gray-700">
                    <option value="">🗺️ Mengikuti Zona Sekolah / Kelas</option>
                    <?php foreach ($list_zona as $z): ?>
                        <option value="<?= (string) $z['id_zona'] ?>">📍 <?= esc((string) $z['nama_zona']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Ganti Foto <span class="text-gray-400 font-normal">(Kosongkan jika tidak mengubah)</span></label>
                <input type="file" id="edit-foto" name="foto" accept="image/*"
                       class="w-full border border-gray-200 rounded-xl p-2 bg-gray-50 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-400 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btn-submit-edit"
                        class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 btn-submit transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL IMPORT ===== -->
<div id="modal-import" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeImportModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-md p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Import Data</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors">
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
                <p class="text-[10px] text-amber-600 mt-1 text-center font-semibold italic">*Siswa magang/PKL harap diatur manual setelah proses impor selesai.</p>
                <?php if ($is_wali_kelas): ?>
                    <p class="text-[11px] text-red-600 leading-relaxed font-bold text-center mt-2">Anda hanya dapat mengimpor data khusus untuk Kelas <?= esc((string) session()->get('nama_kelas')) ?>.</p>
                <?php endif; ?>
                <a href="<?= base_url('admin/siswa/downloadTemplate') ?>"
                   class="block text-center text-amber-900 font-bold text-xs mt-3 border border-amber-200 bg-amber-100 py-2 rounded-lg hover:bg-amber-200 transition-colors">Unduh Template Excel</a>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 btn-submit transition-all">Mulai Import</button>
        </form>
    </div>
</div>

<!-- ===== LOADING OVERLAY (Import) ===== -->
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
    // File import name preview
    document.getElementById('file_excel').addEventListener('change', function(e) {
        document.getElementById('file-name-preview').textContent = e.target.files[0] ? e.target.files[0].name : 'Klik pilih file Excel (.xlsx)';
    });

    // ================================================================
    // FITUR 6: SEARCH DEBOUNCE AUTO-SUBMIT (1 detik)
    // ================================================================
    const searchInput = document.getElementById('search-input');
    let searchTimer;
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => this.form.submit(), 1000);
        });
    }

    // ================================================================
    // FITUR 9: SMOOTH ANIMATED FORM TAMBAH
    // ================================================================
    let formTambahIsOpen = false;

    function toggleFormTambah() {
        formTambahIsOpen = !formTambahIsOpen;
        const wrapper  = document.getElementById('form-tambah-wrapper');
        const label    = document.getElementById('btn-label-tambah');
        const iconPlus = document.getElementById('btn-icon-plus');

        if (formTambahIsOpen) {
            wrapper.classList.add('open');
            label.textContent         = 'Tutup';
            iconPlus.style.transform  = 'rotate(45deg)';
        } else {
            wrapper.classList.remove('open');
            label.textContent         = 'Tambah';
            iconPlus.style.transform  = 'rotate(0deg)';
        }
    }

    // Auto-buka form jika ada error (withInput redirect)
    <?php if (session()->getFlashdata('error') && old('nama_siswa')): ?>
        toggleFormTambah();
    <?php endif; ?>

    // ================================================================
    // FITUR 7: FOTO PREVIEW DI FORM TAMBAH
    // ================================================================
    function previewTambahFoto(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            const img  = document.getElementById('foto-preview-img');
            const icon = document.getElementById('foto-preview-icon');
            img.src    = e.target.result;
            img.classList.remove('hidden');
            icon.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    // ================================================================
    // MODAL EDIT
    // ================================================================
    let initialEditState = {};

    function openEditModal(data) {
        document.getElementById('edit-nis').value  = data.nis;
        document.getElementById('edit-nama').value = data.nama_siswa;

        const editKelas = document.getElementById('edit-kelas');
        if (editKelas) editKelas.value = data.kelas_id;

        const editZona = document.getElementById('edit-zona');
        if (editZona) editZona.value = data.zona_id || '';

        document.getElementById('edit-foto').value = '';
        document.getElementById('form-edit-action').action = '<?= base_url("admin/siswa/update/") ?>' + data.id_siswa;

        initialEditState = {
            nis  : data.nis,
            nama : data.nama_siswa,
            kelas: editKelas ? data.kelas_id : '',
            zona : editZona ? (data.zona_id || '') : '',
            foto : ''
        };

        document.getElementById('edit-pwd-hint').classList.add('hidden');
        document.getElementById('btn-submit-edit').disabled = true;

        document.getElementById('modal-edit').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function checkEditChanges() {
        const currentNis  = document.getElementById('edit-nis').value;
        const currentNama = document.getElementById('edit-nama').value;
        const editKelas   = document.getElementById('edit-kelas');
        const currentKelas = editKelas ? editKelas.value : '';
        const editZona    = document.getElementById('edit-zona');
        const currentZona = editZona ? editZona.value : '';
        const currentFoto = document.getElementById('edit-foto').value;

        if (currentNis !== initialEditState.nis && currentNis !== '') {
            document.getElementById('edit-pwd-hint').classList.remove('hidden');
        } else {
            document.getElementById('edit-pwd-hint').classList.add('hidden');
        }

        const isChanged = (
            currentNis  !== initialEditState.nis  ||
            currentNama !== initialEditState.nama  ||
            (editKelas && currentKelas != initialEditState.kelas) ||
            (editZona  && currentZona  != initialEditState.zona)  ||
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
    const editZonaInput = document.getElementById('edit-zona');
    if (editZonaInput) editZonaInput.addEventListener('change', checkEditChanges);
    document.getElementById('edit-foto').addEventListener('change', checkEditChanges);

    function closeEditModal() {
        document.getElementById('modal-edit').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    // ================================================================
    // MODAL IMPORT
    // ================================================================
    function openImportModal() {
        document.getElementById('formImport').reset();
        document.getElementById('file-name-preview').textContent = 'Klik pilih file Excel (.xlsx)';
        document.getElementById('modal-import').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function closeImportModal() {
        document.getElementById('modal-import').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    // ================================================================
    // FITUR 8: ESC KEY SUPPORT
    // ================================================================
    // ================================================================
    // DROPDOWN ⋮ AKSI SEKUNDER (Blokir/Unblokir + Reset HP)
    // ================================================================
    function toggleActionMenu(event, menuId) {
        event.stopPropagation();
        const menu   = document.getElementById(menuId);
        const isOpen = !menu.classList.contains('hidden');

        // Tutup semua dropdown lain
        document.querySelectorAll('.action-menu').forEach(m => m.classList.add('hidden'));

        // Toggle target
        if (!isOpen) {
            menu.classList.remove('hidden');

            // Cegah terpotong di bawah tabel: hitung apakah perlu flip ke atas atau bawah
            const rect = menu.getBoundingClientRect();
            if (rect.top < 0) {
                menu.classList.remove('bottom-full', 'mb-1.5');
                menu.classList.add('top-full', 'mt-1.5');
            }
        }
    }

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function () {
        document.querySelectorAll('.action-menu').forEach(m => m.classList.add('hidden'));
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;

        // 1. Tutup dropdown ⋮ yang terbuka
        const openMenus = document.querySelectorAll('.action-menu:not(.hidden)');
        if (openMenus.length > 0) {
            openMenus.forEach(m => m.classList.add('hidden'));
            return;
        }
        if (!document.getElementById('modal-edit').classList.contains('hidden')) {
            closeEditModal();
        } else if (!document.getElementById('modal-import').classList.contains('hidden')) {
            closeImportModal();
        } else if (formTambahIsOpen) {
            toggleFormTambah();
        }
    });

    // ================================================================
    // BULK SELECT (tidak diubah)
    // ================================================================
    let selectedSiswa = JSON.parse(sessionStorage.getItem('selectedSiswa')) || [];

    function updateCheckboxesState() {
        let allChecked     = true;
        let hasSiswaOnPage = false;

        document.querySelectorAll('.chk-siswa').forEach(chk => {
            hasSiswaOnPage = true;
            if (selectedSiswa.includes(chk.value)) {
                chk.checked  = true;
            } else {
                chk.checked  = false;
                allChecked   = false;
            }
        });

        const chkAll = document.getElementById('chk-all');
        if (chkAll) chkAll.checked = hasSiswaOnPage && allChecked;

        updateBulkActionBar();
    }

    function toggleSiswa(cb) {
        if (cb.checked) {
            if (!selectedSiswa.includes(cb.value)) selectedSiswa.push(cb.value);
        } else {
            selectedSiswa = selectedSiswa.filter(id => id !== cb.value);
        }
        saveSelection();
    }

    function toggleAllSiswa(cb) {
        document.querySelectorAll('.chk-siswa').forEach(chk => {
            chk.checked = cb.checked;
            if (cb.checked) {
                if (!selectedSiswa.includes(chk.value)) selectedSiswa.push(chk.value);
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
        const bar   = document.getElementById('bulk-action-bar');
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
                input.type  = 'hidden';
                input.name  = 'ids[]';
                input.value = id;
                container.appendChild(input);
            });
            sessionStorage.removeItem('selectedSiswa');
            document.getElementById('form-bulk-delete').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', () => updateCheckboxesState());

    // Form submit: loading state
    document.querySelectorAll('#formSiswa, #form-edit-action, #formImport').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (this.id === 'formImport') {
                closeImportModal();
                const overlay = document.getElementById('loading-overlay');
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                setTimeout(() => overlay.classList.remove('opacity-0'), 20);
            } else {
                const btn = this.querySelector('.btn-submit');
                if (btn && !btn.disabled) {
                    btn.classList.add('btn-loading');
                    btn.setAttribute('disabled', 'true');
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>