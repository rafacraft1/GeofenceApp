<?php

/**
 * @var array<string, mixed> $pengaturan
 */

$envFirebaseUrl = env('FIREBASE_DATABASE_URL') ?: env('FIREBASE_URL');
$isFirebaseEmpty = empty($envFirebaseUrl);
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    /* CSS Spinner Loading untuk Tombol Submit */
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
</style>

<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row justify-between md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">Pengaturan Sistem</h2>
            <p class="text-sm text-gray-500 mt-1 font-medium">Kelola identitas sistem, konfigurasi dasar, dan kontrol versi pembaruan APK.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-10 flex flex-col relative overflow-hidden">
        
        <!-- Dekorasi Latar Belakang -->
        <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-bl from-blue-50/50 to-transparent rounded-bl-full opacity-60 pointer-events-none"></div>

        <!-- Indikator Firebase -->
        <div class="absolute top-6 right-6 z-10 pointer-events-none">
            <?php if ($isFirebaseEmpty): ?>
                <div class="bg-red-50 border border-red-100 px-3 py-2 flex items-center gap-2 rounded-xl shadow-sm backdrop-blur-sm bg-opacity-80" title="Silakan konfigurasi Firebase di file .env Anda">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-red-600 tracking-wider uppercase">Firebase Kosong</span>
                </div>
            <?php else: ?>
                <div class="bg-emerald-50 border border-emerald-100 px-3 py-2 flex items-center gap-2 rounded-xl shadow-sm backdrop-blur-sm bg-opacity-80">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-emerald-600 tracking-wider uppercase">Firebase Aktif</span>
                </div>
            <?php endif; ?>
        </div>

        <form action="<?= base_url('admin/pengaturan/save') ?>" method="POST" id="formPengaturan" class="space-y-8 relative z-10">
            <?= csrf_field() ?>

            <!-- BAGIAN IDENTITAS APLIKASI -->
            <div>
                <div class="flex items-center gap-3 mb-5 border-b border-gray-50 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-id-card text-sm"></i>
                    </div>
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Identitas Instansi</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Aplikasi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <input type="text" name="nama_aplikasi" value="<?= esc((string) ($pengaturan['nama_aplikasi'] ?? 'GeofenceApp')) ?>" class="w-full bg-gray-50 hover:bg-white border border-gray-200 rounded-xl py-3.5 pl-10 pr-4 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all shadow-sm" required placeholder="Cth: GeofenceApp">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Sekolah / Instansi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-school"></i>
                            </div>
                            <input type="text" name="nama_sekolah" value="<?= esc((string) ($pengaturan['nama_sekolah'] ?? 'SMKN 1 TGB')) ?>" class="w-full bg-gray-50 hover:bg-white border border-gray-200 rounded-xl py-3.5 pl-10 pr-4 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all shadow-sm" required placeholder="Cth: Nama Instansi Anda">
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN KONTROL VERSI APK -->
            <div class="pt-2">
                <div class="flex items-center gap-3 mb-5 border-b border-gray-50 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-cloud-download-alt text-sm"></i>
                    </div>
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Kontrol Pembaruan (Force Update)</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Versi Aplikasi Wajib (App Version)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-code-branch"></i>
                            </div>
                            <input type="text" name="app_version" value="<?= esc((string) ($pengaturan['app_version'] ?? '1.0.0')) ?>" class="w-full bg-gray-50 hover:bg-white border border-gray-200 rounded-xl py-3.5 pl-10 pr-4 text-sm font-black text-gray-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all shadow-sm font-mono tracking-widest" required placeholder="Cth: 1.0.0">
                        </div>
                        <p class="text-[10px] font-medium text-gray-400 mt-2 leading-relaxed bg-gray-50/50 px-2 py-1.5 rounded-lg border border-gray-100"><i class="fas fa-info-circle mr-1 text-blue-400"></i> Jika versi aplikasi di HP siswa berada di bawah versi ini, siswa diwajibkan untuk mengunduh versi terbaru.</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Tautan Unduhan (Link Download APK)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-link"></i>
                            </div>
                            <input type="url" name="app_link" value="<?= esc((string) ($pengaturan['app_link'] ?? '')) ?>" class="w-full bg-gray-50 hover:bg-white border border-gray-200 rounded-xl py-3.5 pl-10 pr-4 text-sm font-medium text-blue-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all shadow-sm" placeholder="https:// ...">
                        </div>
                        <p class="text-[10px] font-medium text-gray-400 mt-2 leading-relaxed bg-gray-50/50 px-2 py-1.5 rounded-lg border border-gray-100"><i class="fas fa-info-circle mr-1 text-blue-400"></i> Tautan bebas dari G-Drive atau Github tempat Anda menyimpan file .apk.</p>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTON -->
            <div class="pt-6 border-t border-gray-100 mt-8 flex justify-end">
                <button type="submit" class="w-full md:w-auto md:px-10 flex justify-center items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition-all btn-submit group">
                    <i class="fas fa-save group-hover:-translate-y-0.5 transition-transform"></i>
                    Simpan Konfigurasi Sistem
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
            btn.innerHTML = 'Menyimpan...';
            btn.classList.add('btn-loading', 'cursor-not-allowed', 'opacity-90');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>