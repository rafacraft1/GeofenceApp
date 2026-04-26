<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .select2-container .select2-selection--single {
        height: 46px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.75rem !important;
        display: flex;
        align-items: center;
        padding-left: 0.5rem;
        transition: all 0.2s;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 10px !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        outline: none !important;
    }

    .select2-dropdown {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
        z-index: 9999;
    }
</style>

<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="p-5 border-b flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Data Absensi Harian</h3>
                <p class="text-sm text-gray-500">Log kehadiran seluruh siswa berdasarkan tanggal.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <form action="/admin/absensi" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="date" name="tanggal" value="<?= esc($tanggal) ?>" class="border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 font-medium w-full sm:w-40 transition-all cursor-pointer">
                    <button type="submit" class="bg-slate-800 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-900 transition-all active:scale-95 shadow-md">Filter</button>
                </form>

                <button onclick="toggleFormManual()" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md active:scale-95 whitespace-nowrap transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Input Manual
                </button>
            </div>
        </div>

        <div id="form-manual" class="bg-blue-50/50 p-6 border-b hidden transition-all">
            <h4 class="font-bold text-blue-800 mb-4 text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Catat / Ralat Kehadiran Khusus (Sakit / Izin / Hadir Manual)
            </h4>
            <form action="/admin/absensi/input_manual" method="POST" id="formManualSubmit" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">

                <div class="md:col-span-5 relative">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Siswa</label>
                    <select name="siswa_id" id="siswa_select" class="w-full" required>
                        <option value="">-- Cari Nama atau NIS --</option>
                        <?php foreach ($siswa as $s): ?>
                            <option value="<?= esc($s->id) ?>"><?= esc($s->nis) ?> - <?= esc($s->nama_lengkap) ?> (<?= esc($s->kelas) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= esc($tanggal) ?>" required class="w-full border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Status</label>
                    <select name="status" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none bg-white focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                        <option value="Hadir">Hadir</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin">Izin</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Ket. (Opsional)</label>
                    <input type="text" name="keterangan" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Contoh: Surat Dokter">
                </div>

                <div class="md:col-span-12 flex justify-end gap-3 pt-2">
                    <button type="button" onclick="toggleFormManual()" class="text-gray-500 px-5 py-2.5 text-sm font-semibold hover:text-gray-800 transition-colors">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow hover:bg-blue-700 transition-all btn-submit">Simpan Data</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-y border-gray-100">
                        <th class="px-6 py-4">Nama Siswa</th>
                        <th class="px-6 py-4 text-center">Masuk</th>
                        <th class="px-6 py-4 text-center">Pulang</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($absensi)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="bg-gray-50 p-4 rounded-full mb-4 text-gray-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-gray-800 font-bold">Data Kosong</h4>
                                    <p class="text-gray-500 text-sm max-w-xs mx-auto mt-1">Tidak ada catatan absensi untuk tanggal <?= esc(date('d M Y', strtotime($tanggal))) ?>.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($absensi as $a): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 text-sm"><?= esc($a->nama_lengkap) ?></div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tight mt-0.5"><?= esc($a->nis) ?> • <?= esc($a->kelas) ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($a->waktu_masuk): ?>
                                    <span class="text-sm font-semibold text-slate-700"><?= esc(substr($a->waktu_masuk, 0, 5)) ?></span>
                                    <?php if ($a->menit_telat > 0): ?>
                                        <div class="text-[10px] text-red-500 font-black mt-0.5" title="Terlambat <?= esc($a->menit_telat) ?> Menit">+<?= esc($a->menit_telat) ?>m</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($a->waktu_pulang): ?>
                                    <span class="text-sm font-semibold text-slate-700"><?= esc(substr($a->waktu_pulang, 0, 5)) ?></span>
                                <?php else: ?>
                                    <span class="text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $color = 'bg-gray-100 text-gray-600 border-gray-200';
                                if ($a->status == 'Hadir') $color = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                else if ($a->status == 'Terlambat') $color = 'bg-amber-50 text-amber-600 border-amber-200';
                                else if ($a->status == 'Alpa') $color = 'bg-red-50 text-red-600 border-red-200';
                                else if ($a->status == 'Manipulasi') $color = 'bg-rose-600 text-white border-rose-700';
                                else if (in_array($a->status, ['Sakit', 'Izin'])) $color = 'bg-blue-50 text-blue-600 border-blue-200';
                                ?>
                                <span class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border <?= $color ?>"><?= esc(strtoupper($a->status)) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($a->is_fake_gps): ?>
                                    <span class="text-xs font-bold text-red-600 flex items-center gap-1" title="Sistem mendeteksi penggunaan aplikasi Fake GPS">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Terdeteksi Fake GPS
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500 italic"><?= esc($a->keterangan ?? '-') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function toggleFormManual() {
        document.getElementById('form-manual').classList.toggle('hidden');
    }

    $(document).ready(function() {
        $('#siswa_select').select2({
            placeholder: "-- Ketik Nama atau NIS --",
            allowClear: true,
            width: '100%'
        });

        $('#formManualSubmit').on('submit', function() {
            $(this).find('.btn-submit').addClass('btn-loading');
        });
    });
</script>
<?= $this->endSection() ?>