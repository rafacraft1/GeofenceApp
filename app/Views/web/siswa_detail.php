<?php

/**
 * @var array<string, mixed> $siswa
 * @var array<int, array<string, mixed>> $absensi
 * @var array<int, array<string, mixed>> $logFraud
 * @var array<string, int> $stats
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Profil 360 Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Detail informasi, statistik, dan riwayat aktivitas.</p>
    </div>
    <a href="<?= base_url('admin/siswa') ?>" class="bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-xl font-semibold hover:bg-gray-50 transition-colors text-sm flex items-center justify-center gap-2 shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center h-fit">
        <div class="w-24 h-24 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-3xl shadow-inner overflow-hidden border-4 border-white ring-2 ring-gray-100 shrink-0 mb-4">
            <?php if (!empty($siswa['foto_profil'])): ?>
                <img src="<?= base_url('uploads/siswa/' . (string) $siswa['foto_profil']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <?= esc(strtoupper(substr((string) $siswa['nama_siswa'], 0, 1))) ?>
            <?php endif; ?>
        </div>

        <h3 class="text-xl font-bold text-gray-800"><?= esc((string) $siswa['nama_siswa']) ?></h3>
        <p class="text-sm text-gray-500 font-medium mb-6"><?= esc((string) $siswa['nis']) ?> • <?= esc((string) ($siswa['nama_kelas'] ?? 'Belum ada kelas')) ?></p>

        <div class="w-full grid grid-cols-2 gap-3 mt-auto">
            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100">
                <div class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider mb-1">Total Hadir</div>
                <div class="text-2xl font-black text-emerald-700"><?= (int) $stats['hadir'] ?></div>
            </div>
            <div class="bg-red-50 p-4 rounded-xl border border-red-100">
                <div class="text-[10px] text-red-600 font-bold uppercase tracking-wider mb-1">Total Alpa</div>
                <div class="text-2xl font-black text-red-700"><?= (int) $stats['alpa'] ?></div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b bg-gray-50/50 flex justify-between items-center">
                <h4 class="font-bold text-gray-800 text-sm">Riwayat Presensi (10 Terakhir)</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($absensi)): ?>
                            <?php foreach ($absensi as $ab): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 font-medium text-gray-800 whitespace-nowrap">
                                        <?= date('d M Y', strtotime((string) $ab['tanggal'])) ?>
                                    </td>
                                    <td class="px-5 py-4 font-semibold <?= ((string) $ab['status'] === 'Hadir') ? 'text-emerald-600' : (((string) $ab['status'] === 'Terlambat') ? 'text-amber-600' : 'text-red-600') ?>">
                                        <?= esc((string) $ab['status']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 font-mono text-xs whitespace-nowrap text-right">
                                        <?= esc((string) ($ab['jam_masuk'] ?? '-')) ?> &mdash; <?= esc((string) ($ab['jam_pulang'] ?? '-')) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-gray-400">Belum ada riwayat presensi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col border-t-4 border-t-red-500">
            <div class="p-5 border-b bg-gray-50/50 flex justify-between items-center">
                <h4 class="font-bold text-red-600 text-sm">Riwayat Pelanggaran (Fraud Log)</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($logFraud)): ?>
                            <?php foreach ($logFraud as $log): ?>
                                <tr class="hover:bg-red-50/30 transition-colors">
                                    <td class="px-5 py-4 font-medium text-gray-800 whitespace-nowrap">
                                        <?= date('d M Y H:i', strtotime((string) $log['created_at'])) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded border border-red-200 uppercase">
                                            <?= esc((string) $log['tipe_fraud']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="px-5 py-10 text-center text-gray-400 italic">Bersih. Tidak ada catatan pelanggaran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>