<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<?php
/**
 * UI Profil Elegan & Profesional
 * @var array $user
 */
$fotoProfil = (!empty($user['foto']) && file_exists(FCPATH . 'uploads/profiles/' . $user['foto']))
    ? base_url('uploads/profiles/' . $user['foto'])
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['nama_lengkap'] ?? 'U') . '&background=eff6ff&color=1d4ed8&size=256';
?>

<div class="max-w-5xl mx-auto mt-2">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pengaturan Profil</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola informasi pribadi, keamanan akun, dan foto profil Anda.</p>
    </div>

    <form action="<?= base_url('admin/profile/update') ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?= csrf_field() ?>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="relative w-32 h-32 mb-4 group">
                        <div class="w-full h-full rounded-full overflow-hidden border-4 border-gray-50 shadow-md">
                            <img id="avatar-preview" src="<?= esc($fotoProfil) ?>" alt="Foto Profil" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </div>
                        <label for="foto" class="absolute bottom-0 right-0 bg-blue-600 hover:bg-blue-700 text-white p-2.5 rounded-full shadow-lg cursor-pointer transition-colors" title="Ubah Foto">
                            <i class="fas fa-camera text-sm"></i>
                        </label>
                        <input type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="previewAndCompressImage(this)">
                    </div>
                    <h3 class="text-lg font-bold text-gray-800"><?= esc((string) ($user['nama_lengkap'] ?? session()->get('nama_lengkap'))) ?></h3>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mt-1"><?= esc((string) session()->get('nama_role')) ?></p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-bold text-gray-800"><i class="fas fa-id-card text-blue-500 mr-2"></i>Informasi Akun</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="<?= esc((string) ($user['nama_lengkap'] ?? session()->get('nama_lengkap') ?? '')) ?>" class="w-full rounded-lg border-gray-300 bg-gray-50/50 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Username Login <span class="text-red-500">*</span></label>
                        <input type="text" name="username" value="<?= esc((string) ($user['username'] ?? session()->get('username') ?? '')) ?>" class="w-full rounded-lg border-gray-300 bg-gray-50/50 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" required>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="text-base font-bold text-gray-800"><i class="fas fa-lock text-red-500 mr-2"></i>Keamanan & Password</h3>
                    <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2 py-1 rounded-md">Opsional</span>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-500 mb-5 leading-relaxed">Kosongkan form di bawah ini jika tidak bermaksud mengubah kata sandi saat ini. Jika ingin mengubahnya, <b>Kata Sandi Lama</b> wajib diisi untuk verifikasi keamanan.</p>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi Lama</label>
                            <div class="relative">
                                <input type="password" id="password_lama" name="password_lama" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" placeholder="Masukkan kata sandi saat ini">
                                <button type="button" onclick="togglePasswordVisibility('password_lama', 'eye_lama')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600 transition-colors focus:outline-none" title="Tampilkan/Sembunyikan">
                                    <i class="fas fa-eye" id="eye_lama"></i>
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5 mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi Baru</label>
                                <div class="relative">
                                    <input type="password" id="password_baru" name="password" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" placeholder="Minimal 6 karakter">
                                    <button type="button" onclick="togglePasswordVisibility('password_baru', 'eye_baru')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600 transition-colors focus:outline-none" title="Tampilkan/Sembunyikan">
                                        <i class="fas fa-eye" id="eye_baru"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                                <div class="relative">
                                    <input type="password" id="pass_confirm" name="pass_confirm" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" placeholder="Ulangi kata sandi">
                                    <button type="button" onclick="togglePasswordVisibility('pass_confirm', 'eye_confirm')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600 transition-colors focus:outline-none" title="Tampilkan/Sembunyikan">
                                        <i class="fas fa-eye" id="eye_confirm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg shadow-sm shadow-blue-500/30 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // FUNGSI 1: Tampilkan/Sembunyikan Password
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // FUNGSI 2: Live Preview & Klien-Side Kompresi Gambar
    function previewAndCompressImage(input) {
        if (input.files && input.files[0]) {
            let file = input.files[0];

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                toastr.error("Berkas harus berupa gambar valid (JPG, JPEG, PNG)!", "Gagal");
                input.value = '';
                return;
            }

            let reader = new window.FileReader();
            reader.onload = function(e) {
                let img = new window.Image();
                img.src = e.target.result;
                img.onload = function() {
                    let canvas = document.createElement('canvas');
                    let ctx = canvas.getContext('2d');

                    let maxWidth = 700;
                    let maxHeight = 700;
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width *= maxHeight / height;
                            height = maxHeight;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function(blob) {
                        let compressedUrl = URL.createObjectURL(blob);
                        document.getElementById('avatar-preview').src = compressedUrl;

                        let compressedFile = new window.File([blob], file.name.split('.')[0] + '.jpg', {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });

                        let dataTransfer = new window.DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        input.files = dataTransfer.files;

                        let ukuranAsal = (file.size / 1024).toFixed(1);
                        let ukuranBaru = (blob.size / 1024).toFixed(1);
                        toastr.success(`Gambar berhasil dioptimasi (${ukuranAsal} KB -> ${ukuranBaru} KB)`, "Optimasi Berhasil");
                    }, 'image/jpeg', 0.75);
                };
            };
            reader.readAsDataURL(file);
        }
    }
</script>
<?= $this->endSection() ?>