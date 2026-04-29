<?php

/**
 * @var int|string $total_siswa
 * @var int|string $hadir_hari_ini
 * @var int|string $alpa_hari_ini
 * @var int|string $fraud_hari_ini
 * @var string $chart_labels
 * @var string $chart_hadir
 * @var string $chart_terlambat
 * @var string $chart_alpa
 * @var object{waktu_masuk: string, status: string, is_fake_gps: int, nama_lengkap: string, kelas: string, nis: string, foto: string|null}[] $list_manipulasi
 */
?>
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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center hover:shadow-md transition-shadow">
        <div class="p-3 rounded-full bg-blue-50 text-blue-600 mr-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Total Siswa Aktif</p>
            <p class="text-2xl font-bold text-gray-800"><?= esc($total_siswa) ?></p>
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
            <p class="text-2xl font-bold text-gray-800"><?= esc($hadir_hari_ini) ?></p>
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
            <p class="text-2xl font-bold text-gray-800"><?= esc($alpa_hari_ini) ?></p>
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
            <p class="text-2xl font-bold text-gray-800"><?= esc($fraud_hari_ini) ?></p>
        </div>
    </div>

</div>

<!-- AREA BAWAH: GRAFIK & LIST MANIPULASI -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <!-- Bagian Grafik (Lebar 2 Kolom) -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Tren Kehadiran & Pelanggaran</h3>
            <span class="text-xs font-medium bg-blue-100 text-blue-700 px-2 py-1 rounded">7 Hari Terakhir</span>
        </div>

        <div class="relative flex-1 min-h-[300px] w-full">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>

    <!-- Bagian List Manipulasi Hari Ini (Lebar 1 Kolom) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col h-[400px]">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Anomali Hari Ini</h3>
            <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-bold animate-pulse">Live Alert</span>
        </div>

        <div class="flex-1 overflow-y-auto pr-2 space-y-3">
            <?php if (empty($list_manipulasi)): ?>
                <div class="h-full flex flex-col items-center justify-center text-center opacity-70">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-700">Aman Terkendali</p>
                    <p class="text-xs text-gray-500 mt-1">Tidak ada deteksi manipulasi lokasi hari ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($list_manipulasi as $m): ?>
                    <div class="flex items-start gap-3 p-3 bg-red-50/50 border border-red-100 rounded-xl">
                        <!-- Avatar Kecil -->
                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-xs shadow-inner overflow-hidden shrink-0 border border-red-200">
                            <?php if (!empty($m->foto)): ?>
                                <img src="/uploads/siswa/<?= esc($m->foto) ?>" alt="Foto" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= esc(strtoupper(substr($m->nama_lengkap ?? '', 0, 1))) ?>
                            <?php endif; ?>
                        </div>

                        <!-- Info Detail -->
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-gray-800 leading-tight"><?= esc($m->nama_lengkap) ?></h4>
                            <p class="text-[10px] text-gray-500 font-medium mb-1"><?= esc($m->kelas) ?> • <?= esc($m->nis) ?></p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                                    <?= $m->is_fake_gps ? 'FAKE GPS' : 'LUAR ZONA' ?>
                                </span>
                                <span class="text-[10px] text-gray-500 font-semibold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?= date('H:i', strtotime($m->waktu_masuk)) ?> WIB
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');

        new window.Chart(ctx, {
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