<?php

/**
 * @var object{id: int|string, nis: string, nama_lengkap: string, kelas: string}[] $list_siswa
 * @var int|string|null $target_id
 * @var object{latitude_sekolah: string|float, longitude_sekolah: string|float, radius_meter: int|string, firebase_url: string} $config
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[80vh]">

    <!-- Sidebar Pemilihan Siswa -->
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

    <!-- Area Peta Radar -->
    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden h-full">

        <!-- Peringatan Firebase Belum Disetting -->
        <?php if (empty($config->firebase_url)): ?>
            <div class="absolute inset-0 z-[500] bg-white/80 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white p-6 rounded-2xl shadow-xl border border-red-100 text-center max-w-sm">
                    <div class="w-12 h-12 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h4 class="font-bold text-gray-800 text-lg">Firebase Belum Terhubung</h4>
                    <p class="text-sm text-gray-500 mt-2">Silakan masukkan URL Firebase di menu Pengaturan terlebih dahulu.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Indikator Firebase Aktif (Sekarang dikendalikan oleh JS secara Real-time) -->
        <div class="absolute top-4 right-4 z-[400] flex items-center gap-2 bg-white/90 backdrop-blur-sm px-4 py-2 rounded-full shadow-md border border-gray-100 transition-colors duration-300">
            <div id="status-dot" class="w-2.5 h-2.5 bg-gray-300 rounded-full"></div>
            <span id="status-text" class="text-xs font-bold text-gray-700 uppercase tracking-wide">Menyambungkan...</span>
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
    // Elemen UI Status Koneksi
    const statusDot = document.getElementById('status-dot');
    const statusText = document.getElementById('status-text');

    // 1. Inisialisasi Firebase (Dinamis dari Database)
    const firebaseUrl = "<?= esc($config->firebase_url ?? '') ?>";
    let db = null;

    if (firebaseUrl !== "") {
        const firebaseConfig = {
            databaseURL: firebaseUrl
        };
        firebase.initializeApp(firebaseConfig);
        db = firebase.database();

        // 🌟 LISTENER STATUS KONEKSI REAL-TIME 🌟
        db.ref('.info/connected').on('value', function(snap) {
            if (snap.val() === true) {
                // Terhubung ke server Firebase
                statusDot.className = 'w-2.5 h-2.5 bg-emerald-500 animate-ping rounded-full';
                statusText.textContent = 'CONNECTED';
                statusText.className = 'text-xs font-bold text-emerald-700 uppercase tracking-wide';
            } else {
                // Terputus (karena tidak ada internet, ditolak, atau sedang menyambung)
                statusDot.className = 'w-2.5 h-2.5 bg-red-500 rounded-full';
                statusText.textContent = 'DISCONNECTED';
                statusText.className = 'text-xs font-bold text-red-600 uppercase tracking-wide';
            }
        });
    } else {
        // Jika URL Firebase belum diatur di database
        statusDot.className = 'w-2.5 h-2.5 bg-gray-500 rounded-full';
        statusText.textContent = 'TIDAK ADA URL';
    }

    // 2. Inisialisasi Peta
    const sekolahLat = <?= esc($config->latitude_sekolah ?? 0) ?>;
    const sekolahLon = <?= esc($config->longitude_sekolah ?? 0) ?>;
    const radiusM = <?= esc($config->radius_meter ?? 0) ?>;

    const map = L.map('map-radar', {
        zoomControl: false
    }).setView([sekolahLat, sekolahLon], 16);

    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(map);

    // MARKER SEKOLAH MODERN (Dengan Text Overlay)
    let schoolIcon = L.divIcon({
        html: `
            <div class="relative flex flex-col items-center justify-end w-full h-full group">
                <div class="absolute -top-2 bg-blue-600 px-3 py-1.5 rounded-md border-2 border-white shadow-lg text-white text-[11px] font-extrabold tracking-wide whitespace-nowrap z-20">
                    🏫 LOKASI SEKOLAH
                    <div class="absolute -bottom-1.5 left-1/2 transform -translate-x-1/2 w-2.5 h-2.5 bg-blue-600 rotate-45 border-r-2 border-b-2 border-white"></div>
                </div>
                <div class="absolute bottom-0 w-6 h-6 bg-blue-500 rounded-full animate-ping opacity-60"></div>
                <div class="absolute bottom-[2px] w-5 h-5 bg-blue-600 rounded-full shadow-[0_0_15px_8px_rgba(37,99,235,0.4)] opacity-50 z-0"></div>
                <div class="relative z-10 flex flex-col items-center transform transition-transform duration-300 group-hover:-translate-y-1 origin-bottom mt-6">
                    <svg class="w-12 h-14 text-blue-600 drop-shadow-2xl" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22.5C12 22.5 4.5 14.5 4.5 9.5C4.5 5.35786 7.85786 2 12 2C16.1421 2 19.5 5.35786 19.5 9.5C19.5 14.5 12 22.5 12 22.5Z" fill="currentColor" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
                        <circle cx="12" cy="9.5" r="4.5" fill="white" />
                    </svg>
                    <div class="absolute top-[11px] text-blue-700">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9L4 10.63V17C4 18.65 7.58 20 12 20C16.42 20 20 18.65 20 17V10.63L23 9L12 3ZM12 5.16L18.89 9L12 12.84L5.11 9L12 5.16ZM18 17C18 17.5 15.35 18 12 18C8.65 18 6 17.5 6 17V11.75L12 15.08L18 11.75V17Z"/></svg>
                    </div>
                </div>
            </div>
        `,
        className: 'bg-transparent',
        iconSize: [60, 90],
        iconAnchor: [30, 85]
    });

    L.marker([sekolahLat, sekolahLon], {
        icon: schoolIcon
    }).addTo(map).bindPopup("<b>SMK Negeri 1 TGB</b>");

    // LINGKARAN GEOFENCE RADAR
    L.circle([sekolahLat, sekolahLon], {
        color: '#2563eb',
        fillColor: '#60a5fa',
        fillOpacity: 0.15,
        weight: 2,
        dashArray: '8, 6', // Garis putus-putus
        radius: radiusM
    }).addTo(map);

    // 3. Logika Multi-Tracking & Auto-Focus
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
                if (db) db.ref('live_tracking/' + id).off(); // Matikan listener Firebase
                delete activeMarkers[id];
            }
        }

        // Pantau siswa yang dicentang
        if (db) {
            selectedIds.forEach(id => {
                if (!activeMarkers[id]) {
                    // Pre-inisialisasi marker kosong
                    activeMarkers[id] = L.marker([0, 0], {
                        opacity: 0
                    });

                    db.ref('live_tracking/' + id).on('value', (snapshot) => {
                        const data = snapshot.val();
                        if (data && activeMarkers[id]) {
                            const pos = [data.lat, data.long];

                            // Deteksi apakah ini pertama kalinya marker mendapat kordinat asli
                            const isNewMarker = activeMarkers[id].getLatLng().lat === 0;

                            activeMarkers[id].setLatLng(pos);
                            activeMarkers[id].setOpacity(1); // Tampilkan marker
                            activeMarkers[id].addTo(map);

                            // Ambil nama depan siswa saja agar overlay tidak kepanjangan
                            const firstName = data.nama.split(' ')[0].substring(0, 12);

                            // MARKER SISWA (Dengan Text Overlay)
                            activeMarkers[id].setIcon(L.divIcon({
                                html: `
                                    <div class="flex flex-col items-center">
                                        <!-- TEXT OVERLAY SISWA -->
                                        <div class="bg-red-500 px-2.5 py-1.5 rounded-md border-2 border-white shadow-lg text-white text-[11px] whitespace-nowrap font-bold relative flex items-center gap-1.5 hover:scale-110 transition-transform cursor-pointer">
                                            <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></div>
                                            ${firstName}
                                            <div class="absolute -bottom-1.5 left-1/2 transform -translate-x-1/2 w-2.5 h-2.5 bg-red-500 rotate-45 border-r-2 border-b-2 border-white"></div>
                                        </div>
                                    </div>
                                `,
                                className: 'bg-transparent',
                                iconSize: [40, 40],
                                iconAnchor: [20, 35]
                            }));

                            activeMarkers[id].bindPopup(`<b>${data.nama}</b><br>${data.kelas}<br>Waktu: ${data.waktu}`);

                            // FITUR AUTO-FOCUS: Memundurkan peta jika posisi baru sangat jauh
                            if (isNewMarker) {
                                const group = new L.featureGroup(Object.values(activeMarkers));
                                map.fitBounds(group.getBounds(), {
                                    padding: [50, 50],
                                    maxZoom: 17
                                });
                            }
                        }
                    });
                }
            });
        }
    }

    // 4. Event Listener Sidebar dengan LIMIT 4
    document.querySelectorAll('.track-checkbox').forEach(cb => {
        cb.addEventListener('change', function(e) {
            const selectedCount = document.querySelectorAll('.track-checkbox:checked').length;

            if (selectedCount > 4) {
                this.checked = false;
                alert('Batas Maksimal Tercapai!\n\nAnda hanya dapat melacak maksimal 4 siswa secara bersamaan.');
                return;
            }

            updateMapMarkers();
        });
    });

    // 5. Fitur Pencarian di Sidebar
    document.getElementById('search-siswa').addEventListener('keyup', function(e) {
        const keyword = e.target.value.toLowerCase();
        document.querySelectorAll('.student-item').forEach(item => {
            const text = item.innerText.toLowerCase();
            item.style.display = text.includes(keyword) ? 'flex' : 'none';
        });
    });

    // Jalankan pertama kali saat halaman dimuat
    updateMapMarkers();
</script>
<?= $this->endSection() ?>