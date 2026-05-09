<?php

/**
 * @var array $daftar_libur
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Manajemen Hari Libur</h2>
    <p class="text-sm text-gray-500 mt-1">Atur tanggal merah, libur nasional, dan kalender akademik sekolah.</p>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Tambah Hari Libur
            </h3>
            <form action="<?= base_url('admin/libur/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Libur</label>
                    <input type="date" name="tanggal" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan / Nama Libur</label>
                    <textarea name="keterangan" rows="3" required placeholder="Misal: Libur Semester Genap" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors flex justify-center items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Simpan Tanggal
                </button>
            </form>
        </div>
    </div>

    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-semibold text-gray-800">Daftar Hari Libur</h3>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">Total: <?= count($daftar_libur) ?> Hari</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($daftar_libur)) : ?>
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">Belum ada hari libur yang didaftarkan.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1;
                            foreach ($daftar_libur as $libur) : ?>
                                <?php
                                $date = new DateTime((string) $libur['tanggal']);
                                $tanggal_indo = $date->format('d-m-Y');
                                $is_passed = (new DateTime())->setTime(0, 0, 0) > $date;
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors <?= $is_passed ? 'opacity-60' : '' ?>">
                                    <td class="px-6 py-4 text-sm text-gray-600 font-medium"><?= $no++ ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded bg-<?= $is_passed ? 'gray' : 'red' ?>-100 flex items-center justify-center text-<?= $is_passed ? 'gray' : 'red' ?>-600 mr-3 font-bold text-xs"><?= $date->format('d') ?></div>
                                            <span class="text-sm font-medium text-gray-800"><?= $tanggal_indo ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= esc((string) $libur['keterangan']) ?>
                                        <?php if ($is_passed): ?><span class="ml-2 text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">Selesai</span><?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="<?= base_url('admin/libur/delete/' . (string) $libur['id_libur']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="inline-flex items-center justify-center p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors btn-confirm" data-text="Yakin ingin menghapus hari libur ini?" data-btn="Ya, Hapus" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>