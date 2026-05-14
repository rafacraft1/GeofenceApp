<?php

/**
 * @var array $config
 * @var array $list_siswa
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
                    <button onclick="focusSiswa(<?= esc((string)$s['id_siswa']) ?>, '<?= esc((string)$s['nama_siswa']) ?>')"
                        class="siswa-item w-full flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition-all text-left group border border-transparent hover:border-blue-100 focus:outline-none"
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
                    MENAMPILKAN: <span id="target-name">-</span>
                </span>
            </div>

            <button id="btn-ping" onclick="pingSiswa()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-xl transition-all active:scale-95 hidden pointer-events-auto items-center gap-2">
                PAKSA UPDATE LOKASI (PING)
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map, marker, circle;
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
    });

    function focusSiswa(idSiswa, nama) {
        if (intervalId) clearInterval(intervalId);
        currentTargetId = idSiswa;
        document.getElementById('target-name').textContent = nama.toUpperCase();
        document.getElementById('tracking-status').classList.remove('hidden');
        document.getElementById('btn-ping').classList.remove('hidden');
        document.getElementById('btn-ping').classList.add('flex');
        fetchLocation();
        intervalId = setInterval(fetchLocation, 5000);
    }

    function fetchLocation() {
        if (!currentTargetId) return;
        fetch(`<?= base_url('admin/tracking/getLocation/') ?>${currentTargetId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 200 && data.lat) {
                    const pos = [data.lat, data.lng];
                    if (marker) map.removeLayer(marker);
                    marker = window.L.marker(pos).addTo(map).bindPopup(`<b>${data.nama}</b><br>Terakhir: ${data.last_update}`).openPopup();
                    map.panTo(pos);
                }
            }).catch(err => console.log('Menunggu pembaruan...'));
    }

    function pingSiswa() {
        if (!currentTargetId) return;
        toastr.info("Mengirim sinyal ping...");
        fetch(`<?= base_url('admin/tracking/pingSiswa/') ?>${currentTargetId}`, {
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
                if (data.status === 200) toastr.success(data.message);
                else toastr.error(data.message);
            })
            .catch(err => toastr.error(err.message));
    }
</script>
<?= $this->endSection() ?>