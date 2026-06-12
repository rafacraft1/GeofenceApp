<?php

/**
 * @var array<int, array<string, mixed>> $daftar_libur
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
            <form action="<?= base_url('admin/libur/store') ?>" method="POST" id="formLibur">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Tanggal</label>
                    <input type="date" name="tanggal" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Keterangan Libur</label>
                    <input type="text" name="keterangan" required placeholder="Contoh: Hari Raya Idul Fitri" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors shadow-md btn-submit">Simpan Hari Libur</button>
            </form>
        </div>
    </div>

    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($daftar_libur)): ?>
                            <?php foreach ($daftar_libur as $libur): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-800"><?= date('d F Y', strtotime((string) $libur['tanggal'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600"><?= esc((string) $libur['keterangan']) ?></span>
                                        <?php if ($libur['tanggal'] < date('Y-m-d')): ?><span class="ml-2 text-[10px] font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Selesai</span><?php endif; ?>
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
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-10 text-center text-gray-400 italic">Belum ada data hari libur.</td>
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
<script>
    document.getElementById('formLibur').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>