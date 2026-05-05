<?php

/**
 * @var array $daftar_jadwal
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Manajemen Jadwal Harian</h2>
    <p class="text-sm text-gray-500 mt-1">Atur jam masuk dan jam pulang standar untuk setiap hari dalam seminggu.</p>
</div>

<form action="<?= base_url('admin/jadwal/update') ?>" method="POST" id="formJadwal">
    <?= csrf_field() ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Hari</th>
                        <th class="px-6 py-4 text-center">Status Libur</th>
                        <th class="px-6 py-4">Jam Masuk</th>
                        <th class="px-6 py-4">Jam Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($daftar_jadwal as $hari) : ?>
                        <?php
                        $id = (string) $hari['id_jadwal'];
                        $isLibur = $hari['is_libur'] == 1;
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <!-- Nama Hari -->
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800 <?= $isLibur ? 'text-red-500' : '' ?>">
                                    <?= esc((string) $hari['nama_hari']) ?>
                                </span>
                            </td>

                            <!-- Checkbox Libur -->
                            <td class="px-6 py-4 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                        name="jadwal[<?= $id ?>][is_libur]"
                                        value="1"
                                        class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 toggle-libur"
                                        data-target="<?= $id ?>"
                                        <?= $isLibur ? 'checked' : '' ?>>
                                </label>
                            </td>

                            <!-- Jam Masuk -->
                            <td class="px-6 py-4">
                                <input type="time"
                                    id="masuk_<?= $id ?>"
                                    name="jadwal[<?= $id ?>][jam_masuk]"
                                    value="<?= esc((string) $hari['jam_masuk']) ?>"
                                    class="px-3 py-2 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
                                    <?= $isLibur ? 'disabled' : 'required' ?>>
                            </td>

                            <!-- Jam Pulang -->
                            <td class="px-6 py-4">
                                <input type="time"
                                    id="pulang_<?= $id ?>"
                                    name="jadwal[<?= $id ?>][jam_pulang]"
                                    value="<?= esc((string) $hari['jam_pulang']) ?>"
                                    class="px-3 py-2 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
                                    <?= $isLibur ? 'disabled' : 'required' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-5 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button type="button" class="btn-confirm bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-all flex items-center" data-text="Pastikan jadwal yang Anda masukkan sudah benar." data-btn="Ya, Simpan Jadwal">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                Simpan Jadwal Mingguan
            </button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logika untuk mendisable input jam jika hari ditandai Libur
        const toggles = document.querySelectorAll('.toggle-libur');
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target');
                const isChecked = this.checked;

                const inputMasuk = document.getElementById('masuk_' + targetId);
                const inputPulang = document.getElementById('pulang_' + targetId);

                if (isChecked) {
                    inputMasuk.disabled = true;
                    inputPulang.disabled = true;
                    inputMasuk.removeAttribute('required');
                    inputPulang.removeAttribute('required');
                } else {
                    inputMasuk.disabled = false;
                    inputPulang.disabled = false;
                    inputMasuk.setAttribute('required', 'required');
                    inputPulang.setAttribute('required', 'required');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>