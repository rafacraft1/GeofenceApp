<?php

/**
 * @var string $title
 * @var array<int, array{id_jadwal: string|int, nama_hari: string, kode_hari: string|int, is_libur: string|int, jam_masuk: string|null, jam_pulang: string|null}> $daftar_jadwal
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-calendar-alt text-blue-600"></i> Manajemen Jadwal Harian
        </h2>
        <p class="text-sm text-gray-500 mt-1">Atur jam masuk dan pulang standar (Default Rule) untuk aplikasi absensi.</p>
    </div>
</div>

<form action="<?= base_url('admin/jadwal/update') ?>" method="POST" id="formJadwal" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative flex flex-col">
    <?= csrf_field() ?>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800 text-white font-bold uppercase text-[10px] tracking-widest">
                    <th class="px-6 py-4 w-1/4">Hari</th>
                    <th class="px-6 py-4 text-center w-1/4">Status Libur</th>
                    <th class="px-6 py-4 w-1/4">Jam Masuk</th>
                    <th class="px-6 py-4 w-1/4">Jam Pulang</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($daftar_jadwal as $hari) : ?>
                    <?php
                    $id = (string) $hari['id_jadwal'];
                    $isLibur = (int) $hari['is_libur'] === 1;

                    // Style dinamis jika hari libur maka baris akan meredup
                    $rowClass = $isLibur ? 'bg-red-50/50 opacity-70' : 'hover:bg-blue-50/30';
                    $textClass = $isLibur ? 'text-red-500' : 'text-gray-800';
                    ?>
                    <tr id="row_<?= $id ?>" class="transition-all duration-300 <?= $rowClass ?>">
                        <td class="px-6 py-4">
                            <span id="label_<?= $id ?>" class="font-bold uppercase tracking-wide text-sm transition-colors duration-300 <?= $textClass ?>">
                                <?= esc((string) $hari['nama_hari']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center relative">
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="checkbox" name="jadwal[<?= $id ?>][is_libur]" value="1" class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500 toggle-libur transition-all hover:scale-110" data-target="<?= $id ?>" <?= $isLibur ? 'checked' : '' ?>>
                                <span class="ml-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-red-500 transition-colors">Tandai Libur</span>
                            </label>
                        </td>
                        <td class="px-6 py-4">
                            <div class="relative">
                                <i class="far fa-clock absolute left-3 top-3 text-gray-400"></i>
                                <input type="time" id="masuk_<?= $id ?>" name="jadwal[<?= $id ?>][jam_masuk]" value="<?= esc((string) ($hari['jam_masuk'] ?? '')) ?>" class="w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm font-bold text-gray-700 transition-all disabled:opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed" <?= $isLibur ? 'disabled' : 'required' ?>>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="relative">
                                <i class="far fa-clock absolute left-3 top-3 text-gray-400"></i>
                                <input type="time" id="pulang_<?= $id ?>" name="jadwal[<?= $id ?>][jam_pulang]" value="<?= esc((string) ($hari['jam_pulang'] ?? '')) ?>" class="w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm font-bold text-gray-700 transition-all disabled:opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed" <?= $isLibur ? 'disabled' : 'required' ?>>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="p-5 bg-gray-50 border-t border-gray-100 flex justify-end shrink-0">
        <button type="button" onclick="konfirmasiJadwal()" class="btn-submit bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2 active:scale-95 min-w-[250px]">
            <i class="fas fa-save"></i>
            Simpan Jadwal Mingguan
        </button>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.toggle-libur');

        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target');
                const isChecked = this.checked;

                const row = document.getElementById('row_' + targetId);
                const label = document.getElementById('label_' + targetId);
                const inputMasuk = document.getElementById('masuk_' + targetId);
                const inputPulang = document.getElementById('pulang_' + targetId);

                if (isChecked) {
                    // Meredupkan Row & Menonaktifkan Input
                    row.classList.add('bg-red-50/50', 'opacity-70');
                    row.classList.remove('hover:bg-blue-50/30');

                    label.classList.add('text-red-500');
                    label.classList.remove('text-gray-800');

                    inputMasuk.disabled = true;
                    inputPulang.disabled = true;
                    inputMasuk.removeAttribute('required');
                    inputPulang.removeAttribute('required');

                    // Kosongkan nilai agar rapi
                    inputMasuk.value = '';
                    inputPulang.value = '';
                } else {
                    // Menghidupkan Row & Mewajibkan Input
                    row.classList.remove('bg-red-50/50', 'opacity-70');
                    row.classList.add('hover:bg-blue-50/30');

                    label.classList.remove('text-red-500');
                    label.classList.add('text-gray-800');

                    inputMasuk.disabled = false;
                    inputPulang.disabled = false;
                    inputMasuk.setAttribute('required', 'required');
                    inputPulang.setAttribute('required', 'required');
                }
            });
        });
    });

    // Fungsi Konfirmasi & AJAX Submit
    function konfirmasiJadwal() {
        // Validasi HTML5 Manual Form (Jika ada input wajib yang belum diisi)
        const form = document.getElementById('formJadwal');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Simpan Jadwal?',
            text: "Pastikan jam masuk dan jam pulang sudah logis dan sesuai.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                prosesSubmitJadwal(form);
            }
        });
    }

    function prosesSubmitJadwal(form) {
        const btn = form.querySelector('.btn-submit');
        const originalContent = btn.innerHTML;

        // Animasi Loading Tombol
        btn.classList.add('btn-loading');
        btn.innerHTML = 'Menyimpan...';
        btn.setAttribute('disabled', 'true');

        fetch(form.action, {
                method: 'POST',
                body: new window.FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Kembalikan tombol ke semula
                btn.classList.remove('btn-loading');
                btn.innerHTML = originalContent;
                btn.removeAttribute('disabled');

                if (data.status === 'success') {
                    toastr.success(data.message, 'Berhasil Disimpan!');
                } else {
                    toastr.error(data.message, 'Validasi Gagal!');
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                btn.classList.remove('btn-loading');
                btn.innerHTML = originalContent;
                btn.removeAttribute('disabled');
                toastr.error('Terjadi kesalahan jaringan atau server merespon buruk.', 'Koneksi Gagal');
            });
    }
</script>
<?= $this->endSection() ?>