<?php

/**
 * @var string $title
 * @var array<int, array<string, mixed>> $listKelas
 * @var array<int, array<string, mixed>> $siswaAsal
 * @var array{id_kelas: string|int, nama_kelas: string, wali_kelas_id: string|int|null, nama_wali: string|null}|null $kelasAsalData
 * @var string|null $kelasAsalId
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-exchange-alt text-blue-600"></i> Mutasi & Kenaikan Kelas
        </h2>
        <p class="text-sm text-gray-500 mt-1">Pindahkan siswa secara massal dengan perlindungan integritas rekap absensi.</p>
    </div>
</div>

<div id="data-container" class="relative">

    <div id="loading-overlay" class="absolute inset-0 bg-gray-50/50 backdrop-blur-[2px] z-50 hidden items-center justify-center rounded-2xl">
        <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-fit sticky top-24">
                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide border-b pb-2 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">1</span>
                    Pilih Kelas Asal
                </h3>

                <label class="block text-xs font-bold text-gray-600 mb-2">Kelas Saat Ini</label>
                <select id="select-kelas-asal" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer font-bold text-gray-700">
                    <option value="">-- Silakan Pilih --</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelasAsalId === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                            <?= esc((string) $k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if ($kelasAsalData): ?>
                    <div class="mt-6 bg-blue-50 p-5 rounded-xl border border-blue-100 shadow-inner">
                        <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest mb-1">Informasi Kelas</p>
                        <p class="text-xl font-black text-blue-800"><?= esc((string) $kelasAsalData['nama_kelas']) ?></p>
                        <div class="mt-3 space-y-2">
                            <p class="text-xs text-blue-700 flex items-center gap-2">
                                <i class="fas fa-user-tie w-4 text-center"></i> Wali: <span class="font-bold"><?= esc((string) ($kelasAsalData['nama_wali'] ?? 'Kosong')) ?></span>
                            </p>
                            <p class="text-xs text-blue-700 flex items-center gap-2">
                                <i class="fas fa-users w-4 text-center"></i> Total: <span class="font-bold"><?= count($siswaAsal) ?> Siswa</span>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="lg:col-span-2">
            <?php if (empty($kelasAsalId)): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 flex flex-col items-center justify-center text-center h-full min-h-[300px]">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-400 border border-gray-100 shadow-sm">
                        <i class="fas fa-chalkboard text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700">Belum Ada Kelas yang Dipilih</h3>
                    <p class="text-sm text-gray-500 mt-2 max-w-sm">Pilih kelas asal terlebih dahulu dari panel di sebelah kiri untuk melihat daftar siswa.</p>
                </div>
            <?php elseif (empty($siswaAsal)): ?>
                <div class="bg-amber-50 rounded-2xl border border-amber-100 p-10 text-center text-amber-700 flex flex-col items-center justify-center h-full min-h-[300px]">
                    <i class="fas fa-box-open text-4xl text-amber-300 mb-3"></i>
                    <p class="font-black text-lg">Kelas Ini Kosong</p>
                    <p class="text-sm mt-1">Tidak ada siswa yang dapat dipindahkan dari kelas ini.</p>
                </div>
            <?php else: ?>
                <form action="<?= base_url('admin/mutasi/proses') ?>" method="POST" id="formMutasi" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="kelas_asal" value="<?= esc((string) $kelasAsalId) ?>">

                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide border-b pb-2 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">2</span>
                            Seleksi Siswa
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold text-blue-600 cursor-pointer bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                            <input type="checkbox" id="checkAll" checked class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            Pilih Semua
                        </label>
                    </h3>

                    <div class="relative mb-3">
                        <input type="text" id="searchSiswa" placeholder="Cari nama siswa di kelas ini..." class="w-full border-gray-200 rounded-xl p-2.5 pl-9 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                    </div>

                    <div class="max-h-[300px] overflow-y-auto custom-scrollbar mb-8 pr-2 space-y-2 border border-gray-100 rounded-xl p-2 bg-gray-50/50">
                        <?php foreach ($siswaAsal as $siswa): ?>
                            <label class="siswa-item-row flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl hover:bg-blue-50/50 hover:border-blue-200 cursor-pointer transition-colors group shadow-sm">
                                <div class="flex items-center gap-3 w-full">
                                    <input type="checkbox" name="siswa_ids[]" value="<?= esc((string) $siswa['id_siswa']) ?>" checked class="siswa-checkbox rounded text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer mt-0.5">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 group-hover:text-blue-700 transition-colors siswa-nama"><?= esc((string) $siswa['nama_siswa']) ?></p>
                                        <p class="text-[10px] font-medium text-gray-500">NIS: <?= esc((string) $siswa['nis']) ?></p>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide border-b pb-2 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">3</span>
                        Konfigurasi Destinasi
                    </h3>

                    <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 mb-6">
                        <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">Pindah Menuju Kelas</label>
                        <select name="kelas_tujuan" id="kelas_tujuan" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer font-bold text-gray-700" required>
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            <?php foreach ($listKelas as $k): ?>
                                <?php if ($k['id_kelas'] != $kelasAsalId): ?>
                                    <option value="<?= esc((string) $k['id_kelas']) ?>"><?= esc((string) $k['nama_kelas']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>

                        <div id="mergeWarning" class="mt-3 hidden"></div>

                        <label class="mt-5 flex items-start gap-3 cursor-pointer p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-md transition-all group">
                            <div class="relative flex items-center h-5 mt-0.5">
                                <input type="checkbox" name="pindah_wali" value="1" class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border-2 border-gray-300 checked:border-blue-600 checked:bg-blue-600 transition-all">
                                <svg class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 pointer-events-none" viewBox="0 0 14 10" fill="none">
                                    <path d="M1 5L4.5 8.5L13 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 group-hover:text-blue-700 transition-colors">Ikut Pindahkan Wali Kelas?</p>
                                <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">Jika dicentang, <span class="font-bold text-gray-700"><?= esc((string) ($kelasAsalData['nama_wali'] ?? 'Wali Kelas saat ini')) ?></span> akan dicabut dari kelas ini dan otomatis menjadi wali kelas di kelas tujuan.</p>
                            </div>
                        </label>
                    </div>

                    <button type="button" onclick="konfirmasiMutasi()" class="w-full bg-blue-600 text-white font-black py-4 px-4 rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 text-sm btn-submit-action">
                        <i class="fas fa-rocket"></i> Eksekusi Mutasi Massal
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Fungsi re-bind event listeners agar tetap berfungsi setelah AJAX reload
    function initMutasiEvents() {
        // 1. AJAX SPA untuk Dropdown Kelas Asal
        const selectKelasAsal = document.getElementById('select-kelas-asal');
        if (selectKelasAsal) {
            selectKelasAsal.addEventListener('change', function() {
                const url = new window.URL(window.location.href);
                if (this.value) url.searchParams.set('asal', this.value);
                else url.searchParams.delete('asal');

                fetchMutasiData(url.toString());
            });
        }

        // 2. Client-Side Live Search Siswa (Mencegah checklist reset)
        const searchSiswa = document.getElementById('searchSiswa');
        if (searchSiswa) {
            searchSiswa.addEventListener('input', function() {
                const keyword = this.value.toLowerCase();
                document.querySelectorAll('.siswa-item-row').forEach(row => {
                    const name = row.querySelector('.siswa-nama').textContent.toLowerCase();
                    row.style.display = name.includes(keyword) ? 'flex' : 'none';
                });
            });
        }

        // 3. Logika Check All Siswa
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.siswa-checkbox');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    // Hanya centang yang sedang tampil (tidak ter-filter oleh search)
                    if (cb.closest('.siswa-item-row').style.display !== 'none') {
                        cb.checked = checkAll.checked;
                    }
                });
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const visibleBoxes = Array.from(checkboxes).filter(c => c.closest('.siswa-item-row').style.display !== 'none');
                    const allChecked = visibleBoxes.every(c => c.checked);
                    const someChecked = visibleBoxes.some(c => c.checked);
                    checkAll.checked = allChecked;
                    checkAll.indeterminate = someChecked && !allChecked;
                });
            });
        }

        // 4. Smart Merge Warning (AJAX Check Kelas Tujuan)
        const kelasTujuan = document.getElementById('kelas_tujuan');
        const warningDiv = document.getElementById('mergeWarning');
        if (kelasTujuan) {
            kelasTujuan.addEventListener('change', function() {
                const id = this.value;
                if (!id) {
                    warningDiv.classList.add('hidden');
                    return;
                }

                fetch(`<?= base_url('admin/mutasi/checkTujuan/') ?>${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 200) {
                            if (data.jumlah > 0) {
                                warningDiv.innerHTML = `
                                    <div class="flex gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 shadow-inner">
                                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                                        <div class="text-[11px] leading-relaxed">
                                            <strong class="block mb-0.5 text-xs">SMART MERGE PERINGATAN:</strong>
                                            Kelas tujuan ini sudah memiliki <b>${data.jumlah} Siswa</b>. <br>
                                            Siswa yang dipindah akan <b>digabungkan</b> dengan mereka. Wali kelas tujuan saat ini: <b>${data.nama_wali}</b>.
                                        </div>
                                    </div>
                                `;
                                warningDiv.classList.remove('hidden');
                            } else {
                                warningDiv.innerHTML = `
                                    <div class="flex gap-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 shadow-inner">
                                        <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                                        <div class="text-[11px] leading-relaxed">
                                            <strong class="block mb-0.5 text-xs">KELAS KOSONG & AMAN:</strong>
                                            Kelas tujuan ini belum memiliki siswa. Sangat aman untuk dieksekusi.
                                        </div>
                                    </div>
                                `;
                                warningDiv.classList.remove('hidden');
                            }
                        }
                    });
            });
        }
    }

    // Fungsi fetch data SPA HTML-Over-The-Wire
    function fetchMutasiData(url) {
        window.history.pushState({}, '', url);
        const overlay = document.getElementById('loading-overlay');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new window.DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                document.getElementById('data-container').innerHTML = doc.querySelector('#data-container').innerHTML;

                // Re-initialize events setelah DOM diganti
                initMutasiEvents();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                overlay.classList.replace('flex', 'hidden');
            });
    }

    // Fungsi Konfirmasi Submit
    window.konfirmasiMutasi = function() {
        const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
        const tujuan = document.getElementById('kelas_tujuan');
        const textTujuan = tujuan.options[tujuan.selectedIndex]?.text;

        if (checkedBoxes.length === 0) {
            Swal.fire('Validasi Gagal', 'Centang minimal satu siswa yang akan dipindahkan.', 'warning');
            return;
        }
        if (!tujuan.value) {
            Swal.fire('Validasi Gagal', 'Kelas destinasi tujuan wajib dipilih.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Eksekusi Mutasi Massal?',
            html: `Memindahkan <b class="text-blue-600">${checkedBoxes.length} Siswa</b> menuju kelas <b class="text-blue-600">${textTujuan}</b>.<br><br><span class="text-xs text-red-500 font-bold">Proses ini akan mengubah struktur kelas data siswa!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Eksekusi Sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.querySelector('.btn-submit-action');
                btn.classList.add('btn-loading');
                btn.innerHTML = '';
                document.getElementById('formMutasi').submit();
            }
        });
    }

    // Inisialisasi event pertama kali halaman dimuat
    document.addEventListener("DOMContentLoaded", initMutasiEvents);
</script>
<?= $this->endSection() ?>