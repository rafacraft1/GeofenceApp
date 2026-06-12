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
        <div class="bg-white rounded-xl shadow-sm p-6 h-fit border-t-4 border-blue-600 transition-all" id="form-container">
            <h2 class="text-lg font-bold mb-1 text-gray-800" id="form-title">Tambah Kelas Baru</h2>
            <p class="text-xs text-gray-500 mb-5" id="form-subtitle">Tentukan kelas dan pilih Wali Kelas dari daftar guru.</p>

            <form action="<?= base_url('admin/kelas/store') ?>" method="POST" id="form-kelas">
                <?= csrf_field() ?>
                <input type="hidden" name="id_kelas" id="id_kelas">

                <div class="mb-4">
                    <label for="nama_kelas" class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Contoh: XII IPA 1">
                </div>

                <div class="mb-6">
                    <label for="wali_kelas_id" class="block text-xs font-bold text-gray-600 uppercase mb-2">Wali Kelas (Opsional)</label>
                    <select name="wali_kelas_id" id="wali_kelas_id" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 cursor-pointer">
                        <option value="">-- Tidak ada Wali Kelas --</option>
                        <?php foreach ($listGuru as $guru): ?>
                            <option value="<?= esc((string) $guru['id_user']) ?>"><?= esc((string) $guru['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="submit" id="btn-submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors shadow-md">Simpan Data</button>
                    <button type="button" id="btn-cancel" onclick="resetForm()" class="w-full bg-gray-100 text-gray-600 font-bold py-3 rounded-xl hover:bg-gray-200 transition-colors hidden">Batal Edit</button>
                </div>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Nama Kelas</th>
                            <th class="px-6 py-4">Wali Kelas</th>
                            <th class="px-6 py-4 text-center">Total Siswa</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($kelas)) : ?>
                            <?php $no = 1;
                            foreach ($kelas as $k): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-500"><?= $no++ ?></td>
                                    <td class="px-6 py-4 font-bold text-gray-800"><?= esc((string) $k['nama_kelas']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php if (!empty($k['nama_wali'])): ?>
                                            <span class="flex items-center gap-1.5">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div> <?= esc((string) $k['nama_wali']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic text-xs">Belum diatur</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-xs font-bold"><?= (int) $k['jumlah_siswa'] ?> Siswa</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="editKelas('<?= (string) $k['id_kelas'] ?>', '<?= esc((string) $k['nama_kelas'], 'js') ?>', '<?= (string) $k['wali_kelas_id'] ?>')" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors" title="Edit Kelas">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 111.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>

                                            <form action="<?= base_url('admin/kelas/delete/' . (string) $k['id_kelas']) ?>" method="POST" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="button" class="btn-confirm p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors" data-text="Seluruh data siswa dan absensi di dalamnya akan ikut terhapus!" data-btn="Ya, Hapus Semua" title="Hapus Kelas">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-10 text-center text-gray-400 italic">Belum ada data kelas terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function editKelas(id, nama, waliId) {
        document.getElementById('id_kelas').value = id;
        document.getElementById('nama_kelas').value = nama;
        document.getElementById('wali_kelas_id').value = waliId;

        document.getElementById('form-title').innerText = 'Edit Data Kelas';
        document.getElementById('form-subtitle').innerText = 'Perbarui nama kelas atau ganti wali kelas.';

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Perubahan';
        btnSubmit.classList.replace('bg-blue-600', 'bg-amber-500');
        btnSubmit.classList.replace('hover:bg-blue-700', 'hover:bg-amber-600');

        document.getElementById('btn-cancel').classList.remove('hidden');
        document.getElementById('form-container').classList.replace('border-blue-600', 'border-amber-500');
    }

    function resetForm() {
        document.getElementById('id_kelas').value = '';
        document.getElementById('nama_kelas').value = '';
        document.getElementById('wali_kelas_id').value = '';

        document.getElementById('form-title').innerText = 'Tambah Kelas Baru';
        document.getElementById('form-subtitle').innerText = 'Tentukan kelas dan pilih Wali Kelas dari daftar guru.';

        let btnSubmit = document.getElementById('btn-submit');
        btnSubmit.innerText = 'Simpan Data';
        btnSubmit.classList.replace('bg-amber-500', 'bg-blue-600');
        btnSubmit.classList.replace('hover:bg-amber-600', 'hover:bg-blue-700');

        document.getElementById('btn-cancel').classList.add('hidden');
        document.getElementById('form-container').classList.replace('border-amber-500', 'border-blue-600');
    }

    document.getElementById('form-kelas').addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'true');
        }
    });
</script>
<?= $this->endSection() ?>