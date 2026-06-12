<?php

/**
 * @var array<int, array<string, mixed>> $jadwal
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan Hari Aktif Global</h2>
            <p class="text-sm text-gray-500 mt-1">Tentukan libur akhir pekan. Jam masuk & pulang diatur pada menu Zona Absensi.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
        <form action="<?= base_url('admin/jadwal/update') ?>" method="POST" id="formJadwal">
            <?= csrf_field() ?>

            <div class="bg-blue-50 text-blue-700 text-xs font-medium p-4 rounded-xl border border-blue-100 mb-6 flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p>Jika hari ditandai sebagai <b>"Libur"</b>, sistem akan menolak semua permintaan absensi masuk maupun pulang pada hari tersebut.</p>
            </div>

            <div class="space-y-3">
                <?php foreach ($jadwal as $j): ?>
                    <?php
                    $cardClass  = $j['is_libur'] ? 'bg-slate-50 border-gray-200 opacity-60' : 'bg-white border-emerald-200 shadow-sm';
                    $iconClass  = $j['is_libur'] ? 'bg-slate-200 text-slate-500' : 'bg-emerald-100 text-emerald-600';
                    $textClass  = $j['is_libur'] ? 'text-red-500' : 'text-emerald-500';
                    $statusText = $j['is_libur'] ? 'Sistem Absensi Terkunci (Akhir Pekan/Libur)' : 'Hari Efektif Aktif';
                    ?>
                    <label class="flex items-center justify-between p-4 rounded-2xl border <?= $cardClass ?> cursor-pointer hover:bg-gray-50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm <?= $iconClass ?>">
                                <?= substr((string)$j['nama_hari'], 0, 3) ?>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-base"><?= esc((string)$j['nama_hari']) ?></p>
                                <p class="text-[11px] font-semibold <?= $textClass ?>"><?= $statusText ?></p>
                            </div>
                        </div>

                        <div class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="is_libur[]" value="<?= $j['kode_hari'] ?>" class="sr-only peer" <?= $j['is_libur'] ? 'checked' : '' ?>>
                            <div class="w-14 h-7 bg-emerald-500 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-slate-300 shadow-inner"></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="w-full md:w-auto px-8 flex justify-center items-center gap-2 bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 active:scale-95 transition-all btn-submit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('formJadwal').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'true');
        }
    });

    document.querySelectorAll('input[type="checkbox"]').forEach(chk => {
        chk.addEventListener('change', function() {
            const card = this.closest('label');
            const icon = card.querySelector('.w-10');
            const statusText = card.querySelector('p.text-\\[11px\\]');

            if (this.checked) {
                card.className = "flex items-center justify-between p-4 rounded-2xl border bg-slate-50 border-gray-200 opacity-60 cursor-pointer hover:bg-gray-50 transition-all group";
                icon.className = "w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm bg-slate-200 text-slate-500";
                statusText.className = "text-[11px] font-semibold text-red-500";
                statusText.innerText = "Sistem Absensi Terkunci (Akhir Pekan/Libur)";
            } else {
                card.className = "flex items-center justify-between p-4 rounded-2xl border bg-white border-emerald-200 shadow-sm cursor-pointer hover:bg-gray-50 transition-all group";
                icon.className = "w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm bg-emerald-100 text-emerald-600";
                statusText.className = "text-[11px] font-semibold text-emerald-500";
                statusText.innerText = "Hari Efektif Aktif";
            }
        });
    });
</script>
<?= $this->endSection() ?>