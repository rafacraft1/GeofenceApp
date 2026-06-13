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
</style>

<div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Zona Absensi</h2>
        <p class="text-sm text-gray-500 mt-1">Atur multi-lokasi, jadwal 7 hari, dan anggota PKL (Per Kelas / Per Siswa).</p>
    </div>
    <button onclick="openFormModal()" class="flex items-center justify-center gap-1.5 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 shadow-md transition-all">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg> Tambah Zona Baru
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
                                <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest border border-blue-200">Sekolah Pusat (Default)</span>
                            <?php else: ?>
                                <span class="bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest border border-amber-200">Khusus / PKL</span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 p-3 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-3 text-sm text-gray-600 font-medium">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                                </svg>
                                <span>Jadwal Khusus Zona Ini (Senin - Minggu)</span>
                            </div>
                            <button onclick='openJadwalModal(<?= htmlspecialchars(json_encode($z), ENT_QUOTES, "UTF-8") ?>)' class="bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-colors">Atur Jadwal & Jam</button>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3 pt-4 border-t border-gray-100">
                        <button onclick='openEditModal(<?= htmlspecialchars(json_encode($z), ENT_QUOTES, "UTF-8") ?>)' class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition-colors">Lokasi & Radius</button>

                        <?php if (!$z['is_default']): ?>
                            <button onclick='openAssignModal(<?= htmlspecialchars(json_encode($z), ENT_QUOTES, "UTF-8") ?>)' class="text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-4 py-2 rounded-lg transition-colors">Kelola Anggota</button>

                            <form action="<?= base_url('admin/zona/setDefault/' . (string)$z['id_zona']) ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-xs font-bold text-slate-600 bg-slate-50 hover:bg-slate-200 px-4 py-2 rounded-lg transition-colors">Jadikan Default</button>
                            </form>
                            <form action="<?= base_url('admin/zona/delete/' . (string)$z['id_zona']) ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <button type="button" class="btn-confirm text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-lg transition-colors" data-text="Hapus zona PKL ini beserta seluruh jadwal di dalamnya?" data-btn="Hapus Permanen">Hapus Zona</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-full md:w-1/3 min-h-[200px] md:min-h-full border-l border-gray-100 relative z-0">
                    <div class="map-preview absolute inset-0" data-lat="<?= $z['latitude'] ?>" data-lng="<?= $z['longitude'] ?>" data-rad="<?= $z['radius'] ?>"></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="modal-zona" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeFormModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-5xl p-0 relative overflow-hidden flex flex-col md:flex-row h-[90vh] md:h-auto max-h-[800px]">
        <div class="w-full md:w-1/2 p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modal-title" class="text-xl font-bold text-gray-800">Tambah Zona Baru</h3>
                <button onclick="closeFormModal()" class="md:hidden text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg></button>
            </div>

            <form id="form-action" method="POST" action="<?= base_url('admin/zona/store') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Nama Lokasi / Instansi PKL</label>
                    <input type="text" id="input-nama" name="nama_zona" required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Contoh: PT. Telkom Indonesia">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Latitude</label>
                        <input type="text" id="input-lat" name="latitude" readonly required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Longitude</label>
                        <input type="text" id="input-lng" name="longitude" readonly required class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-100 text-gray-500 cursor-not-allowed outline-none">
                    </div>
                </div>
                <div class="bg-blue-50 text-blue-700 text-[11px] font-medium p-3 rounded-xl border border-blue-100 flex items-start gap-2">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    Ketik lokasi di kotak pencarian peta, atau geser peta lalu klik titik gedungnya secara manual.
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Radius Area Absen (Meter)</label>
                    <input type="number" id="input-rad" name="radius" required value="50" min="10" class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <div id="wrapper-jam-default">
                    <hr class="border border-gray-100 my-4">
                    <p class="text-xs font-bold text-gray-600 mb-2">PENGATURAN JAM DEFAULT ZONA</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Buka Absen</label>
                            <input type="time" id="input-buka" name="waktu_buka_absen" class="w-full border border-gray-200 rounded-xl p-2.5 text-xs bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-emerald-600 uppercase mb-1">Jam Masuk</label>
                            <input type="time" id="input-masuk" name="jam_masuk" class="w-full border border-emerald-200 rounded-xl p-2.5 text-xs bg-emerald-50 outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-indigo-600 uppercase mb-1">Jam Pulang</label>
                            <input type="time" id="input-pulang" name="jam_pulang" class="w-full border border-indigo-200 rounded-xl p-2.5 text-xs bg-indigo-50 outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6">
                    <button type="button" onclick="closeFormModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 transition-all">Simpan Zona</button>
                </div>
            </form>
        </div>

        <div class="w-full md:w-1/2 min-h-[400px] relative z-0 bg-slate-100 border-l border-gray-100">
            <div id="interactive-map" class="absolute inset-0 z-0"></div>

            <div class="absolute top-4 left-4 right-16 z-[1000]">
                <div class="relative flex items-center shadow-lg rounded-xl overflow-hidden bg-white border border-gray-100">
                    <svg class="w-5 h-5 absolute left-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="map-search" placeholder="Cari nama lokasi, jalan, kota, atau URL Google Maps..." class="w-full pl-10 pr-10 py-3 text-sm font-medium outline-none border-none focus:ring-0 placeholder-gray-400 text-gray-700" autocomplete="off">

                    <button type="button" id="search-clear" class="absolute right-3 hidden text-gray-400 hover:text-red-500 transition-colors bg-white z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <div id="search-spinner" class="absolute right-3 hidden pointer-events-none bg-white z-10 pl-2">
                        <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </div>
                <ul id="search-results" class="absolute w-full bg-white mt-1.5 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden text-xs divide-y divide-gray-100 border border-gray-100 scrollbar-hide"></ul>
            </div>

            <button onclick="closeFormModal()" class="absolute top-4 right-4 z-[1000] bg-white p-2.5 rounded-xl shadow-lg hidden md:block text-gray-500 hover:text-red-500 border border-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>

<div id="modal-jadwal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeJadwalModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-4xl p-8 relative flex flex-col max-h-[90vh]">
        <h3 id="modal-jadwal-title" class="text-xl font-bold text-gray-800 mb-6 shrink-0">Atur Jadwal Zona</h3>

        <form id="form-jadwal-action" method="POST" class="overflow-y-auto pr-2 flex-1 scrollbar-hide">
            <?= csrf_field() ?>
            <div class="grid gap-3" id="jadwal-container"></div>

            <div class="flex justify-end gap-3 pt-6 mt-4 border-t border-gray-100 shrink-0">
                <button type="button" onclick="closeJadwalModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 transition-all">Simpan Jadwal Zona</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-assign" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAssignModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl z-10 w-full max-w-xl p-8 relative flex flex-col max-h-[85vh]">
        <div class="flex justify-between items-center mb-2 shrink-0">
            <h3 id="modal-assign-title" class="text-xl font-bold text-gray-800">Atur Anggota Zona</h3>
            <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex gap-4 mb-4 border-b border-gray-100 shrink-0">
            <button type="button" onclick="switchTab('kelas')" id="tab-kelas" class="pb-2 text-sm font-bold border-b-2 border-blue-600 text-blue-600 transition-all">Per Kelas (Group)</button>
            <button type="button" onclick="switchTab('siswa')" id="tab-siswa" class="pb-2 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-800 transition-all">Per Siswa (Individu)</button>
        </div>

        <input type="text" id="search-anggota" placeholder="Cari nama kelas atau siswa..." class="w-full border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 transition-all mb-4 shrink-0">

        <form id="form-assign-action" method="POST" class="flex-1 overflow-hidden flex flex-col">
            <?= csrf_field() ?>

            <div id="content-kelas" class="tab-content overflow-y-auto pr-2 flex-1 space-y-2 mb-4 border border-gray-100 rounded-xl p-2 bg-gray-50/50 block">
                <?php foreach ($all_kelas as $k): ?>
                    <label class="kelas-item flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-all"
                        data-nama="<?= strtolower((string)$k['nama_kelas']) ?>"
                        data-zona="<?= $k['zona_id'] ?>"
                        data-namazona="<?= esc((string)($k['nama_zona_kelas'] ?? '')) ?>">
                        <input type="checkbox" name="kelas_ids[]" value="<?= $k['id_kelas'] ?>" class="chk-assign-kelas w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shrink-0">
                        <div class="flex-1 overflow-hidden">
                            <div class="flex justify-between items-start gap-2">
                                <p class="text-sm font-bold text-gray-800 leading-none truncate">Kelas <?= esc((string)$k['nama_kelas']) ?></p>
                                <span class="badge-zona shrink-0"></span>
                            </div>
                            <p class="text-[10px] font-medium text-gray-400 mt-1 truncate">Seluruh siswa di kelas ini akan dialihkan ke zona ini</p>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div id="content-siswa" class="tab-content overflow-y-auto pr-2 flex-1 space-y-2 mb-4 border border-gray-100 rounded-xl p-2 bg-gray-50/50 hidden">
                <?php foreach ($all_siswa as $s): ?>
                    <label class="siswa-item flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-all"
                        data-nama="<?= strtolower((string)$s['nama_siswa']) ?>"
                        data-kelas="<?= strtolower((string)$s['nama_kelas']) ?>"
                        data-zona="<?= $s['zona_id'] ?>"
                        data-namazona="<?= esc((string)($s['nama_zona_siswa'] ?? '')) ?>">
                        <input type="checkbox" name="siswa_ids[]" value="<?= $s['id_siswa'] ?>" class="chk-assign-siswa w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shrink-0">
                        <div class="flex-1 overflow-hidden">
                            <div class="flex justify-between items-start gap-2">
                                <p class="text-sm font-bold text-gray-800 leading-none truncate"><?= esc((string)$s['nama_siswa']) ?></p>
                                <span class="badge-zona shrink-0"></span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1"><?= esc((string)$s['nis']) ?> • Kelas <?= esc((string)($s['nama_kelas'] ?? '-')) ?></p>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 shrink-0">
                <button type="button" onclick="closeAssignModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:bg-blue-700 transition-all">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.map-preview').forEach((el) => {
            const lat = parseFloat(el.getAttribute('data-lat'));
            const lng = parseFloat(el.getAttribute('data-lng'));
            const rad = parseFloat(el.getAttribute('data-rad'));
            const m = window.L.map(el, {
                zoomControl: false,
                dragging: false,
                scrollWheelZoom: false
            }).setView([lat, lng], 15);
            window.L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(m);
            window.L.circle([lat, lng], {
                radius: rad,
                fillColor: '#3B82F6',
                color: '#2563EB',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.2
            }).addTo(m);
            window.L.circleMarker([lat, lng], {
                radius: 4,
                fillColor: '#DC2626',
                color: '#fff',
                weight: 1,
                fillOpacity: 1
            }).addTo(m);
        });
    });

    let interactiveMap = null;
    let interactiveMarker = null;
    let interactiveCircle = null;
    const defaultLat = -6.20000000;
    const defaultLng = 106.81666600;

    function initInteractiveMap(lat, lng, rad) {
        if (!interactiveMap) {
            interactiveMap = window.L.map('interactive-map').setView([lat, lng], 16);
            window.L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(interactiveMap);
            interactiveMap.on('click', function(e) {
                updateMapMarker(e.latlng.lat, e.latlng.lng, document.getElementById('input-rad').value);
            });
        } else {
            interactiveMap.setView([lat, lng], 16);
        }
        setTimeout(() => {
            interactiveMap.invalidateSize();
            updateMapMarker(lat, lng, rad);
        }, 150);
    }

    function updateMapMarker(lat, lng, rad) {
        document.getElementById('input-lat').value = lat.toFixed(8);
        document.getElementById('input-lng').value = lng.toFixed(8);
        if (interactiveMarker) interactiveMap.removeLayer(interactiveMarker);
        if (interactiveCircle) interactiveMap.removeLayer(interactiveCircle);
        interactiveMarker = window.L.marker([lat, lng]).addTo(interactiveMap);
        interactiveCircle = window.L.circle([lat, lng], {
            radius: rad,
            fillColor: '#3B82F6',
            color: '#2563EB',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.3
        }).addTo(interactiveMap);
    }

    document.getElementById('input-rad').addEventListener('input', function(e) {
        const lat = parseFloat(document.getElementById('input-lat').value);
        const lng = parseFloat(document.getElementById('input-lng').value);
        if (!isNaN(lat) && !isNaN(lng)) updateMapMarker(lat, lng, this.value);
    });

    let searchTimeout;
    const searchInput = document.getElementById('map-search');
    const searchResults = document.getElementById('search-results');
    const searchSpinner = document.getElementById('search-spinner');
    const searchClear = document.getElementById('search-clear');

    searchInput.addEventListener('input', function(e) {
        if (e.target.value.length > 0) {
            searchClear.classList.remove('hidden');
        } else {
            searchClear.classList.add('hidden');
        }
    });

    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        searchResults.classList.add('hidden');
        searchClear.classList.add('hidden');
        searchInput.focus();
    });

    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        if (query.length === 0) {
            searchResults.classList.add('hidden');
            searchSpinner.classList.add('hidden');
            return;
        }

        searchClear.classList.remove('hidden');

        const gmapsRegex = /@(-?\d+\.\d+),(-?\d+\.\d+)/;
        const gmapsMatch = query.match(gmapsRegex);

        const coordRegex = /^(-?\d+(\.\d+)?)(?:\s*,\s*|\s+)(-?\d+(\.\d+)?)$/;
        const coordMatch = query.match(coordRegex);

        if (gmapsMatch || coordMatch) {
            let lat, lng;
            if (gmapsMatch) {
                lat = parseFloat(gmapsMatch[1]);
                lng = parseFloat(gmapsMatch[2]);
            } else {
                lat = parseFloat(coordMatch[1]);
                lng = parseFloat(coordMatch[3]);
            }

            interactiveMap.flyTo([lat, lng], 17, {
                animate: true,
                duration: 1.5
            });
            updateMapMarker(lat, lng, document.getElementById('input-rad').value);

            searchSpinner.classList.add('hidden');
            searchResults.innerHTML = `
                <li class="p-4 bg-emerald-50 text-emerald-700 text-center font-bold border-b border-emerald-100">
                    <i class="fa-solid fa-check-circle mr-1"></i> Data Koordinat berhasil diekstrak!
                </li>`;
            searchResults.classList.remove('hidden');

            setTimeout(() => {
                searchResults.classList.add('hidden');
            }, 3500);
            return;
        }

        if (query.length < 3) {
            searchResults.classList.add('hidden');
            searchSpinner.classList.add('hidden');
            return;
        }

        searchSpinner.classList.remove('hidden');

        searchTimeout = setTimeout(() => {
            const apiUrl = `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5`;

            fetch(apiUrl)
                .then(res => {
                    if (!res.ok) throw new Error('API Error');
                    return res.json();
                })
                .then(data => {
                    searchSpinner.classList.add('hidden');
                    searchResults.innerHTML = '';

                    if (data.features && data.features.length > 0) {
                        data.features.forEach(item => {
                            const props = item.properties;

                            let displayName = '';
                            if (props.city && props.city !== props.name) displayName += props.city;
                            else if (props.state && props.state !== props.name) displayName += props.state;

                            if (!displayName) displayName = `${props.street || 'Area Publik'}, ${props.country || ''}`;

                            const li = document.createElement('li');
                            li.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer transition-colors text-gray-700 font-medium border-b border-gray-50 last:border-0';
                            li.innerHTML = `
                                <div class="font-bold text-gray-800">${props.name || 'Lokasi Terdaftar'}</div>
                                <div class="text-[10px] text-gray-500 mt-0.5"><i class="fa-solid fa-map-pin mr-1 text-gray-400"></i>${displayName}</div>
                            `;

                            li.onclick = () => {
                                const lat = parseFloat(item.geometry.coordinates[1]);
                                const lon = parseFloat(item.geometry.coordinates[0]);

                                interactiveMap.flyTo([lat, lon], 17, {
                                    animate: true,
                                    duration: 1.5
                                });
                                updateMapMarker(lat, lon, document.getElementById('input-rad').value);
                                searchInput.value = props.name || displayName;
                                searchResults.classList.add('hidden');
                            };
                            searchResults.appendChild(li);
                        });
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.innerHTML = `
                            <li class="p-4 flex flex-col items-center justify-center text-center bg-slate-50">
                                <i class="fa-solid fa-search-location text-2xl text-slate-300 mb-2"></i>
                                <span class="text-sm font-bold text-slate-600">Tidak ditemukan di Database</span>
                                <span class="text-[10px] text-slate-500 mt-1">Tips: Cari lokasi di Google Maps lalu paste Link URL-nya ke kotak ini.</span>
                            </li>`;
                        searchResults.classList.remove('hidden');
                    }
                }).catch(() => {
                    searchSpinner.classList.add('hidden');
                    searchResults.innerHTML = '<li class="p-4 text-red-500 text-center font-bold italic"><i class="fa-solid fa-wifi mr-1"></i> Gagal terhubung ke satelit pencarian.</li>';
                    searchResults.classList.remove('hidden');
                });
        }, 600);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target) && !searchClear.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    function openFormModal() {
        document.getElementById('modal-title').innerText = "Tambah Zona PKL / Kegiatan";
        document.getElementById('form-action').reset();
        document.getElementById('form-action').action = "<?= base_url('admin/zona/store') ?>";
        document.getElementById('wrapper-jam-default').style.display = 'block';
        document.getElementById('input-buka').value = "05:00";
        document.getElementById('input-masuk').value = "06:30";
        document.getElementById('input-pulang').value = "15:00";
        document.getElementById('input-rad').value = "50";
        document.getElementById('map-search').value = "";
        searchResults.classList.add('hidden');

        document.getElementById('modal-zona').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
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
        document.getElementById('map-search').value = "";
        searchResults.classList.add('hidden');

        document.getElementById('input-buka').removeAttribute('required');
        document.getElementById('input-masuk').removeAttribute('required');
        document.getElementById('input-pulang').removeAttribute('required');

        document.getElementById('modal-zona').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
        initInteractiveMap(parseFloat(data.latitude), parseFloat(data.longitude), data.radius);
    }

    function closeFormModal() {
        document.getElementById('modal-zona').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    function openJadwalModal(data) {
        document.getElementById('modal-jadwal-title').innerText = "Jadwal & Jam: " + data.nama_zona;
        document.getElementById('form-jadwal-action').action = "<?= base_url('admin/zona/updateJadwal/') ?>" + data.id_zona;

        let html = '';
        data.jadwal.forEach(j => {
            let isLibur = j.is_libur == 1 ? 'checked' : '';
            let opacity = j.is_libur == 1 ? 'opacity-50 grayscale' : '';

            html += `
            <div class="flex items-center gap-4 bg-gray-50 p-3 rounded-xl border border-gray-100 transition-all ${opacity}" id="row-hari-${j.kode_hari}">
                <div class="w-24 shrink-0 font-bold text-gray-700">${j.nama_hari}</div>
                <div class="flex-1 grid grid-cols-3 gap-2">
                    <div><span class="text-[9px] font-bold uppercase text-gray-400 block mb-1">Buka Absen</span>
                    <input type="time" name="buka[${j.kode_hari}]" value="${j.waktu_buka_absen}" class="w-full text-xs p-2 rounded-lg border border-gray-200 outline-none focus:border-blue-500"></div>
                    <div><span class="text-[9px] font-bold uppercase text-emerald-600 block mb-1">Masuk</span>
                    <input type="time" name="masuk[${j.kode_hari}]" value="${j.jam_masuk}" class="w-full text-xs p-2 rounded-lg border border-emerald-200 outline-none focus:border-emerald-500"></div>
                    <div><span class="text-[9px] font-bold uppercase text-indigo-600 block mb-1">Pulang</span>
                    <input type="time" name="pulang[${j.kode_hari}]" value="${j.jam_pulang}" class="w-full text-xs p-2 rounded-lg border border-indigo-200 outline-none focus:border-indigo-500"></div>
                </div>
                <div class="w-24 shrink-0 flex flex-col items-center justify-center border-l border-gray-200 pl-4">
                    <span class="text-[9px] font-bold uppercase text-red-500 block mb-1">Libur?</span>
                    <input type="checkbox" name="is_libur[]" value="${j.kode_hari}" ${isLibur} class="w-5 h-5 rounded border-gray-300 text-red-500 cursor-pointer" onchange="toggleLiburStyle(${j.kode_hari}, this)">
                </div>
            </div>`;
        });

        document.getElementById('jadwal-container').innerHTML = html;
        document.getElementById('modal-jadwal').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function toggleLiburStyle(kodeHari, checkbox) {
        const row = document.getElementById('row-hari-' + kodeHari);
        if (checkbox.checked) row.classList.add('opacity-50', 'grayscale');
        else row.classList.remove('opacity-50', 'grayscale');
    }

    function closeJadwalModal() {
        document.getElementById('modal-jadwal').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }

    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.replace('block', 'hidden'));
        document.getElementById('content-' + tab).classList.replace('hidden', 'block');

        document.getElementById('tab-kelas').className = "pb-2 text-sm font-bold transition-all " + (tab === 'kelas' ? "border-b-2 border-blue-600 text-blue-600" : "border-b-2 border-transparent text-gray-500 hover:text-gray-800");
        document.getElementById('tab-siswa').className = "pb-2 text-sm font-bold transition-all " + (tab === 'siswa' ? "border-b-2 border-blue-600 text-blue-600" : "border-b-2 border-transparent text-gray-500 hover:text-gray-800");

        document.getElementById('search-anggota').dispatchEvent(new window.Event('input'));
    }

    document.getElementById('search-anggota').addEventListener('input', function(e) {
        const keyword = e.target.value.toLowerCase();
        document.querySelectorAll('.kelas-item').forEach(el => {
            const nama = el.getAttribute('data-nama');
            el.style.display = nama.includes(keyword) ? 'flex' : 'none';
        });
        document.querySelectorAll('.siswa-item').forEach(el => {
            const nama = el.getAttribute('data-nama');
            const kelas = el.getAttribute('data-kelas');
            el.style.display = (nama.includes(keyword) || kelas.includes(keyword)) ? 'flex' : 'none';
        });
    });

    function openAssignModal(data) {
        document.getElementById('modal-assign-title').innerText = "Anggota: " + data.nama_zona;
        document.getElementById('form-assign-action').action = "<?= base_url('admin/zona/assignAnggota/') ?>" + data.id_zona;

        document.getElementById('search-anggota').value = '';
        switchTab('kelas');

        document.querySelectorAll('.kelas-item').forEach(el => {
            el.style.display = 'flex';
            const chk = el.querySelector('.chk-assign-kelas');
            const badge = el.querySelector('.badge-zona');
            const zonaId = el.getAttribute('data-zona');
            const namaZona = el.getAttribute('data-namazona');

            if (zonaId == data.id_zona) {
                chk.checked = true;
                badge.className = "badge-zona shrink-0 text-[9px] font-black px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-200";
                badge.innerHTML = "✔️ Zona Ini";
            } else {
                chk.checked = false;
                if (zonaId && zonaId !== 'null' && zonaId !== '') {
                    badge.className = "badge-zona shrink-0 text-[9px] font-black px-2 py-0.5 rounded-md bg-amber-50 text-amber-600 border border-amber-200";
                    badge.innerHTML = "📍 " + namaZona;
                } else {
                    badge.className = "badge-zona shrink-0 text-[9px] font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-500 border border-gray-200";
                    badge.innerHTML = "Sekolah Pusat";
                }
            }
        });

        document.querySelectorAll('.siswa-item').forEach(el => {
            el.style.display = 'flex';
            const chk = el.querySelector('.chk-assign-siswa');
            const badge = el.querySelector('.badge-zona');
            const zonaId = el.getAttribute('data-zona');
            const namaZona = el.getAttribute('data-namazona');

            if (zonaId == data.id_zona) {
                chk.checked = true;
                badge.className = "badge-zona shrink-0 text-[9px] font-black px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-200";
                badge.innerHTML = "✔️ Zona Ini";
            } else {
                chk.checked = false;
                if (zonaId && zonaId !== 'null' && zonaId !== '') {
                    badge.className = "badge-zona shrink-0 text-[9px] font-black px-2 py-0.5 rounded-md bg-amber-50 text-amber-600 border border-amber-200";
                    badge.innerHTML = "📍 " + namaZona;
                } else {
                    badge.className = "badge-zona shrink-0 text-[9px] font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-500 border border-gray-200";
                    badge.innerHTML = "Mengikuti Kelas";
                }
            }
        });

        document.getElementById('modal-assign').classList.replace('hidden', 'flex');
        document.body.classList.add('modal-active');
    }

    function closeAssignModal() {
        document.getElementById('modal-assign').classList.replace('flex', 'hidden');
        document.body.classList.remove('modal-active');
    }
</script>
<?= $this->endSection() ?>