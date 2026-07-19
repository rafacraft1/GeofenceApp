<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Autentikasi | GeofenceApp</title>

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Animasi Blob Custom jika tidak ada di tailwind.config */
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 10s infinite alternate;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>

<body class="bg-slate-900 flex items-center justify-center min-h-screen relative overflow-hidden font-sans selection:bg-blue-500 selection:text-white">

    <!-- Efek Latar Belakang Aurora / Mesh Gradient -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-900/40 via-slate-900 to-slate-950 z-0"></div>
    
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-blob z-0"></div>
    <div class="absolute top-[20%] right-[-10%] w-[400px] h-[400px] bg-indigo-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob animation-delay-2000 z-0"></div>
    <div class="absolute bottom-[-10%] left-[20%] w-[600px] h-[600px] bg-emerald-600 rounded-full mix-blend-screen filter blur-[130px] opacity-10 animate-blob animation-delay-4000 z-0"></div>

    <!-- Kotak Login Utama -->
    <div class="glass-panel p-8 md:p-12 rounded-[2rem] shadow-2xl w-full max-w-md z-10 border border-white/20 transform transition-all duration-500 hover:shadow-blue-900/30">

        <!-- Header Identitas -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white mb-5 shadow-xl shadow-blue-500/30 transform -rotate-6 hover:rotate-0 transition-transform duration-300">
                <i class="fas fa-fingerprint text-4xl"></i>
            </div>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Geofence<span class="text-blue-600">App</span></h1>
            <p class="text-xs font-bold text-gray-400 mt-2 uppercase tracking-widest">Portal Manajemen Presensi</p>
        </div>

        <form action="<?= base_url('admin/login_action') ?>" method="POST" id="formLogin" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Field Username -->
            <div>
                <label for="username" class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Nama Pengguna (Username)</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-500 transition-colors">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <input type="text" name="username" id="username" value="<?= old('username') ?>" required class="w-full pl-11 pr-4 py-3.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white shadow-sm" placeholder="Ketik username Anda" autocomplete="username">
                </div>
            </div>

            <!-- Field Password -->
            <div>
                <label for="password" class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Kata Sandi (Password)</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-500 transition-colors">
                        <i class="fas fa-lock text-sm"></i>
                    </div>
                    <input type="password" name="password" id="password" required class="w-full pl-11 pr-12 py-3.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white shadow-sm" placeholder="••••••••" autocomplete="current-password">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-blue-600 transition-colors outline-none" title="Intip Sandi">
                        <i class="fas fa-eye text-sm" id="iconEye"></i>
                    </button>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-2">
                <button type="submit" id="btnSubmit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 rounded-xl font-bold shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-indigo-700 hover:shadow-blue-500/40 transition-all active:scale-95 flex justify-center items-center gap-2 group">
                    <span id="btnText">Otorisasi & Masuk</span>
                    <i class="fas fa-sign-in-alt group-hover:translate-x-1 transition-transform" id="btnIcon"></i>
                </button>
            </div>
        </form>
        
        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><i class="fas fa-shield-alt mr-1"></i> Dilindungi oleh Enkripsi End-to-End</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        function togglePassword() {
            const pwField = document.getElementById('password');
            const iconEye = document.getElementById('iconEye');

            if (pwField.type === 'password') {
                pwField.type = 'text';
                iconEye.classList.remove('fa-eye');
                iconEye.classList.add('fa-eye-slash');
                iconEye.classList.add('text-blue-600');
            } else {
                pwField.type = 'password';
                iconEye.classList.remove('fa-eye-slash');
                iconEye.classList.remove('text-blue-600');
                iconEye.classList.add('fa-eye');
            }
        }

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-center",
            "timeOut": "4000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        <?php if (session()->getFlashdata('error')) : ?>
            toastr.error("<?= esc((string) session()->getFlashdata('error')) ?>", "Akses Ditolak");
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')) : ?>
            toastr.warning("<?= esc((string) session()->getFlashdata('warning')) ?>", "Sesi Berakhir");
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('success')) : ?>
            toastr.success("<?= esc((string) session()->getFlashdata('success')) ?>", "Informasi");
        <?php endif; ?>

        $('#formLogin').on('submit', function() {
            const btn = $('#btnSubmit');
            const icon = $('#btnIcon');
            const text = $('#btnText');
            
            icon.removeClass('fa-sign-in-alt group-hover:translate-x-1').addClass('fa-spinner fa-spin');
            text.text('Mengautentikasi...');
            
            btn.prop('disabled', true);
            btn.addClass('opacity-90 cursor-not-allowed');
        });
    </script>
</body>

</html>