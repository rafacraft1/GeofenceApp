<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Panel' ?> - Sistem Absensi</title>

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        /* Custom Spinner untuk Micro-interactions */
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Logic Hide Sidebar Desktop */
        @media (min-width: 768px) {
            .sidebar-collapsed {
                margin-left: -16rem !important;
                /* -256px (w-64) */
            }
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased flex h-screen overflow-hidden">

    <?php
    $uri = service('uri');
    $segment = $uri->getSegment(2);
    ?>

    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

    <aside id="sidebar" class="bg-slate-900 text-slate-300 w-64 flex-shrink-0 fixed md:relative inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out z-50 flex flex-col shadow-2xl">

        <div class="h-16 flex items-center justify-between md:justify-center px-4 md:px-0 font-bold text-xl text-white border-b border-slate-800 bg-slate-950">
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span>Geofence<span class="text-blue-500">App</span></span>
            </div>
            <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
            <a href="/admin/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $segment == 'dashboard' ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                <span class="font-medium text-sm">Dashboard</span>
            </a>
            <a href="/admin/siswa" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $segment == 'siswa' ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="font-medium text-sm">Data Siswa</span>
            </a>
            <a href="/admin/absensi" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $segment == 'absensi' ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span class="font-medium text-sm">Data Absensi</span>
            </a>

            <!-- Menu Pengumuman Baru -->
            <a href="/admin/pengumuman" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $segment == 'pengumuman' ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                </svg>
                <span class="font-medium text-sm">Pengumuman</span>
            </a>

            <a href="/admin/pengaturan" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $segment == 'pengaturan' ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="font-medium text-sm">Pengaturan</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="/admin/logout" class="flex items-center justify-center gap-2 w-full py-2.5 bg-slate-800 hover:bg-red-600 text-slate-300 hover:text-white rounded-lg transition-colors text-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Keluar
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">

        <header class="h-16 bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-4 lg:px-6 z-30 relative">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="p-2 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors bg-gray-50 border border-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-lg md:text-xl font-bold text-gray-800 hidden sm:block"><?= $title ?? 'Dashboard' ?></h2>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shadow-inner">
                    <?= substr(session()->get('nama_lengkap') ?? 'A', 0, 1) ?>
                </div>
                <span class="text-sm font-medium text-gray-700 hidden sm:block"><?= session()->get('nama_lengkap') ?? 'Admin' ?></span>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-6">
            <div class="max-w-7xl mx-auto">
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // --- Logic Hamburger Menu Mobile & Desktop ---
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            // Cek jika ukuran layar adalah Desktop (md ke atas / >= 768px)
            if (window.innerWidth >= 768) {
                // Di desktop: Tambahkan class 'sidebar-collapsed' untuk menyembunyikan ke kiri (Negative Margin)
                sidebar.classList.toggle('sidebar-collapsed');
            } else {
                // Di mobile: Gunakan logic transform dan munculkan overlay hitam
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        // --- Konfigurasi Default Toastr ---
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "4000"
        };

        <?php if (session()->getFlashdata('success')) : ?> toastr.success("<?= session()->getFlashdata('success') ?>", "Berhasil!");
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')) : ?> toastr.error("<?= session()->getFlashdata('error') ?>", "Gagal!");
        <?php endif; ?>

        // --- Automasi SweetAlert2 ---
        $(document).on('click', '.btn-confirm', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            let textAlert = $(this).data('text') || "Anda yakin ingin melanjutkan aksi ini?";
            let confirmBtnText = $(this).data('btn') || "Ya, Lanjutkan";

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: textAlert,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#ef4444',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'shadow-sm',
                    cancelButton: 'shadow-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>