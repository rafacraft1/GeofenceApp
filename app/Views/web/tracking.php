<?php

/**
 * @var object{id: int|string, nis: string, nama_lengkap: string, kelas: string}[] $list_siswa
 * @var int|string|null $target_id
 * @var object{latitude_sekolah: string|float, longitude_sekolah: string|float, radius_meter: int|string} $config
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[80vh]">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden">
        <div class="p-4 border-b bg-gray-50">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-gray-800">Target Pelacakan</h3>
                    <p class="text-xs text-gray-500 mt-1">Pilih maksimal 4 siswa.</p>
                </div>
                <span id="counter-badge" class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-lg">0/4</span>
            </div>
            <input type="text" id="search-siswa" placeholder="Cari nama/kelas..." class="mt-3 w-full border-gray-200 rounded-lg p-2 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>

        <div class="flex-1 overflow-y-auto p-2" id="student-list">
            <?php foreach ($list_siswa as $s): ?>
                <label class="flex items-center gap-3 p-3 hover:bg-blue-50 rounded-xl cursor-pointer transition-colors student-item">
                    <input type="checkbox" value="<?= esc($s->id) ?>" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 track-checkbox" <?= ($target_id == $s->id) ? 'checked' : '' ?>>
                    <div>
                        <div class="text-sm font-bold text-gray-800 student-name"><?= esc($s->nama_lengkap) ?></div>
                        <div class="text-xs text-gray-500 student-class"><?= esc($s->kelas) ?> - <?= esc($s->nis) ?></div>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden h-full">
        <div class="absolute top-4 right-4 z-[400] flex items-center gap-2 bg-white/90 backdrop-blur-sm px-4 py-2 rounded-full shadow-md border border-gray-100">
            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-ping"></div>
            <span class="text-xs font-bold text-gray-700">FIREBASE CONNECTED</span>
        </div>

        <div id="map-radar" class="w-full h-full z-0"></div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>

<script>
    // 1. Inisialisasi Firebase (GANTI DENGAN URL DATABASE ANDA)
    const firebaseConfig = {
        databaseURL: "https://NAMA-PROJECT-ANDA.asia-southeast1.firebasedatabase.app",
        projectId: "NAMA-PROJECT-ANDA",
    };
    firebase.initializeApp(firebaseConfig);
    const db = firebase.database();

    // 2. Inisialisasi Peta
    const sekolahLat = <?= esc($config->latitude_sekolah) ?>;
    const sekolahLon = <?= esc($config->longitude_sekolah) ?>;
    const map = L.map('map-radar').setView([sekolahLat, sekolahLon], 16);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(map);

    // Marker Sekolah
    L.circle([sekolahLat, sekolahLon], {
        radius: <?= esc($config->radius_meter) ?>,
        color: '#3b82f6',
        fillOpacity: 0.1,
        weight: 2
    }).addTo(map);
    L.marker([sekolahLat, sekolahLon], {
        icon: L.divIcon({
            html: `<div class="bg-blue-600 p-2 rounded-full border-2 border-white shadow-lg text-white"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L4 9v12h16V9l-8-6z"></path></svg></div>`,
            className: 'custom-icon',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        })
    }).addTo(map).bindPopup("<b>SMK Negeri 1 TGB</b>");

    // 3. Logika Multi-Tracking (Batas Maksimal 4)
    let activeMarkers = {};

    function updateMapMarkers() {
        const selectedCheckboxes = document.querySelectorAll('.track-checkbox:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

        // Update badge counter di sidebar
        document.getElementById('counter-badge').textContent = `${selectedIds.length}/4`;

        // Hapus marker yang tidak dicentang lagi
        for (let id in activeMarkers) {
            if (!selectedIds.includes(id)) {
                map.removeLayer(activeMarkers[id]);
                delete activeMarkers[id];
            }
        }

        // Pantau siswa yang dicentang
        selectedIds.forEach(id => {
            if (!activeMarkers[id]) {
                const icon = L.divIcon({
                    html: `<div class="bg-red-500 p-1.5 rounded-full border-2 border-white shadow-md text-white text-xs text-center relative font-bold min-w-[60px] transform -translate-x-1/2">...</div>`,
                    className: 'student-marker',
                    iconSize: [60, 30],
                    iconAnchor: [30, 15]
                });
                activeMarkers[id] = L.marker([0, 0], {
                    icon: icon
                });

                db.ref('live_tracking/' + id).on('value', (snapshot) => {
                    const data = snapshot.val();
                    if (data && activeMarkers[id]) {
                        const pos = [data.lat, data.long];
                        activeMarkers[id].setLatLng(pos);
                        activeMarkers[id].addTo(map);

                        const initial = data.nama.substring(0, 10);
                        activeMarkers[id].setIcon(L.divIcon({
                            html: `<div class="bg-red-500 px-2 py-1 rounded border-2 border-white shadow-lg text-white text-[10px] whitespace-nowrap font-bold relative group">
                                        <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-red-500 rotate-45"></div>
                                        ${initial}
                                   </div>`,
                            className: 'student-marker',
                            iconSize: [0, 0],
                            iconAnchor: [0, 20]
                        }));

                        activeMarkers[id].bindPopup(`<b>${data.nama}</b><br>${data.kelas}<br>Waktu: ${data.waktu}`);
                    }
                });
            }
        });
    }

    // 4. Event Listener Sidebar dengan LIMIT 4
    document.querySelectorAll('.track-checkbox').forEach(cb => {
        cb.addEventListener('change', function(e) {
            const selectedCount = document.querySelectorAll('.track-checkbox:checked').length;

            // Logika Pembatasan
            if (selectedCount > 4) {
                this.checked = false; // Batalkan centangan secara otomatis
                alert('Batas Maksimal Tercapai!\\n\\nAnda hanya dapat melacak maksimal 4 siswa secara bersamaan.');
                return;
            }

            updateMapMarkers();
        });
    });

    // Fitur Pencarian
    document.getElementById('search-siswa').addEventListener('keyup', function(e) {
        const keyword = e.target.value.toLowerCase();
        document.querySelectorAll('.student-item').forEach(item => {
            const text = item.innerText.toLowerCase();
            item.style.display = text.includes(keyword) ? 'flex' : 'none';
        });
    });

    // Jalankan pertama kali
    updateMapMarkers();
</script>
<?= $this->endSection() ?>