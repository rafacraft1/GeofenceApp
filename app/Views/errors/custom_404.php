<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="flex flex-col items-center justify-center min-h-[70vh] px-4 text-center">
    <div class="relative mb-6">
        <div class="absolute inset-0 bg-blue-100 rounded-full blur-xl opacity-50 scale-150 animate-pulse"></div>
        <div class="relative flex items-center justify-center w-24 h-24 bg-blue-50 border border-blue-100 text-blue-600 rounded-2xl shadow-sm">
            <i class="fa-solid fa-compass-slash text-4xl animate-bounce"></i>
        </div>
    </div>

    <h1 class="text-6xl font-black text-slate-900 tracking-tight mb-2">404</h1>
    <h3 class="text-xl font-bold text-slate-800 mb-3">Halaman Tidak Ditemukan</h3>
    <p class="text-sm text-slate-500 max-w-md mb-8 leading-relaxed">
        Maaf, tautan atau halaman URL yang Anda tuju tidak valid, telah dihapus, atau Anda tidak memiliki akses ke rute tersebut.
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center w-full sm:w-auto">
        <a href="<?= base_url('admin/dashboard') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fa-solid fa-house text-xs"></i>
            Kembali ke Dashboard
        </a>
        <button onclick="window.history.back()" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white hover:bg-gray-50 text-slate-700 border border-gray-200 rounded-lg font-medium text-sm transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Halaman Sebelumnya
        </button>
    </div>
</div>
<?= $this->endSection() ?>