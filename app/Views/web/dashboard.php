<?php

/**
 * @var string $title
 * @var bool $is_wali_kelas
 * @var int $total_siswa
 * @var int $hadir_hari_ini
 * @var int $alpa_hari_ini
 * @var int $fraud_hari_ini
 * @var int $persen_hadir
 * @var string $chart_labels
 * @var string $chart_hadir
 * @var string $chart_terlambat
 * @var string $chart_alpa
 * @var string $chart_izin
 * @var string $chart_sakit
 * @var string $chart_dispensasi
 * @var string $chart_distribution
 * @var array<int, array<string, mixed>> $top_classes
 * @var array<int, array<string, mixed>> $list_manipulasi
 * @var string $default_filter
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php
    $isWaliKelas = $is_wali_kelas ?? false;
?>

<!-- ===== HERO BANNER ===== -->
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

<!-- ===== FILTER BAR ===== -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-wrap">

        <!-- Label -->
        <div class="flex items-center gap-2 text-gray-500 text-sm font-semibold shrink-0">
            <i class="fas fa-calendar-alt text-indigo-400"></i>
            <span>Periode:</span>
        </div>

        <!-- Preset Buttons -->
        <div id="filterPresets" class="flex items-center gap-2 flex-wrap">
            <button data-filter="hari_ini"   class="filter-btn px-4 py-1.5 rounded-full text-sm font-bold border transition-all duration-200 border-gray-200 text-gray-500 hover:border-indigo-400 hover:text-indigo-600">Hari Ini</button>
            <button data-filter="minggu_ini" class="filter-btn px-4 py-1.5 rounded-full text-sm font-bold border transition-all duration-200 border-gray-200 text-gray-500 hover:border-indigo-400 hover:text-indigo-600">7 Hari</button>
            <button data-filter="bulan_ini"  class="filter-btn px-4 py-1.5 rounded-full text-sm font-bold border transition-all duration-200 border-gray-200 text-gray-500 hover:border-indigo-400 hover:text-indigo-600 filter-active">30 Hari</button>
            <button data-filter="custom"     class="filter-btn px-4 py-1.5 rounded-full text-sm font-bold border transition-all duration-200 border-gray-200 text-gray-500 hover:border-indigo-400 hover:text-indigo-600" id="btnCustom">
                <i class="fas fa-sliders-h mr-1.5"></i>Kustom
            </button>
        </div>

        <!-- Custom Date Range (Flatpickr) - hidden by default -->
        <div id="customRangeWrapper" class="hidden flex items-center gap-2 flex-wrap">
            <div class="relative">
                <input type="text" id="dateRangePicker" placeholder="Pilih rentang tanggal..."
                    class="pl-9 pr-4 py-1.5 rounded-lg border border-indigo-300 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 w-56 cursor-pointer bg-white shadow-sm"
                    readonly>
                <i class="fas fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-indigo-400 text-xs"></i>
            </div>
            <button id="btnApplyCustom" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                Terapkan
            </button>
        </div>

        <!-- Divider -->
        <div class="hidden sm:block w-px h-5 bg-gray-200 mx-1"></div>

        <!-- Active Period Badge -->
        <div id="activePeriodBadge" class="flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-xs font-black px-3 py-1.5 rounded-full border border-indigo-200">
            <i class="fas fa-circle text-[6px] text-indigo-500 animate-pulse"></i>
            <span id="activePeriodLabel">Memuat data...</span>
        </div>

        <!-- Loading Spinner (hidden until AJAX runs) -->
        <div id="filterLoading" class="hidden items-center gap-2 text-indigo-500 text-xs font-bold ml-auto">
            <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Memuat data...
        </div>
    </div>
</div>

<!-- ===== STAT CARDS ===== -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    <div class="stat-card bg-white p-5 rounded-xl border border-gray-100 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-blue-100 group">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Siswa Terdaftar</p>
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-users text-blue-500 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-black text-gray-800" id="statTotalSiswa"><?= number_format($total_siswa) ?></p>
        <p class="text-[10px] text-gray-400 mt-1 font-medium">Total keseluruhan</p>
    </div>

    <div class="stat-card bg-white p-5 rounded-xl border border-gray-100 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-emerald-100 group">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-bold text-emerald-500 uppercase tracking-widest">Tingkat Kehadiran</p>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
            </div>
        </div>
        <div class="flex items-end gap-2">
            <p class="text-2xl font-black text-gray-800" id="statPersenHadir"><?= (int) $persen_hadir ?>%</p>
            <p class="text-xs text-gray-400 mb-1">(<span id="statHadir"><?= (int) $hadir_hari_ini ?></span> Siswa)</p>
        </div>
    </div>

    <div class="stat-card bg-white p-5 rounded-xl border border-gray-100 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-amber-100 group">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-bold text-amber-500 uppercase tracking-widest">Absen (Alpa)</p>
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-times-circle text-amber-500 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-black text-gray-800" id="statAlpa"><?= (int) $alpa_hari_ini ?></p>
        <p class="text-[10px] text-gray-400 mt-1 font-medium">Tanpa keterangan</p>
    </div>

    <div class="stat-card bg-white p-5 rounded-xl border-y border-r border-gray-100 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md border-l-4 border-l-red-500 group">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-bold text-red-500 uppercase tracking-widest">Anomali Geofence</p>
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-black text-gray-800" id="statFraud"><?= (int) $fraud_hari_ini ?></p>
        <p class="text-[10px] text-gray-400 mt-1 font-medium">Fake GPS / Luar zona</p>
    </div>

</div>

<!-- ===== CHARTS ROW ===== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Tren Kehadiran & Pelanggaran</h3>
            <span id="chartRangeLabel" class="text-[10px] font-black text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-1 rounded-full"></span>
        </div>
        <!-- Skeleton / Chart -->
        <div id="chartSkeleton" class="flex-1 min-h-[250px] rounded-xl bg-gradient-to-r from-gray-100 via-gray-50 to-gray-100 animate-pulse hidden"></div>
        <div class="flex-1 min-h-[250px]"><canvas id="attendanceChart"></canvas></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center">
        <h3 class="text-xs font-bold text-gray-700 mb-6 uppercase tracking-wider w-full text-left">Proporsi Kehadiran</h3>
        <div id="donutSkeleton" class="w-full h-48 rounded-xl bg-gradient-to-r from-gray-100 via-gray-50 to-gray-100 animate-pulse hidden"></div>
        <div class="w-full h-48"><canvas id="distributionChart"></canvas></div>
        <div class="mt-6 grid grid-cols-2 lg:grid-cols-3 gap-x-2 gap-y-3 w-full text-xs font-bold text-gray-500">
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> HADIR</div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-teal-500 rounded-full"></span> DISPEN</div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-amber-400 rounded-full"></span> TELAT</div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-blue-400 rounded-full"></span> SAKIT</div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-indigo-400 rounded-full"></span> IZIN</div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-red-500 rounded-full"></span> ALPA</div>
        </div>
    </div>
</div>

<!-- ===== BOTTOM ROW: Leaderboard + Map & Fraud Table ===== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-16">

    <!-- Leaderboard Kelas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
        <h3 class="text-xs font-bold text-gray-700 mb-6 uppercase tracking-wider">
            <?= $isWaliKelas ? 'Performa Kelas Anda' : 'Performa Kelas Terbaik' ?>
        </h3>
        <div id="leaderboardContainer" class="space-y-5">
            <?php foreach ($top_classes as $index => $tc): ?>
                <div class="flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 text-xs font-black"><?= $index + 1 ?></span>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-blue-600 transition-colors"><?= esc((string) ($tc['nama_kelas'] ?? '')) ?></span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black text-emerald-600"><?= (int) ($tc['total_hadir'] ?? 0) ?></span>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Hadir</p>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($top_classes)): ?>
                <p class="text-xs text-center text-gray-400 italic">Belum ada data performa kelas.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fraud Map + Table -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Visualisasi Pelanggaran Lokasi</h3>
            <span id="fraudCount" class="text-[10px] font-black text-red-600 bg-red-50 border border-red-100 px-2 py-1 rounded-full hidden"></span>
        </div>
        <div id="mapFraud" class="h-64 rounded-xl border border-gray-100 shadow-sm mb-6 z-0 relative"></div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-gray-400 uppercase border-b border-gray-50">
                    <tr>
                        <th class="pb-3">Siswa</th>
                        <th class="pb-3">Kelas</th>
                        <th class="pb-3 text-center">Tipe Anomali</th>
                        <th class="pb-3 text-right">Jam</th>
                    </tr>
                </thead>
                <tbody id="fraudTableBody" class="divide-y divide-gray-100">
                    <?php if (empty($list_manipulasi)): ?>
                        <tr>
                            <td colspan="4" class="py-10 text-center text-gray-400 italic text-xs">Aman terkendali. Tidak ada anomali terdeteksi.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list_manipulasi as $m): ?>
                            <tr class="hover:bg-red-50/30 transition-colors">
                                <td class="py-3 font-bold text-gray-800"><?= esc((string) ($m['nama_siswa'] ?? '')) ?></td>
                                <td class="py-3 text-gray-500 text-xs font-medium"><?= esc((string) ($m['kelas'] ?? '-')) ?></td>
                                <td class="py-3 text-center">
                                    <span class="bg-red-100 text-red-600 text-[10px] font-black px-2 py-0.5 rounded shadow-sm border border-red-200">
                                        <?= !empty($m['is_fake_gps']) ? '🚨 FAKE GPS' : '⚠️ MANIPULASI' ?>
                                    </span>
                                </td>
                                <td class="py-3 text-right text-gray-400 font-mono text-xs"><?= date('H:i', strtotime((string)($m['jam_masuk'] ?? '00:00:00'))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<style>
    .filter-active {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
        color: #fff !important;
    }
    /* Flatpickr custom style */
    .flatpickr-calendar {
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        border: 1px solid #e0e7ff;
    }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
        background: #4f46e5 !important;
        border-color: #4f46e5 !important;
    }
    .flatpickr-day.inRange {
        background: #e0e7ff !important;
        border-color: #e0e7ff !important;
        color: #4f46e5 !important;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ================================================================
    // STATE
    // ================================================================
    let attendanceChart  = null;
    let distributionChart = null;
    let fraudMap         = null;
    let currentFilter    = '<?= esc($default_filter ?? 'bulan_ini') ?>';
    let customStart      = null;
    let customEnd        = null;

    // ================================================================
    // INIT CHARTS (with server-rendered initial data)
    // ================================================================
    function buildAttendanceChart(labels, hadir, dispensasi, terlambat, sakit, izin, alpa) {
        if (attendanceChart) attendanceChart.destroy();
        attendanceChart = new Chart(document.getElementById('attendanceChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Hadir',      data: hadir,      backgroundColor: '#10B981', borderRadius: 5 },
                    { label: 'Dispensasi', data: dispensasi, backgroundColor: '#14B8A6', borderRadius: 5 },
                    { label: 'Terlambat',  data: terlambat,  backgroundColor: '#FBBF24', borderRadius: 5 },
                    { label: 'Sakit',      data: sakit,      backgroundColor: '#60A5FA', borderRadius: 5 },
                    { label: 'Izin',       data: izin,       backgroundColor: '#818CF8', borderRadius: 5 },
                    { label: 'Alpa',       data: alpa,       backgroundColor: '#EF4444', borderRadius: 5 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { stacked: true }, x: { stacked: true } },
                animation: {
                    duration: 900, easing: 'easeOutQuart',
                    delay: (ctx) => ctx.type === 'data' && ctx.mode === 'default' ? ctx.dataIndex * 25 : 0
                }
            }
        });
    }

    function buildDistributionChart(distribution) {
        if (distributionChart) distributionChart.destroy();
        distributionChart = new Chart(document.getElementById('distributionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Dispensasi', 'Terlambat', 'Sakit', 'Izin', 'Alpa'],
                datasets: [{
                    data: distribution,
                    backgroundColor: ['#10B981', '#14B8A6', '#FBBF24', '#60A5FA', '#818CF8', '#EF4444'],
                    borderWidth: 0, cutout: '75%'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                animation: { animateScale: true, animateRotate: true, duration: 1500, easing: 'easeOutBounce' }
            }
        });
    }

    // Initial render with PHP-injected data
    buildAttendanceChart(
        <?= $chart_labels ?>,
        <?= $chart_hadir ?>,
        <?= $chart_dispensasi ?>,
        <?= $chart_terlambat ?>,
        <?= $chart_sakit ?>,
        <?= $chart_izin ?>,
        <?= $chart_alpa ?>
    );
    buildDistributionChart(<?= $chart_distribution ?>);

    // ================================================================
    // INIT MAP (Leaflet)
    // ================================================================
    fraudMap = window.L.map('mapFraud').setView([-6.20000000, 106.81666600], 13);
    window.L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }).addTo(fraudMap);

    function renderMapMarkers(manipulasi) {
        // Clear existing markers
        fraudMap.eachLayer(layer => {
            if (layer instanceof window.L.CircleMarker) fraudMap.removeLayer(layer);
        });

        const bounds = [];
        manipulasi.forEach(m => {
            if (m.lat_masuk && m.long_masuk) {
                const isFake  = m.is_fake_gps == 1;
                const marker  = window.L.circleMarker([m.lat_masuk, m.long_masuk], {
                    radius: 8, fillColor: isFake ? '#EF4444' : '#F59E0B',
                    color: '#fff', weight: 2, opacity: 1, fillOpacity: 0.9
                }).addTo(fraudMap);

                const safeName  = (m.nama_siswa || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const safeKelas = (m.kelas || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                marker.bindPopup(`
                    <div class="text-xs">
                        <p class="font-black text-gray-800">${safeName}</p>
                        <p class="text-gray-500 mb-1">${safeKelas}</p>
                        <span class="bg-red-50 text-red-600 font-bold px-1 rounded border border-red-100 inline-block mt-1 text-[10px]">
                            ${isFake ? 'Fake GPS' : 'Luar Zona'}
                        </span>
                    </div>
                `);
                bounds.push([m.lat_masuk, m.long_masuk]);
            }
        });

        if (bounds.length > 0) {
            fraudMap.fitBounds(window.L.latLngBounds(bounds), { padding: [30, 30] });
        }
    }

    // Initial map render
    renderMapMarkers(<?= json_encode($list_manipulasi) ?>);
    const initialFraudCount = <?= count($list_manipulasi) ?>;
    if (initialFraudCount > 0) {
        const fc = document.getElementById('fraudCount');
        fc.textContent = initialFraudCount + ' pelanggaran';
        fc.classList.remove('hidden');
    }

    // ================================================================
    // RENDER FRAUD TABLE
    // ================================================================
    function renderFraudTable(manipulasi) {
        const tbody = document.getElementById('fraudTableBody');
        if (!manipulasi || manipulasi.length === 0) {
            tbody.innerHTML = `
                <tr><td colspan="4" class="py-10 text-center text-gray-400 italic text-xs">
                    Aman terkendali. Tidak ada anomali terdeteksi.
                </td></tr>`;
            return;
        }
        tbody.innerHTML = manipulasi.map(m => {
            const name  = (m.nama_siswa || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const kelas = (m.kelas || '-').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const jam   = m.jam_masuk ? m.jam_masuk.substring(11, 16) : '--:--';
            const badge = m.is_fake_gps == 1
                ? '<span class="bg-red-100 text-red-600 text-[10px] font-black px-2 py-0.5 rounded shadow-sm border border-red-200">🚨 FAKE GPS</span>'
                : '<span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-0.5 rounded shadow-sm border border-amber-200">⚠️ MANIPULASI</span>';
            return `
                <tr class="hover:bg-red-50/30 transition-colors">
                    <td class="py-3 font-bold text-gray-800">${name}</td>
                    <td class="py-3 text-gray-500 text-xs font-medium">${kelas}</td>
                    <td class="py-3 text-center">${badge}</td>
                    <td class="py-3 text-right text-gray-400 font-mono text-xs">${jam}</td>
                </tr>`;
        }).join('');
    }

    // ================================================================
    // RENDER LEADERBOARD
    // ================================================================
    function renderLeaderboard(topClasses) {
        const container = document.getElementById('leaderboardContainer');
        if (!topClasses || topClasses.length === 0) {
            container.innerHTML = '<p class="text-xs text-center text-gray-400 italic">Belum ada data performa kelas.</p>';
            return;
        }
        container.innerHTML = topClasses.map((tc, i) => {
            const name = (tc.nama_kelas || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return `
                <div class="flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 text-xs font-black">${i + 1}</span>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-blue-600 transition-colors">${name}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black text-emerald-600">${tc.total_hadir || 0}</span>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Hadir</p>
                    </div>
                </div>`;
        }).join('');
    }

    // ================================================================
    // FETCH DASHBOARD DATA (AJAX)
    // ================================================================
    function fetchDashboardData(filter, start, end) {
        const loading = document.getElementById('filterLoading');
        const chartSkeleton = document.getElementById('chartSkeleton');
        const donutSkeleton = document.getElementById('donutSkeleton');
        loading.classList.remove('hidden');
        loading.classList.add('flex');

        let url = `<?= base_url('admin/dashboard/data') ?>?filter=${filter}`;
        if (filter === 'custom' && start && end) {
            url += `&start=${start}&end=${end}`;
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
            if (!r.ok) throw new Error('Gagal mengambil data');
            return r.json();
        })
        .then(data => {
            // Update stat cards
            document.getElementById('statTotalSiswa').textContent   = parseInt(data.stats.total_siswa).toLocaleString('id-ID');
            document.getElementById('statPersenHadir').textContent   = data.stats.persen_hadir + '%';
            document.getElementById('statHadir').textContent         = data.stats.hadir;
            document.getElementById('statAlpa').textContent          = data.stats.alpa;
            document.getElementById('statFraud').textContent         = data.stats.fraud;

            // Update range label
            document.getElementById('activePeriodLabel').textContent = data.range.label;
            document.getElementById('chartRangeLabel').textContent   = data.range.label;

            // Update charts
            buildAttendanceChart(
                data.chart.labels,
                data.chart.hadir,
                data.chart.dispensasi,
                data.chart.terlambat,
                data.chart.sakit,
                data.chart.izin,
                data.chart.alpa
            );
            buildDistributionChart(data.distribution);

            // Update fraud count badge
            const fc = document.getElementById('fraudCount');
            if (data.manipulasi.length > 0) {
                fc.textContent = data.manipulasi.length + ' pelanggaran';
                fc.classList.remove('hidden');
            } else {
                fc.classList.add('hidden');
            }

            // Update fraud table & map
            renderFraudTable(data.manipulasi);
            renderMapMarkers(data.manipulasi);

            // Update leaderboard
            renderLeaderboard(data.top_classes);
        })
        .catch(err => {
            console.error(err);
            toastr.error('Gagal memuat data dashboard.', 'Error');
        })
        .finally(() => {
            loading.classList.add('hidden');
            loading.classList.remove('flex');
        });
    }

    // ================================================================
    // FILTER BUTTON LOGIC
    // ================================================================
    function setActiveFilterBtn(filter) {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('filter-active');
            if (btn.dataset.filter === filter) {
                btn.classList.add('filter-active');
            }
        });
    }

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.dataset.filter;
            currentFilter = filter;

            if (filter === 'custom') {
                document.getElementById('customRangeWrapper').classList.remove('hidden');
                fp.open();
                return;
            }

            document.getElementById('customRangeWrapper').classList.add('hidden');
            setActiveFilterBtn(filter);
            fetchDashboardData(filter, null, null);
        });
    });

    // ================================================================
    // FLATPICKR — Date Range Picker
    // ================================================================
    const fp = flatpickr('#dateRangePicker', {
        mode: 'range',
        locale: 'id',
        dateFormat: 'd M Y',
        maxDate: 'today',
        showMonths: window.innerWidth >= 768 ? 2 : 1,
        onChange: function (selectedDates) {
            if (selectedDates.length === 2) {
                const fmt = d => d.toISOString().split('T')[0];
                customStart = fmt(selectedDates[0]);
                customEnd   = fmt(selectedDates[1]);
            }
        }
    });

    document.getElementById('btnApplyCustom').addEventListener('click', function () {
        if (!customStart || !customEnd) {
            toastr.warning('Pilih rentang tanggal terlebih dahulu.', 'Perhatian');
            return;
        }
        setActiveFilterBtn('custom');
        fetchDashboardData('custom', customStart, customEnd);
        document.getElementById('customRangeWrapper').classList.add('hidden');
    });

    // ================================================================
    // INITIAL PERIOD BADGE
    // ================================================================
    // Set the badge on page load to reflect the default filter (bulan_ini)
    const today     = new Date();
    const toDateStr = d => d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const past30    = new Date(); past30.setDate(past30.getDate() - 29);
    document.getElementById('activePeriodLabel').textContent = toDateStr(past30) + ' – ' + toDateStr(today);
    document.getElementById('chartRangeLabel').textContent   = toDateStr(past30) + ' – ' + toDateStr(today);

});
</script>
<?= $this->endSection() ?>