<?php

/**
 * @var array<string, mixed> $pengaturan
 */

// Mengambil URL Firebase dari file .env (Mendukung key FIREBASE_DATABASE_URL atau FIREBASE_URL)
$envFirebaseUrl = env('FIREBASE_DATABASE_URL') ?: env('FIREBASE_URL');
$isFirebaseEmpty = empty($envFirebaseUrl);
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-2 h-[500px] lg:h-auto min-h-[500px] relative">

        <div class="absolute top-6 left-6 z-[400] pointer-events-none">
            <?php if ($isFirebaseEmpty): ?>
                <div class="bg-white/90 backdrop-blur-md border border-red-100 shadow-lg rounded-xl px-3 py-2 flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-red-600 tracking-wide uppercase">Firebase Kosong</span>
                </div>
            <?php else: ?>
                <div class="bg-white/90 backdrop-blur-md border border-emerald-100 shadow-lg rounded-xl px-3 py-2 flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-emerald-600 tracking-wide uppercase">Firebase Aktif</span>
                </div>
            <?php endif; ?>
        </div>

        <div id="map-hint" class="absolute top-6 left-1/2 transform -translate-x-1/2 z-[400] bg-slate-800/90 backdrop-blur-sm px-5 py-2.5 rounded-full shadow-xl border border-white/10 pointer-events-none transition-all duration-700 hidden md:block">
            <span class="text-xs font-bold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                </svg>
                Klik peta atau ketik koordinat manual
            </span>
        </div>

        <div id="map" class="h-full w-full rounded-xl z-0"></div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Konfigurasi Sistem</h3>
        <p class="text-xs text-gray-500 mb-4">Tentukan identitas aplikasi, lokasi koordinat sekolah dan batas radius.</p>

        <form action="<?= base_url('admin/pengaturan/save') ?>" method="POST" id="formPengaturan" class="space-y-5 flex-1 flex flex-col">
            <?= csrf_field() ?>

            <div class="space-y-3">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Nama Aplikasi</label>
                    <input type="text" name="nama_aplikasi" value="<?= esc((string) ($pengaturan['nama_aplikasi'] ?? 'GeofenceApp')) ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all" required>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Nama Sekolah / Instansi</label>
                    <input type="text" name="nama_sekolah" value="<?= esc((string) ($pengaturan['nama_sekolah'] ?? 'SMKN 1 TGB')) ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all" required>
                </div>
            </div>

            <hr class="border-gray-100 my-1">

            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <div>
                    <label class="text-[10px] font-bold text-blue-500 uppercase">Latitude</label>
                    <input type="text" id="lat" name="latitude_sekolah" value="<?= esc((string) ($pengaturan['latitude_sekolah'] ?? '')) ?>" class="w-full bg-transparent font-bold text-sm text-gray-800 outline-none border-b border-gray-200 focus:border-blue-500 pb-1 transition-colors" placeholder="-6.200000">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-blue-500 uppercase">Longitude</label>
                    <input type="text" id="long" name="longitude_sekolah" value="<?= esc((string) ($pengaturan['longitude_sekolah'] ?? '')) ?>" class="w-full bg-transparent font-bold text-sm text-gray-800 outline-none border-b border-gray-200 focus:border-blue-500 pb-1 transition-colors" placeholder="106.816666">
                </div>
            </div>

            <div class="pt-2">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-3">Radius Diizinkan (Meter)</label>
                <div class="flex items-center gap-4">
                    <input type="range" id="radius-slider" min="10" max="500" step="5" value="<?= esc((string) ($pengaturan['radius_meter'] ?? 50)) ?>" class="flex-1 accent-blue-600 cursor-grab">
                    <input type="number" id="radius" name="radius_meter" value="<?= esc((string) ($pengaturan['radius_meter'] ?? 50)) ?>" class="w-24 border border-gray-200 rounded-xl p-2.5 text-center text-sm font-bold text-blue-600 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all bg-gray-50">
                </div>
            </div>

            <input type="hidden" name="firebase_url" value="<?= esc((string) ($pengaturan['firebase_url'] ?? '')) ?>">

            <div class="pt-4 mt-auto">
                <button type="submit" class="w-full flex justify-center items-center gap-2 bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 active:scale-95 transition-all btn-submit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Pengaturan
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
    document.addEventListener('DOMContentLoaded', function() {
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('long');
        const radiusInput = document.getElementById('radius');
        const radiusSlider = document.getElementById('radius-slider');
        const mapHint = document.getElementById('map-hint');

        // Logic Auto-Hide Instruksi
        if (mapHint) {
            setTimeout(() => {
                mapHint.classList.add('opacity-0', '-translate-y-4');
                setTimeout(() => mapHint.remove(), 700);
            }, 6000);
        }

        // UX: Smart Default Location (Jika DB kosong, fallback ke Jakarta)
        let defaultLat = latInput.value ? parseFloat(latInput.value) : -6.200000;
        let defaultLng = lngInput.value ? parseFloat(lngInput.value) : 106.816666;
        let center = [defaultLat, defaultLng];

        // Memakai window.L untuk mencegah indikator "Class L not imported" di VS Code
        let map = window.L.map('map', {
            zoomControl: false
        }).setView(center, 18);

        window.L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Konsistensi Visual: Menggunakan Google Maps Tile Server
        window.L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        }).addTo(map);

        let schoolIcon = window.L.divIcon({
            html: `
                <div class="relative flex flex-col items-center justify-end w-full h-full">
                    <div class="absolute bottom-0 w-6 h-6 bg-blue-500 rounded-full animate-ping opacity-60"></div>
                    <div class="absolute bottom-[2px] w-5 h-5 bg-blue-600 rounded-full shadow-[0_0_15px_8px_rgba(37,99,235,0.4)] opacity-50 z-0"></div>
                    <div class="relative z-10 flex flex-col items-center origin-bottom cursor-grab">
                        <svg class="w-14 h-16 text-blue-600 drop-shadow-2xl" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 22.5C12 22.5 4.5 14.5 4.5 9.5C4.5 5.35786 7.85786 2 12 2C16.1421 2 19.5 5.35786 19.5 9.5C19.5 14.5 12 22.5 12 22.5Z" stroke="white" stroke-width="1.5"/>
                            <circle cx="12" cy="9.5" r="4.5" fill="white" />
                        </svg>
                        <div class="absolute top-[13px] text-blue-700">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9L4 10.63V17C4 18.65 7.58 20 12 20C16.42 20 20 18.65 20 17V10.63L23 9L12 3ZM12 5.16L18.89 9L12 12.84L5.11 9L12 5.16ZM18 17C18 17.5 15.35 18 12 18C8.65 18 6 17.5 6 17V11.75L12 15.08L18 11.75V17Z"/></svg>
                        </div>
                    </div>
                </div>
            `,
            className: 'bg-transparent',
            iconSize: [56, 72],
            iconAnchor: [28, 70]
        });

        let marker = window.L.marker(center, {
            icon: schoolIcon
        }).addTo(map);

        let circle = window.L.circle(center, {
            color: '#2563eb',
            fillColor: '#60a5fa',
            fillOpacity: 0.15,
            weight: 2,
            dashArray: '8, 6',
            radius: radiusInput.value
        }).addTo(map);

        // Event 1: Memindahkan pin jika user mengklik area peta
        map.on('click', function(e) {
            let lat = e.latlng.lat;
            let lng = e.latlng.lng;
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
        });

        // Event 2 (Two-Way Binding UX): Memindahkan pin jika user mengetik/paste koordinat manual
        function updateMapFromInput() {
            let lat = parseFloat(latInput.value);
            let lng = parseFloat(lngInput.value);

            if (!isNaN(lat) && !isNaN(lng)) {
                let newLatLng = new window.L.LatLng(lat, lng);
                marker.setLatLng(newLatLng);
                circle.setLatLng(newLatLng);
                map.panTo(newLatLng);
            }
        }
        latInput.addEventListener('input', updateMapFromInput);
        lngInput.addEventListener('input', updateMapFromInput);

        // Event 3: Sinkronisasi slider dan radius peta
        function updateRadius(val) {
            circle.setRadius(val);
            radiusInput.value = val;
            radiusSlider.value = val;
            map.fitBounds(circle.getBounds(), {
                padding: [50, 50]
            });
        }
        radiusSlider.addEventListener('input', (e) => updateRadius(e.target.value));
        radiusInput.addEventListener('change', (e) => updateRadius(e.target.value));

        // Loading button state
        document.getElementById('formPengaturan').addEventListener('submit', function() {
            const btn = this.querySelector('.btn-submit');
            if (btn) {
                btn.classList.add('btn-loading');
                btn.setAttribute('disabled', 'true');
            }
        });
    });
</script>
<?= $this->endSection() ?>