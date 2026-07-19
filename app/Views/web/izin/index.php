<?php

/**
 * @var array<int, array<string, mixed>> $daftarIzin
 * @var array<string, int> $counts
 * @var string|null $search_aktif
 * @var string|null $status_aktif
 * @var string|null $date_from
 * @var string|null $date_to
 * @var string $sort_col
 * @var string $sort_dir
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 * @var string $pager_links
 */

$buildSortLink = function ($column) use ($search_aktif, $status_aktif, $date_from, $date_to, $sort_col, $sort_dir) {
    $newDir = ($sort_col === $column && $sort_dir === 'asc') ? 'desc' : 'asc';
    $url = base_url('admin/izin') . "?sort={$column}-{$newDir}";
    if ($search_aktif)  $url .= '&search=' . urlencode($search_aktif);
    if ($status_aktif)  $url .= '&status=' . urlencode($status_aktif);
    if ($date_from)     $url .= '&date_from=' . urlencode($date_from);
    if ($date_to)       $url .= '&date_to=' . urlencode($date_to);
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

// Helper: relative timestamp (FITUR 6)
$timeAgo = function (string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Baru saja';
    if ($diff < 3600)   return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400)  return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
};
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    .modal-active { overflow: hidden; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .pill-active   { @apply ring-2 ring-offset-1; }
</style>

<!-- ===== PAGE HEADER ===== -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Pengajuan Izin Siswa</h2>
    <p class="text-sm text-gray-500 mt-1">Review dan kelola pengajuan sakit, izin, atau dispensasi.</p>
</div>

<!-- ===== FITUR 1: COUNTER BADGE CARDS ===== -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <?php
    $counterItems = [
        ['label' => 'Menunggu',  'key' => 'Pending',  'color' => 'amber',   'icon' => 'fa-hourglass-half', 'pill_val' => 'Pending'],
        ['label' => 'Disetujui', 'key' => 'Approved', 'color' => 'emerald', 'icon' => 'fa-check-circle',   'pill_val' => 'Approved'],
        ['label' => 'Ditolak',   'key' => 'Rejected', 'color' => 'red',     'icon' => 'fa-times-circle',   'pill_val' => 'Rejected'],
    ];
    $counterColorMap = [
        'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'border' => 'border-amber-200',   'label' => 'text-amber-500',   'ring' => 'ring-amber-400'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'label' => 'text-emerald-500', 'ring' => 'ring-emerald-400'],
        'red'     => ['bg' => 'bg-red-50',     'text' => 'text-red-600',     'border' => 'border-red-200',     'label' => 'text-red-500',     'ring' => 'ring-red-400'],
    ];
    foreach ($counterItems as $ci):
        $cc = $counterColorMap[$ci['color']];
        $isActive = (string)($status_aktif ?? '') === $ci['pill_val'];
        $filterUrl = base_url('admin/izin') . '?status=' . $ci['pill_val'] . ($search_aktif ? '&search=' . urlencode($search_aktif) : '');
        $clearUrl  = base_url('admin/izin') . ($search_aktif ? '?search=' . urlencode($search_aktif) : '');
    ?>
    <a href="<?= $isActive ? $clearUrl : $filterUrl ?>"
       class="bg-white rounded-xl p-4 border <?= $cc['border'] ?> <?= $isActive ? 'ring-2 ring-offset-1 ' . $cc['ring'] : '' ?> shadow-sm flex items-center gap-4 transition-all hover:-translate-y-0.5 hover:shadow-md group">
        <div class="w-10 h-10 rounded-full <?= $cc['bg'] ?> <?= $cc['text'] ?> flex items-center justify-center shrink-0 transition-transform group-hover:scale-110">
            <i class="fas <?= $ci['icon'] ?> text-base"></i>
        </div>
        <div>
            <p class="text-2xl font-black text-gray-800 leading-none"><?= $counts[$ci['key']] ?></p>
            <p class="text-[11px] font-bold <?= $cc['label'] ?> uppercase tracking-wider mt-0.5"><?= $ci['label'] ?></p>
        </div>
        <?php if ($isActive): ?>
            <div class="ml-auto <?= $cc['text'] ?> text-xs font-black bg-white border <?= $cc['border'] ?> rounded-full px-2 py-0.5">Aktif ×</div>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- ===== FITUR 2 & 8: FILTER BAR (Status Pills + Pencarian + Date Range) ===== -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col gap-4">
    <form action="<?= base_url('admin/izin') ?>" method="GET" id="form-filter" class="flex flex-col md:flex-row gap-3 items-center w-full">
        <!-- Search -->
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" name="search" id="search-input" value="<?= esc((string)($search_aktif ?? '')) ?>"
                   placeholder="Cari Nama Siswa atau Alasan..."
                   class="w-full border border-gray-200 rounded-xl py-2.5 pl-10 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>

        <!-- Date From & To (FITUR 8) -->
        <div class="flex items-center gap-2 shrink-0">
            <input type="date" name="date_from" value="<?= esc((string)($date_from ?? '')) ?>"
                   class="border border-gray-200 rounded-xl p-2.5 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-600 font-medium cursor-pointer"
                   onchange="document.getElementById('form-filter').submit()">
            <span class="text-gray-400 text-xs font-bold">s/d</span>
            <input type="date" name="date_to" value="<?= esc((string)($date_to ?? '')) ?>"
                   class="border border-gray-200 rounded-xl p-2.5 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-600 font-medium cursor-pointer"
                   onchange="document.getElementById('form-filter').submit()">
        </div>

        <!-- Hidden status (dikendalikan dari card) -->
        <?php if ($status_aktif): ?>
            <input type="hidden" name="status" value="<?= esc((string) $status_aktif) ?>">
        <?php endif; ?>

        <?php if ($date_from || $date_to || $search_aktif || $status_aktif): ?>
            <a href="<?= base_url('admin/izin') ?>" class="shrink-0 px-4 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 rounded-xl transition-colors border border-red-100 whitespace-nowrap">
                <i class="fas fa-times mr-1"></i> Reset Filter
            </a>
        <?php endif; ?>

        <button type="submit" class="hidden">Cari</button>
    </form>
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
                            Siswa <?= $getSortIcon('nama_siswa') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortLink('tanggal_mulai') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600">
                            Durasi Izin <?= $getSortIcon('tanggal_mulai') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">Detail Pengajuan</th>
                    <th class="px-6 py-4 text-center">
                        <div class="flex justify-center">
                            <a href="<?= $buildSortLink('status') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600">
                                Status <?= $getSortIcon('status') ?>
                            </a>
                        </div>
                    </th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($daftarIzin)) : ?>
                    <?php
                    $safePage    = max(1, (int)($page ?? 1));
                    $safePerPage = max(1, (int)($perPage ?? 20));
                    $no = (($safePage - 1) * $safePerPage) + 1;

                    foreach ($daftarIzin as $izin):
                        // FITUR 5: Hitung durasi hari
                        $tglMulai   = strtotime((string) $izin['tanggal_mulai']);
                        $tglSelesai = strtotime((string) $izin['tanggal_selesai']);
                        $durasiHari = (int) round(($tglSelesai - $tglMulai) / 86400) + 1;
                    ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>

                            <!-- Kolom Siswa -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $izin['nama_siswa']) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium bg-gray-100 px-2 py-0.5 rounded inline-block mt-1">
                                    <?= esc((string) $izin['nis']) ?> • <?= esc((string) ($izin['nama_kelas'] ?? '-')) ?>
                                </div>
                            </td>

                            <!-- FITUR 5: Kolom Durasi dengan jumlah hari -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-700">
                                    <?= date('d M Y', $tglMulai) ?>
                                </div>
                                <?php if ($izin['tanggal_mulai'] !== $izin['tanggal_selesai']): ?>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        s/d <?= date('d M Y', $tglSelesai) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-1.5 text-[10px] font-black text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-md px-2 py-0.5 inline-flex items-center gap-1">
                                    <i class="fas fa-calendar-day text-[9px]"></i>
                                    <?= $durasiHari ?> hari
                                </div>
                            </td>

                            <!-- Detail Pengajuan -->
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <?php if (!empty($izin['bukti_foto'])): ?>
                                        <!-- FITUR 4: Lightbox foto bukti -->
                                        <img src="<?= base_url('uploads/izin/' . (string) $izin['bukti_foto']) ?>"
                                             onclick="openLightbox('<?= base_url('uploads/izin/' . (string) $izin['bukti_foto']) ?>')"
                                             title="Klik untuk memperbesar bukti"
                                             class="w-10 h-10 rounded-lg object-cover cursor-zoom-in hover:opacity-80 hover:scale-105 transition-all border border-gray-200 shadow-sm shrink-0"
                                             alt="Bukti Izin">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center shrink-0" title="Tidak ada lampiran">
                                            <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>

                                    <div class="min-w-0">
                                        <?php
                                        $jenisColor = match ($izin['jenis']) {
                                            'Sakit'       => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'Izin'        => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'Dispensasi'  => 'bg-purple-100 text-purple-700 border-purple-200',
                                            default       => 'bg-gray-100 text-gray-700 border-gray-200'
                                        };
                                        ?>
                                        <div class="mb-1.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wide <?= $jenisColor ?>">
                                                <?= esc((string) $izin['jenis']) ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 line-clamp-2 max-w-xs" title="<?= esc((string) $izin['alasan']) ?>">
                                            <?= esc((string) $izin['alasan']) ?>
                                        </p>
                                        <?php if (!empty($izin['catatan_penolakan'])): ?>
                                            <div class="mt-1 text-[10px] font-bold text-red-600 bg-red-50 border border-red-200 rounded px-2 py-0.5 max-w-xs">
                                                <i class="fas fa-comment-slash text-[9px] mr-0.5"></i>
                                                <?= esc((string) $izin['catatan_penolakan']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                <?php
                                $statusColor = match ($izin['status']) {
                                    'Pending'  => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'Rejected' => 'bg-red-100 text-red-700 border-red-200',
                                    default    => 'bg-gray-100 text-gray-700'
                                };
                                ?>
                                <span class="inline-block px-3 py-1 rounded-lg text-xs font-bold border <?= $statusColor ?>">
                                    <?= esc((string) $izin['status']) ?>
                                </span>
                                <!-- FITUR 6: Timestamp relatif -->
                                <div class="text-[10px] text-gray-400 mt-1 font-medium" title="<?= date('d/m/Y H:i', strtotime((string) $izin['created_at'])) ?>">
                                    <?= $timeAgo((string) $izin['created_at']) ?>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4 text-right">
                                <?php if ($izin['status'] === 'Pending'): ?>
                                    <div class="flex justify-end gap-2">
                                        <!-- Approve -->
                                        <form action="<?= base_url('admin/izin/approve/' . (string) $izin['id_izin']) ?>" method="POST" class="inline form-action">
                                            <?= csrf_field() ?>
                                            <button type="button"
                                                    class="btn-confirm p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors border border-emerald-100"
                                                    data-text="Setujui izin ini? Data absensi akan otomatis dibuat (hari libur & weekend dilewati)."
                                                    data-btn="Ya, Setujui"
                                                    title="Approve">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </button>
                                        </form>
                                        <!-- FITUR 7: Reject via modal dengan catatan -->
                                        <button type="button"
                                                onclick='openRejectModal("<?= esc((string) $izin['id_izin']) ?>", "<?= esc(addslashes((string) $izin['nama_siswa'])) ?>", "<?= esc(addslashes((string) $izin['jenis'])) ?>", "<?= esc(addslashes((string) $izin['alasan'])) ?>")'
                                                class="p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors border border-red-100"
                                                title="Tolak dengan Alasan">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-gray-400 italic">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-16 text-center text-gray-400 italic">
                            <i class="fas fa-inbox text-4xl text-gray-200 mb-3 block"></i>
                            Tidak ada data pengajuan izin<?= $status_aktif ? ' dengan status ini' : '' ?>.
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

<!-- ===== FITUR 4: LIGHTBOX ===== -->
<div id="lightbox" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/95 cursor-zoom-out" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 text-white bg-white/10 rounded-full p-2.5 hover:bg-white/20 transition-colors" onclick="closeLightbox()">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
    <p class="text-white/40 text-xs font-bold absolute bottom-5 left-1/2 -translate-x-1/2 select-none">ESC atau klik di mana saja untuk menutup</p>
    <img id="lightbox-img" src="" class="max-h-[88vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl cursor-default" alt="Bukti Izin" onclick="event.stopPropagation()">
</div>

<!-- ===== FITUR 7: MODAL REJECT DENGAN CATATAN PENOLAKAN ===== -->
<div id="modal-reject" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeRejectModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-md p-6 md:p-8 relative">
        <div class="flex justify-between items-center mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Tolak Pengajuan Izin</h3>
            </div>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Info siswa di modal -->
        <div class="bg-gray-50 rounded-xl p-4 mb-5 border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Siswa</p>
            <p class="text-sm font-black text-gray-800" id="reject-siswa-nama">-</p>
            <p class="text-xs text-gray-500 mt-0.5"><span id="reject-jenis" class="font-bold"></span> — <span id="reject-alasan" class="italic"></span></p>
        </div>

        <form id="form-reject" action="" method="POST">
            <?= csrf_field() ?>
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">
                    Catatan Penolakan <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea name="catatan_penolakan" rows="3"
                          class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-red-500 transition-all resize-none"
                          placeholder="Contoh: Bukti tidak lengkap, harap lampirkan surat dari dokter..."></textarea>
                <p class="text-[10px] text-gray-400 mt-1.5">Catatan ini akan disimpan dan dapat dilihat oleh wali kelas.</p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeRejectModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-400 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btn-reject-submit" class="bg-red-600 text-white px-7 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-red-700 transition-all">
                    Ya, Tolak Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ================================================================
    // SEARCH DEBOUNCE
    // ================================================================
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-input');
        if (searchInput && searchInput.value.length > 0) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }

        let searchTimer;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => this.form.submit(), 1000);
            });
        }
    });

    // ================================================================
    // APPROVE: Form submit loading state
    // ================================================================
    document.querySelectorAll('.form-action').forEach(function (form) {
        form.addEventListener('submit', function () {
            const btns = this.querySelectorAll('button');
            btns.forEach(btn => {
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.setAttribute('disabled', 'true');
            });
        });
    });

    // ================================================================
    // FITUR 4: LIGHTBOX
    // ================================================================
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.replace('hidden', 'flex');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.replace('flex', 'hidden');
        if (document.getElementById('modal-reject').classList.contains('hidden')) {
            document.body.style.overflow = '';
        }
    }

    // ================================================================
    // FITUR 7: MODAL REJECT
    // ================================================================
    function openRejectModal(idIzin, namaSiswa, jenis, alasan) {
        document.getElementById('reject-siswa-nama').textContent = namaSiswa;
        document.getElementById('reject-jenis').textContent      = jenis;
        document.getElementById('reject-alasan').textContent     = alasan;
        document.getElementById('form-reject').action            = '<?= base_url('admin/izin/reject/') ?>' + idIzin;

        // Reset textarea
        document.querySelector('#form-reject textarea[name="catatan_penolakan"]').value = '';

        document.getElementById('modal-reject').classList.replace('hidden', 'flex');
        document.body.style.overflow = 'hidden';
    }

    function closeRejectModal() {
        document.getElementById('modal-reject').classList.replace('flex', 'hidden');
        document.body.style.overflow = '';
    }

    document.getElementById('form-reject').addEventListener('submit', function () {
        const btn = document.getElementById('btn-reject-submit');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Memproses...';
    });

    // ================================================================
    // ESC KEY
    // ================================================================
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (!document.getElementById('lightbox').classList.contains('hidden')) {
            closeLightbox();
        } else if (!document.getElementById('modal-reject').classList.contains('hidden')) {
            closeRejectModal();
        }
    });
</script>
<?= $this->endSection() ?>