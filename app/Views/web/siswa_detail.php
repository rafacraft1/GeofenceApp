<?php

/**
 * @var array<string, string|null> $siswa
 * @var array<int, array<string, string|null>> $absensi
 * @var array<int, array<string, string|null>> $logFraud
 * @var array<string, int> $stats
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Profil 360 Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Detail informasi, statistik, dan riwayat aktivitas.</p>
    </div>
    <a href="<?= base_url('admin/siswa') ?>" class="bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-50 transition-colors text-sm">Kembali</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
        <div class="w-24 h-24 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-3xl shadow-inner overflow-hidden border-4 border-white ring-2 ring-gray-100 shrink-0 mb-4">
            <?php if (!empty($siswa['foto_profil'])): ?>
                <img src="/uploads/siswa/<?= esc((string) $siswa['foto_profil']) ?>" alt="Foto" class="w-full h-full object-cover">
            <?php else: ?>
                <?= esc(strtoupper(substr((string) $siswa['nama_siswa'], 0, 1))) ?>
            <?php endif; ?>
        </div>
        <h3 class="text-xl font-bold text-gray-800"><?= esc((string) $siswa['nama_siswa']) ?></h3>
        <p class="text-sm font-semibold text-gray-500 mt-1"><?= esc((string) $siswa['nis']) ?> • <?= esc((string) ($siswa['nama_kelas'] ?? '-')) ?></p>

        <div class="w-full mt-6 space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-gray-50">
                <span class="text-xs text-gray-500 font-bold uppercase">Status Akun</span>
                <?php if ($siswa['is_blocked'] == 1): ?>
                    <span class="text-[10px] bg-red-100 text-red-700 px-2 py-1 rounded font-bold">DIBLOKIR</span>
                <?php else: ?>
                    <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-1 rounded font-bold">AKTIF</span>
                <?php endif; ?>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-50">
                <span class="text-xs text-gray-500 font-bold uppercase">Device ID</span>
                <span class="text-xs font-mono font-semibold text-gray-800"><?= !empty($siswa['device_id']) ? 'Terikat' : 'Kosong' ?></span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="text-xs text-gray-500 font-bold uppercase">Skor Fraud</span>
                <span class="text-xs font-bold <?= $siswa['fraud_count'] > 0 ? 'text-red-600' : 'text-emerald-600' ?>"><?= (int)$siswa['fraud_count'] ?> / 3</span>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4 h-fit">
        <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-100">
            <div class="text-emerald-500 text-sm font-bold mb-1">Hadir Tepat</div>
            <div class="text-3xl font-black text-emerald-700"><?= $stats['hadir'] ?></div>
        </div>
        <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100">
            <div class="text-amber-500 text-sm font-bold mb-1">Terlambat</div>
            <div class="text-3xl font-black text-amber-700"><?= $stats['terlambat'] ?></div>
        </div>
        <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100">
            <div class="text-blue-500 text-sm font-bold mb-1">Sakit / Izin</div>
            <div class="text-3xl font-black text-blue-700"><?= $stats['sakit'] + $stats['izin'] ?></div>
        </div>
        <div class="bg-red-50 rounded-2xl p-5 border border-red-100">
            <div class="text-red-500 text-sm font-bold mb-1">Alpa</div>
            <div class="text-3xl font-black text-red-700"><?= $stats['alpa'] ?></div>
        </div>

        <div class="col-span-2 md:col-span-4 bg-white rounded-2xl shadow-sm border border-gray-100 mt-2 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                <h4 class="font-bold text-gray-800 text-sm">Riwayat Presensi (10 Hari Terakhir)</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($absensi)): ?>
                            <?php foreach ($absensi as $ab): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3 font-medium text-gray-800"><?= date('d M Y', strtotime((string) $ab['tanggal'])) ?></td>
                                    <td class="px-5 py-3 font-semibold <?= $ab['status'] == 'Hadir' ? 'text-emerald-600' : ($ab['status'] == 'Terlambat' ? 'text-amber-600' : 'text-red-600') ?>"><?= esc((string) $ab['status']) ?></td>
                                    <td class="px-5 py-3 text-gray-500 font-mono text-xs"><?= esc((string) $ab['jam_masuk'] ?? '-') ?> &mdash; <?= esc((string) $ab['jam_pulang'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center text-gray-400">Belum ada riwayat presensi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>