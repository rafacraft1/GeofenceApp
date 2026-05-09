<?php

/**
 * @var array<int, array<string, string|null>> $logFraud
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Log Pelanggaran Keamanan</h2>
    <p class="text-sm text-gray-500 mt-1">Pantau riwayat deteksi Fake GPS dan manipulasi waktu dari aplikasi siswa.</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                    <th class="px-6 py-4">Waktu Kejadian</th>
                    <th class="px-6 py-4">Identitas Siswa</th>
                    <th class="px-6 py-4">Tipe Pelanggaran</th>
                    <th class="px-6 py-4">Titik Koordinat Palsu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($logFraud)) : ?>
                    <?php foreach ($logFraud as $log): ?>
                        <tr class="hover:bg-red-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= date('d M Y', strtotime((string) $log['created_at'])) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium"><?= date('H:i:s', strtotime((string) $log['created_at'])) ?> WIB</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $log['nama_siswa']) ?></div>
                                <div class="text-[11px] text-gray-500 font-medium"><?= esc((string) $log['nis']) ?> • <?= esc((string) ($log['nama_kelas'] ?? '-')) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wide">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <?= esc((string) $log['tipe_fraud']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-mono text-gray-600 bg-gray-50 px-2 py-1 rounded border border-gray-200 inline-block">
                                    <?= esc((string) $log['lat_fraud']) ?>, <?= esc((string) $log['long_fraud']) ?>
                                </div>
                                <a href="https://www.google.com/maps?q=<?= esc((string) $log['lat_fraud']) ?>,<?= esc((string) $log['long_fraud']) ?>" target="_blank" class="ml-2 text-[10px] text-blue-600 hover:underline font-bold">Lihat Peta</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500">Belum ada log pelanggaran keamanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>