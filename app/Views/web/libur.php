<?php

/**
 * @var string $title
 * @var array<int, array<string, mixed>> $daftar_libur
 * @var string|null $pager_links
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    .btn-loading {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }
    .btn-loading::after {
        content: '';
        position: absolute;
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
        left: calc(50% - 0.625rem);
        top: calc(50% - 0.625rem);
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Manajemen Hari Libur</h2>
        <p class="text-sm text-gray-500 mt-1">Atur tanggal merah, libur nasional, dan kalender akademik sekolah.</p>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    <!-- KOLOM KIRI: FORM TAMBAH -->
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2.5 pb-3 border-b border-gray-50">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-calendar-plus text-sm"></i>
                </div>
                Tambah Libur Baru
            </h3>
            
            <form action="<?= base_url('admin/libur/store') ?>" method="POST" id="formLibur">
                <?= csrf_field() ?>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Pilih Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-day text-gray-400"></i>
                        </div>
                        <input type="date" name="tanggal" value="<?= old('tanggal') ?>" required class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 hover:bg-white text-gray-700">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Cakupan / Tipe Libur</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-layer-group text-gray-400"></i>
                        </div>
                        <select name="tipe_libur" required class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 hover:bg-white cursor-pointer text-gray-700 appearance-none">
                            <option value="Nasional">🌍 Nasional (Termasuk PKL)</option>
                            <option value="Internal">🏫 Internal (PKL Tetap Masuk)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="mb-7">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Keterangan Libur</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-tag text-gray-400"></i>
                        </div>
                        <input type="text" name="keterangan" value="<?= esc(old('keterangan')) ?>" required placeholder="Misal: Hari Raya Idul Fitri" class="w-full border border-gray-200 rounded-xl py-3 pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 hover:bg-white text-gray-700">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3.5 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg shadow-blue-500/30 btn-submit flex items-center justify-center gap-2 hover:-translate-y-0.5">
                    <i class="fa-solid fa-save"></i> Simpan ke Kalender
                </button>
            </form>
        </div>
    </div>

    <!-- KOLOM KANAN: TABEL DAFTAR -->
    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full min-h-[500px]">
            <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider"><i class="fas fa-list-ul mr-2 text-gray-400"></i> Daftar Tanggal Merah</h3>
            </div>
            
            <div class="overflow-x-auto flex-1 custom-scrollbar">
                <table class="w-full text-left">
                    <tbody class="divide-y divide-gray-50">
                        <?php if (!empty($daftar_libur)): ?>
                            <?php foreach ($daftar_libur as $libur): 
                                $tgl = strtotime((string) $libur['tanggal']);
                                $isPassed = $tgl < strtotime(date('Y-m-d'));
                                $rowClass = $isPassed ? 'opacity-60 bg-gray-50/30 hover:opacity-100 transition-opacity' : 'hover:bg-blue-50/20 transition-colors bg-white';
                                
                                $bulanEn = date('M', $tgl);
                                // Translate bulan singkat ke ID (opsional)
                                $bulanIdMap = ['Jan'=>'JAN', 'Feb'=>'FEB', 'Mar'=>'MAR', 'Apr'=>'APR', 'May'=>'MEI', 'Jun'=>'JUN', 'Jul'=>'JUL', 'Aug'=>'AGU', 'Sep'=>'SEP', 'Oct'=>'OKT', 'Nov'=>'NOV', 'Dec'=>'DES'];
                                $bulanId = $bulanIdMap[$bulanEn] ?? strtoupper($bulanEn);
                            ?>
                                <tr class="<?= $rowClass ?> group">
                                    <td class="px-5 py-4 w-56">
                                        <div class="flex items-center gap-4">
                                            <!-- Mini Calendar Sheet -->
                                            <div class="w-12 h-13 rounded-xl border <?= $isPassed ? 'border-gray-200' : 'border-red-100 shadow-sm shadow-red-500/10' ?> bg-white flex flex-col overflow-hidden shrink-0">
                                                <div class="<?= $isPassed ? 'bg-gray-400' : 'bg-red-500' ?> text-white text-[9px] font-black tracking-widest text-center py-1 uppercase leading-none"><?= $bulanId ?></div>
                                                <div class="flex-1 flex items-center justify-center text-lg font-black <?= $isPassed ? 'text-gray-500' : 'text-gray-800' ?>"><?= date('d', $tgl) ?></div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-800 text-sm whitespace-nowrap"><?= date('d F Y', $tgl) ?></div>
                                                <?php if ($isPassed): ?>
                                                    <span class="inline-block mt-1 text-[9px] font-black text-gray-400 border border-gray-200 bg-white px-2 py-0.5 rounded-md uppercase tracking-widest"><i class="fas fa-history mr-1"></i> Berlalu</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-800 text-sm mb-1.5"><?= esc((string) $libur['keterangan']) ?></div>
                                        
                                        <?php if (($libur['tipe_libur'] ?? 'Nasional') === 'Nasional'): ?>
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-md uppercase tracking-wider">
                                                <i class="fas fa-globe-asia"></i> Nasional
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-orange-600 bg-orange-50 border border-orange-200 px-2.5 py-1 rounded-md uppercase tracking-wider">
                                                <i class="fas fa-school"></i> Internal
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <form action="<?= base_url('admin/libur/delete/' . (string) $libur['id_libur']) ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="inline-flex items-center justify-center w-9 h-9 text-gray-400 hover:text-red-600 bg-white hover:bg-red-50 border border-gray-100 hover:border-red-100 rounded-xl transition-all btn-confirm shadow-sm hover:shadow-md" data-text="Yakin ingin menghapus hari libur ini dari kalender?" data-btn="Ya, Hapus" title="Hapus Libur">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-24 text-center">
                                    <div class="w-20 h-20 bg-gray-50 border border-gray-100 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                        <i class="fa-solid fa-calendar-xmark text-3xl"></i>
                                    </div>
                                    <h3 class="text-gray-800 font-bold text-lg mb-1">Kalender Kosong</h3>
                                    <p class="text-gray-500 text-sm max-w-sm mx-auto">Belum ada hari libur yang didaftarkan. Gunakan form di sebelah kiri untuk menambah tanggal merah.</p>
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