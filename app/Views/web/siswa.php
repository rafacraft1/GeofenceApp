<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    /* Pagination Styling */
    .pagination {
        display: flex;
        flex-wrap: wrap;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 0.35rem;
    }

    .pagination li a,
    .pagination li span {
        display: block;
        padding: 0.5rem 0.85rem;
        font-size: 0.875rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #4b5563;
        text-decoration: none;
    }

    .pagination li.active a,
    .pagination li.active span {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
        font-weight: 700;
    }

    /* Modal Center Logic */
    .modal-active {
        overflow: hidden;
    }
</style>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

    <div class="p-5 border-b flex flex-col md:flex-row justify-between items-center gap-4 bg-white">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Daftar Siswa</h3>
            <p class="text-sm text-gray-500">Total: <?= $total_data ?> Siswa</p>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
            <form action="/admin/siswa" method="GET" class="flex-1 md:w-48">
                <select name="kelas" onchange="this.form.submit()" class="w-full border-gray-200 rounded-xl p-2.5 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 min-w-[120px]">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($list_kelas as $k): ?>
                        <option value="<?= $k->kelas ?>" <?= ($kelas_aktif == $k->kelas) ? 'selected' : '' ?>><?= $k->kelas ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <a href="/admin/siswa/export?kelas=<?= $kelas_aktif ?>" class="flex items-center justify-center gap-2 bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 shadow-md transition-all active:scale-95 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </a>

            <button onclick="openImportModal()" class="flex items-center justify-center gap-2 bg-slate-100 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 shadow-sm transition-all active:scale-95 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import
            </button>

            <button onclick="toggleFormTambah()" class="flex items-center justify-center gap-1 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md whitespace-nowrap">
                + Tambah
            </button>
        </div>
    </div>

    <div id="form-tambah" class="bg-blue-50/50 p-6 border-b hidden">
        <form action="/admin/siswa/store" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">NIS</label>
                <input type="text" name="nis" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="102938">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nama Lengkap">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Kelas</label>
                <input type="text" name="kelas" required oninput="this.value = this.value.toUpperCase()" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="XII-RPL">
            </div>
            <div class="md:col-span-4 flex justify-end gap-3 pt-2">
                <button type="button" onclick="toggleFormTambah()" class="text-sm font-semibold text-gray-500 hover:text-gray-800">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-400 text-[11px] font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Informasi Siswa</th>
                    <th class="px-6 py-4">Status HP</th>
                    <th class="px-6 py-4 text-center">Keamanan</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($siswa)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">Belum ada data siswa.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($siswa as $s): ?>
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs"><?= substr($s->nama_lengkap, 0, 1) ?></div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800"><?= $s->nama_lengkap ?></div>
                                    <div class="text-[11px] text-gray-400 font-medium"><?= $s->nis ?> • <?= $s->kelas ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-lg text-[10px] font-bold <?= $s->device_id ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-500' ?>">
                                <?= $s->device_id ? 'TERIKAT' : 'KOSONG' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-center">
                                <span class="text-[10px] font-bold text-gray-600"><?= $s->fraud_count ?>/3 Fraud</span>
                                <div class="w-16 h-1 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                    <div class="h-full <?= $s->is_blocked ? 'bg-red-500' : 'bg-blue-500' ?>" style="width: <?= ($s->fraud_count / 3) * 100 ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick='openEditModal(<?= json_encode($s) ?>)' class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <?php if ($s->device_id): ?>
                                    <form action="/admin/siswa/reset_device/<?= $s->id ?>" method="POST" class="inline">
                                        <button type="submit" class="btn-confirm p-2 text-amber-600 hover:bg-amber-50 rounded-lg" data-text="Reset perangkat siswa ini?">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($s->is_blocked): ?>
                                    <form action="/admin/siswa/unblock/<?= $s->id ?>" method="POST" class="inline">
                                        <button type="submit" class="btn-confirm p-2 text-red-600 hover:bg-red-50 rounded-lg" data-text="Buka blokir siswa ini?">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="p-5 border-t bg-gray-50/30 flex justify-between items-center">
        <span class="text-xs text-gray-500 font-medium">Halaman <?= $page ?></span>
        <?= $pager_links ?>
    </div>
</div>

<div id="modal-edit" class="fixed inset-0 z-[60] hidden items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-lg p-8 mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Edit Data Siswa</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="form-edit-action" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">NIS</label>
                <input type="text" id="edit-nis" name="nis" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Lengkap</label>
                <input type="text" id="edit-nama" name="nama_lengkap" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Kelas</label>
                <input type="text" id="edit-kelas" name="kelas" required oninput="this.value = this.value.toUpperCase()" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-500">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-import" class="fixed inset-0 z-[60] hidden items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeImportModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-md p-8 mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Import Data Siswa</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="/admin/siswa/import" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="p-6 border-2 border-dashed border-slate-300 rounded-2xl text-center bg-slate-50 hover:bg-slate-100 transition-colors">
                <input type="file" name="file_excel" id="file_excel" class="hidden" required accept=".xlsx">
                <label for="file_excel" class="cursor-pointer block w-full h-full">
                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-sm font-medium text-slate-600">Klik untuk pilih file Excel (.xlsx)</p>
                    <p class="text-xs text-slate-400 mt-1" id="file-name-preview">Belum ada file dipilih</p>
                </label>
            </div>
            <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl">
                <p class="text-[11px] text-amber-700 leading-relaxed font-medium text-center">
                    Gunakan format template yang sudah disediakan agar tidak terjadi error saat proses import.
                </p>
                <a href="/admin/siswa/download_template" class="block text-center text-amber-900 font-bold text-xs mt-2 underline hover:text-amber-700">Unduh Template Excel</a>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition-all">Mulai Import Data</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Preview nama file yang diupload pada Modal Import
    document.getElementById('file_excel').addEventListener('change', function(e) {
        let fileName = e.target.files[0] ? e.target.files[0].name : "Belum ada file dipilih";
        document.getElementById('file-name-preview').textContent = fileName;
    });

    function toggleFormTambah() {
        document.getElementById('form-tambah').classList.toggle('hidden');
    }

    // Modal Edit Logic (Menghindari CSS Conflict Tailwind)
    function openEditModal(data) {
        document.getElementById('edit-nis').value = data.nis;
        document.getElementById('edit-nama').value = data.nama_lengkap;
        document.getElementById('edit-kelas').value = data.kelas;
        document.getElementById('form-edit-action').action = '/admin/siswa/update/' + data.id;

        const modal = document.getElementById('modal-edit');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('modal-active');
    }

    function closeEditModal() {
        const modal = document.getElementById('modal-edit');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('modal-active');
    }

    // Modal Import Logic
    function openImportModal() {
        const modal = document.getElementById('modal-import');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('modal-active');
    }

    function closeImportModal() {
        const modal = document.getElementById('modal-import');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('modal-active');
    }
</script>
<?= $this->endSection() ?>