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
<style>
    /* Transisi mulus untuk Smart Merge Warning */
    .warning-collapsible {
        display: grid;
        grid-template-rows: 0fr;
        opacity: 0;
        transition: grid-template-rows 0.35s ease, opacity 0.3s ease, margin-top 0.3s ease;
        margin-top: 0;
    }
    .warning-collapsible.open {
        grid-template-rows: 1fr;
        opacity: 1;
        margin-top: 0.75rem;
    }
    .warning-collapsible > .warning-inner { overflow: hidden; }

    /* Custom scrollbar untuk list siswa */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Mutasi & Kenaikan Kelas</h2>
        <p class="text-sm text-gray-500 mt-1">Pindahkan siswa secara massal dengan perlindungan integritas rekap absensi.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- KOLOM KIRI: STEP 1 -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-fit sticky top-6">
            <h3 class="flex items-center gap-3 text-sm font-bold text-gray-800 mb-5 border-b border-gray-100 pb-3">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">1</span>
                Pilih Kelas Asal
            </h3>
            
            <form action="<?= base_url('admin/mutasi') ?>" method="GET" id="formPilihAsal">
                <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">Kelas Saat Ini</label>
                <select name="asal" onchange="document.getElementById('formPilihAsal').submit()" 
                        class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 cursor-pointer transition-all hover:bg-white">
                    <option value="">-- Silakan Pilih --</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?= esc((string) $k['id_kelas']) ?>" <?= ((string) $kelasAsalId === (string) $k['id_kelas']) ? 'selected' : '' ?>>
                            <?= esc((string) $k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($kelasAsalData): ?>
                <div class="mt-6 bg-gradient-to-br from-blue-50 to-indigo-50 p-5 rounded-xl border border-blue-100 shadow-sm">
                    <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-info-circle"></i> Informasi Kelas
                    </p>
                    <p class="text-xl font-black text-blue-900 mb-3"><?= esc((string) $kelasAsalData['nama_kelas']) ?></p>
                    
                    <div class="space-y-2">
                        <div class="flex items-start gap-2.5">
                            <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-user-tie text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-blue-400 uppercase">Wali Kelas</p>
                                <p class="text-xs font-bold text-blue-800 leading-tight"><?= esc((string) ($kelasAsalData['nama_wali'] ?? 'Belum Ditentukan')) ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <div class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-users text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-indigo-400 uppercase">Total Siswa</p>
                                <p class="text-xs font-bold text-indigo-800 leading-tight"><?= count($siswaAsal) ?> Orang</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- KOLOM KANAN: STEP 2 & 3 -->
    <div class="lg:col-span-2">
        <?php if (empty($kelasAsalId)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 flex flex-col items-center justify-center text-center h-full min-h-[350px]">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-5 text-gray-300 shadow-inner">
                    <i class="fas fa-exchange-alt text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-700">Belum Ada Kelas Asal</h3>
                <p class="text-sm text-gray-500 mt-2 max-w-md leading-relaxed">Pilih kelas asal terlebih dahulu dari panel di sebelah kiri untuk melihat daftar siswa dan mengatur tujuan mutasi.</p>
            </div>
        <?php elseif (empty($siswaAsal)): ?>
            <div class="bg-amber-50 rounded-2xl border border-amber-100 p-12 text-center text-amber-600 h-full min-h-[350px] flex flex-col justify-center items-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 text-amber-400 shadow-sm">
                    <i class="fas fa-box-open text-2xl"></i>
                </div>
                <p class="font-bold text-lg">Kelas ini kosong</p>
                <p class="text-sm mt-1 text-amber-700/70 max-w-sm">Tidak ada data siswa yang dapat dipindahkan dari kelas ini. Silakan pilih kelas lain.</p>
            </div>
        <?php else: ?>
            <form action="<?= base_url('admin/mutasi/proses') ?>" method="POST" id="formMutasi" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col min-h-full">
                <?= csrf_field() ?>
                <input type="hidden" name="kelas_asal" value="<?= esc((string) $kelasAsalId) ?>">

                <!-- STEP 2: SELEKSI SISWA -->
                <h3 class="flex justify-between items-center text-sm font-bold text-gray-800 mb-4 border-b border-gray-100 pb-3">
                    <span class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs shrink-0">2</span>
                        Seleksi Siswa
                    </span>
                    <label class="flex items-center gap-2 text-xs font-bold text-blue-700 cursor-pointer bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors border border-blue-100">
                        <input type="checkbox" id="checkAll" checked class="rounded border-blue-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                        Pilih Semua
                    </label>
                </h3>

                <!-- Live Search Inline -->
                <div class="relative mb-3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" id="searchSiswa" placeholder="Ketik nama atau NIS untuk mencari..." 
                           class="w-full border border-gray-200 rounded-xl py-2 pl-9 pr-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <div class="max-h-[280px] overflow-y-auto custom-scrollbar mb-8 pr-2 space-y-1.5" id="siswaListContainer">
                    <?php foreach ($siswaAsal as $siswa): 
                        // Inisial untuk avatar
                        $inisial = mb_strtoupper(mb_substr((string) ($siswa['nama_siswa'] ?? 'U'), 0, 1));
                    ?>
                        <label class="siswa-item flex items-center justify-between p-2.5 border border-transparent border-b-gray-50 hover:border-blue-100 hover:bg-blue-50/50 rounded-xl cursor-pointer transition-colors group">
                            <div class="flex items-center gap-3.5 w-full">
                                <input type="checkbox" name="siswa_ids[]" value="<?= esc((string) $siswa['id_siswa']) ?>" checked 
                                       class="siswa-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4.5 h-4.5 cursor-pointer ml-1">
                                <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0 shadow-sm group-hover:bg-white group-hover:text-blue-600 transition-colors">
                                    <?= $inisial ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="siswa-nama text-sm font-bold text-gray-800 truncate group-hover:text-blue-700 transition-colors"><?= esc((string) $siswa['nama_siswa']) ?></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <p class="siswa-nis text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded inline-block">NIS: <?= esc((string) $siswa['nis']) ?></p>
                                        <?php if (!empty($siswa['is_blocked'])): ?>
                                            <span class="text-[9px] font-black text-white bg-red-500 px-1.5 py-0.5 rounded">BLOCKED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                    
                    <!-- Empty state untuk pencarian -->
                    <div id="emptySearch" class="hidden py-8 text-center text-gray-400">
                        <i class="fas fa-search-minus text-2xl mb-2"></i>
                        <p class="text-xs font-medium">Siswa tidak ditemukan.</p>
                    </div>
                </div>

                <div class="mt-auto">
                    <!-- STEP 3: DESTINASI -->
                    <h3 class="flex items-center gap-3 text-sm font-bold text-gray-800 mb-4 border-b border-gray-100 pb-3">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs shrink-0">3</span>
                        Konfigurasi Tujuan
                    </h3>
                    
                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 mb-6 relative overflow-hidden">
                        <!-- Dekorasi background -->
                        <div class="absolute -right-4 -bottom-4 opacity-5 pointer-events-none">
                            <i class="fas fa-random text-6xl"></i>
                        </div>

                        <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">Pindah Menuju Kelas:</label>
                        <select name="kelas_tujuan" id="kelas_tujuan" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer shadow-sm relative z-10" required>
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            <?php foreach ($listKelas as $k): ?>
                                <?php if ($k['id_kelas'] != $kelasAsalId): ?>
                                    <option value="<?= esc((string) $k['id_kelas']) ?>"><?= esc((string) $k['nama_kelas']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>

                        <!-- Smart Merge Warning (Animated) -->
                        <div class="warning-collapsible" id="mergeWarningWrapper">
                            <div class="warning-inner" id="mergeWarning"></div>
                        </div>

                        <!-- Opsi Pindah Wali Kelas -->
                        <label id="lbl-pindah-wali" class="mt-4 flex items-start gap-3 cursor-pointer p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 transition-all shadow-sm relative z-10 group">
                            <div class="relative flex items-center h-5 mt-0.5">
                                <input type="checkbox" name="pindah_wali" id="chk-pindah-wali" value="1" class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border-2 border-gray-300 checked:border-blue-600 checked:bg-blue-600 transition-all">
                                <svg class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" viewBox="0 0 14 10" fill="none">
                                    <path d="M1 5L4.5 8.5L13 1" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 group-hover:text-blue-700 transition-colors">Pindahkan juga Wali Kelas ke kelas tujuan?</p>
                                <p class="text-[10px] text-gray-500 mt-1 leading-relaxed">Jika dicentang, <span class="font-bold text-gray-700"><?= esc((string) ($kelasAsalData['nama_wali'] ?? 'Wali Kelas')) ?></span> akan dicabut dari kelas saat ini dan otomatis menjadi wali di kelas tujuan.</p>
                            </div>
                        </label>
                    </div>

                    <button type="button" onclick="konfirmasiMutasi()" class="w-full bg-blue-600 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-blue-600/30 hover:bg-blue-700 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-600/40 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-exchange-alt"></i>
                        Eksekusi Mutasi Massal
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- 1. Logika Check All Siswa ---
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.siswa-checkbox');
        const countVisibleCheckboxes = () => document.querySelectorAll('.siswa-item:not(.hidden) .siswa-checkbox').length;
        const countCheckedVisible = () => document.querySelectorAll('.siswa-item:not(.hidden) .siswa-checkbox:checked').length;

        function updateCheckAllState() {
            if(!checkAll) return;
            const total = countVisibleCheckboxes();
            const checked = countCheckedVisible();
            
            if (total === 0) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            } else {
                checkAll.checked = (total === checked);
                checkAll.indeterminate = (checked > 0 && checked < total);
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                // Hanya centang yang visible dari hasil filter pencarian
                document.querySelectorAll('.siswa-item:not(.hidden) .siswa-checkbox').forEach(cb => {
                    cb.checked = checkAll.checked;
                });
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateCheckAllState);
            });
        }

        // --- 2. Live Search Filter Siswa ---
        const searchInput = document.getElementById('searchSiswa');
        if(searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const items = document.querySelectorAll('.siswa-item');
                let found = false;

                items.forEach(item => {
                    const nama = item.querySelector('.siswa-nama').innerText.toLowerCase();
                    const nis = item.querySelector('.siswa-nis').innerText.toLowerCase();
                    
                    if (nama.includes(term) || nis.includes(term)) {
                        item.classList.remove('hidden');
                        found = true;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                document.getElementById('emptySearch').classList.toggle('hidden', found);
                updateCheckAllState(); // Update checkbox "Pilih Semua" saat list berubah
            });
        }


        // --- 3. Styling State Opsi Wali Kelas ---
        const chkWali = document.getElementById('chk-pindah-wali');
        const lblWali = document.getElementById('lbl-pindah-wali');
        if(chkWali && lblWali) {
            chkWali.addEventListener('change', function() {
                if (this.checked) {
                    lblWali.classList.remove('border-gray-200', 'bg-white');
                    lblWali.classList.add('border-blue-400', 'bg-blue-50/50');
                } else {
                    lblWali.classList.add('border-gray-200', 'bg-white');
                    lblWali.classList.remove('border-blue-400', 'bg-blue-50/50');
                }
            });
        }


        // --- 4. Logika Smart Merge Warning (AJAX) ---
        const kelasTujuan = document.getElementById('kelas_tujuan');
        const warningWrapper = document.getElementById('mergeWarningWrapper');
        const warningDiv = document.getElementById('mergeWarning');

        if (kelasTujuan) {
            kelasTujuan.addEventListener('change', function() {
                const id = this.value;
                if (!id) {
                    warningWrapper.classList.remove('open');
                    return;
                }

                // Call AJAX
                fetch(`<?= base_url('admin/mutasi/checkTujuan/') ?>${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 200) {
                            if (data.jumlah > 0) {
                                warningDiv.innerHTML = `
                                    <div class="flex gap-3.5 p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 shadow-sm">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                            <i class="fas fa-exclamation-triangle text-amber-500 text-sm"></i>
                                        </div>
                                        <div class="text-[11px] leading-relaxed pt-0.5">
                                            <strong class="block mb-1 text-xs text-amber-700 tracking-wide">SMART MERGE WARNING</strong>
                                            Kelas tujuan ini sudah memiliki <b>${data.jumlah} Siswa</b> (Siswa tinggal kelas). <br>
                                            Siswa yang dipindahkan akan digabungkan. Wali kelas saat ini: <b>${data.nama_wali}</b>.
                                        </div>
                                    </div>
                                `;
                            } else {
                                warningDiv.innerHTML = `
                                    <div class="flex gap-3.5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 shadow-sm">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                                            <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                                        </div>
                                        <div class="text-[11px] leading-relaxed pt-0.5">
                                            <strong class="block mb-1 text-xs text-emerald-700 tracking-wide">KELAS KOSONG & AMAN</strong>
                                            Kelas tujuan ini belum memiliki siswa sama sekali. Aman untuk dieksekusi.
                                        </div>
                                    </div>
                                `;
                            }
                            warningWrapper.classList.add('open');
                        }
                    })
                    .catch(() => {
                        warningWrapper.classList.remove('open');
                    });
            });
        }
    });

    function konfirmasiMutasi() {
        const checkboxes = document.querySelectorAll('.siswa-checkbox:checked');
        const checkedCount = checkboxes.length;
        const tujuan = document.getElementById('kelas_tujuan');
        const textTujuan = tujuan.options[tujuan.selectedIndex].text;

        if (checkedCount === 0) {
            Swal.fire({
                title: 'Validasi',
                text: 'Pilih minimal satu siswa yang akan dipindahkan.',
                icon: 'warning',
                confirmButtonColor: '#3b82f6',
                customClass: { popup: 'rounded-2xl' }
            });
            return;
        }
        if (!tujuan.value) {
            Swal.fire({
                title: 'Validasi',
                text: 'Kelas tujuan wajib dipilih.',
                icon: 'warning',
                confirmButtonColor: '#3b82f6',
                customClass: { popup: 'rounded-2xl' }
            });
            return;
        }

        Swal.fire({
            title: 'Eksekusi Mutasi Massal?',
            html: `Anda akan memindahkan <b>${checkedCount} Siswa</b> menuju kelas <b>${textTujuan}</b>.<br><br><span class="text-xs text-red-500 font-bold bg-red-50 px-3 py-2 rounded-lg inline-block border border-red-100">Pastikan data yang dipilih sudah benar!</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Pindahkan Sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-lg shadow-blue-500/30',
                cancelButton: 'rounded-xl font-bold px-5 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading visual saat proses submit
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang memindahkan data siswa dan absensi',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });
                document.getElementById('formMutasi').submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>