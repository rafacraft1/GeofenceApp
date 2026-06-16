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

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Pengajuan Izin Siswa</h2>
    <p class="text-sm text-gray-500 mt-1">Review dan kelola pengajuan sakit, izin, atau dispensasi.</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex justify-between items-center">
    <form action="<?= base_url('admin/izin') ?>" method="GET" class="w-full max-w-lg relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
        </div>
        <input type="text" name="search" value="<?= esc((string)($search_aktif ?? '')) ?>" placeholder="Cari Nama Siswa atau Alasan..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-10 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
    </form>
</div>

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

                    foreach ($daftarIzin as $izin): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $izin['nama_siswa']) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium bg-gray-100 px-2 py-0.5 rounded inline-block mt-1">
                                    <?= esc((string) $izin['nis']) ?> • <?= esc((string) ($izin['nama_kelas'] ?? '-')) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-700">
                                    <?= date('d M Y', strtotime((string) $izin['tanggal_mulai'])) ?>
                                </div>
                                <?php if ($izin['tanggal_mulai'] !== $izin['tanggal_selesai']): ?>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        s/d <?= date('d M Y', strtotime((string) $izin['tanggal_selesai'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <?php if (!empty($izin['bukti_foto'])): ?>
                                        <img src="<?= base_url('uploads/izin/' . (string) $izin['bukti_foto']) ?>"
                                            onclick="window.open(this.src, '_blank')"
                                            title="Klik untuk membaca surat/bukti"
                                            class="w-10 h-10 rounded-lg object-cover cursor-pointer hover:opacity-80 hover:scale-105 transition-all border border-gray-200 shadow-sm shrink-0"
                                            alt="Bukti Izin">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center shrink-0" title="Tidak ada lampiran">
                                            <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <div class="mb-1.5">
                                            <?php
                                            $jenisColor = match ($izin['jenis']) {
                                                'Sakit' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'Izin' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'Dispensasi' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                default => 'bg-gray-100 text-gray-700 border-gray-200'
                                            };
                                            ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wide <?= $jenisColor ?>">
                                                <?= esc((string) $izin['jenis']) ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 line-clamp-2 max-w-xs" title="<?= esc((string) $izin['alasan']) ?>">
                                            <?= esc((string) $izin['alasan']) ?>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <?php
                                $statusColor = match ($izin['status']) {
                                    'Pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'Rejected' => 'bg-red-100 text-red-700 border-red-200',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                                ?>
                                <span class="inline-block px-3 py-1 rounded-lg text-xs font-bold border <?= $statusColor ?>">
                                    <?= esc((string) $izin['status']) ?>
                                </span>
                                <div class="text-[10px] text-gray-400 mt-1 font-mono">
                                    <?= date('d/m/Y H:i', strtotime((string) $izin['created_at'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($izin['status'] === 'Pending'): ?>
                                    <div class="flex justify-end gap-2">
                                        <form action="<?= base_url('admin/izin/approve/' . (string) $izin['id_izin']) ?>" method="POST" class="inline form-action">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors border border-emerald-100" data-text="Setujui izin ini? Data absensi akan otomatis dibuat." data-btn="Ya, Setujui" title="Approve">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="<?= base_url('admin/izin/reject/' . (string) $izin['id_izin']) ?>" method="POST" class="inline form-action">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors border border-red-100" data-text="Tolak pengajuan izin ini?" data-btn="Ya, Tolak" title="Reject">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-gray-400 italic">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-400 italic">Tidak ada data pengajuan izin.</td>
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
    document.querySelectorAll('.form-action').forEach(function(form) {
        form.addEventListener('submit', function() {
            const btns = this.querySelectorAll('button');
            btns.forEach(btn => {
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.setAttribute('disabled', 'true');
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.querySelector('input[name="search"]');

        // 1. Kembalikan fokus ke input pencarian setelah halaman ter-reload
        if (searchInput && searchInput.value.length > 0) {
            searchInput.focus();
            // Trik untuk memindahkan posisi kursor ke ujung akhir teks
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }

        // 2. Auto-Submit Form Pencarian saat selesai mengetik
        let searchTimer;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                // Delay diset 1 detik (1000ms) agar tidak reload mid-typing
                searchTimer = setTimeout(() => {
                    this.form.submit();
                }, 1000);
            });
        }
    });
</script>
<?= $this->endSection() ?>