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
                    <input type="text" id="lat" name="lat" value="<?= $config->sekolah_lat ?>" class="w-full bg-transparent font-bold text-sm text-gray-800 outline-none" readonly>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Longitude</label>
                    <input type="text" id="long" name="long" value="<?= $config->sekolah_long ?>" class="w-full bg-transparent font-bold text-sm text-gray-800 outline-none" readonly>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Radius Jangkauan (Meter)</label>
                    <div class="relative">
                        <input type="number" id="radius" name="radius" value="<?= $config->radius_meter ?>" class="w-full border-gray-200 rounded-xl p-3 pl-10 text-sm focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                        <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Jam Masuk</label>
                        <input type="time" name="jam_masuk" value="<?= $config->jam_masuk ?>" class="w-full border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2 text-right">Jam Pulang</label>
                        <input type="time" name="jam_pulang" value="<?= $config->jam_pulang ?>" class="w-full border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-blue-700 transition shadow btn-submit">Simpan Perubahan</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let latInput = document.getElementById('lat');
        let lngInput = document.getElementById('long');
        let radiusInput = document.getElementById('radius');

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

        radiusInput.addEventListener('input', (e) => circle.setRadius(e.target.value));

        $('#formPengaturan').on('submit', function() {
            $(this).find('.btn-submit').addClass('btn-loading');
        });
    });
</script>
<?= $this->endSection() ?>