<?php

/**
 * @var array<int, array<string, mixed>> $daftarIzin
 * @var string|null $search_aktif
 * @var string $sort_col
 * @var string $sort_dir
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 * @var string $pager_links
 */

$buildSortLink = function ($column) use ($search_aktif, $sort_col, $sort_dir) {
    $newDir = ($sort_col === $column && $sort_dir === 'asc') ? 'desc' : 'asc';
    $url = base_url('admin/izin') . "?sort={$column}-{$newDir}";
    if ($search_aktif) $url .= "&search=" . urlencode($search_aktif);
    return $url;
};

// Heroicons: chevron-up-down, chevron-up, chevron-down
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
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Persetujuan Izin & Sakit</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola pengajuan tidak masuk sekolah dari siswa.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <form action="<?= base_url('admin/izin') ?>" method="GET" class="w-full md:flex-1 max-w-2xl">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" name="search" value="<?= esc((string)($search_aktif ?? '')) ?>" placeholder="Cari Nama, NIS, atau Alasan..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-10 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium text-gray-700">
        </div>
        <button type="submit" class="hidden">Submit</button>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
    <div class="overflow-x-auto flex-1">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortLink('nama_siswa') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600" title="Urutkan Nama Siswa">
                            Informasi Siswa <?= $getSortIcon('nama_siswa') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="<?= $buildSortLink('tanggal_mulai') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600" title="Urutkan Tanggal Pengajuan">
                            Detail Pengajuan <?= $getSortIcon('tanggal_mulai') ?>
                        </a>
                    </th>
                    <th class="px-6 py-4 text-center">Bukti</th>
                    <th class="px-6 py-4 text-center">
                        <div class="flex justify-center">
                            <a href="<?= $buildSortLink('status') ?>" class="flex items-center group cursor-pointer transition-colors hover:text-blue-600" title="Urutkan Status">
                                Status <?= $getSortIcon('status') ?>
                            </a>
                        </div>
                    </th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($daftarIzin)) : ?>
                    <?php foreach ($daftarIzin as $izin): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs shadow-inner border border-indigo-100 shrink-0">
                                        <?= esc(strtoupper(substr((string) ($izin['nama_siswa'] ?? ''), 0, 1))) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800"><?= esc((string) $izin['nama_siswa']) ?></div>
                                        <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                            <?= esc((string) $izin['nis']) ?> • <?= esc((string) ($izin['nama_kelas'] ?? '-')) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-700 mb-1">
                                    <span class="<?= ((string)$izin['jenis'] === 'Sakit') ? 'text-blue-600' : 'text-amber-600' ?>"><?= esc((string) $izin['jenis']) ?></span>
                                    <span class="text-gray-400 font-normal mx-1">&bull;</span>
                                    <?= date('d M', strtotime((string) $izin['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime((string) $izin['tanggal_selesai'])) ?>
                                </div>
                                <div class="text-xs text-gray-500 line-clamp-2 italic">
                                    "<?= esc((string) $izin['alasan']) ?>"
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (!empty($izin['bukti_foto'])): ?>
                                    <a href="<?= base_url('uploads/izin/' . (string) $izin['bukti_foto']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg border border-blue-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                        Lihat
                                    </a>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-400 italic">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                $status = (string) $izin['status'];
                                $badgeClass = match ($status) {
                                    'Approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    default    => 'bg-amber-50 text-amber-700 border-amber-200', // Pending
                                };
                                ?>
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold border uppercase tracking-wide <?= $badgeClass ?>">
                                    <?= esc($status) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ((string) $izin['status'] === 'Pending'): ?>
                                    <div class="flex justify-end gap-2">
                                        <form action="<?= base_url('admin/izin/approve/' . (string) $izin['id_izin']) ?>" method="POST" class="inline form-action">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-700 border border-emerald-100 rounded-lg transition-colors focus:outline-none" data-text="Setujui izin ini? Absensi akan otomatis diisi." data-btn="Ya, Setujui" title="Approve">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </button>
                                        </form>

                                        <form action="<?= base_url('admin/izin/reject/' . (string) $izin['id_izin']) ?>" method="POST" class="inline form-action">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-700 border border-red-100 rounded-lg transition-colors focus:outline-none" data-text="Tolak pengajuan izin ini?" data-btn="Ya, Tolak" title="Reject">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] font-semibold text-gray-400 border border-gray-200 bg-gray-50 px-2 py-1 rounded-lg">Diproses</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400 italic">Tidak ada data pengajuan izin.</td>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Mencegah double submit saat menekan Approve/Reject
    document.querySelectorAll('.form-action').forEach(function(form) {
        form.addEventListener('submit', function() {
            const btns = this.querySelectorAll('button');
            btns.forEach(btn => {
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.setAttribute('disabled', 'true');
            });
        });
    });
</script>
<?= $this->endSection() ?>