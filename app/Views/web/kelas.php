<?php

/**
 * @var array<int, array<string, mixed>> $kelas
 * @var array<int, array<string, mixed>> $listGuru
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Kelas</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6 h-fit border-t-4 border-blue-600 transition-all" id="form-container">
            <h2 class="text-lg font-semibold mb-1 text-gray-700" id="form-title">Tambah Kelas Baru</h2>
            <p class="text-xs text-gray-500 mb-4" id="form-subtitle">Tentukan kelas dan pilih Wali Kelas dari daftar guru.</p>

            <form action="<?= base_url('admin/kelas/store') ?>" method="POST" id="form-kelas">
                <?= csrf_field() ?>
                <input type="hidden" name="id_kelas" id="id_kelas">

                <div class="mb-4">
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas (Unik)</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" autocomplete="off" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Contoh: XII RPL 1" required>
                </div>

                <div class="mb-5">
                    <label for="wali_kelas" class="block text-sm font-medium text-gray-700 mb-2">Pilih Wali Kelas (Guru)</label>
                    <select name="wali_kelas" id="wali_kelas" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none transition-all bg-white cursor-pointer">
                        <option value="">-- Tanpa Wali Kelas --</option>
                        <?php foreach ($listGuru as $guru): ?>
                            <option value="<?= esc((string) $guru['nama_lengkap']) ?>"><?= esc((string) $guru['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">*Hanya menampilkan user dengan peran Guru.</p>
                </div>

                <div class="flex gap-2">
                    <button type="reset" onclick="resetForm()" id="btn-cancel" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 px-4 rounded transition duration-200 hidden">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 shadow-lg shadow-blue-200">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold">
                        <tr>
                            <th class="py-4 px-6">No</th>
                            <th class="py-4 px-6">Nama Kelas</th>
                            <th class="py-4 px-6">Wali Kelas</th>
                            <th class="py-4 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm divide-y divide-gray-100">
                        <?php $no = 1;
                        foreach ($kelas as $k) : ?>
                            <tr class="hover:bg-blue-50/50 transition-colors group">
                                <td class="py-3 px-6 whitespace-nowrap font-medium text-gray-500"><?= $no++ ?></td>
                                <td class="py-3 px-6 font-bold text-gray-800"><?= esc((string) $k['nama_kelas']) ?></td>
                                <td class="py-3 px-6">
                                    <span class="inline-flex items-center gap-1.5 font-medium text-gray-600">
                                        <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                                        </svg>
                                        <?= esc((string) ($k['wali_kelas'] ?? '-')) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-3">
                                        <button type="button"
                                            onclick="fillForm('<?= esc((string) $k['id_kelas']) ?>', '<?= esc((string) $k['nama_kelas'], 'js') ?>', '<?= esc((string) ($k['wali_kelas'] ?? ''), 'js') ?>')"
                                            class="text-blue-500 hover:text-blue-700 transition-transform hover:scale-110 bg-blue-50 p-1.5 rounded-lg border border-blue-100" title="Edit Data">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <form action="<?= base_url('admin/kelas/delete/' . (string) $k['id_kelas']) ?>" method="POST" id="form-delete-<?= (string) $k['id_kelas'] ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="button" onclick="konfirmasiHapusKelas('<?= (string) $k['id_kelas'] ?>', '<?= esc((string) $k['nama_kelas'], 'js') ?>')" class="text-red-500 hover:text-red-700 transition-transform hover:scale-110 bg-red-50 p-1.5 rounded-lg border border-red-100" title="Hapus Kelas">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function fillForm(id, nama, wali) {
        document.getElementById('id_kelas').value = id;
        document.getElementById('nama_kelas').value = nama;
        document.getElementById('wali_kelas').value = wali; // Dropdown akan otomatis menyesuaikan berdasarkan nama teks

        document.getElementById('form-title').innerText = 'Edit Kelas';
        document.getElementById('form-subtitle').innerText = 'Mengubah data kelas ' + nama;

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Perubahan';
        btnSubmit.classList.replace('bg-blue-600', 'bg-amber-500');
        btnSubmit.classList.replace('hover:bg-blue-700', 'hover:bg-amber-600');

        document.getElementById('btn-cancel').classList.remove('hidden');
        document.getElementById('form-container').classList.replace('border-blue-600', 'border-amber-500');
        document.getElementById('form-kelas').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetForm() {
        document.getElementById('id_kelas').value = '';
        document.getElementById('form-title').innerText = 'Tambah Kelas Baru';
        document.getElementById('form-subtitle').innerText = 'Tentukan kelas dan pilih Wali Kelas dari daftar guru.';

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Data';
        btnSubmit.classList.replace('bg-amber-500', 'bg-blue-600');
        btnSubmit.classList.replace('hover:bg-amber-600', 'hover:bg-blue-700');

        document.getElementById('btn-cancel').classList.add('hidden');
        document.getElementById('form-container').classList.replace('border-amber-500', 'border-blue-600');
    }

    function konfirmasiHapusKelas(id, namaKelas) {
        Swal.fire({
            title: 'Hapus ' + namaKelas + '?',
            text: "Seluruh data siswa dan absensi di dalamnya akan ikut terhapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Semua',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-delete-' + id).submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>