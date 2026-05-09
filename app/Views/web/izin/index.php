<?php

/**
 * @var array<int, array<string, mixed>> $daftarIzin
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Persetujuan Izin & Sakit</h2>
    <p class="text-sm text-gray-500 mt-1">Kelola pengajuan tidak masuk sekolah dari siswa.</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
    <div class="overflow-x-auto flex-1">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                    <th class="px-6 py-4">Informasi Siswa</th>
                    <th class="px-6 py-4">Detail Pengajuan</th>
                    <th class="px-6 py-4 text-center">Bukti</th>
                    <th class="px-6 py-4 text-center">Status</th>
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
                                    <span class="text-blue-600"><?= esc((string) $izin['jenis']) ?></span>
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
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
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
                                        <form action="<?= base_url('admin/izin/approve/' . (string) $izin['id_izin']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-lg transition-colors" data-text="Setujui izin ini? Absensi akan otomatis diisi." data-btn="Ya, Setujui" title="Approve">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </form>

                                        <form action="<?= base_url('admin/izin/reject/' . (string) $izin['id_izin']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition-colors" data-text="Tolak pengajuan izin ini?" data-btn="Ya, Tolak" title="Reject">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] font-semibold text-gray-400">Telah Diproses</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">Belum ada pengajuan izin.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>