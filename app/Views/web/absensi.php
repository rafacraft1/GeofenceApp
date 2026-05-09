<?php

/**
 * @var string $title
 * @var string $tanggal
 * @var string $kelas_aktif
 * @var array<int, array<string, string|null>> $absensi
 * @var array<int, array<string, string|null>> $siswa
 * @var array<int, array<string, string|null>> $list_kelas
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Data Absensi Harian</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau kehadiran, keterlambatan, dan input absen manual.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <form action="<?= base_url('admin/absensi') ?>" method="GET" class="flex w-full md:w-auto gap-3 items-center">
        <div>
            <input type="date" name="tanggal" value="<?= esc((string) $tanggal) ?>" class="border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer bg-gray-50" onchange="this.form.submit()">
        </div>
        <div>
            <select name="kelas_id" class="border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                <?php foreach ($list_kelas as $k): ?>
                    <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ($kelas_aktif === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                        <?= esc((string) $k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <button onclick="openManualModal()" class="w-full md:w-auto flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md transition-all active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Input Manual
    </button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                    <th class="px-6 py-4">No</th>
                    <th class="px-6 py-4">Identitas Siswa</th>
                    <th class="px-6 py-4">Waktu Presensi</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($absensi)) : ?>
                    <?php $no = 1;
                    foreach ($absensi as $ab): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800"><?= esc((string) $ab['nama_siswa']) ?></div>
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
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                $status = (string) $ab['status'];
                                $badgeColor = match ($status) {
                                    'Hadir'     => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'Terlambat' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Sakit', 'Izin' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'Alpa', 'Manipulasi' => 'bg-red-100 text-red-700 border-red-200',
                                    default     => 'bg-gray-100 text-gray-700 border-gray-200'
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
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Fake GPS
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">Belum ada data absensi untuk tanggal ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-manual" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeManualModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-lg p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Input Absensi Manual</h3>
            <button onclick="closeManualModal()" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg></button>
        </div>

        <form action="<?= base_url('admin/absensi/inputManual') ?>" method="POST" id="form-manual" class="space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="tanggal" value="<?= esc((string) $tanggal) ?>">

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Siswa</label>
                <select name="siswa_id" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                    <option value="" disabled selected>-- Cari / Pilih Siswa --</option>
                    <?php foreach ($siswa as $s): ?>
                        <option value="<?= esc((string) $s['id_siswa']) ?>">
                            <?= esc((string) $s['nama_siswa']) ?> (<?= esc((string) ($s['nama_kelas'] ?? '-')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Status Kehadiran</label>
                <select name="status" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                    <option value="Hadir">Hadir</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Catatan tambahan admin...">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeManualModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-400">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 btn-submit transition-all">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function openManualModal() {
        document.getElementById('modal-manual').classList.replace('hidden', 'flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeManualModal() {
        document.getElementById('modal-manual').classList.replace('flex', 'hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
<?= $this->endSection() ?>