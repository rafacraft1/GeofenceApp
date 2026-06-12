<?php

/**
 * @var array<string, mixed> $pengaturan
 */

$envFirebaseUrl = env('FIREBASE_DATABASE_URL') ?: env('FIREBASE_URL');
$isFirebaseEmpty = empty($envFirebaseUrl);
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan Sistem</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola identitas aplikasi dan konfigurasi server Firebase.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-10 flex flex-col relative overflow-hidden">

        <div class="absolute top-6 right-6 z-10 pointer-events-none">
            <?php if ($isFirebaseEmpty): ?>
                <div class="bg-red-50 border border-red-100 px-3 py-2 flex items-center gap-2 rounded-xl shadow-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-red-600 tracking-wide uppercase">Firebase Kosong</span>
                </div>
            <?php else: ?>
                <div class="bg-emerald-50 border border-emerald-100 px-3 py-2 flex items-center gap-2 rounded-xl shadow-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-emerald-600 tracking-wide uppercase">Firebase Aktif</span>
                </div>
            <?php endif; ?>
        </div>

        <form action="<?= base_url('admin/pengaturan/save') ?>" method="POST" id="formPengaturan" class="space-y-6">
            <?= csrf_field() ?>

            <div class="space-y-5 mt-2">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Aplikasi</label>
                    <input type="text" name="nama_aplikasi" value="<?= esc((string) ($pengaturan['nama_aplikasi'] ?? 'GeofenceApp')) ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all" required placeholder="Cth: GeofenceApp">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Sekolah / Instansi</label>
                    <input type="text" name="nama_sekolah" value="<?= esc((string) ($pengaturan['nama_sekolah'] ?? 'SMKN 1 TGB')) ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all" required placeholder="Cth: Nama Instansi Anda">
                </div>
            </div>

            <div class="w-full h-px bg-gray-100 my-6"></div>

            <div class="bg-indigo-50/50 border border-indigo-50 rounded-2xl p-5">
                <label class="block text-xs font-bold text-indigo-800 uppercase mb-2">URL Database Firebase (Opsional)</label>
                <input type="text" name="firebase_url" value="<?= esc((string) ($pengaturan['firebase_url'] ?? '')) ?>" placeholder="https://project-id.firebaseio.com" class="w-full bg-white border border-indigo-100 rounded-xl p-3 text-sm text-gray-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all">
                <p class="text-[10px] font-medium text-indigo-400 mt-2 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    URL ini dibutuhkan untuk mengaktifkan fitur Live Radar Tracker secara Real-time.
                </p>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full md:w-auto md:px-10 flex justify-center items-center gap-2 bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 active:scale-95 transition-all btn-submit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Identitas Sistem
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('formPengaturan').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>