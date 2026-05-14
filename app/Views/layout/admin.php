<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc((string) ($title ?? 'Admin Panel')) ?> - Sistem Absensi</title>

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
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

        @media (min-width: 768px) {
            .sidebar-collapsed {
                margin-left: -16rem !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased flex h-screen overflow-hidden">

    <?php
    $uri = service('uri');
    $segment = (string) $uri->getSegment(2);
    $roleId = (int) session()->get('role_id');
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
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Menu Utama</p>

            <?php
            // Mengambil menu dinamis dari database
            $db = \Config\Database::connect();
            $allowedMenus = $db->table('menus')
                ->join('role_menus', 'role_menus.id_menu = menus.id_menu')
                ->where('role_menus.id_role', $roleId)
                ->where('menus.is_active', 1)
                ->orderBy('menus.urutan', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($allowedMenus as $menu):
                // FIX INTELEPHENSE: Eksplisit Casting menjadi String
                $urlMenu  = (string) $menu['url'];
                $namaMenu = (string) $menu['nama_menu'];
                $iconMenu = (string) $menu['icon'];

                // Cek apakah URL saat ini sama dengan URL dari Database
                $isActive = strpos((string) uri_string(), $urlMenu) !== false;
                $activeClass = $isActive ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white';
            ?>
                <a href="<?= base_url($urlMenu) ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $activeClass ?>">
                    <i class="<?= esc($iconMenu) ?> opacity-80 text-center w-5 text-lg"></i>
                    <span class="font-medium text-sm"><?= esc($namaMenu) ?></span>
                </a>
            <?php endforeach; ?>
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
                <h2 class="text-lg md:text-xl font-bold text-gray-800 hidden sm:block"><?= esc((string) ($title ?? 'Dashboard')) ?></h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex flex-col text-right">
                    <span class="text-sm font-bold text-gray-800"><?= esc((string) (session()->get('nama_lengkap') ?? 'Admin')) ?></span>
                    <span class="text-[10px] font-bold <?= $roleId === 1 ? 'text-indigo-600' : 'text-emerald-600' ?> uppercase tracking-wider"><?= esc((string) (session()->get('nama_role') ?? 'User')) ?></span>
                </div>
                <div class="w-9 h-9 rounded-full <?= $roleId === 1 ? 'bg-indigo-100 text-indigo-600' : 'bg-emerald-100 text-emerald-600' ?> flex items-center justify-center font-black shadow-inner border <?= $roleId === 1 ? 'border-indigo-200' : 'border-emerald-200' ?>">
                    <?= esc(substr((string) (session()->get('nama_lengkap') ?? 'A'), 0, 1)) ?>
                </div>
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
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (window.innerWidth >= 768) {
                sidebar.classList.toggle('sidebar-collapsed');
            } else {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "4000"
        };

        <?php if (session()->getFlashdata('success')) : ?>
            toastr.success("<?= esc((string) session()->getFlashdata('success')) ?>", "Berhasil!");
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            toastr.error("<?= esc((string) session()->getFlashdata('error')) ?>", "Gagal!");
        <?php endif; ?>

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

        $(document).ready(function() {
            let activeMenu = document.querySelector('#sidebar nav a.bg-blue-600');
            if (activeMenu) {
                setTimeout(() => {
                    activeMenu.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 100);
            }
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>