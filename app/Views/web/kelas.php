<?php

/**
 * @var array<int, array<string, mixed>> $kelas
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Kelas</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6 h-fit border-t-4 border-blue-600">
            <h2 class="text-lg font-semibold mb-1 text-gray-700" id="form-title">Kelola Kelas</h2>
            <p class="text-xs text-gray-500 mb-4">Pilih kelas yang ada untuk update, atau ketik baru untuk tambah data.</p>

            <form action="<?= base_url('admin/kelas/store') ?>" method="POST" id="form-kelas">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas (Unik)</label>

                    <input type="text" name="nama_kelas" id="nama_kelas" list="existing-kelas" autocomplete="off" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ketik atau pilih kelas..." required>

                    <datalist id="existing-kelas">
                        <?php if (!empty($kelas)) : ?>
                            <?php foreach ($kelas as $k): ?>
                                <option value="<?= esc((string) $k['nama_kelas']) ?>">
                                <?php endforeach; ?>
                            <?php endif; ?>
                    </datalist>
                </div>

                <div class="mb-4">
                    <label for="wali_kelas" class="block text-sm font-medium text-gray-700 mb-2">Wali Kelas</label>
                    <input type="text" name="wali_kelas" id="wali_kelas" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Nama Lengkap Wali Kelas">
                </div>

                <div class="flex gap-2">
                    <button type="reset" onclick="resetForm()" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 px-4 rounded transition duration-200">
                        Reset
                    </button>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 shadow-lg shadow-blue-200">
                        Proses Data
                    </button>
                </div>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold">
                        <tr>
                            <th class="py-4 px-6 text-left">No</th>
                            <th class="py-4 px-6 text-left">Nama Kelas</th>
                            <th class="py-4 px-6 text-left">Wali Kelas</th>
                            <th class="py-4 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light divide-y divide-gray-100">
                        <?php $no = 1;
                        foreach ($kelas as $k) : ?>
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="py-3 px-6 text-left whitespace-nowrap font-medium"><?= $no++ ?></td>
                                <td class="py-3 px-6 text-left font-bold text-gray-800"><?= esc((string) $k['nama_kelas']) ?></td>
                                <td class="py-3 px-6 text-left"><?= esc((string) ($k['wali_kelas'] ?? '-')) ?></td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-3">
                                        <button type="button" onclick="fillForm('<?= esc((string) $k['nama_kelas'], 'js') ?>', '<?= esc((string) ($k['wali_kelas'] ?? ''), 'js') ?>')" class="text-blue-500 hover:text-blue-700 transition-transform hover:scale-110" title="Edit Data">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <form action="<?= base_url('admin/kelas/delete/' . (string) $k['id_kelas']) ?>" method="POST" id="form-delete-<?= (string) $k['id_kelas'] ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" onclick="konfirmasiHapusKelas('<?= (string) $k['id_kelas'] ?>', '<?= esc((string) $k['nama_kelas'], 'js') ?>')" class="text-red-400 hover:text-red-600 transition-transform hover:scale-110" title="Hapus Kelas">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($kelas)): ?>
                            <tr>
                                <td colspan="4" class="py-10 text-center text-gray-400">Tidak ada data kelas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function fillForm(nama, wali) {
        document.getElementById('nama_kelas').value = nama;
        document.getElementById('wali_kelas').value = wali;
        document.getElementById('form-title').innerText = 'Edit Kelas: ' + nama;
        document.getElementById('form-kelas').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetForm() {
        document.getElementById('form-title').innerText = 'Kelola Kelas';
    }

    function konfirmasiHapusKelas(id, namaKelas) {
        Swal.fire({
            title: 'Hapus ' + namaKelas + '?',
            html: "<div class='text-sm text-gray-600 mt-2 text-left space-y-2'>" +
                "<p class='text-red-600 font-bold'>⚠️ PERINGATAN!</p>" +
                "<p>Menghapus kelas ini juga akan <b>menghapus seluruh data siswa dan riwayat absensi</b> di dalamnya.</p>" +
                "</div>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Semuanya',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-delete-' + id).submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>