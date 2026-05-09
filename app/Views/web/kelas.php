<?php

/**
 * @var array<int, array<string, string>> $kelas
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Kelas</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6 h-fit">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Tambah Kelas Baru</h2>
            <form action="<?= base_url('admin/kelas/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: XII RPL 1" required>
                </div>
                <div class="mb-4">
                    <label for="wali_kelas" class="block text-sm font-medium text-gray-700 mb-2">Wali Kelas</label>
                    <input type="text" name="wali_kelas" id="wali_kelas" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nama Lengkap Wali Kelas">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                    Simpan Data
                </button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-left">No</th>
                            <th class="py-3 px-6 text-left">Nama Kelas</th>
                            <th class="py-3 px-6 text-left">Wali Kelas</th>
                            <th class="py-3 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php $no = 1;
                        foreach ($kelas as $k) : ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                    <span class="font-medium"><?= $no++ ?></span>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <span><?= esc($k['nama_kelas']) ?></span>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <span><?= esc($k['wali_kelas']) ?: '-' ?></span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        <form action="<?= base_url('admin/kelas/delete/' . $k['id_kelas']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn-confirm text-red-500 hover:text-red-700 transform hover:scale-110" data-text="Menghapus kelas ini juga akan menghapus data siswa di dalamnya. Yakin?" data-btn="Ya, Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                <td colspan="4" class="py-4 text-center text-gray-500">Belum ada data kelas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>