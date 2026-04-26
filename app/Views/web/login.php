<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Geofence System</title>

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body class="bg-slate-900 flex items-center justify-center min-h-screen relative overflow-hidden font-sans">

    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-32 left-20 w-96 h-96 bg-emerald-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>

    <div class="bg-white/95 backdrop-blur-sm p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-md z-10 border border-white/20">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4 shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Geofence Admin</h1>
            <p class="text-gray-500 text-sm mt-1">Sistem Absensi & Pelacakan Kehadiran</p>
        </div>

        <form action="/admin/login_action" method="POST" id="formLogin" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <input type="text" name="username" required autocomplete="off" class="w-full border-gray-200 bg-gray-50 rounded-xl py-3 pl-10 pr-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all outline-none" placeholder="Masukkan username">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>

                    <input type="password" id="password" name="password" required class="w-full border-gray-200 bg-gray-50 rounded-xl py-3 pl-10 pr-12 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all outline-none" placeholder="••••••••">

                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600 transition-colors focus:outline-none">
                        <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" id="btnSubmit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-blue-700 active:scale-[0.98] transition-all shadow-md flex justify-center items-center">
                <span>Masuk ke Sistem</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-xs text-gray-400">&copy; <?= date('Y') ?> SMKN 1 TGB. All rights reserved.</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // --- 1. UX Logika Show/Hide Password ---
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Ubah SVG menjadi Mata Tercoret (Eye-slash)
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                // Kembalikan SVG menjadi Mata Normal (Eye)
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        // --- 2. Integrasi Toastr untuk Error Login ---
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-center", // Muncul di tengah atas agar lebih jelas
            "timeOut": "4000",
        };

        <?php if (session()->getFlashdata('error')) : ?>
            toastr.error("<?= session()->getFlashdata('error') ?>", "Akses Ditolak");
        <?php endif; ?>

        // --- 3. Loading State pada Tombol Submit ---
        $('#formLogin').on('submit', function() {
            const btn = $('#btnSubmit');
            btn.html('<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');
            btn.addClass('opacity-80 cursor-not-allowed pointer-events-none');
        });
    </script>
</body>

</html>