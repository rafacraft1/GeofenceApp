<?php

/**
 * @var string|null $title
 * @var array|null $allowedMenus
 * @var int|null $pendingIzinCount
 */
$safeIzinCount = $pendingIzinCount ?? 0;
?>
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
            height: 6px;
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
        
        /* Animasi Dropdown */
        .dropdown-enter {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
            pointer-events: none;
        }
        .dropdown-active {
            opacity: 1;
            transform: scale(1) translateY(0);
            pointer-events: auto;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased flex h-screen overflow-hidden">

    <?php
    $uri     = service('uri');
    $segment = (string) $uri->getSegment(2);
    $roleId  = (int) session()->get('role_id');
    ?>

    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="bg-slate-900 text-slate-300 w-64 flex-shrink-0 fixed md:relative inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out z-50 flex flex-col shadow-2xl border-r border-slate-800">

        <!-- LOGO HEADER -->
        <div class="h-16 flex items-center justify-between md:justify-center px-4 md:px-0 font-black text-xl text-white border-b border-slate-800/80 bg-slate-950/50 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-indigo-600/10"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <span class="tracking-tight"><?= esc((string) (session()->get('nama_aplikasi') ?? 'GeofenceApp')) ?></span>
            </div>
            <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white relative z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- NAVIGATION MENU -->
        <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto">
            <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Navigasi Utama</p>

            <?php
            $currentUri = uri_string();

            foreach ($allowedMenus ?? [] as $menu):
                $urlMenu  = (string) $menu['url'];
                $namaMenu = (string) $menu['nama_menu'];
                $iconMenu = (string) $menu['icon'];

                $isActive = ($currentUri === $urlMenu) || (strpos($currentUri, $urlMenu . '/') === 0);
                $activeClass = $isActive 
                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25' 
                    : 'text-slate-400 hover:bg-slate-800/80 hover:text-white';
            ?>
                <a href="<?= base_url($urlMenu) ?>" class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-200 <?= $activeClass ?> group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-5 flex justify-center">
                            <i class="<?= esc($iconMenu) ?> text-lg transition-transform duration-300 <?= $isActive ? 'scale-110' : 'group-hover:scale-110 group-hover:text-blue-400' ?>"></i>
                        </div>
                        <span class="font-bold text-sm tracking-wide"><?= esc($namaMenu) ?></span>
                    </div>

                    <?php if ($urlMenu === 'admin/izin' && $safeIzinCount > 0): ?>
                        <span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm shadow-rose-500/40 animate-pulse">
                            <?= $safeIzinCount > 99 ? '99+' : $safeIzinCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- LOGOUT BUTTON -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-900">
            <a href="<?= base_url('admin/logout') ?>" class="flex items-center justify-center gap-2 w-full py-3 bg-slate-800 text-slate-300 hover:bg-gradient-to-r hover:from-rose-500 hover:to-red-600 hover:text-white rounded-xl transition-all duration-300 hover:shadow-lg hover:shadow-rose-500/30 text-sm font-bold group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Keluar Sistem
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50/50">
        
        <!-- TOP NAVBAR -->
        <header class="h-16 bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-200 flex items-center justify-between px-4 lg:px-6 z-30 relative">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-blue-600 transition-colors bg-white border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                </button>
                <h2 class="text-lg md:text-xl font-black text-gray-800 tracking-tight hidden sm:block"><?= esc((string) ($title ?? 'Dashboard')) ?></h2>
            </div>

            <?php
            $fotoSession = session()->get('foto');
            $namaLengkap = session()->get('nama_lengkap') ?? 'Admin';
            $namaRole    = session()->get('nama_role') ?? 'User';
            ?>
            <div class="relative">
                <button onclick="toggleProfileDropdown()" class="flex items-center gap-3 p-1.5 rounded-2xl hover:bg-gray-50 transition-colors cursor-pointer group focus:outline-none relative z-10 border border-transparent hover:border-gray-200" id="profileDropdownBtn" title="Menu Pengguna">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-black text-gray-800 group-hover:text-blue-700 transition-colors"><?= esc((string) $namaLengkap) ?></span>
                        <span class="text-[9px] font-black <?= $roleId === 1 ? 'text-indigo-600' : 'text-emerald-600' ?> uppercase tracking-widest mt-0.5">
                            <?= esc((string) $namaRole) ?>
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-black shadow-inner border border-blue-100 group-hover:ring-2 group-hover:ring-blue-400 group-hover:ring-offset-2 transition-all overflow-hidden relative">
                        <?php if (!empty($fotoSession)): ?>
                            <img src="<?= base_url('uploads/profiles/' . $fotoSession) ?>" alt="Profil" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= esc(substr((string) $namaLengkap, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-blue-500 transition-transform duration-300" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div id="profileDropdownMenu" class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 dropdown-enter z-50 origin-top-right transition-all duration-300">
                    
                    <div class="px-4 py-3 border-b border-gray-50 sm:hidden">
                        <p class="text-sm font-black text-gray-800 truncate"><?= esc((string) $namaLengkap) ?></p>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-0.5"><?= esc((string) $namaRole) ?></p>
                    </div>

                    <a href="<?= base_url('admin/profile') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group/item">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 group-hover/item:bg-blue-100 text-gray-400 group-hover/item:text-blue-500 flex items-center justify-center transition-colors">
                            <i class="fas fa-user-edit text-xs"></i>
                        </div>
                        <span>Pengaturan Profil</span>
                    </a>
                    
                    <div class="border-t border-gray-100 my-1"></div>
                    
                    <a href="<?= base_url('admin/logout') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-rose-600 hover:bg-rose-50 transition-colors group/item">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 group-hover/item:bg-rose-100 text-rose-400 group-hover/item:text-rose-600 flex items-center justify-center transition-colors">
                            <i class="fas fa-sign-out-alt text-xs"></i>
                        </div>
                        <span>Keluar Sistem</span>
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-8">
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
        // Toggle Sidebar Navigasi
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

        // Toggle Animasi Dropdown Profil
        function toggleProfileDropdown() {
            const menu = document.getElementById('profileDropdownMenu');
            const arrow = document.getElementById('dropdownArrow');
            
            if (menu.classList.contains('dropdown-enter')) {
                // Open
                menu.classList.remove('dropdown-enter');
                menu.classList.add('dropdown-active');
                arrow.classList.add('rotate-180');
            } else {
                // Close
                menu.classList.remove('dropdown-active');
                menu.classList.add('dropdown-enter');
                arrow.classList.remove('rotate-180');
            }
        }

        // Klik di luar untuk menutup dropdown
        window.addEventListener('click', function(e) {
            const menu = document.getElementById('profileDropdownMenu');
            const btn = document.getElementById('profileDropdownBtn');
            if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                if (menu.classList.contains('dropdown-active')) {
                    menu.classList.remove('dropdown-active');
                    menu.classList.add('dropdown-enter');
                    document.getElementById('dropdownArrow').classList.remove('rotate-180');
                }
            }
        });

        // Pengaturan Global Toastr Notification
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "4000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Render Notifikasi Session
        <?php if (session()->getFlashdata('success')) : ?>
            toastr.success("<?= esc((string) session()->getFlashdata('success')) ?>", "Berhasil!");
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            toastr.error("<?= esc((string) session()->getFlashdata('error')) ?>", "Gagal!");
        <?php endif; ?>

        // Global Alert Confirmasi (SweetAlert2)
        $(document).on('click', '.btn-confirm', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            let textAlert = $(this).data('text') || "Tindakan ini tidak dapat dibatalkan.";
            let confirmBtnText = $(this).data('btn') || "Ya, Lanjutkan";

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: textAlert,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB', // blue-600
                cancelButtonColor: '#EF4444', // red-500
                confirmButtonText: `<i class="fas fa-check mr-2"></i> ${confirmBtnText}`,
                cancelButtonText: `<i class="fas fa-times mr-2"></i> Batal`,
                customClass: {
                    confirmButton: 'shadow-lg shadow-blue-500/30 rounded-xl px-6',
                    cancelButton: 'shadow-sm rounded-xl px-6'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Auto Scroll ke Menu Aktif di Sidebar (Anti-Tertutup)
        $(document).ready(function() {
            let activeMenu = document.querySelector('#sidebar nav a.bg-gradient-to-r');
            if (activeMenu) {
                setTimeout(() => {
                    activeMenu.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 150);
            }
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>