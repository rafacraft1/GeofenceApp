<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-2 h-[500px]">
        <div id="map" class="h-full w-full rounded-xl z-0"></div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Konfigurasi Geofence</h3>
        <p class="text-xs text-gray-500 mb-6">Tentukan radius sekolah dan batas waktu operasional absensi harian.</p>

        <form action="/admin/pengaturan/save" method="POST" id="formPengaturan" class="space-y-6">
            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Latitude</label>
                    <input type="text" id="lat" name="lat" value="<?= esc($config->latitude_sekolah) ?>" class="w-full bg-transparent font-bold text-sm text-gray-800 outline-none" readonly>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Longitude</label>
                    <input type="text" id="long" name="long" value="<?= esc($config->longitude_sekolah) ?>" class="w-full bg-transparent font-bold text-sm text-gray-800 outline-none" readonly>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Radius Diizinkan (Meter)</label>
                <div class="flex items-center gap-3">
                    <input type="range" id="radius-slider" min="10" max="500" step="5" value="<?= esc($config->radius_meter) ?>" class="flex-1 accent-blue-600">
                    <input type="number" id="radius" name="radius" value="<?= esc($config->radius_meter) ?>" class="w-20 border border-gray-200 rounded-lg p-2 text-center text-sm font-bold text-blue-600 outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5 pt-4 border-t border-gray-100">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Jam Masuk</label>
                    <input type="time" name="jam_masuk" value="<?= esc($config->jam_masuk) ?>" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Jam Pulang</label>
                    <input type="time" name="jam_pulang" value="<?= esc($config->jam_pulang) ?>" class="w-full border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                </div>
            </div>

            <div class="pt-4">
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
        let map = L.map('map').setView(center, 17);

        L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(map);

        let schoolIcon = L.divIcon({
            html: `<div class="bg-blue-600 p-2 rounded-full border-4 border-white shadow-lg text-white">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L4 9v12h16V9l-8-6zm0 15a3 3 0 110-6 3 3 0 010 6z"></path></svg>
                   </div>`,
            className: 'custom-div-icon',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        let marker = L.marker(center, {
            icon: schoolIcon
        }).addTo(map);

        let circle = L.circle(center, {
            color: '#2563eb',
            fillColor: '#3b82f6',
            fillOpacity: 0.15,
            weight: 2,
            radius: radiusInput.value
        }).addTo(map);

        map.on('click', function(e) {
            let lat = e.latlng.lat;
            let lng = e.latlng.lng;
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
        });

        function updateRadius(val) {
            circle.setRadius(val);
            radiusInput.value = val;
            radiusSlider.value = val;
            map.fitBounds(circle.getBounds());
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