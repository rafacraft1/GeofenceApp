<?php

/**
 * @var array<int, array<string, mixed>> $list_siswa
 * @var array<string, mixed> $pengaturan
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="flex flex-col lg:flex-row gap-6 h-[80vh] lg:h-[calc(100vh-140px)]">
    <div class="w-full lg:w-80 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden h-1/3 lg:h-full">
        <div class="p-4 border-b bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Monitoring Siswa</h3>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Cari Nama..." class="w-full border-gray-200 rounded-xl p-2 pl-8 text-xs outline-none focus:ring-2 focus:ring-blue-500 bg-white transition-all">
                <svg class="w-4 h-4 absolute left-2.5 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-1" id="siswaList">
            <?php foreach ($list_siswa as $s): ?>
                <button onclick="focusSiswa(<?= (int)$s['id_siswa'] ?>, '<?= esc((string)$s['nama_siswa']) ?>')" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition-all text-left border border-transparent">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px]"><?= esc(strtoupper(substr((string)$s['nama_siswa'], 0, 1))) ?></div>
                    <div>
                        <div class="text-xs font-bold text-gray-800"><?= esc((string)$s['nama_siswa']) ?></div>
                        <div class="text-[10px] text-gray-500"><?= esc((string)$s['nama_kelas']) ?></div>
                    </div>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative h-2/3 lg:h-full">
        <div id="map" class="w-full h-full z-10 min-h-[300px]"></div>
        <div class="absolute bottom-6 left-6 z-20 flex gap-2">
            <div id="tracking-status" class="bg-slate-900/80 backdrop-blur-md text-white px-4 py-2 rounded-full text-[10px] font-bold shadow-lg border border-white/10 hidden">
                <span id="target-name">MEMANTAU: -</span>
            </div>
            <button id="btn-ping" onclick="pingSiswa()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full text-[10px] font-bold shadow-lg hidden">LACAK</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let map, markers = [],
        polylineLayer = null,
        currentTargetId = null,
        intervalId = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Menggunakan variabel $pengaturan langsung (Bebas dari deteksi error class)
        map = window.L.map('map').setView([<?= esc((string)$pengaturan['latitude_sekolah']) ?>, <?= esc((string)$pengaturan['longitude_sekolah']) ?>], 16);

        window.L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        }).addTo(map);
    });

    function focusSiswa(idSiswa, nama) {
        if (intervalId) clearInterval(intervalId);
        currentTargetId = idSiswa;
        document.getElementById('target-name').textContent = "MEMANTAU: " + nama.toUpperCase();
        document.getElementById('tracking-status').classList.remove('hidden');
        document.getElementById('btn-ping').classList.remove('hidden');

        intervalId = setInterval(fetchLocationArray, 3000);
        fetchLocationArray();
    }

    function fetchLocationArray() {
        fetch(`<?= base_url('admin/tracking/poll/') ?>${currentTargetId}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') drawRoute(res.data);
            });
    }

    function drawRoute(locations) {
        markers.forEach(m => map.removeLayer(m));
        if (polylineLayer) map.removeLayer(polylineLayer);

        let latlngs = [];
        locations.forEach((loc, index) => {
            let pos = [parseFloat(loc.lat), parseFloat(loc.lng)];
            latlngs.push(pos);
            let m = window.L.circleMarker(pos, {
                radius: 8,
                fillColor: (index === locations.length - 1 ? '#ef4444' : '#3b82f6'),
                color: '#fff',
                weight: 2,
                fillOpacity: 1
            }).addTo(map);
            markers.push(m);
        });

        if (latlngs.length > 1) {
            polylineLayer = window.L.polyline(latlngs, {
                color: '#ef4444',
                weight: 3,
                dashArray: '5, 5'
            }).addTo(map);
            map.fitBounds(polylineLayer.getBounds(), {
                padding: [50, 50]
            });
        }
    }

    // Fix Bug Layar HP
    window.addEventListener("orientationchange", () => setTimeout(() => map.invalidateSize(), 500));

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (window.innerWidth >= 768) {
            sidebar.classList.toggle('sidebar-collapsed');
            setTimeout(() => window.dispatchEvent(new window.Event('resize')), 310);
        } else {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        setTimeout(() => map.invalidateSize(), 350);
    }

    // Fungsionalitas Fitur Search Input Sidebar
    document.getElementById('searchInput').addEventListener('input', function() {
        const keyword = this.value.toLowerCase();
        const siswaItems = document.querySelectorAll('.siswa-item');

        siswaItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(keyword) ? 'flex' : 'none';
        });
    });
</script>
<?= $this->endSection() ?>