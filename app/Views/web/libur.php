<?php

/**
 * @var string $title
 * @var array<int, array<string, mixed>> $daftar_libur
 * @var string|null $pager_links
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Manajemen Hari Libur</h2>
        <p class="text-sm text-gray-500 mt-1">Atur tanggal merah, libur nasional, dan kalender akademik sekolah.</p>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Tambah Hari Libur
            </h3>
            <form action="<?= base_url('admin/libur/store') ?>" method="POST" id="formLibur">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Pilih Tanggal</label>
                    <input type="date" name="tanggal" value="<?= old('tanggal') ?>" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Keterangan Libur</label>
                    <input type="text" name="keterangan" value="<?= esc(old('keterangan')) ?>" required placeholder="Contoh: Hari Raya Idul Fitri" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 btn-submit flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save"></i> Simpan Hari Libur
                </button>
            </form>
        </div>
    </div>

    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-4">Tanggal Libur</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($daftar_libur)): ?>
                            <?php foreach ($daftar_libur as $libur): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-800"><?= date('d F Y', strtotime((string) $libur['tanggal'])) ?></div>
                                        <?php if (!empty($libur['created_at'])): ?>
                                            <div class="text-[10px] text-gray-400 mt-0.5">Dibuat: <?= date('d M Y', strtotime((string) $libur['created_at'])) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-700 font-medium"><?= esc((string) $libur['keterangan']) ?></span>
                                        <?php if ($libur['tanggal'] < date('Y-m-d')): ?>
                                            <span class="ml-2 text-[10px] font-bold text-gray-400 border border-gray-200 bg-gray-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Telah Berlalu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="<?= base_url('admin/libur/delete/' . (string) $libur['id_libur']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="inline-flex items-center justify-center w-8 h-8 text-red-500 hover:text-white bg-red-50 hover:bg-red-500 border border-red-100 rounded-lg transition-all btn-confirm shadow-sm" data-text="Yakin ingin menghapus hari libur ini?" data-btn="Ya, Hapus" title="Hapus">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-16 text-center">
                                    <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fa-solid fa-calendar-xmark text-2xl"></i>
                                    </div>
                                    <h3 class="text-gray-800 font-bold text-lg">Kosong</h3>
                                    <p class="text-gray-500 text-sm mt-1">Belum ada kalender hari libur yang didaftarkan.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager_links)) : ?>
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    <?= $pager_links ?>
                </div>
            <?php endif; ?>
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