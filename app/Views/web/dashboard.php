<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-lg p-6 md:p-8 mb-6 text-white relative overflow-hidden">
    <div class="relative z-10">
        <h2 class="text-2xl md:text-3xl font-bold mb-2">Selamat Datang, <?= session()->get('nama_lengkap') ?? 'Admin' ?> 👋</h2>
        <p class="text-blue-100 text-sm md:text-base max-w-2xl">Pantau aktivitas absensi, tren kehadiran, dan anomali keamanan (Geofence) secara real-time untuk hari ini.</p>
    </div>
    <div class="absolute -right-10 -top-10 opacity-20">
        <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
        </svg>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center hover:shadow-md transition-shadow">
        <div class="p-3 rounded-full bg-blue-50 text-blue-600 mr-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Total Siswa Aktif</p>
            <p class="text-2xl font-bold text-gray-800"><?= $total_siswa ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center hover:shadow-md transition-shadow">
        <div class="p-3 rounded-full bg-emerald-50 text-emerald-600 mr-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Hadir Hari Ini</p>
            <p class="text-2xl font-bold text-gray-800"><?= $hadir_hari_ini ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center hover:shadow-md transition-shadow">
        <div class="p-3 rounded-full bg-gray-50 text-gray-600 mr-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Alpa / Tdk Hadir</p>
            <p class="text-2xl font-bold text-gray-800"><?= $alpa_hari_ini ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center hover:shadow-md transition-shadow">
        <div class="p-3 rounded-full bg-red-50 text-red-600 mr-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Deteksi Manipulasi</p>
            <p class="text-2xl font-bold text-gray-800"><?= $fraud_hari_ini ?></p>
        </div>
    </div>

</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800">Tren Kehadiran & Pelanggaran</h3>
        <span class="text-xs font-medium bg-blue-100 text-blue-700 px-2 py-1 rounded">7 Hari Terakhir</span>
    </div>

    <div class="relative h-80 w-full">
        <canvas id="attendanceChart"></canvas>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $chart_labels ?>, // Data dari Controller
                datasets: [{
                        label: 'Tepat Waktu',
                        data: <?= $chart_hadir ?>,
                        backgroundColor: '#10B981', // Tailwind emerald-500
                        borderRadius: 4
                    },
                    {
                        label: 'Terlambat',
                        data: <?= $chart_terlambat ?>,
                        backgroundColor: '#F59E0B', // Tailwind amber-500
                        borderRadius: 4
                    },
                    {
                        label: 'Alpa',
                        data: <?= $chart_alpa ?>,
                        backgroundColor: '#EF4444', // Tailwind red-500
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true, // Menggabungkan bar secara vertikal
                        grid: {
                            display: false
                        } // Hilangkan garis vertikal
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }, // Hindari angka desimal di sumbu Y
                        grid: {
                            color: '#f3f4f6',
                            borderDash: [5, 5] // Garis putus-putus
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(17, 24, 39, 0.9)', // slate-900
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 10
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>