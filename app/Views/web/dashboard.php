<?php

/**
 * Petunjuk untuk Intelephense agar tidak error P1008 & P1006
 * @var string $title
 * @var int $total_siswa
 * @var int $hadir_hari_ini
 * @var int $alpa_hari_ini
 * @var int $fraud_hari_ini
 * @var string $chart_labels
 * @var string $chart_hadir
 * @var string $chart_terlambat
 * @var string $chart_alpa
 * @var string $chart_distribution
 * @var array<int, array<string, mixed>> $top_classes
 * @var array<int, array<string, mixed>> $list_manipulasi
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="bg-gradient-to-r from-indigo-600 to-blue-700 rounded-2xl shadow-lg p-6 mb-6 text-white relative overflow-hidden">
    <div class="relative z-10">
        <h2 class="text-2xl font-bold">Analytics Command Center</h2>
        <p class="text-indigo-100 text-sm mt-1">Pantau kehadiran siswa dan efektivitas Geofencing secara real-time.</p>
    </div>
    <div class="absolute -right-10 -top-10 opacity-10">
        <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
        </svg>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa Terdaftar</p>
        <p class="text-2xl font-black text-gray-800"><?= number_format($total_siswa) ?></p>
    </div>

    <?php
    // Logic internal view untuk persentase
    $persenHadir = ($total_siswa > 0) ? round(($hadir_hari_ini / $total_siswa) * 100) : 0;
    ?>

    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Tingkat Kehadiran</p>
        <div class="flex items-end gap-2">
            <p class="text-2xl font-black text-gray-800"><?= (int) $persenHadir ?>%</p>
            <p class="text-[10px] text-gray-400 mb-1">(<?= (int) $hadir_hari_ini ?> Siswa)</p>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest">Absen (Alpa)</p>
        <p class="text-2xl font-black text-gray-800"><?= (int) $alpa_hari_ini ?></p>
    </div>
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm border-l-4 border-l-red-500">
        <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Anomali Geofence</p>
        <p class="text-2xl font-black text-gray-800"><?= (int) $fraud_hari_ini ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
        <h3 class="text-xs font-bold text-gray-700 mb-6 uppercase tracking-wider">Tren Kehadiran & Pelanggaran (7 Hari)</h3>
        <div class="flex-1 min-h-[250px]"><canvas id="attendanceChart"></canvas></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center">
        <h3 class="text-xs font-bold text-gray-700 mb-6 uppercase tracking-wider w-full text-left">Proporsi Kehadiran</h3>
        <div class="w-full h-48"><canvas id="distributionChart"></canvas></div>
        <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-2 w-full text-[9px] font-bold text-gray-500">
            <div class="flex items-center gap-2"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> HADIR</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 bg-amber-400 rounded-full"></span> TERLAMBAT</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 bg-blue-400 rounded-full"></span> IZIN/SAKIT</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 bg-red-500 rounded-full"></span> ALPA</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-xs font-bold text-gray-700 mb-6 uppercase tracking-wider">Performa Kelas Terbaik</h3>
        <div class="space-y-5">
            <?php foreach ($top_classes as $index => $tc): ?>
                <div class="flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 text-[10px] font-black"><?= $index + 1 ?></span>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-blue-600 transition-colors"><?= esc((string) $tc['nama_kelas']) ?></span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black text-emerald-600"><?= (int) $tc['total_hadir'] ?></span>
                        <p class="text-[8px] text-gray-400 uppercase font-bold">Hadir</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-xs font-bold text-gray-700 mb-4 uppercase tracking-wider">Visualisasi Pelanggaran Lokasi</h3>
        <div id="mapFraud" class="h-64 rounded-xl border border-gray-100 shadow-sm mb-6 z-10"></div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] text-gray-400 uppercase border-b border-gray-50">
                    <tr>
                        <th class="pb-3">Siswa</th>
                        <th class="pb-3">Kelas</th>
                        <th class="pb-3 text-center">Tipe Anomali</th>
                        <th class="pb-3 text-right">Jam</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($list_manipulasi)): ?>
                        <tr>
                            <td colspan="4" class="py-10 text-center text-gray-400 italic text-xs">Aman terkendali. Tidak ada anomali terdeteksi.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($list_manipulasi as $m): ?>
                        <tr class="hover:bg-red-50/30 transition-colors">
                            <td class="py-3 font-bold text-gray-800"><?= esc((string) $m['nama_siswa']) ?></td>
                            <td class="py-3 text-gray-500 text-xs font-medium"><?= esc((string) $m['kelas']) ?></td>
                            <td class="py-3 text-center">
                                <span class="bg-red-100 text-red-600 text-[9px] font-black px-2 py-0.5 rounded shadow-sm border border-red-200">
                                    <?= $m['is_fake_gps'] ? '🚨 FAKE GPS' : '⚠️ MANIPULASI' ?>
                                </span>
                            </td>
                            <td class="py-3 text-right text-gray-400 font-mono text-xs"><?= date('H:i', strtotime((string)$m['jam_masuk'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Inisialisasi Peta (Leaflet)
        const map = window.L.map('mapFraud').setView([-6.20000000, 106.81666600], 13);
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const listManipulasi = <?= json_encode($list_manipulasi) ?>;
        const markers = [];

        listManipulasi.forEach(m => {
            if (m.lat_masuk && m.long_masuk) {
                const markerColor = m.is_fake_gps == 1 ? '#EF4444' : '#F59E0B';
                const marker = window.L.circleMarker([m.lat_masuk, m.long_masuk], {
                    radius: 8,
                    fillColor: markerColor,
                    color: "#fff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);

                marker.bindPopup(`
                <div class="text-[10px]">
                    <p class="font-black text-gray-800">${m.nama_siswa}</p>
                    <p class="text-gray-500 mb-1">${m.kelas}</p>
                    <span class="bg-red-50 text-red-600 font-bold px-1 rounded border border-red-100">
                        ${m.is_fake_gps == 1 ? 'Fake GPS' : 'Luar Zona'}
                    </span>
                </div>
            `);
                markers.push([m.lat_masuk, m.long_masuk]);
            }
        });

        if (markers.length > 0) {
            map.fitBounds(window.L.latLngBounds(markers), {
                padding: [30, 30]
            });
        }

        // 2. Bar Chart Trend
        new window.Chart(document.getElementById('attendanceChart'), {
            type: 'bar',
            data: {
                labels: <?= $chart_labels ?>,
                datasets: [{
                        label: 'Hadir',
                        data: <?= $chart_hadir ?>,
                        backgroundColor: '#10B981',
                        borderRadius: 5
                    },
                    {
                        label: 'Terlambat',
                        data: <?= $chart_terlambat ?>,
                        backgroundColor: '#FBBF24',
                        borderRadius: 5
                    },
                    {
                        label: 'Alpa',
                        data: <?= $chart_alpa ?>,
                        backgroundColor: '#EF4444',
                        borderRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        stacked: true
                    },
                    x: {
                        stacked: true
                    }
                }
            }
        });

        // 3. Doughnut Chart Distribusi
        new window.Chart(document.getElementById('distributionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpa'],
                datasets: [{
                    data: <?= $chart_distribution ?>,
                    backgroundColor: ['#10B981', '#FBBF24', '#60A5FA', '#818CF8', '#EF4444'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>