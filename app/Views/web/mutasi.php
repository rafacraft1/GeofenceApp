<?php

/**
 * @var array<int, array<string, mixed>> $listKelas
 * @var array<int, array<string, mixed>> $siswaAsal
 * @var array<string, mixed>|null $kelasAsalData
 * @var string|null $kelasAsalId
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Mutasi & Kenaikan Kelas</h2>
        <p class="text-sm text-gray-500 mt-1">Pindahkan siswa secara massal dengan perlindungan integritas rekap absensi.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-fit sticky top-6">
            <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide border-b pb-2">1. Pilih Kelas Asal</h3>
            <form action="<?= base_url('admin/mutasi') ?>" method="GET" id="formPilihAsal">
                <label class="block text-xs font-bold text-gray-600 mb-2">Kelas Saat Ini</label>
                <select name="asal" onchange="document.getElementById('formPilihAsal').submit()" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer">
                    <option value="">-- Silakan Pilih --</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelasAsalId === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                            <?= esc((string) $k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($kelasAsalData): ?>
                <div class="mt-6 bg-blue-50 p-4 rounded-xl border border-blue-100">
                    <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest mb-1">Informasi Kelas</p>
                    <p class="text-lg font-black text-blue-800"><?= esc((string) $kelasAsalData['nama_kelas']) ?></p>
                    <p class="text-xs text-blue-600 mt-1 flex items-center gap-2">
                        <i class="fas fa-user-tie"></i> Wali: <span class="font-bold"><?= esc((string) ($kelasAsalData['nama_wali'] ?? 'Kosong')) ?></span>
                    </p>
                    <p class="text-xs text-blue-600 mt-1 flex items-center gap-2">
                        <i class="fas fa-users"></i> Total: <span class="font-bold"><?= count($siswaAsal) ?> Siswa</span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-2">
        <?php if (empty($kelasAsalId)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 flex flex-col items-center justify-center text-center h-full min-h-[300px]">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700">Belum Ada Kelas yang Dipilih</h3>
                <p class="text-sm text-gray-500 mt-2 max-w-sm">Pilih kelas asal terlebih dahulu dari panel di sebelah kiri untuk melihat daftar siswa yang dapat dimutasi.</p>
            </div>
        <?php elseif (empty($siswaAsal)): ?>
            <div class="bg-amber-50 rounded-2xl border border-amber-100 p-8 text-center text-amber-600">
                <p class="font-bold">Kelas ini kosong.</p>
                <p class="text-sm mt-1">Tidak ada siswa yang dapat dipindahkan.</p>
            </div>
        <?php else: ?>
            <form action="<?= base_url('admin/mutasi/proses') ?>" method="POST" id="formMutasi" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <?= csrf_field() ?>
                <input type="hidden" name="kelas_asal" value="<?= esc((string) $kelasAsalId) ?>">

                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide border-b pb-2 flex justify-between items-center">
                    <span>2. Seleksi Siswa</span>
                    <label class="flex items-center gap-2 text-xs font-bold text-blue-600 cursor-pointer bg-blue-50 px-3 py-1.5 rounded-lg">
                        <input type="checkbox" id="checkAll" checked class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                        Pilih Semua Siswa
                    </label>
                </h3>

                <div class="max-h-[300px] overflow-y-auto custom-scrollbar mb-8 pr-2 space-y-2">
                    <?php foreach ($siswaAsal as $siswa): ?>
                        <label class="flex items-center justify-between p-3 border border-gray-100 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="siswa_ids[]" value="<?= esc((string) $siswa['id_siswa']) ?>" checked class="siswa-checkbox rounded text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                <div>
                                    <p class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition-colors"><?= esc((string) $siswa['nama_siswa']) ?></p>
                                    <p class="text-[10px] font-medium text-gray-500">NIS: <?= esc((string) $siswa['nis']) ?></p>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide border-b pb-2">3. Konfigurasi Destinasi</h3>
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-100 mb-6">
                    <label class="block text-xs font-bold text-gray-600 mb-2">Pindah Menuju Kelas:</label>
                    <select name="kelas_tujuan" id="kelas_tujuan" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer" required>
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        <?php foreach ($listKelas as $k): ?>
                            <?php if ($k['id_kelas'] != $kelasAsalId): ?>
                                <option value="<?= esc((string) $k['id_kelas']) ?>"><?= esc((string) $k['nama_kelas']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>

                    <div id="mergeWarning" class="mt-3 hidden"></div>

                    <label class="mt-5 flex items-start gap-3 cursor-pointer p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 transition-all">
                        <div class="relative flex items-center h-5">
                            <input type="checkbox" name="pindah_wali" value="1" class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-gray-300 checked:border-blue-600 checked:bg-blue-600 transition-all">
                            <svg class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 pointer-events-none" viewBox="0 0 14 10" fill="none">
                                <path d="M1 5L4.5 8.5L13 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Pindahkan juga Wali Kelas ke kelas tujuan?</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">Jika dicentang, <?= esc((string) ($kelasAsalData['nama_wali'] ?? 'Wali Kelas')) ?> akan dicabut dari kelas ini dan otomatis menjadi wali kelas di kelas tujuan.</p>
                        </div>
                    </label>
                </div>

                <button type="button" onclick="konfirmasiMutasi()" class="w-full bg-blue-600 text-white font-bold py-3.5 px-4 rounded-xl shadow-md hover:bg-blue-700 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    Eksekusi Mutasi Massal
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Logika Check All Siswa
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.siswa-checkbox');

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = checkAll.checked);
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    const someChecked = Array.from(checkboxes).some(c => c.checked);
                    checkAll.checked = allChecked;
                    checkAll.indeterminate = someChecked && !allChecked;
                });
            });
        }

        // Logika Smart Merge Warning (AJAX)
        const kelasTujuan = document.getElementById('kelas_tujuan');
        const warningDiv = document.getElementById('mergeWarning');

        if (kelasTujuan) {
            kelasTujuan.addEventListener('change', function() {
                const id = this.value;
                if (!id) {
                    warningDiv.classList.add('hidden');
                    return;
                }

                // Call AJAX
                fetch(`<?= base_url('admin/mutasi/checkTujuan/') ?>${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 200) {
                            if (data.jumlah > 0) {
                                warningDiv.innerHTML = `
                                    <div class="flex gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800">
                                        <svg class="w-5 h-5 shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        <div class="text-[11px] leading-relaxed">
                                            <strong class="block mb-0.5">SMART MERGE PERINGATAN:</strong>
                                            Kelas tujuan ini sudah memiliki <b>${data.jumlah} Siswa</b> (Siswa tinggal kelas). <br>
                                            Siswa yang Anda pindahkan akan digabungkan dengan mereka. Wali kelas tujuan saat ini adalah: <b>${data.nama_wali}</b>.
                                        </div>
                                    </div>
                                `;
                                warningDiv.classList.remove('hidden');
                            } else {
                                warningDiv.innerHTML = `
                                    <div class="flex gap-3 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800">
                                        <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        <div class="text-[11px] leading-relaxed">
                                            <strong class="block mb-0.5">KELAS KOSONG & AMAN:</strong>
                                            Kelas tujuan ini belum memiliki siswa. Aman untuk dieksekusi.
                                        </div>
                                    </div>
                                `;
                                warningDiv.classList.remove('hidden');
                            }
                        }
                    });
            });
        }
    });

    function konfirmasiMutasi() {
        const checkedCount = document.querySelectorAll('.siswa-checkbox:checked').length;
        const tujuan = document.getElementById('kelas_tujuan');
        const textTujuan = tujuan.options[tujuan.selectedIndex].text;

        if (checkedCount === 0) {
            Swal.fire('Validasi', 'Pilih minimal satu siswa yang akan dipindahkan.', 'warning');
            return;
        }
        if (!tujuan.value) {
            Swal.fire('Validasi', 'Kelas tujuan wajib dipilih.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Eksekusi Mutasi Massal?',
            html: `Anda akan memindahkan <b>${checkedCount} Siswa</b> menuju kelas <b>${textTujuan}</b>.<br><br><span class="text-xs text-red-500">Pastikan data yang dipilih sudah benar!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Pindahkan Sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formMutasi').submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>