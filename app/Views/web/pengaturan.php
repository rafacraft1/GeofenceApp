<?php

/**
 * Dideklarasikan untuk menghilangkan false-positive error Intelephense
 * @var object{latitude_sekolah: string|float, longitude_sekolah: string|float, radius_meter: int|string, firebase_url: string|null, jam_masuk: string, jam_pulang: string} $config
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-2 h-[500px] lg:h-auto min-h-[500px]">
        <div id="map" class="h-full w-full rounded-xl z-0 relative">
            <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-[400] bg-white/90 backdrop-blur-sm px-5 py-2 rounded-full shadow-md border border-gray-200 pointer-events-none">
                <span class="text-xs font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                    </svg>
                    Klik area peta untuk memindahkan titik sekolah
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Konfigurasi Sistem</h3>
        <p class="text-xs text-gray-500 mb-6">Tentukan lokasi sekolah, radius, waktu absensi, dan koneksi Firebase.</p>

        <form action="/admin/pengaturan/save" method="POST" id="formPengaturan" class="space-y-5 flex-1 flex flex-col">

            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Latitude</label>
                    <input type="text" id="lat" name="latitude_sekolah" value="<?= esc($config->latitude_sekolah) ?>" class="w-full bg-transparent font-bold text-sm text-gray-800 outline-none" readonly>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Longitude</label>
                    <input type="text" id="long" name="longitude_sekolah" value="<?= esc($config->longitude_sekolah) ?>" class="w-full bg-transparent font-bold text-sm text-gray-800 outline-none" readonly>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Radius Diizinkan (Meter)</label>
                <div class="flex items-center gap-3">
                    <input type="range" id="radius-slider" min="10" max="500" step="5" value="<?= esc($config->radius_meter) ?>" class="flex-1 accent-blue-600 cursor-grab">
                    <input type="number" id="radius" name="radius_meter" value="<?= esc($config->radius_meter) ?>" class="w-20 border border-gray-200 rounded-lg p-2 text-center text-sm font-bold text-blue-600 outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-xs font-bold text-orange-600 uppercase mb-2">Firebase Database URL</label>
                <input type="url" name="firebase_url" value="<?= esc($config->firebase_url ?? '') ?>" placeholder="https://nama-project.asia-southeast1.firebasedatabase.app" required class="w-full border border-orange-200 rounded-xl p-3 text-[11px] font-mono outline-none focus:ring-2 focus:ring-orange-500 transition-all bg-orange-50/30">
            </div>

            <div class="grid grid-cols-2 gap-5 pt-4 border-t border-gray-100">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Jam Masuk</label>
                    <input type="time" name="jam_masuk" value="<?= esc($config->jam_masuk) ?>" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Jam Pulang</label>
                    <input type="time" name="jam_pulang" value="<?= esc($config->jam_pulang) ?>" required class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                </div>
            </div>

            <div class="pt-4 mt-auto">
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 active:scale-95 transition-all btn-submit">Simpan Pengaturan</button>
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
        let latInput = document.getElementById('lat');
        let lngInput = document.getElementById('long');
        let radiusInput = document.getElementById('radius');
        let radiusSlider = document.getElementById('radius-slider');

        let center = [latInput.value, lngInput.value];
        let map = L.map('map', {
            zoomControl: false // Kita matikan zoom bawaan agar bisa diatur posisinya
        }).setView(center, 18);

        // Pindahkan tombol Zoom ke kanan bawah agar tidak tertimpa instruksi
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Menampilkan Peta dari OpenStreetMap (Tema Bersih)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            maxZoom: 20
        }).addTo(map);

        // DESAIN MARKER BARU YANG PROFESIONAL
        let schoolIcon = L.divIcon({
            html: `
                <div class="relative flex flex-col items-center justify-end w-full h-full group">
                    <div class="absolute bottom-0 w-6 h-6 bg-blue-500 rounded-full animate-ping opacity-60"></div>
                    <div class="absolute bottom-[2px] w-5 h-5 bg-blue-600 rounded-full shadow-[0_0_15px_8px_rgba(37,99,235,0.4)] opacity-50 z-0"></div>

                    <div class="relative z-10 flex flex-col items-center transform transition-transform duration-300 group-hover:-translate-y-2 origin-bottom cursor-grab">
                        <svg class="w-14 h-16 text-blue-600 drop-shadow-2xl" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22.5C12 22.5 4.5 14.5 4.5 9.5C4.5 5.35786 7.85786 2 12 2C16.1421 2 19.5 5.35786 19.5 9.5C19.5 14.5 12 22.5 12 22.5Z" fill="currentColor" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
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
            iconAnchor: [28, 70] // Tepat menunjuk ke titik kordinat
        });

        let marker = L.marker(center, {
            icon: schoolIcon
        }).addTo(map);

        // Desain Lingkaran Geofence Diperbarui
        let circle = L.circle(center, {
            color: '#2563eb', // Border Biru
            fillColor: '#60a5fa', // Fill Biru Muda
            fillOpacity: 0.15,
            weight: 2,
            dashArray: '8, 6', // Membuat border putus-putus seperti area pindaian
            radius: radiusInput.value
        }).addTo(map);

        // Fitur Click-to-Pick Kordinat
        map.on('click', function(e) {
            let lat = e.latlng.lat;
            let lng = e.latlng.lng;
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
        });

        // Sinkronisasi Slider Radius dan Input Number
        function updateRadius(val) {
            circle.setRadius(val);
            radiusInput.value = val;
            radiusSlider.value = val;
            map.fitBounds(circle.getBounds(), {
                padding: [50, 50]
            }); // Otomatis menyesuaikan layar jika radius membesar
        }

        radiusSlider.addEventListener('input', function(e) {
            updateRadius(e.target.value);
        });

        radiusInput.addEventListener('change', function(e) {
            updateRadius(e.target.value);
        });

        $('#formPengaturan').on('submit', function() {
            $(this).find('.btn-submit').addClass('btn-loading');
        });
    });
</script>
<?= $this->endSection() ?>