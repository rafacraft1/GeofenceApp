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
            $db = \Config\Database::connect();
            $allowedMenus = $db->table('menus')
                ->join('role_menus', 'role_menus.id_menu = menus.id_menu')
                ->where('role_menus.id_role', $roleId)
                ->where('menus.is_active', 1)
                ->orderBy('menus.urutan', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($allowedMenus as $menu):
                $urlMenu  = (string) $menu['url'];
                $namaMenu = (string) $menu['nama_menu'];
                $iconMenu = (string) $menu['icon'];

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

            <?php
            $fotoSession = session()->get('foto');
            $namaLengkap = session()->get('nama_lengkap') ?? 'Admin';

            // PENGAMAN OTOMATIS: Deteksi nama role secara realtime jika session kosong
            $namaRole = session()->get('nama_role');
            if (empty($namaRole)) {
                if ($roleId === 1) {
                    $namaRole = 'Admin';
                } elseif ($roleId === 2) {
                    $namaRole = 'Guru';
                } else {
                    $db = \Config\Database::connect();
                    $roleRow = $db->table('roles')->where('id_role', $roleId)->get()->getRowArray();
                    $namaRole = $roleRow['nama_role'] ?? 'User';
                }
            }
            ?>
            <div class="relative">
                <button onclick="toggleProfileDropdown()" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer group focus:outline-none relative z-10" id="profileDropdownBtn" title="Menu Pengguna">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-bold text-gray-800 group-hover:text-blue-700 transition-colors"><?= esc((string) $namaLengkap) ?></span>
                        <span class="text-[10px] font-bold <?= $roleId === 1 ? 'text-indigo-600' : 'text-emerald-600' ?> uppercase tracking-wider">
                            <?= esc((string) $namaRole) ?>
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-black shadow-inner border border-blue-200 group-hover:ring-2 group-hover:ring-blue-400 transition-all overflow-hidden">
                        <?php if (!empty($fotoSession)): ?>
                            <img src="<?= base_url('uploads/profiles/' . $fotoSession) ?>" alt="Profil" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= esc(substr((string) $namaLengkap, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div id="profileDropdownMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1 hidden z-50 transform origin-top-right transition-all duration-200">
                    <a href="<?= base_url('admin/profile') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                        <i class="fas fa-user-edit text-gray-400 w-4"></i>
                        <span>Edit Profil</span>
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="<?= base_url('admin/logout') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <i class="fas fa-sign-out-alt text-red-400 w-4"></i>
                        <span>Keluar</span>
                    </a>
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

        function toggleProfileDropdown() {
            const menu = document.getElementById('profileDropdownMenu');
            const arrow = document.getElementById('dropdownArrow');
            menu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        window.addEventListener('click', function(e) {
            const menu = document.getElementById('profileDropdownMenu');
            const btn = document.getElementById('profileDropdownBtn');
            if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
                document.getElementById('dropdownArrow').classList.remove('rotate-180');
            }
        });

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