<?php

/**
 * @var array<int, array<string, string|null>> $daftarIzin
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
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $izin['nama_siswa']) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium bg-gray-100 px-2 py-0.5 rounded inline-block mt-1">
                                    <?= esc((string) $izin['nis']) ?> • <?= esc((string) ($izin['nama_kelas'] ?? '-')) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $badgeColor = $izin['jenis'] == 'Sakit' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700';
                                ?>
                                <span class="inline-block px-2 py-1 rounded text-[10px] font-bold <?= $badgeColor ?> mb-1 uppercase tracking-wide">
                                    <?= esc((string) $izin['jenis']) ?>
                                </span>
                                <div class="text-xs font-semibold text-gray-700 mt-1">
                                    <?= date('d M Y', strtotime((string) $izin['tanggal_mulai'])) ?> s/d <?= date('d M Y', strtotime((string) $izin['tanggal_selesai'])) ?>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 max-w-xs truncate" title="<?= esc((string) $izin['alasan']) ?>">
                                    "<?= esc((string) $izin['alasan']) ?>"
                                </p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (!empty($izin['bukti_foto'])): ?>
                                    <a href="<?= base_url('uploads/izin/' . esc((string) $izin['bukti_foto'])) ?>" target="_blank" class="inline-flex items-center justify-center p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors" title="Lihat Surat/Bukti">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($izin['status'] == 'Pending'): ?>
                                    <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-1.5 rounded-lg border border-orange-100">MENUNGGU</span>
                                <?php elseif ($izin['status'] == 'Approved'): ?>
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1.5 rounded-lg border border-emerald-100">DISETUJUI</span>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-1.5 rounded-lg border border-red-100">DITOLAK</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($izin['status'] == 'Pending'): ?>
                                    <div class="flex justify-end gap-2">
                                        <form action="<?= base_url('admin/izin/approve/' . (string) $izin['id_izin']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-lg transition-colors" data-text="Setujui pengajuan ini? Kehadiran akan otomatis terisi <?= esc((string) $izin['jenis']) ?>." data-btn="Ya, Setujui" title="Approve">
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