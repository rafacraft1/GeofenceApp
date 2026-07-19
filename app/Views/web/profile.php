<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $user
 */
$fotoProfil = (!empty($user['foto']) && file_exists(FCPATH . 'uploads/profiles/' . $user['foto']))
    ? base_url('uploads/profiles/' . $user['foto'])
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['nama_lengkap'] ?? 'U') . '&background=eff6ff&color=1d4ed8&size=256';

$roleId  = (int) session()->get('role_id');
$isAdmin = ($roleId === 1);
?>

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

<div class="max-w-5xl mx-auto mt-2">
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Pengaturan Profil</h2>
        <p class="text-sm text-gray-500 mt-1 font-medium">Kelola informasi pribadi, keamanan akun, dan perbarui foto profil Anda.</p>
    </div>

    <form action="<?= base_url('admin/profile/update') ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8" id="formProfile">
        <?= csrf_field() ?>

        <!-- Kiri: Foto Profil -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden relative group">
                <!-- Dekorasi Background -->
                <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-blue-600 to-indigo-600"></div>
                
                <div class="p-6 pt-16 flex flex-col items-center text-center relative z-10">
                    <div class="relative w-36 h-36 mb-5 group/avatar">
                        <div class="w-full h-full rounded-full overflow-hidden border-4 border-white shadow-xl bg-white">
                            <img id="avatar-preview" src="<?= esc($fotoProfil) ?>" alt="Foto Profil" class="w-full h-full object-cover transition-transform duration-500 group-hover/avatar:scale-110">
                        </div>
                        <label for="foto" class="absolute bottom-1 right-1 bg-white hover:bg-blue-50 text-blue-600 p-3 rounded-full shadow-lg border border-gray-100 cursor-pointer transition-all hover:scale-110" title="Ubah Foto Profil">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="previewAndCompressImage(this)">
                    </div>
                    
                    <h3 class="text-xl font-black text-gray-800"><?= esc((string) ($user['nama_lengkap'] ?? session()->get('nama_lengkap'))) ?></h3>
                    <p class="inline-flex items-center gap-1.5 text-[10px] font-black text-blue-600 bg-blue-50 border border-blue-100 px-3 py-1 rounded-full uppercase tracking-widest mt-2 shadow-sm">
                        <i class="fas fa-user-shield"></i> <?= esc((string) session()->get('nama_role')) ?>
                    </p>
                </div>
            </div>
            
            <!-- Info Box Kiri -->
            <div class="mt-6 bg-blue-50 border border-blue-100 rounded-2xl p-5 shadow-sm text-sm text-blue-800">
                <p class="font-bold mb-2 flex items-center gap-2"><i class="fas fa-info-circle text-blue-500"></i> Info Otomatisasi</p>
                <p class="text-[11px] leading-relaxed text-blue-600/90 font-medium">Foto yang diunggah akan secara otomatis dikompresi di dalam peramban Anda (Client-Side) menjadi maksimal 700x700px untuk menghemat bandwidth server.</p>
            </div>
        </div>

        <!-- Kanan: Form Data -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Card Informasi Akun -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden relative">
                <div class="absolute right-0 top-0 w-32 h-32 bg-gradient-to-bl from-blue-50/50 to-transparent rounded-bl-full opacity-60 pointer-events-none"></div>
                
                <div class="px-7 py-5 border-b border-gray-50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shadow-sm">
                        <i class="fas fa-id-card text-sm"></i>
                    </div>
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Identitas Diri</h3>
                </div>
                
                <div class="p-7 space-y-6 relative z-10">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" name="nama_lengkap" value="<?= esc((string) ($user['nama_lengkap'] ?? session()->get('nama_lengkap') ?? '')) ?>" class="w-full bg-gray-50 hover:bg-white border border-gray-200 rounded-xl py-3.5 pl-10 pr-4 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all shadow-sm" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Username Login <span class="text-red-500">*</span></label>
                        <?php
                        $usernameClass = $isAdmin
                            ? 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed outline-none shadow-none focus:ring-0'
                            : 'bg-gray-50 hover:bg-white border-gray-200 text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 shadow-sm';
                        ?>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <input type="text" name="username" value="<?= esc((string) ($user['username'] ?? session()->get('username') ?? '')) ?>" <?= $isAdmin ? 'readonly' : 'required' ?> class="w-full rounded-xl py-3.5 pl-10 pr-4 text-sm font-bold border transition-all <?= $usernameClass ?>">
                        </div>

                        <?php if ($isAdmin): ?>
                            <div class="mt-2.5 text-[10px] font-bold text-red-500 flex items-center gap-1.5 bg-red-50 px-3 py-2 rounded-lg border border-red-100 w-max shadow-sm">
                                <i class="fas fa-lock"></i> Sesuai regulasi, Username Superadmin (Root) tidak dapat diubah.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card Keamanan Password -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden relative">
                <div class="px-7 py-5 border-b border-gray-50 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center shadow-sm">
                            <i class="fas fa-shield-alt text-sm"></i>
                        </div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Ganti Kata Sandi</h3>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest bg-gray-100 border border-gray-200 text-gray-500 px-2 py-1 rounded shadow-sm">Opsional</span>
                </div>
                
                <div class="p-7 relative z-10">
                    <p class="text-[11px] text-gray-500 font-medium mb-6 leading-relaxed bg-gray-50 p-3 rounded-xl border border-gray-100"><i class="fas fa-exclamation-triangle text-amber-500 mr-1.5"></i>Kosongkan bidang di bawah ini jika tidak ingin mengubah sandi. Jika diisi, <b>Sandi Lama</b> wajib disertakan sebagai validasi otorisasi perubahan.</p>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Kata Sandi Saat Ini</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-unlock"></i>
                                </div>
                                <input type="password" id="password_lama" name="password_lama" class="w-full bg-gray-50 hover:bg-white border border-gray-200 rounded-xl py-3.5 pl-10 pr-10 text-sm font-bold text-gray-800 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition-all shadow-sm" placeholder="Masukkan kata sandi lama">
                                <button type="button" onclick="togglePasswordVisibility('password_lama', 'eye_lama')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-rose-500 transition-colors focus:outline-none" title="Intip Sandi">
                                    <i class="fas fa-eye" id="eye_lama"></i>
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-gray-50 pt-6 mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Kata Sandi Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <input type="password" id="password_baru" name="password" class="w-full bg-gray-50 hover:bg-white border border-gray-200 rounded-xl py-3.5 pl-10 pr-10 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all shadow-sm" placeholder="Minimal 6 karakter">
                                    <button type="button" onclick="togglePasswordVisibility('password_baru', 'eye_baru')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-blue-600 transition-colors focus:outline-none">
                                        <i class="fas fa-eye" id="eye_baru"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Konfirmasi Sandi Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                    <input type="password" id="pass_confirm" name="pass_confirm" class="w-full bg-gray-50 hover:bg-white border border-gray-200 rounded-xl py-3.5 pl-10 pr-10 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all shadow-sm" placeholder="Ulangi kata sandi">
                                    <button type="button" onclick="togglePasswordVisibility('pass_confirm', 'eye_confirm')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-blue-600 transition-colors focus:outline-none">
                                        <i class="fas fa-eye" id="eye_confirm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button Submit -->
            <div class="pt-2 pb-10 flex justify-end">
                <button type="submit" class="w-full md:w-auto md:px-12 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition-all flex items-center justify-center gap-2 btn-submit group">
                    <i class="fas fa-save group-hover:-translate-y-0.5 transition-transform"></i> Terapkan Pembaruan Profil
                </button>
            </div>

        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Toggle visibilitas kata sandi
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            icon.classList.add('text-blue-500'); // Indikator nyala
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.remove('text-blue-500');
            icon.classList.add('fa-eye');
        }
    }

    // Kompresi gambar disisi client sebelum upload (Canvas resize)
    function previewAndCompressImage(input) {
        if (input.files && input.files[0]) {
            let file = input.files[0];

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                if(typeof toastr !== 'undefined') toastr.error("Berkas harus berupa gambar valid (JPG, JPEG, PNG)!", "Gagal");
                else alert("Berkas harus berupa gambar valid (JPG, JPEG, PNG)!");
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
                        if(typeof toastr !== 'undefined') toastr.success(`Otomatis dioptimasi: ${ukuranAsal} KB -> ${ukuranBaru} KB`, "Upload Siap");
                        console.log(`Optimasi gambar berhasil. Original: ${ukuranAsal} KB, Compress: ${ukuranBaru} KB`);
                    }, 'image/jpeg', 0.8);
                };
            };
            reader.readAsDataURL(file);
        }
    }

    // Spinner loading & pencegah double click
    document.getElementById('formProfile').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-submit');
        if (btn) {
            btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan Perubahan...`;
            btn.classList.add('btn-loading', 'cursor-not-allowed', 'opacity-90');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>