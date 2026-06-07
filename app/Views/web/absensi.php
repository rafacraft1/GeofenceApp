<?php

/**
 * @var string $title
 * @var string $tanggal
 * @var string|int|null $kelas_aktif
 * @var string|null $search_aktif
 * @var array<int, array<string, string|null>> $absensi
 * @var array<int, array<string, string|null>> $siswa
 * @var array<int, array<string, string|null>> $list_kelas
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 * @var string $pager_links
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    .modal-active {
        overflow: hidden;
    }

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
        <h2 class="text-2xl font-bold text-gray-800">Data Absensi Harian</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau kehadiran, keterlambatan, dan input absen manual.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <form action="<?= base_url('admin/absensi') ?>" method="GET" class="flex flex-col md:flex-row w-full md:flex-1 max-w-3xl gap-3 items-center">
        <input type="date" name="tanggal" value="<?= esc((string) $tanggal) ?>" class="w-full md:w-auto border border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer bg-gray-50 font-medium text-gray-600" onchange="this.form.submit()">

        <?php if (session()->get('is_wali_kelas')): ?>
            <input type="text" value="<?= esc((string) session()->get('nama_kelas')) ?>" class="w-full md:w-48 border border-gray-200 rounded-xl p-2.5 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none font-medium" readonly>
            <input type="hidden" name="kelas_id" value="<?= session()->get('kelas_id') ?>">
        <?php else: ?>
            <select name="kelas_id" class="w-full md:w-48 border border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer font-medium text-gray-600" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                <?php foreach ($list_kelas as $k): ?>
                    <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelas_aktif === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                        <?= esc((string) $k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" name="search" value="<?= esc((string)($search_aktif ?? '')) ?>" placeholder="Cari Siswa..." class="w-full border border-gray-200 rounded-xl py-2.5 pl-10 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
        <button type="submit" class="hidden">Submit</button>
    </form>

    <button onclick="openManualModal()" class="w-full md:w-auto flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md transition-all active:scale-95 whitespace-nowrap">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
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
                    <?php
                    $safePage    = max(1, (int)($page ?? 1));
                    $safePerPage = max(1, (int)($perPage ?? 20));
                    $no = (($safePage - 1) * $safePerPage) + 1;

                    foreach ($absensi as $ab): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>

                            <td class="px-6 py-4">
                                <button onclick='openDetailModal(<?= htmlspecialchars(json_encode($ab), ENT_QUOTES, "UTF-8") ?>)' class="text-sm font-bold text-blue-700 hover:text-blue-500 hover:underline transition-colors focus:outline-none flex items-center gap-1.5 text-left group">
                                    <?= esc((string) $ab['nama_siswa']) ?>
                                    <svg class="w-3.5 h-3.5 text-blue-400 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                </button>
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
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        Fake GPS
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-400 italic">Belum ada data absensi untuk tanggal ini.</td>
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

<div id="modal-detail" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-2xl p-6 md:p-8 relative flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-xl font-black text-gray-800" id="dtl-nama">-</h3>
                <p class="text-xs text-gray-500 font-bold mt-1 bg-gray-100 px-2 py-0.5 rounded inline-block" id="dtl-nis-kelas">-</p>
            </div>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto scrollbar-hide flex-1 pb-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-blue-50/40 rounded-2xl p-4 md:p-5 border border-blue-100 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-black text-blue-900 uppercase tracking-wider">Masuk</h4>
                        </div>
                        <span class="text-xs font-mono bg-blue-600 text-white px-2 py-1 rounded-md font-bold shadow-sm" id="dtl-jam-masuk">--:--:--</span>
                    </div>

                    <div class="w-full h-40 md:h-48 rounded-xl bg-gray-200 border-4 border-white shadow-sm overflow-hidden mb-4 flex flex-col items-center justify-center relative">
                        <img id="dtl-foto-masuk" src="" class="w-full h-full object-cover hidden" alt="Foto Masuk">
                        <div id="dtl-nofoto-masuk" class="text-center flex flex-col items-center">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                            </svg>
                            <span class="text-xs font-bold text-gray-400">Tidak ada foto</span>
                        </div>
                    </div>

                    <div class="mt-auto bg-white rounded-xl p-3 border border-blue-100 shadow-sm">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase mb-1">Koordinat Masuk</span>
                        <a id="dtl-lokasi-masuk" href="#" target="_blank" class="text-blue-600 font-bold hover:underline flex items-center gap-1 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span id="dtl-latlong-masuk" class="truncate">Tidak tercatat</span>
                        </a>
                    </div>
                </div>

                <div class="bg-emerald-50/40 rounded-2xl p-4 md:p-5 border border-emerald-100 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-black text-emerald-900 uppercase tracking-wider">Pulang</h4>
                        </div>
                        <span class="text-xs font-mono bg-emerald-600 text-white px-2 py-1 rounded-md font-bold shadow-sm" id="dtl-jam-pulang">--:--:--</span>
                    </div>

                    <div class="w-full h-40 md:h-48 rounded-xl bg-gray-200 border-4 border-white shadow-sm overflow-hidden mb-4 flex flex-col items-center justify-center relative">
                        <img id="dtl-foto-pulang" src="" class="w-full h-full object-cover hidden" alt="Foto Pulang">
                        <div id="dtl-nofoto-pulang" class="text-center flex flex-col items-center">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                            </svg>
                            <span class="text-xs font-bold text-gray-400">Tidak ada foto</span>
                        </div>
                    </div>

                    <div class="mt-auto bg-white rounded-xl p-3 border border-emerald-100 shadow-sm">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase mb-1">Koordinat Pulang</span>
                        <a id="dtl-lokasi-pulang" href="#" target="_blank" class="text-emerald-600 font-bold hover:underline flex items-center gap-1 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span id="dtl-latlong-pulang" class="truncate">Tidak tercatat</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-manual" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeManualModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-md p-6 md:p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Input Manual</h3>
            <button onclick="closeManualModal()" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="<?= base_url('admin/absensi/inputManual') ?>" method="POST" id="form-manual" class="space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="tanggal" value="<?= esc((string) $tanggal) ?>">

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Siswa</label>
                <select name="siswa_id" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer text-gray-700 font-medium">
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
                <select name="status" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer text-gray-700 font-medium">
                    <option value="Hadir">Hadir</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Catatan tambahan...">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeManualModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-400 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 btn-submit transition-all">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // FUNGSI UNTUK MODAL INPUT MANUAL
    function openManualModal() {
        document.getElementById('modal-manual').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function closeManualModal() {
        document.getElementById('modal-manual').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    // FUNGSI UNTUK MODAL DETAIL ABSENSI (FOTO & TITIK KOORDINAT)
    function openDetailModal(data) {
        // Render Header
        document.getElementById('dtl-nama').textContent = data.nama_siswa || '-';
        document.getElementById('dtl-nis-kelas').textContent = `${data.nis || '-'} • ${data.nama_kelas || '-'}`;

        // Render Data Masuk
        document.getElementById('dtl-jam-masuk').textContent = data.jam_masuk || '--:--:--';
        const imgMasuk = document.getElementById('dtl-foto-masuk');
        const noImgMasuk = document.getElementById('dtl-nofoto-masuk');
        if (data.foto_masuk) {
            imgMasuk.src = '<?= base_url("uploads/absensi/") ?>' + data.foto_masuk;
            imgMasuk.classList.remove('hidden');
            noImgMasuk.classList.add('hidden');
        } else {
            imgMasuk.src = '';
            imgMasuk.classList.add('hidden');
            noImgMasuk.classList.remove('hidden');
        }

        const latLongMasuk = document.getElementById('dtl-latlong-masuk');
        const linkMasuk = document.getElementById('dtl-lokasi-masuk');
        if (data.lat_masuk && data.long_masuk) {
            latLongMasuk.textContent = `${data.lat_masuk}, ${data.long_masuk}`;
            // ✅ PERBAIKAN: Menggunakan template literal yang benar dan format URL Google Maps yang tepat
            linkMasuk.href = `https://www.google.com/maps/search/?api=1&query=${data.lat_masuk},${data.long_masuk}`;
            linkMasuk.classList.remove('pointer-events-none', 'text-gray-400');
        } else {
            latLongMasuk.textContent = 'Lokasi tidak tercatat';
            linkMasuk.href = '#';
            linkMasuk.classList.add('pointer-events-none', 'text-gray-400');
        }

        // Render Data Pulang
        document.getElementById('dtl-jam-pulang').textContent = data.jam_pulang || '--:--:--';
        const imgPulang = document.getElementById('dtl-foto-pulang');
        const noImgPulang = document.getElementById('dtl-nofoto-pulang');
        if (data.foto_pulang) {
            imgPulang.src = '<?= base_url("uploads/absensi/") ?>' + data.foto_pulang;
            imgPulang.classList.remove('hidden');
            noImgPulang.classList.add('hidden');
        } else {
            imgPulang.src = '';
            imgPulang.classList.add('hidden');
            noImgPulang.classList.remove('hidden');
        }

        const latLongPulang = document.getElementById('dtl-latlong-pulang');
        const linkPulang = document.getElementById('dtl-lokasi-pulang');
        if (data.lat_pulang && data.long_pulang) {
            latLongPulang.textContent = `${data.lat_pulang}, ${data.long_pulang}`;
            // ✅ PERBAIKAN: Menggunakan template literal yang benar dan format URL Google Maps yang tepat
            linkPulang.href = `https://www.google.com/maps/search/?api=1&query=${data.lat_pulang},${data.long_pulang}`;
            linkPulang.classList.remove('pointer-events-none', 'text-gray-400');
        } else {
            latLongPulang.textContent = 'Lokasi tidak tercatat';
            linkPulang.href = '#';
            linkPulang.classList.add('pointer-events-none', 'text-gray-400');
        }

        // Tampilkan Modal
        document.getElementById('modal-detail').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function closeDetailModal() {
        document.getElementById('modal-detail').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    // Mencegah double submit form input manual
    document.getElementById('form-manual').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.innerHTML = 'Menyimpan...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>