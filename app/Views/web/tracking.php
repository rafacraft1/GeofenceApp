<?php

/**
 * @var array<string, mixed> $config
 * @var array<int, array<string, mixed>> $list_siswa
 * @var string|null $keyword
 * @var string|null $target_id
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-140px)]">

    <div class="w-full lg:w-80 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
        <div class="p-4 border-b bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Monitoring Siswa</h3>
            <form action="<?= base_url('admin/tracking') ?>" method="GET">
                <div class="relative">
                    <input type="text" name="keyword" id="searchInput" value="<?= esc((string)($keyword ?? '')) ?>" placeholder="Cari Nama atau NIS..." autocomplete="off" class="w-full border-gray-200 rounded-xl p-2 pl-8 text-xs outline-none focus:ring-2 focus:ring-blue-500 bg-white transition-all">
                    <svg class="w-4 h-4 absolute left-2.5 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </form>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1" id="siswaList">
            <?php if (!empty($list_siswa)) : ?>
                <?php foreach ($list_siswa as $s): ?>
                    <button id="btn-siswa-<?= esc((string)$s['id_siswa']) ?>"
                        onclick="focusSiswa(<?= esc((string)$s['id_siswa']) ?>, '<?= esc((string)$s['nama_siswa']) ?>')"
                        class="siswa-item w-full flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition-all text-left group border border-transparent focus:outline-none"
                        data-nama="<?= esc(strtolower((string)$s['nama_siswa'])) ?>"
                        data-nis="<?= esc(strtolower((string)$s['nis'])) ?>">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px] group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <?= esc(strtoupper(substr((string)$s['nama_siswa'], 0, 1))) ?>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-800 group-hover:text-blue-700"><?= esc((string)$s['nama_siswa']) ?></div>
                            <div class="text-[10px] text-gray-500"><?= esc((string)$s['nama_kelas']) ?> &bull; <?= esc((string)$s['nis']) ?></div>
                        </div>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-4 text-center text-xs text-gray-400">Tidak ada data.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
        <div id="map" class="w-full h-full z-10"></div>

        <div class="absolute bottom-6 left-6 right-6 z-20 flex justify-between items-center pointer-events-none">
            <div id="tracking-status" class="bg-slate-900/80 backdrop-blur-md text-white px-4 py-2 rounded-full text-[10px] font-bold shadow-lg border border-white/10 hidden pointer-events-auto">
                <span class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span id="status-text">MENAMPILKAN:</span> <span id="target-name" class="text-emerald-300">-</span>
                </span>
            </div>

            <button id="btn-ping" onclick="pingSiswa()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-xl transition-all active:scale-95 hidden pointer-events-auto items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                LACAK SEKARANG
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* Hilangkan background default icon leaflet untuk custom icon kita */
    .custom-div-icon {
        background: transparent;
        border: none;
    }
</style>

<script>
    let map, circle;
    // Variabel array untuk menampung banyak marker & 1 polyline (garis)
    let markers = [];
    let polylineLayer = null;

    let currentTargetId = null;
    let intervalId = null;

    document.addEventListener('DOMContentLoaded', function() {
        map = window.L.map('map').setView([<?= (string)$config['latitude_sekolah'] ?>, <?= (string)$config['longitude_sekolah'] ?>], 15);
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        window.L.marker([<?= (string)$config['latitude_sekolah'] ?>, <?= (string)$config['longitude_sekolah'] ?>])
            .addTo(map)
            .bindPopup("<b>Lokasi Sekolah</b><br>Radius: <?= (string)$config['radius_meter'] ?>m").openPopup();

        window.L.circle([<?= (string)$config['latitude_sekolah'] ?>, <?= (string)$config['longitude_sekolah'] ?>], {
            color: '#3b82f6',
            fillColor: '#3b82f6',
            fillOpacity: 0.1,
            radius: <?= (string)$config['radius_meter'] ?>
        }).addTo(map);

        // FITUR LIVE SEARCH
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                const items = document.querySelectorAll('.siswa-item');

                items.forEach(item => {
                    const nama = item.getAttribute('data-nama');
                    const nis = item.getAttribute('data-nis');

                    if (nama.includes(filter) || nis.includes(filter)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        }

        // AUTO-FOCUS JIKA ADA TARGET ID DARI URL
        const initialTargetId = '<?= esc((string)($target_id ?? '')) ?>';
        if (initialTargetId) {
            setTimeout(() => {
                const targetBtn = document.getElementById('btn-siswa-' + initialTargetId);
                if (targetBtn) {
                    targetBtn.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    targetBtn.click();
                }
            }, 500);
        }
    });

    function focusSiswa(idSiswa, nama) {
        if (intervalId) clearInterval(intervalId);
        currentTargetId = idSiswa;

        // Visual Feedback Sidebar
        document.querySelectorAll('.siswa-item').forEach(el => {
            el.classList.remove('bg-blue-50', 'border-blue-200');
            el.classList.add('border-transparent');
        });
        const activeBtn = document.getElementById('btn-siswa-' + idSiswa);
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent');
            activeBtn.classList.add('bg-blue-50', 'border-blue-200');
        }

        document.getElementById('target-name').textContent = nama.toUpperCase();
        document.getElementById('status-text').textContent = "MEMANTAU:";
        document.getElementById('tracking-status').classList.remove('hidden');
        document.getElementById('btn-ping').classList.remove('hidden');
        document.getElementById('btn-ping').classList.add('flex');

        // Pertama kali klik, lakukan ping/trigger untuk meminta data dari HP
        pingSiswa();

        // Polling ke API Cache CI4 setiap 2 detik untuk menarik array data rute
        intervalId = setInterval(fetchLocationArray, 2000);
    }

    // Fungsi Fetch ke Endpoint API Baru (Menarik Array Lokasi)
    function fetchLocationArray() {
        if (!currentTargetId) return;

        fetch(`<?= base_url('api/tracking/poll/') ?>${currentTargetId}`)
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    document.getElementById('status-text').textContent = "JALUR DITEMUKAN:";
                    drawRoute(res.data);
                } else if (res.status === 'pending') {
                    document.getElementById('status-text').textContent = "MENUNGGU HP...";
                }
            }).catch(err => console.log('Menunggu pembaruan jaringan...'));
    }

    // Fungsi untuk menggambar Riwayat (Polylines) & Marker
    function drawRoute(locations) {
        // 1. Bersihkan map dari titik & garis milik siswa sebelumnya
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        if (polylineLayer) map.removeLayer(polylineLayer);

        let latlngs = [];

        // 2. Looping array lokasi (dari Terlama -> Terbaru)
        locations.forEach((loc, index) => {
            // Standarisasi key json (bisa lat/latitude, lng/longitude)
            let lat = loc.lat || loc.latitude;
            let lng = loc.lng || loc.longitude;
            let pos = [parseFloat(lat), parseFloat(lng)];
            latlngs.push(pos);

            let isLatest = (index === locations.length - 1);

            // Tampilan: Merah = Terbaru, Biru = Riwayat
            let markerColor = isLatest ? '#ef4444' : '#3b82f6';
            let ukuran = isLatest ? '16px' : '10px';
            let labelTitle = isLatest ? 'Titik Terkini (On-Demand)' : `Riwayat (Berkala)`;
            let waktuStr = loc.waktu || loc.timestamp || '-';

            // Membuat Custom Dot Marker CSS
            let customIcon = window.L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color:${markerColor}; width:${ukuran}; height:${ukuran}; border-radius:50%; border:2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.4);"></div>`,
                iconSize: [parseInt(ukuran), parseInt(ukuran)],
                iconAnchor: [parseInt(ukuran) / 2, parseInt(ukuran) / 2]
            });

            // Pasang Marker
            let newMarker = window.L.marker(pos, {
                    icon: customIcon
                })
                .addTo(map)
                .bindPopup(`
                    <div style="text-align:center;">
                        <b style="color:${markerColor}; font-size:12px;">${labelTitle}</b><br>
                        <span style="font-size:10px; color:#666;">Waktu: ${waktuStr}</span>
                    </div>
                `);

            if (isLatest) newMarker.openPopup();
            markers.push(newMarker);
        });

        // 3. Tarik Garis (Polyline) jika lokasi lebih dari 1
        if (latlngs.length > 1) {
            polylineLayer = window.L.polyline(latlngs, {
                color: '#ef4444', // Warna garis merah
                weight: 3, // Ketebalan
                dashArray: '5, 8', // Efek garis putus-putus
                lineJoin: 'round'
            }).addTo(map);

            // Auto-Zoom Peta agar seluruh jalur terlihat pas di tengah layar
            map.fitBounds(polylineLayer.getBounds(), {
                padding: [50, 50],
                maxZoom: 18
            });
        } else if (latlngs.length === 1) {
            // Jika hanya ada 1 data (baru pertama kali), flyTo ke titik tersebut
            map.flyTo(latlngs[0], 18, {
                animate: true,
                duration: 1.5
            });
        }
    }

    // Fungsi Trigger/Ping API
    function pingSiswa() {
        if (!currentTargetId) return;
        toastr.info("Mengirim sinyal pelacakan ke HP...");
        document.getElementById('status-text').textContent = "MEMBANGUNKAN HP...";

        fetch(`<?= base_url('api/tracking/trigger/') ?>${currentTargetId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                }
            })
            .then(async (res) => {
                const isJson = res.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await res.json() : null;
                if (!res.ok) throw new Error(data?.message || `Error: ${res.status}`);
                return data;
            })
            .then(data => {
                // Jangan reset interval, biarkan interval API Poll terus berjalan mengecek cache
            })
            .catch(err => toastr.error(err.message));
    }
</script>
<?= $this->endSection() ?>