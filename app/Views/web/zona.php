<?php

/**
 * @var array<int, array<string, mixed>> $zonas
 * @var array<int, array<string, mixed>> $all_kelas
 * @var array<int, array<string, mixed>> $all_siswa
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<style>
    .modal-active {
        overflow: hidden;
    }
    
    /* Animasi Modal Fade & Scale */
    .modal-backdrop-anim {
        opacity: 0;
        transition: opacity 0.3s ease-out;
    }
    .modal-panel-anim {
        opacity: 0;
        transform: scale(0.95);
        transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-show .modal-backdrop-anim { opacity: 1; }
    .modal-show .modal-panel-anim { opacity: 1; transform: scale(1); }

    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Zona Absensi</h2>
        <p class="text-sm text-gray-500 mt-1">Atur multi-lokasi, jadwal 7 hari, dan anggota PKL (Per Kelas / Per Siswa).</p>
    </div>
    <button onclick="openFormModal()" class="flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-plus"></i> Tambah Zona Baru
    </button>
</div>

<div class="grid grid-cols-1 gap-6">
    <?php foreach ($zonas as $z): ?>
        <div class="bg-white rounded-2xl shadow-sm border <?= $z['is_default'] ? 'border-blue-300 ring-4 ring-blue-50/50' : 'border-gray-100' ?> overflow-hidden hover:shadow-md transition-all">
            <div class="flex flex-col md:flex-row">
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-black text-gray-800"><?= esc((string)$z['nama_zona']) ?></h3>
                            <?php if ($z['is_default']): ?>
                                <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest border border-blue-200 shadow-sm"><i class="fas fa-star mr-1"></i> Sekolah Pusat</span>
                            <?php else: ?>
                                <span class="bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest border border-amber-200"><i class="fas fa-map-pin mr-1"></i> Khusus / PKL</span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 p-3 bg-gray-50/80 border border-gray-100 rounded-xl flex items-center justify-between group hover:border-indigo-200 transition-colors">
                            <div class="flex items-center gap-3 text-sm text-gray-600 font-medium">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span>Jadwal Khusus Zona Ini (Sen-Ming)</span>
                            </div>
                            <button onclick='openJadwalModal(<?= htmlspecialchars(json_encode($z), ENT_QUOTES, "UTF-8") ?>)' class="bg-white border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-all shadow-sm">Atur Jadwal</button>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between pt-4 border-t border-gray-100 relative">
                        <div class="flex flex-wrap items-center gap-3">
                            <button onclick='openEditModal(<?= htmlspecialchars(json_encode($z), ENT_QUOTES, "UTF-8") ?>)' class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                <i class="fas fa-map-marked-alt"></i> Lokasi & Radius
                            </button>

                            <?php if (!$z['is_default']): ?>
                                <button onclick='openAssignModal(<?= htmlspecialchars(json_encode($z), ENT_QUOTES, "UTF-8") ?>)' class="text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                    <i class="fas fa-users-cog"></i> Kelola Anggota
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (!$z['is_default']): ?>
                            <div class="relative">
                                <button type="button" onclick="toggleActionMenu(event, 'menu-zona-<?= $z['id_zona'] ?>')" class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <!-- Dropdown Menu (⋮) -->
                                <div id="menu-zona-<?= $z['id_zona'] ?>" class="action-menu hidden absolute right-0 bottom-full mb-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl z-20 py-1.5 overflow-hidden origin-bottom-right transition-all">
                                    <form action="<?= base_url('admin/zona/setDefault/' . (string)$z['id_zona']) ?>" method="POST" class="block w-full">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="w-full text-left px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2.5">
                                            <i class="fas fa-star text-amber-500 text-sm w-4 text-center"></i> Jadikan Default
                                        </button>
                                    </form>
                                    <hr class="border-gray-50 my-1">
                                    <form action="<?= base_url('admin/zona/delete/' . (string)$z['id_zona']) ?>" method="POST" class="block w-full">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn-confirm w-full text-left px-4 py-2.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2.5" data-text="Hapus zona PKL ini beserta seluruh jadwal di dalamnya?" data-btn="Hapus Permanen">
                                            <i class="fas fa-trash text-red-500 text-sm w-4 text-center"></i> Hapus Zona
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-full md:w-1/3 min-h-[200px] md:min-h-full border-l border-gray-100 relative z-0">
                    <div class="map-preview absolute inset-0 bg-slate-100" data-lat="<?= $z['latitude'] ?>" data-lng="<?= $z['longitude'] ?>" data-rad="<?= $z['radius'] ?>"></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ============================================== -->
<!-- MODAL: TAMBAH / EDIT ZONA (PETA)               -->
<!-- ============================================== -->
<div id="modal-zona" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm modal-backdrop-anim" onclick="closeFormModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-5xl p-0 relative overflow-hidden flex flex-col md:flex-row h-[90vh] md:h-auto max-h-[800px] modal-panel-anim">
        <div class="w-full md:w-1/2 p-8 overflow-y-auto custom-scrollbar">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modal-title" class="text-xl font-bold text-gray-800">Tambah Zona Baru</h3>
                <button onclick="closeFormModal()" class="md:hidden text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="form-action" method="POST" action="<?= base_url('admin/zona/store') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Lokasi / Instansi PKL</label>
                    <input type="text" id="input-nama" name="nama_zona" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all hover:bg-white" placeholder="Contoh: PT. Telkom Indonesia">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Latitude</label>
                        <input type="text" id="input-lat" name="latitude" readonly required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Longitude</label>
                        <input type="text" id="input-lng" name="longitude" readonly required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none font-mono">
                    </div>
                </div>
                <div class="bg-blue-50 text-blue-700 text-[11px] font-medium p-3 rounded-xl border border-blue-100 flex items-start gap-2.5">
                    <i class="fas fa-info-circle mt-0.5"></i>
                    Ketik nama jalan/kota di kotak pencarian peta (kanan), atau geser peta lalu klik titik gedungnya secara manual.
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Radius Area Absen (Meter)</label>
                    <input type="number" id="input-rad" name="radius" required value="50" min="10" class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all hover:bg-white font-mono">
                </div>

                <div id="wrapper-jam-default">
                    <hr class="border-t-2 border-dashed border-gray-100 my-5">
                    <p class="text-xs font-bold text-gray-600 mb-3 flex items-center gap-2"><i class="fas fa-cog text-gray-400"></i> PENGATURAN JAM DEFAULT (SEN-MIN)</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Buka Absen</label>
                            <input type="time" id="input-buka" name="waktu_buka_absen" class="w-full border border-gray-200 rounded-xl p-2.5 text-xs bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-emerald-600 uppercase mb-1">Jam Masuk</label>
                            <input type="time" id="input-masuk" name="jam_masuk" class="w-full border border-emerald-200 rounded-xl p-2.5 text-xs bg-emerald-50 outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-emerald-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-indigo-600 uppercase mb-1">Jam Pulang</label>
                            <input type="time" id="input-pulang" name="jam_pulang" class="w-full border border-indigo-200 rounded-xl p-2.5 text-xs bg-indigo-50 outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-indigo-700">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 mt-4">
                    <button type="button" onclick="closeFormModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Zona
                    </button>
                </div>
            </form>
        </div>

        <div class="w-full md:w-1/2 min-h-[400px] relative z-0 bg-slate-100 border-l border-gray-100">
            <div id="interactive-map" class="absolute inset-0 z-0"></div>

            <div class="absolute top-4 left-4 right-16 z-[1000]">
                <div class="relative flex items-center shadow-lg rounded-xl overflow-hidden bg-white border border-gray-100">
                    <i class="fas fa-search absolute left-3.5 text-blue-500"></i>
                    <input type="text" id="map-search" placeholder="Cari gedung, jalan, kota, atau paste URL Maps..." class="w-full pl-10 pr-10 py-3 text-sm font-medium outline-none border-none focus:ring-0 placeholder-gray-400 text-gray-700" autocomplete="off">

                    <button type="button" id="search-clear" class="absolute right-3 hidden text-gray-400 hover:text-red-500 transition-colors bg-white z-10">
                        <i class="fas fa-times"></i>
                    </button>

                    <div id="search-spinner" class="absolute right-3 hidden pointer-events-none bg-white z-10 pl-2">
                        <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </div>
                <ul id="search-results" class="absolute w-full bg-white mt-1.5 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden text-xs divide-y divide-gray-100 border border-gray-100 custom-scrollbar"></ul>
            </div>

            <button onclick="closeFormModal()" class="absolute top-4 right-4 z-[1000] bg-white w-11 h-11 flex items-center justify-center rounded-xl shadow-lg hidden md:flex text-gray-500 hover:text-red-500 border border-gray-100 transition-colors hover:bg-red-50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL: ATUR JADWAL                             -->
<!-- ============================================== -->
<div id="modal-jadwal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm modal-backdrop-anim" onclick="closeJadwalModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-4xl p-8 relative flex flex-col max-h-[90vh] modal-panel-anim">
        <h3 id="modal-jadwal-title" class="text-xl font-bold text-gray-800 mb-6 shrink-0 flex items-center gap-2">
            <i class="fas fa-calendar-alt text-blue-600"></i> Atur Jadwal Zona
        </h3>

        <form id="form-jadwal-action" method="POST" class="overflow-y-auto custom-scrollbar pr-3 flex-1">
            <?= csrf_field() ?>
            <div class="grid gap-3" id="jadwal-container"></div>

            <div class="flex justify-end gap-3 pt-6 mt-5 border-t border-gray-100 shrink-0 sticky bottom-0 bg-white pb-2">
                <button type="button" onclick="closeJadwalModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL: KELOLA ANGGOTA PKL                      -->
<!-- ============================================== -->
<div id="modal-assign" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm modal-backdrop-anim" onclick="closeAssignModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-xl p-8 relative flex flex-col max-h-[85vh] modal-panel-anim">
        <div class="flex justify-between items-center mb-4 shrink-0">
            <h3 id="modal-assign-title" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-users-cog text-emerald-600"></i> Atur Anggota Zona
            </h3>
            <button onclick="closeAssignModal()" class="text-gray-400 hover:text-red-500 transition-colors bg-gray-50 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex gap-6 mb-5 border-b border-gray-100 shrink-0 px-2">
            <button type="button" onclick="switchTab('kelas')" id="tab-kelas" class="pb-2.5 text-sm font-bold border-b-2 border-blue-600 text-blue-600 transition-all">Per Kelas (Group)</button>
            <button type="button" onclick="switchTab('siswa')" id="tab-siswa" class="pb-2.5 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-800 transition-all">Per Siswa (Individu)</button>
        </div>

        <div class="relative mb-5 shrink-0">
            <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400 text-sm"></i>
            <input type="text" id="search-anggota" placeholder="Ketik nama kelas atau siswa..." class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 hover:bg-white outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>

        <form id="form-assign-action" method="POST" class="flex-1 overflow-hidden flex flex-col min-h-[300px]">
            <?= csrf_field() ?>

            <!-- Tab Content Kelas -->
            <div id="content-kelas" class="tab-content overflow-y-auto custom-scrollbar pr-2 flex-1 space-y-2 mb-4 block">
                <?php foreach ($all_kelas as $k): ?>
                    <label class="kelas-item flex items-center gap-4 p-3 bg-white border border-gray-100 rounded-xl cursor-pointer hover:bg-blue-50/50 hover:border-blue-200 transition-colors group"
                        data-nama="<?= strtolower((string)$k['nama_kelas']) ?>"
                        data-zona="<?= $k['zona_id'] ?>"
                        data-namazona="<?= esc((string)($k['nama_zona_kelas'] ?? '')) ?>">
                        <input type="checkbox" name="kelas_ids[]" value="<?= $k['id_kelas'] ?>" class="chk-assign-kelas w-4.5 h-4.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shrink-0 ml-1">
                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <div class="flex justify-between items-start gap-2">
                                <p class="text-sm font-bold text-gray-800 leading-none truncate mt-0.5">Kelas <?= esc((string)$k['nama_kelas']) ?></p>
                                <span class="badge-zona shrink-0 mt-0.5"></span>
                            </div>
                            <p class="text-[10px] font-medium text-gray-400 mt-1 truncate">Semua siswa di kelas ini ikut pindah</p>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Tab Content Siswa -->
            <div id="content-siswa" class="tab-content overflow-y-auto custom-scrollbar pr-2 flex-1 space-y-1.5 mb-4 hidden">
                <?php foreach ($all_siswa as $s): 
                    $inisial = mb_strtoupper(mb_substr((string) ($s['nama_siswa'] ?? 'U'), 0, 1));
                ?>
                    <label class="siswa-item flex items-center gap-3.5 p-2.5 bg-white border border-gray-50 border-b-gray-100 rounded-xl cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-100 transition-colors group"
                        data-nama="<?= strtolower((string)$s['nama_siswa']) ?>"
                        data-kelas="<?= strtolower((string)$s['nama_kelas']) ?>"
                        data-zona="<?= $s['zona_id'] ?>"
                        data-namazona="<?= esc((string)($s['nama_zona_siswa'] ?? '')) ?>">
                        <input type="checkbox" name="siswa_ids[]" value="<?= $s['id_siswa'] ?>" class="chk-assign-siswa w-4.5 h-4.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 shrink-0 ml-1">
                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0 group-hover:bg-white group-hover:text-emerald-600 transition-colors">
                            <?= $inisial ?>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <div class="flex justify-between items-start gap-2">
                                <p class="text-sm font-bold text-gray-800 leading-none truncate mt-1 group-hover:text-emerald-800"><?= esc((string)$s['nama_siswa']) ?></p>
                                <span class="badge-zona shrink-0 mt-0.5"></span>
                            </div>
                            <p class="text-[10px] font-semibold text-gray-400 mt-1 truncate"><span class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-500"><?= esc((string)$s['nis']) ?></span> &bull; Kelas <?= esc((string)($s['nama_kelas'] ?? '-')) ?></p>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Empty Search State -->
            <div id="empty-search-anggota" class="hidden flex-col items-center justify-center h-full text-center pb-8 opacity-60">
                <i class="fas fa-search-minus text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm font-bold text-gray-500">Anggota Tidak Ditemukan</p>
                <p class="text-xs text-gray-400 mt-1">Coba kata kunci pencarian yang lain.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 shrink-0">
                <button type="button" onclick="closeAssignModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ==========================================
    // LOGIKA DROPDOWN AKSI (⋮) 
    // ==========================================
    function toggleActionMenu(event, menuId) {
        event.stopPropagation();
        const menu = document.getElementById(menuId);
        const isOpen = !menu.classList.contains('hidden');

        document.querySelectorAll('.action-menu').forEach(m => m.classList.add('hidden'));

        if (!isOpen) {
            menu.classList.remove('hidden');
        }
    }
    document.addEventListener('click', () => {
        document.querySelectorAll('.action-menu').forEach(m => m.classList.add('hidden'));
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.action-menu').forEach(m => m.classList.add('hidden'));
        }
    });

    // ==========================================
    // INIT PETA PREVIEW
    // ==========================================
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.map-preview').forEach((el) => {
            const lat = parseFloat(el.getAttribute('data-lat'));
            const lng = parseFloat(el.getAttribute('data-lng'));
            const rad = parseFloat(el.getAttribute('data-rad'));
            const m = window.L.map(el, { zoomControl: false, dragging: false, scrollWheelZoom: false, doubleClickZoom: false }).setView([lat, lng], 15);
            window.L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(m);
            window.L.circle([lat, lng], { radius: rad, fillColor: '#3B82F6', color: '#2563EB', weight: 2, fillOpacity: 0.2 }).addTo(m);
            window.L.circleMarker([lat, lng], { radius: 4, fillColor: '#DC2626', color: '#fff', weight: 1.5, fillOpacity: 1 }).addTo(m);
        });
    });

    // ==========================================
    // LOGIKA PETA INTERAKTIF (MODAL FORM)
    // ==========================================
    let interactiveMap = null, interactiveMarker = null, interactiveCircle = null;
    const defaultLat = -6.20000000, defaultLng = 106.81666600;

    function initInteractiveMap(lat, lng, rad) {
        if (!interactiveMap) {
            interactiveMap = window.L.map('interactive-map').setView([lat, lng], 16);
            window.L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] }).addTo(interactiveMap);
            interactiveMap.on('click', function(e) {
                updateMapMarker(e.latlng.lat, e.latlng.lng, document.getElementById('input-rad').value);
            });
        } else {
            interactiveMap.setView([lat, lng], 16);
        }
        // Delay slighty longer to allow modal CSS transition to finish before calculating size
        setTimeout(() => {
            interactiveMap.invalidateSize();
            updateMapMarker(lat, lng, rad);
        }, 350); 
    }

    function updateMapMarker(lat, lng, rad) {
        document.getElementById('input-lat').value = lat.toFixed(8);
        document.getElementById('input-lng').value = lng.toFixed(8);
        if (interactiveMarker) interactiveMap.removeLayer(interactiveMarker);
        if (interactiveCircle) interactiveMap.removeLayer(interactiveCircle);
        interactiveMarker = window.L.marker([lat, lng]).addTo(interactiveMap);
        interactiveCircle = window.L.circle([lat, lng], { radius: rad, fillColor: '#3B82F6', color: '#2563EB', weight: 2, fillOpacity: 0.3 }).addTo(interactiveMap);
    }

    document.getElementById('input-rad').addEventListener('input', function() {
        const lat = parseFloat(document.getElementById('input-lat').value);
        const lng = parseFloat(document.getElementById('input-lng').value);
        if (!isNaN(lat) && !isNaN(lng)) updateMapMarker(lat, lng, this.value);
    });

    // --- Search API (Photon/Komoot) ---
    let searchTimeout;
    const searchInput = document.getElementById('map-search'), searchResults = document.getElementById('search-results'), searchSpinner = document.getElementById('search-spinner'), searchClear = document.getElementById('search-clear');

    searchInput.addEventListener('input', function(e) { searchClear.classList.toggle('hidden', e.target.value.length === 0); });
    searchClear.addEventListener('click', function() { searchInput.value = ''; searchResults.classList.add('hidden'); searchClear.classList.add('hidden'); searchInput.focus(); });

    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        if (query.length === 0) { searchResults.classList.add('hidden'); searchSpinner.classList.add('hidden'); return; }

        const gmapsMatch = query.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        const coordMatch = query.match(/^(-?\d+(\.\d+)?)(?:\s*,\s*|\s+)(-?\d+(\.\d+)?)$/);

        if (gmapsMatch || coordMatch) {
            let lat = parseFloat(gmapsMatch ? gmapsMatch[1] : coordMatch[1]);
            let lng = parseFloat(gmapsMatch ? gmapsMatch[2] : coordMatch[3]);
            interactiveMap.flyTo([lat, lng], 17, { animate: true, duration: 1.5 });
            updateMapMarker(lat, lng, document.getElementById('input-rad').value);
            searchSpinner.classList.add('hidden');
            searchResults.innerHTML = `<li class="p-4 bg-emerald-50 text-emerald-700 text-center font-bold border-b border-emerald-100"><i class="fa-solid fa-check-circle mr-1"></i> Data Koordinat berhasil diekstrak!</li>`;
            searchResults.classList.remove('hidden');
            setTimeout(() => { searchResults.classList.add('hidden'); }, 3500);
            return;
        }

        if (query.length < 3) { searchResults.classList.add('hidden'); searchSpinner.classList.add('hidden'); return; }
        
        searchSpinner.classList.remove('hidden');
        searchTimeout = setTimeout(() => {
            fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5`)
                .then(res => res.ok ? res.json() : Promise.reject('API Error'))
                .then(data => {
                    searchSpinner.classList.add('hidden');
                    searchResults.innerHTML = '';
                    if (data.features && data.features.length > 0) {
                        data.features.forEach(item => {
                            const props = item.properties;
                            let displayName = props.city && props.city !== props.name ? props.city : (props.state && props.state !== props.name ? props.state : `${props.street || 'Area Publik'}, ${props.country || ''}`);
                            const li = document.createElement('li');
                            li.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer transition-colors text-gray-700 font-medium border-b border-gray-50 last:border-0';
                            li.innerHTML = `<div class="font-bold text-gray-800">${props.name || 'Lokasi Terdaftar'}</div><div class="text-[10px] text-gray-500 mt-0.5"><i class="fa-solid fa-map-pin mr-1 text-gray-400"></i>${displayName}</div>`;
                            li.onclick = () => {
                                const lat = parseFloat(item.geometry.coordinates[1]), lon = parseFloat(item.geometry.coordinates[0]);
                                interactiveMap.flyTo([lat, lon], 17, { animate: true, duration: 1.5 });
                                updateMapMarker(lat, lon, document.getElementById('input-rad').value);
                                searchInput.value = props.name || displayName;
                                searchResults.classList.add('hidden');
                            };
                            searchResults.appendChild(li);
                        });
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.innerHTML = `<li class="p-4 flex flex-col items-center justify-center text-center bg-slate-50"><i class="fa-solid fa-search-location text-2xl text-slate-300 mb-2"></i><span class="text-sm font-bold text-slate-600">Tidak ditemukan di Database</span><span class="text-[10px] text-slate-500 mt-1">Tips: Cari lokasi di Google Maps lalu paste Link URL-nya ke kotak ini.</span></li>`;
                        searchResults.classList.remove('hidden');
                    }
                }).catch(() => {
                    searchSpinner.classList.add('hidden');
                    searchResults.innerHTML = '<li class="p-4 text-red-500 text-center font-bold italic"><i class="fa-solid fa-wifi mr-1"></i> Gagal terhubung ke server pencarian.</li>';
                    searchResults.classList.remove('hidden');
                });
        }, 600);
    });
    document.addEventListener('click', function(e) { if (!searchInput.contains(e.target) && !searchResults.contains(e.target) && !searchClear.contains(e.target)) searchResults.classList.add('hidden'); });

    // ==========================================
    // MODAL HANDLERS DENGAN ANIMASI
    // ==========================================
    function openModalWithAnim(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
        // Trigger reflow & add show class for animation
        void modal.offsetWidth;
        modal.classList.add('modal-show');
    }

    function closeModalWithAnim(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('modal-show');
        setTimeout(() => {
            modal.classList.replace('flex', 'hidden');
            document.body.classList.remove('modal-active');
        }, 300); // match CSS transition duration
    }

    // Modal Form Zona
    function openFormModal() {
        document.getElementById('modal-title').innerText = "Tambah Zona PKL / Kegiatan";
        document.getElementById('form-action').reset();
        document.getElementById('form-action').action = "<?= base_url('admin/zona/store') ?>";
        document.getElementById('wrapper-jam-default').style.display = 'block';
        document.getElementById('input-rad').value = "50";
        document.getElementById('map-search').value = ""; searchResults.classList.add('hidden');
        openModalWithAnim('modal-zona');
        initInteractiveMap(defaultLat, defaultLng, 50);
    }
    function openEditModal(data) {
        document.getElementById('modal-title').innerText = "Edit Pengaturan Zona";
        document.getElementById('form-action').action = "<?= base_url('admin/zona/update/') ?>" + data.id_zona;
        document.getElementById('wrapper-jam-default').style.display = 'none';
        document.getElementById('input-nama').value = data.nama_zona;
        document.getElementById('input-lat').value = data.latitude;
        document.getElementById('input-lng').value = data.longitude;
        document.getElementById('input-rad').value = data.radius;
        document.getElementById('map-search').value = ""; searchResults.classList.add('hidden');
        document.getElementById('input-buka').removeAttribute('required'); document.getElementById('input-masuk').removeAttribute('required'); document.getElementById('input-pulang').removeAttribute('required');
        openModalWithAnim('modal-zona');
        initInteractiveMap(parseFloat(data.latitude), parseFloat(data.longitude), data.radius);
    }
    function closeFormModal() { closeModalWithAnim('modal-zona'); }

    // Modal Jadwal
    function openJadwalModal(data) {
        document.getElementById('modal-jadwal-title').innerHTML = `<i class="fas fa-calendar-alt text-blue-600 mr-2"></i> Jadwal: ${data.nama_zona}`;
        document.getElementById('form-jadwal-action').action = "<?= base_url('admin/zona/updateJadwal/') ?>" + data.id_zona;
        let html = '';
        data.jadwal.forEach(j => {
            let isChecked = j.is_libur == 1;
            let checkAttr = isChecked ? 'checked' : '';
            let opacity = isChecked ? 'opacity-50 grayscale' : '';
            // Gunakan pointer-events-none + readonly agar nilai tetap ter-submit tapi UI terkunci
            let lockedClass = isChecked ? 'pointer-events-none bg-gray-100 text-gray-400 border-gray-100' : '';
            let bgMasuk = !isChecked ? 'bg-emerald-50 border-emerald-200 focus:border-emerald-500' : '';
            let bgPulang = !isChecked ? 'bg-indigo-50 border-indigo-200 focus:border-indigo-500' : '';
            let bgBuka = !isChecked ? 'bg-gray-50 border-gray-200 focus:border-blue-500' : '';

            html += `
            <div class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 transition-all bg-white hover:border-blue-100 shadow-sm mb-2 ${opacity}" id="row-hari-${j.kode_hari}">
                <div class="w-24 shrink-0 font-bold text-gray-700 tracking-wide">${j.nama_hari}</div>
                <div class="flex-1 grid grid-cols-3 gap-3">
                    <div><span class="text-[9px] font-bold uppercase text-gray-400 block mb-1">Buka Absen</span>
                    <input type="time" name="buka[${j.kode_hari}]" value="${j.waktu_buka_absen}" class="inp-waktu w-full text-xs p-2.5 rounded-lg border outline-none font-mono transition-all ${bgBuka} ${lockedClass}" ${isChecked?'readonly':''}></div>
                    <div><span class="text-[9px] font-bold uppercase text-emerald-600 block mb-1">Masuk</span>
                    <input type="time" name="masuk[${j.kode_hari}]" value="${j.jam_masuk}" class="inp-waktu w-full text-xs p-2.5 rounded-lg border outline-none font-mono text-emerald-700 transition-all ${bgMasuk} ${lockedClass}" ${isChecked?'readonly':''}></div>
                    <div><span class="text-[9px] font-bold uppercase text-indigo-600 block mb-1">Pulang</span>
                    <input type="time" name="pulang[${j.kode_hari}]" value="${j.jam_pulang}" class="inp-waktu w-full text-xs p-2.5 rounded-lg border outline-none font-mono text-indigo-700 transition-all ${bgPulang} ${lockedClass}" ${isChecked?'readonly':''}></div>
                </div>
                <div class="w-20 shrink-0 flex flex-col items-center justify-center border-l border-gray-100 pl-3">
                    <span class="text-[9px] font-bold uppercase text-red-500 block mb-2">Libur?</span>
                    <input type="checkbox" name="is_libur[]" value="${j.kode_hari}" ${checkAttr} class="w-5 h-5 rounded border-gray-300 text-red-500 cursor-pointer focus:ring-red-500" onchange="toggleLiburStyle(${j.kode_hari}, this)">
                </div>
            </div>`;
        });
        document.getElementById('jadwal-container').innerHTML = html;
        openModalWithAnim('modal-jadwal');
    }

    function toggleLiburStyle(kodeHari, checkbox) {
        const row = document.getElementById('row-hari-' + kodeHari);
        const inputs = row.querySelectorAll('.inp-waktu');
        if (checkbox.checked) {
            row.classList.add('opacity-50', 'grayscale');
            inputs.forEach(inp => {
                inp.readOnly = true;
                inp.className = 'inp-waktu w-full text-xs p-2.5 rounded-lg border outline-none font-mono transition-all pointer-events-none bg-gray-100 text-gray-400 border-gray-100';
            });
        } else {
            row.classList.remove('opacity-50', 'grayscale');
            inputs.forEach(inp => {
                inp.readOnly = false;
                if(inp.name.startsWith('buka')) inp.className = 'inp-waktu w-full text-xs p-2.5 rounded-lg border outline-none font-mono transition-all bg-gray-50 border-gray-200 focus:border-blue-500';
                else if(inp.name.startsWith('masuk')) inp.className = 'inp-waktu w-full text-xs p-2.5 rounded-lg border outline-none font-mono text-emerald-700 transition-all bg-emerald-50 border-emerald-200 focus:border-emerald-500';
                else if(inp.name.startsWith('pulang')) inp.className = 'inp-waktu w-full text-xs p-2.5 rounded-lg border outline-none font-mono text-indigo-700 transition-all bg-indigo-50 border-indigo-200 focus:border-indigo-500';
            });
        }
    }
    function closeJadwalModal() { closeModalWithAnim('modal-jadwal'); }

    // Modal Assign
    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.replace('block', 'hidden'));
        document.getElementById('content-' + tab).classList.replace('hidden', 'block');
        document.getElementById('tab-kelas').className = "pb-2.5 text-sm font-bold transition-all " + (tab === 'kelas' ? "border-b-2 border-blue-600 text-blue-600" : "border-b-2 border-transparent text-gray-500 hover:text-gray-800");
        document.getElementById('tab-siswa').className = "pb-2.5 text-sm font-bold transition-all " + (tab === 'siswa' ? "border-b-2 border-blue-600 text-blue-600" : "border-b-2 border-transparent text-gray-500 hover:text-gray-800");
        document.getElementById('search-anggota').dispatchEvent(new window.Event('input')); // re-trigger search
    }

    document.getElementById('search-anggota').addEventListener('input', function(e) {
        const keyword = e.target.value.toLowerCase();
        let activeTab = document.getElementById('content-kelas').classList.contains('block') ? 'kelas' : 'siswa';
        let found = false;

        if (activeTab === 'kelas') {
            document.querySelectorAll('.kelas-item').forEach(el => {
                if (el.getAttribute('data-nama').includes(keyword)) { el.style.display = 'flex'; found = true; } 
                else { el.style.display = 'none'; }
            });
        } else {
            document.querySelectorAll('.siswa-item').forEach(el => {
                if (el.getAttribute('data-nama').includes(keyword) || el.getAttribute('data-kelas').includes(keyword)) { el.style.display = 'flex'; found = true; } 
                else { el.style.display = 'none'; }
            });
        }
        
        document.getElementById('empty-search-anggota').style.display = found ? 'none' : 'flex';
    });

    function openAssignModal(data) {
        document.getElementById('modal-assign-title').innerHTML = `<i class="fas fa-users-cog text-emerald-600 mr-2"></i> Anggota: ${data.nama_zona}`;
        document.getElementById('form-assign-action').action = "<?= base_url('admin/zona/assignAnggota/') ?>" + data.id_zona;
        document.getElementById('search-anggota').value = '';
        switchTab('kelas');
        
        const updateItemState = (el, chk, badge, zonaId, namaZona) => {
            el.style.display = 'flex';
            if (zonaId == data.id_zona) {
                chk.checked = true;
                badge.className = "badge-zona shrink-0 text-[9px] font-black px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200";
                badge.innerHTML = "<i class='fas fa-check'></i> ZONA INI";
                el.classList.add('bg-emerald-50/30', 'border-emerald-100');
            } else {
                chk.checked = false;
                el.classList.remove('bg-emerald-50/30', 'border-emerald-100');
                if (zonaId && zonaId !== 'null' && zonaId !== '') {
                    badge.className = "badge-zona shrink-0 text-[9px] font-black px-2 py-0.5 rounded bg-amber-50 text-amber-600 border border-amber-200";
                    badge.innerHTML = "<i class='fas fa-map-pin'></i> " + namaZona;
                } else {
                    badge.className = "badge-zona shrink-0 text-[9px] font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-500 border border-gray-200";
                    badge.innerHTML = chk.classList.contains('chk-assign-kelas') ? "SEKOLAH PUSAT" : "MENGIKUTI KELAS";
                }
            }
        };

        document.querySelectorAll('.kelas-item').forEach(el => updateItemState(el, el.querySelector('.chk-assign-kelas'), el.querySelector('.badge-zona'), el.getAttribute('data-zona'), el.getAttribute('data-namazona')));
        document.querySelectorAll('.siswa-item').forEach(el => updateItemState(el, el.querySelector('.chk-assign-siswa'), el.querySelector('.badge-zona'), el.getAttribute('data-zona'), el.getAttribute('data-namazona')));

        openModalWithAnim('modal-assign');
    }
    function closeAssignModal() { closeModalWithAnim('modal-assign'); }
</script>
<?= $this->endSection() ?>