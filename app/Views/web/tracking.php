<?php

/**
 * @var array $config
 * @var array $list_siswa
 * @var array $list_kelas
 * @var string $kelas_aktif
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
                <select name="kelas_id" onchange="this.form.submit()" class="w-full border-gray-200 rounded-xl p-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($list_kelas as $k): ?>
                        <option value="<?= (string)$k['id_kelas'] ?>" <?= ($kelas_aktif == (string)$k['id_kelas']) ? 'selected' : '' ?>>
                            <?= esc((string)$k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1">
            <?php if (!empty($list_siswa)) : ?>
                <?php foreach ($list_siswa as $s): ?>
                    <button onclick="focusSiswa(<?= esc((string)$s['id_siswa']) ?>, '<?= esc((string)$s['nama_siswa']) ?>')"
                        class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition-all text-left group border border-transparent hover:border-blue-100 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px] group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <?= esc(strtoupper(substr((string)$s['nama_siswa'], 0, 1))) ?>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-800 group-hover:text-blue-700"><?= esc((string)$s['nama_siswa']) ?></div>
                            <div class="text-[10px] text-gray-500"><?= esc((string)$s['nama_kelas']) ?></div>
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
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
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
        // Menggunakan window.L secara eksplisit
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
    });

    function focusSiswa(idSiswa, nama) {
        if (intervalId) clearInterval(intervalId);

        currentTargetId = idSiswa;
        document.getElementById('target-name').textContent = nama.toUpperCase();
        document.getElementById('tracking-status').classList.remove('hidden');

        let btnPing = document.getElementById('btn-ping');
        btnPing.classList.remove('hidden');
        btnPing.classList.add('flex');

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
            }).catch(err => console.log('Menunggu pembaruan radar...'));
    }

    function pingSiswa() {
        if (!currentTargetId) return;

        toastr.info("Mengirim sinyal ping ke perangkat...");

        fetch(`<?= base_url('admin/tracking/pingSiswa/') ?>${currentTargetId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 200) toastr.success(res.message);
                else toastr.error(res.message);
            });
    }
</script>
<?= $this->endSection() ?>