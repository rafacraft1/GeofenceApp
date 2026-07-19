<?php

/**
 * @var string $title
 * @var string $tanggal
 * @var string|int|null $kelas_aktif
 * @var string|null $search_aktif
 * @var string $sort_col
 * @var string $sort_dir
 * @var array<int, array<string, string|null>> $absensi
 * @var array<int, array<string, string|null>> $siswa
 * @var array<int, array<string, string|null>> $list_kelas
 * @var array<string, int> $summary
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 * @var string $pager_links
 */

$buildSortLink = function ($column) use ($tanggal, $kelas_aktif, $search_aktif, $sort_col, $sort_dir) {
    $newDir = ($sort_col === $column && $sort_dir === 'asc') ? 'desc' : 'asc';
    $url = base_url('admin/absensi') . "?sort={$column}-{$newDir}&tanggal={$tanggal}";
    if ($search_aktif) $url .= "&search=" . urlencode($search_aktif);
    if ($kelas_aktif) $url .= "&kelas_id=" . urlencode((string) $kelas_aktif);
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
</style>

<!-- ===== PAGE HEADER ===== -->
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Data Absensi Harian</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau kehadiran, keterlambatan, dan input absen manual.</p>
    </div>
</div>

<!-- ===== SUMMARY STAT CARDS (FITUR 1) ===== -->
<?php
$summaryItems = [
    ['label' => 'Hadir',      'value' => $summary['hadir'],      'color' => 'emerald', 'icon' => 'fa-check-circle'],
    ['label' => 'Terlambat',  'value' => $summary['terlambat'],  'color' => 'amber',   'icon' => 'fa-clock'],
    ['label' => 'Dispensasi', 'value' => $summary['dispensasi'], 'color' => 'teal',    'icon' => 'fa-stamp'],
    ['label' => 'Sakit',      'value' => $summary['sakit'],      'color' => 'blue',    'icon' => 'fa-heartbeat'],
    ['label' => 'Izin',       'value' => $summary['izin'],       'color' => 'indigo',  'icon' => 'fa-file-alt'],
    ['label' => 'Alpa',       'value' => $summary['alpa'],       'color' => 'red',     'icon' => 'fa-times-circle'],
];
$colorMap = [
    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'text-emerald-500', 'border' => 'border-emerald-100'],
    'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'label' => 'text-amber-500',   'border' => 'border-amber-100'],
    'teal'    => ['bg' => 'bg-teal-50',    'text' => 'text-teal-600',    'label' => 'text-teal-500',    'border' => 'border-teal-100'],
    'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600',    'label' => 'text-blue-500',    'border' => 'border-blue-100'],
    'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600',  'label' => 'text-indigo-500',  'border' => 'border-indigo-100'],
    'red'     => ['bg' => 'bg-red-50',     'text' => 'text-red-600',     'label' => 'text-red-500',     'border' => 'border-red-100'],
];
?>
<div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-6">
    <?php foreach ($summaryItems as $item):
        $c = $colorMap[$item['color']]; ?>
    <div class="bg-white rounded-xl p-4 border <?= $c['border'] ?> shadow-sm text-center transition-all hover:-translate-y-0.5 hover:shadow-md group">
        <div class="w-8 h-8 rounded-full <?= $c['bg'] ?> <?= $c['text'] ?> flex items-center justify-center mx-auto mb-2 transition-transform group-hover:scale-110">
            <i class="fas <?= $item['icon'] ?> text-sm"></i>
        </div>
        <p class="text-xl font-black text-gray-800"><?= $item['value'] ?></p>
        <p class="text-[10px] font-bold <?= $c['label'] ?> uppercase tracking-wider mt-0.5"><?= $item['label'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<!-- ===== FILTER BAR ===== -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <form action="<?= base_url('admin/absensi') ?>" method="GET" class="flex flex-col md:flex-row w-full md:flex-1 max-w-3xl gap-3 items-center">
        <input type="date" name="tanggal" value="<?= esc((string) $tanggal) ?>" class="w-full md:w-auto border border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer bg-gray-50 font-medium text-gray-600" onchange="this.form.submit()">

        <?php if (session()->get('is_wali_kelas')): ?>
            <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full md:w-48 border border-gray-200 rounded-xl p-2.5 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none font-medium" readonly>
            <input type="hidden" name="kelas_id" value="<?= session()->get('kelas_id') ?>">
        <?php else: ?>
            <select name="kelas_id" class="w-full md:w-48 border border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer font-medium text-gray-600" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                <?php foreach ($list_kelas as $k): ?>
                    <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelas_aktif === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                        <?= esc((string) $k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" name="search" id="search-input" value="<?= esc((string)($search_aktif ?? '')) ?>" placeholder="Cari Siswa..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-10 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
        <button type="submit" class="hidden">Submit</button>
    </form>

    <button onclick="openManualModal()" class="w-full md:w-auto flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md transition-all active:scale-95 whitespace-nowrap">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
        Input Manual
    </button>
</div>

<!-- ===== DATA TABLE ===== -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                    <th class="px-6 py-4">No</th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortLink('nama_siswa') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600">
                            Identitas Siswa <?= $getSortIcon('nama_siswa') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortLink('jam_masuk') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600">
                            Waktu Presensi <?= $getSortIcon('jam_masuk') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($absensi)) : ?>
                    <?php
                    $safePage    = max(1, (int)($page ?? 1));
                    $safePerPage = max(1, (int)($perPage ?? 20));
                    $no = (($safePage - 1) * $safePerPage) + 1;
                    foreach ($absensi as $ab): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>

                            <td class="px-6 py-4">
                                <button onclick='openDetailModal(<?= htmlspecialchars(json_encode($ab), ENT_QUOTES, "UTF-8") ?>)' class="text-sm font-bold text-blue-700 hover:text-blue-500 hover:underline transition-colors focus:outline-none flex items-center gap-1.5 text-left group">
                                    <?= esc((string) $ab['nama_siswa']) ?>
                                    <svg class="w-3.5 h-3.5 text-blue-400 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                </button>
                                <div class="text-[11px] text-gray-500 font-medium mt-1">
                                    <?= esc((string) $ab['nis']) ?> • <?= esc((string) ($ab['nama_kelas'] ?? '-')) ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm font-mono text-gray-700 font-semibold">
                                    Masuk: <?= esc((string) ($ab['jam_masuk'] ?? '--:--:--')) ?>
                                </div>
                                <div class="text-xs font-mono text-gray-500 mt-1">
                                    Pulang: <?= esc((string) ($ab['jam_pulang'] ?? '--:--:--')) ?>
                                </div>
                                <?php /* FITUR 4: Tampilkan menit telat di tabel */
                                if ((string)($ab['status'] ?? '') === 'Terlambat' && !empty($ab['menit_telat']) && (int)$ab['menit_telat'] > 0): ?>
                                    <div class="mt-1.5 text-[10px] font-black text-amber-600 bg-amber-50 border border-amber-200 rounded-md px-2 py-0.5 inline-flex items-center gap-1">
                                        <i class="fas fa-clock text-[9px]"></i>
                                        Telat <?= (int)$ab['menit_telat'] ?> menit
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <?php
                                $status = (string) $ab['status'];
                                $badgeColor = match ($status) {
                                    'Hadir'               => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'Terlambat'           => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Dispensasi'          => 'bg-teal-100 text-teal-700 border-teal-200',
                                    'Sakit', 'Izin'       => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'Alpa', 'Manipulasi'  => 'bg-red-100 text-red-700 border-red-200',
                                    default               => 'bg-gray-100 text-gray-700 border-gray-200'
                                };
                                ?>
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold border uppercase tracking-wide <?= $badgeColor ?>">
                                    <?= esc($status) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500"><?= esc((string) ($ab['keterangan'] ?? '-')) ?></span>
                                <?php if (!empty($ab['is_fake_gps'])): ?>
                                    <div class="mt-1 text-[10px] font-bold text-red-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        Fake GPS
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-16 text-center text-gray-400 italic">
                            <i class="fas fa-calendar-times text-4xl text-gray-200 mb-3 block"></i>
                            Belum ada data absensi untuk tanggal ini.
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
            $safePerPage = max(1, (int)($perPage ?? 20));
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

<!-- ===== LIGHTBOX (FITUR 6) ===== -->
<div id="lightbox" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/95 cursor-zoom-out" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 text-white bg-white/10 rounded-full p-2.5 hover:bg-white/20 transition-colors z-10" onclick="closeLightbox()">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
    <p class="text-white/40 text-xs font-bold absolute bottom-5 left-1/2 -translate-x-1/2 select-none">Klik di mana saja atau tekan ESC untuk menutup</p>
    <img id="lightbox-img" src="" class="max-h-[88vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl cursor-default" alt="Preview Foto" onclick="event.stopPropagation()">
</div>

<!-- ===== MODAL DETAIL ===== -->
<div id="modal-detail" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-2xl p-6 md:p-8 relative flex flex-col max-h-[90vh]">

        <!-- Header Modal -->
        <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100">
            <div class="flex-1 min-w-0 mr-3">
                <h3 class="text-xl font-black text-gray-800 truncate" id="dtl-nama">-</h3>
                <p class="text-xs text-gray-500 font-bold mt-1 bg-gray-100 px-2 py-0.5 rounded inline-block" id="dtl-nis-kelas">-</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <!-- FITUR 5: Tombol Lacak Siswa -->
                <a id="dtl-tracking-btn" href="#" target="_blank"
                   class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-black border border-indigo-200 transition-colors whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    Lacak Siswa
                </a>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- FITUR 4: Badge Menit Telat (conditional) -->
        <div id="dtl-telat-wrapper" class="hidden mb-4">
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 flex items-center gap-2">
                <i class="fas fa-clock text-amber-500"></i>
                <span class="text-sm font-black text-amber-700">
                    Terlambat <span id="dtl-menit-telat" class="underline decoration-dotted">-</span> menit dari jadwal masuk
                </span>
            </div>
        </div>

        <div class="overflow-y-auto scrollbar-hide flex-1 pb-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Card Masuk -->
                <div class="bg-blue-50/40 rounded-2xl p-4 md:p-5 border border-blue-100 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-black text-blue-900 uppercase tracking-wider">Masuk</h4>
                        </div>
                        <span class="text-xs font-mono bg-blue-600 text-white px-2 py-1 rounded-md font-bold shadow-sm" id="dtl-jam-masuk">--:--:--</span>
                    </div>

                    <!-- FITUR 6: Foto bisa diklik untuk lightbox -->
                    <div class="w-full h-40 md:h-48 rounded-xl bg-gray-200 border-4 border-white shadow-sm overflow-hidden mb-4 flex flex-col items-center justify-center relative cursor-pointer group"
                         onclick="triggerLightboxFromModal('masuk')" title="Klik untuk memperbesar">
                        <img id="dtl-foto-masuk" src="" class="w-full h-full object-cover hidden transition-opacity group-hover:opacity-80" alt="Foto Masuk">
                        <div id="dtl-nofoto-masuk" class="text-center flex flex-col items-center">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                            </svg>
                            <span class="text-xs font-bold text-gray-400">Tidak ada foto</span>
                        </div>
                        <!-- Zoom overlay hint -->
                        <div id="dtl-zoom-masuk" class="hidden absolute inset-0 bg-black/20 items-center justify-center rounded-xl pointer-events-none">
                            <div class="bg-white/80 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto bg-white rounded-xl p-3 border border-blue-100 shadow-sm">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase mb-1">Koordinat Masuk</span>
                        <a id="dtl-lokasi-masuk" href="#" target="_blank" class="text-blue-600 font-bold hover:underline flex items-center gap-1 text-xs">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span id="dtl-latlong-masuk" class="truncate">Tidak tercatat</span>
                        </a>
                    </div>
                </div>

                <!-- Card Pulang -->
                <div class="bg-emerald-50/40 rounded-2xl p-4 md:p-5 border border-emerald-100 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021.75 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-black text-emerald-900 uppercase tracking-wider">Pulang</h4>
                        </div>
                        <span class="text-xs font-mono bg-emerald-600 text-white px-2 py-1 rounded-md font-bold shadow-sm" id="dtl-jam-pulang">--:--:--</span>
                    </div>

                    <div class="w-full h-40 md:h-48 rounded-xl bg-gray-200 border-4 border-white shadow-sm overflow-hidden mb-4 flex flex-col items-center justify-center relative cursor-pointer group"
                         onclick="triggerLightboxFromModal('pulang')" title="Klik untuk memperbesar">
                        <img id="dtl-foto-pulang" src="" class="w-full h-full object-cover hidden transition-opacity group-hover:opacity-80" alt="Foto Pulang">
                        <div id="dtl-nofoto-pulang" class="text-center flex flex-col items-center">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                            </svg>
                            <span class="text-xs font-bold text-gray-400">Tidak ada foto</span>
                        </div>
                        <div id="dtl-zoom-pulang" class="hidden absolute inset-0 bg-black/20 items-center justify-center rounded-xl pointer-events-none">
                            <div class="bg-white/80 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto bg-white rounded-xl p-3 border border-emerald-100 shadow-sm">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase mb-1">Koordinat Pulang</span>
                        <a id="dtl-lokasi-pulang" href="#" target="_blank" class="text-emerald-600 font-bold hover:underline flex items-center gap-1 text-xs">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span id="dtl-latlong-pulang" class="truncate">Tidak tercatat</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL INPUT MANUAL ===== -->
<div id="modal-manual" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeManualModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-md p-6 md:p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Input Manual</h3>
            <button onclick="closeManualModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="<?= base_url('admin/absensi/inputManual') ?>" method="POST" id="form-manual" class="space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="tanggal" value="<?= esc((string) $tanggal) ?>">

            <!-- FITUR 2: Tom Select searchable dropdown -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Siswa</label>
                <select name="siswa_id" id="siswa-select" required>
                    <option value="" disabled selected>-- Cari nama atau NIS siswa --</option>
                    <?php foreach ($siswa as $s): ?>
                        <option value="<?= esc((string) $s['id_siswa']) ?>">
                            <?= esc((string) $s['nama_siswa']) ?> (<?= esc((string) ($s['nama_kelas'] ?? '-')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- FITUR 3: Tambah Dispensasi ke opsi status -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Status Kehadiran</label>
                <select name="status" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer text-gray-700 font-medium">
                    <option value="Hadir">Hadir</option>
                    <option value="Dispensasi">Dispensasi</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Catatan tambahan...">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeManualModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-400 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 btn-submit transition-all">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Tom Select CDN (FITUR 2) -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
    /* Tom Select Custom Styles */
    .ts-wrapper .ts-control {
        border-radius: 0.75rem !important;
        padding: 0.75rem !important;
        font-size: 0.875rem !important;
        border-color: #e5e7eb !important;
        background-color: #f9fafb !important;
        font-weight: 500;
        min-height: unset !important;
        box-shadow: none !important;
    }
    .ts-wrapper.focus .ts-control {
        box-shadow: 0 0 0 2px #3b82f6 !important;
        border-color: #3b82f6 !important;
        background-color: #fff !important;
    }
    .ts-dropdown {
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
        border: 1px solid #e5e7eb !important;
        font-size: 0.875rem !important;
        margin-top: 4px !important;
    }
    .ts-dropdown .option {
        padding: 0.625rem 1rem !important;
        font-weight: 500 !important;
        color: #374151;
    }
    .ts-dropdown .option.active,
    .ts-dropdown .option:hover { background-color: #eff6ff !important; color: #1d4ed8 !important; }
    .ts-dropdown-content { max-height: 220px !important; }
</style>

<script>
    // ================================================================
    // CONSTANTS
    // ================================================================
    const baseUploadUrl   = '<?= base_url("uploads/absensi/") ?>';
    const baseTrackingUrl = '<?= base_url("admin/tracking/") ?>';

    // ================================================================
    // TOM SELECT — Searchable Dropdown Siswa (FITUR 2)
    // ================================================================
    let tomSelectSiswa = null;

    document.addEventListener('DOMContentLoaded', function () {
        tomSelectSiswa = new TomSelect('#siswa-select', {
            create: false,
            maxOptions: 300,
            placeholder: '-- Cari nama atau NIS siswa --',
            searchField: ['text'],
            sortField: { field: 'text', direction: 'asc' },
            plugins: ['remove_button']
        });

        // Auto-focus search setelah reload
        const searchInput = document.getElementById('search-input');
        if (searchInput && searchInput.value.length > 0) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }

        // Debounce auto-submit search
        let searchTimer;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => this.form.submit(), 1000);
            });
        }
    });

    // ================================================================
    // MODAL MANUAL
    // ================================================================
    function openManualModal() {
        document.getElementById('modal-manual').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
        if (tomSelectSiswa) setTimeout(() => tomSelectSiswa.open(), 150);
    }

    function closeManualModal() {
        document.getElementById('modal-manual').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
        if (tomSelectSiswa) { tomSelectSiswa.clear(); tomSelectSiswa.close(); }
    }

    // Form submit loading state
    document.getElementById('form-manual').addEventListener('submit', function () {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>Menyimpan...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.setAttribute('disabled', 'true');
        }
    });

    // ================================================================
    // LIGHTBOX (FITUR 6)
    // ================================================================
    function openLightbox(src) {
        if (!src) return;
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.replace('hidden', 'flex');
        // Jangan toggle modal-active karena modal-detail masih buka
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.replace('flex', 'hidden');
    }

    function triggerLightboxFromModal(type) {
        const img = document.getElementById('dtl-foto-' + type);
        if (img && !img.classList.contains('hidden') && img.src && !img.src.endsWith('/')) {
            openLightbox(img.src);
        }
    }

    // ================================================================
    // MODAL DETAIL
    // ================================================================
    function openDetailModal(data) {
        // Header
        document.getElementById('dtl-nama').textContent      = data.nama_siswa || '-';
        document.getElementById('dtl-nis-kelas').textContent = `${data.nis || '-'} • ${data.nama_kelas || '-'}`;

        // FITUR 5: Tracking link
        const trackingBtn = document.getElementById('dtl-tracking-btn');
        if (trackingBtn) {
            const siswaId = data.siswa_id || '';
            trackingBtn.href = siswaId ? baseTrackingUrl + siswaId : '#';
        }

        // FITUR 4: Menit Telat
        const telatWrapper = document.getElementById('dtl-telat-wrapper');
        if (data.status === 'Terlambat' && data.menit_telat && parseInt(data.menit_telat) > 0) {
            document.getElementById('dtl-menit-telat').textContent = data.menit_telat;
            telatWrapper.classList.remove('hidden');
        } else {
            telatWrapper.classList.add('hidden');
        }

        // Jam & Foto Masuk
        document.getElementById('dtl-jam-masuk').textContent = data.jam_masuk || '--:--:--';
        const imgMasuk   = document.getElementById('dtl-foto-masuk');
        const noMasuk    = document.getElementById('dtl-nofoto-masuk');
        const zoomMasuk  = document.getElementById('dtl-zoom-masuk');
        if (data.foto_masuk) {
            imgMasuk.src = baseUploadUrl + data.foto_masuk;
            imgMasuk.classList.remove('hidden');
            noMasuk.classList.add('hidden');
            zoomMasuk.classList.replace('hidden', 'flex');
        } else {
            imgMasuk.src = '';
            imgMasuk.classList.add('hidden');
            noMasuk.classList.remove('hidden');
            zoomMasuk.classList.replace('flex', 'hidden');
        }

        // Koordinat Masuk
        const latLongMasuk = document.getElementById('dtl-latlong-masuk');
        const linkMasuk    = document.getElementById('dtl-lokasi-masuk');
        if (data.lat_masuk && data.long_masuk) {
            latLongMasuk.textContent = `${parseFloat(data.lat_masuk).toFixed(6)}, ${parseFloat(data.long_masuk).toFixed(6)}`;
            linkMasuk.href = `https://maps.google.com/?q=${data.lat_masuk},${data.long_masuk}`;
            linkMasuk.classList.remove('pointer-events-none', 'text-gray-400');
        } else {
            latLongMasuk.textContent = 'Lokasi tidak tercatat';
            linkMasuk.href = '#';
            linkMasuk.classList.add('pointer-events-none', 'text-gray-400');
        }

        // Jam & Foto Pulang
        document.getElementById('dtl-jam-pulang').textContent = data.jam_pulang || '--:--:--';
        const imgPulang  = document.getElementById('dtl-foto-pulang');
        const noPulang   = document.getElementById('dtl-nofoto-pulang');
        const zoomPulang = document.getElementById('dtl-zoom-pulang');
        if (data.foto_pulang) {
            imgPulang.src = baseUploadUrl + data.foto_pulang;
            imgPulang.classList.remove('hidden');
            noPulang.classList.add('hidden');
            zoomPulang.classList.replace('hidden', 'flex');
        } else {
            imgPulang.src = '';
            imgPulang.classList.add('hidden');
            noPulang.classList.remove('hidden');
            zoomPulang.classList.replace('flex', 'hidden');
        }

        // Koordinat Pulang
        const latLongPulang = document.getElementById('dtl-latlong-pulang');
        const linkPulang    = document.getElementById('dtl-lokasi-pulang');
        if (data.lat_pulang && data.long_pulang) {
            latLongPulang.textContent = `${parseFloat(data.lat_pulang).toFixed(6)}, ${parseFloat(data.long_pulang).toFixed(6)}`;
            linkPulang.href = `https://maps.google.com/?q=${data.lat_pulang},${data.long_pulang}`;
            linkPulang.classList.remove('pointer-events-none', 'text-gray-400');
        } else {
            latLongPulang.textContent = 'Lokasi tidak tercatat';
            linkPulang.href = '#';
            linkPulang.classList.add('pointer-events-none', 'text-gray-400');
        }

        document.getElementById('modal-detail').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function closeDetailModal() {
        document.getElementById('modal-detail').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    // ================================================================
    // ESC KEY — Tutup lightbox → modal detail → modal manual
    // ================================================================
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        const lightbox     = document.getElementById('lightbox');
        const modalDetail  = document.getElementById('modal-detail');
        const modalManual  = document.getElementById('modal-manual');

        if (!lightbox.classList.contains('hidden')) {
            closeLightbox();
        } else if (!modalDetail.classList.contains('hidden')) {
            closeDetailModal();
        } else if (!modalManual.classList.contains('hidden')) {
            closeManualModal();
        }
    });
</script>
<?= $this->endSection() ?>