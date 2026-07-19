<?php

/**
 * @var array<string, mixed> $zona_default
 * @var array<int, array<string, mixed>> $list_siswa
 * @var string|null $keyword
 * @var string|null $target_id
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="flex flex-col-reverse lg:flex-row gap-4 lg:gap-6 lg:h-[calc(100vh-140px)]">

    <!-- ===== PANEL KIRI: DAFTAR SISWA ===== -->
    <div class="w-full lg:w-80 h-[440px] lg:h-full flex-shrink-0 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
        <div class="p-4 border-b bg-gray-50/50 shrink-0">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Monitoring Siswa</h3>
            <form action="<?= base_url('admin/tracking') ?>" method="GET">
                <div class="relative">
                    <input type="text" name="keyword" id="searchInput" value="<?= esc((string)($keyword ?? '')) ?>" placeholder="Cari Nama atau NIS..." autocomplete="off" class="w-full border border-gray-200 rounded-xl p-2 pl-8 text-xs outline-none focus:ring-2 focus:ring-blue-500 bg-white transition-all font-medium">
                    <svg class="w-4 h-4 absolute left-2.5 top-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
            </form>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1 scrollbar-hide" id="siswaList">
            <?php if (!empty($list_siswa)) : ?>
                <?php foreach ($list_siswa as $s): ?>
                    <button id="btn-siswa-<?= esc((string)$s['id_siswa']) ?>"
                        onclick="focusSiswa(<?= esc((string)$s['id_siswa']) ?>, '<?= esc(addslashes((string)$s['nama_siswa'])) ?>', <?= isset($s['zona_lat']) && $s['zona_lat'] !== null ? $s['zona_lat'] : 'null' ?>, <?= isset($s['zona_lng']) && $s['zona_lng'] !== null ? $s['zona_lng'] : 'null' ?>, <?= isset($s['zona_radius']) && $s['zona_radius'] !== null ? $s['zona_radius'] : 'null' ?>)"
                        class="siswa-item w-full flex items-center justify-between p-3 rounded-xl hover:bg-blue-50 transition-all text-left group border border-transparent focus:outline-none gap-2"
                        data-nama="<?= esc(strtolower((string)$s['nama_siswa'])) ?>"
                        data-nis="<?= esc(strtolower((string)$s['nis'])) ?>">

                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px] group-hover:bg-blue-600 group-hover:text-white transition-colors shrink-0">
                                <?= esc(strtoupper(substr((string)$s['nama_siswa'], 0, 1))) ?>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-xs font-bold text-gray-800 group-hover:text-blue-700 truncate"><?= esc((string)$s['nama_siswa']) ?></div>
                                <div class="text-[10px] text-gray-500 truncate"><?= esc((string)$s['nama_kelas']) ?> &bull; <?= esc((string)$s['nis']) ?></div>
                            </div>
                        </div>

                        <?php if (!empty($s['device_id'])): ?>
                            <div class="text-[9px] font-black text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-md border border-emerald-100 flex items-center gap-1 shrink-0 whitespace-nowrap shadow-sm">
                                <div class="w-1 h-1 bg-emerald-500 rounded-full animate-pulse"></div>TERIKAT
                            </div>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-4 text-center text-xs text-gray-400">Tidak ada data siswa.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== PANEL KANAN: PETA ===== -->
    <div class="w-full h-[400px] lg:h-full lg:flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
        <div id="map" class="w-full h-full z-10"></div>

        <!-- Overlay Bawah: Status Tracking & Tombol PING -->
        <div class="absolute bottom-6 left-6 right-6 z-20 flex flex-wrap justify-between items-end gap-3 pointer-events-none">

            <!-- Status sedang memantau -->
            <div id="tracking-status" class="bg-slate-900/80 backdrop-blur-md text-white px-4 py-2 rounded-full text-[10px] font-bold shadow-lg border border-white/10 hidden pointer-events-auto">
                <span class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span id="status-text" class="hidden sm:inline">MEMANTAU:</span>
                    <span id="target-name" class="text-emerald-300">-</span>
                </span>
            </div>

            <!-- Status Tunggu Respons PING (muncul setelah ping dikirim) -->
            <div id="ping-waiting-status" class="hidden bg-amber-500/90 backdrop-blur-md text-white px-4 py-2 rounded-full text-[10px] font-bold shadow-lg border border-amber-400/30 pointer-events-auto">
                <span class="flex items-center gap-2">
                    <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Menunggu Respons HP... (<span id="ping-countdown">30</span>s)</span>
                </span>
            </div>

            <!-- Tombol PING -->
            <button id="btn-ping" onclick="pingSiswa()" class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-xl transition-all active:scale-95 hidden pointer-events-auto items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                </svg>
                <span id="ping-btn-label" class="hidden sm:inline">LACAK SEKARANG</span>
                <span class="sm:hidden">LACAK</span>
            </button>
        </div>

        <!-- Overlay Kanan Atas: Timestamp Last Update -->
        <div id="last-update-badge" class="absolute top-4 right-4 z-20 hidden">
            <div class="bg-white/90 backdrop-blur-md border border-gray-200 shadow-md rounded-xl px-3 py-2 text-[10px] font-bold text-gray-600 flex items-center gap-2">
                <div id="update-dot" class="w-2 h-2 bg-gray-400 rounded-full"></div>
                <span>Diperbarui </span>
                <span id="last-update-text">baru saja</span>
            </div>
        </div>

        <!-- Overlay Kiri Atas: Notifikasi titik baru diterima -->
        <div id="new-point-toast" class="absolute top-4 left-4 z-20 hidden">
            <div class="bg-emerald-600/90 backdrop-blur-md text-white px-4 py-2 rounded-xl text-[10px] font-black shadow-lg border border-emerald-500/30 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                Titik Lokasi Baru Diterima!
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .custom-div-icon { background: transparent; border: none; }
    .leaflet-control-layers-expanded {
        padding: 8px 12px !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
        border: 1px solid #f3f4f6 !important;
        font-family: inherit !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #374151 !important;
    }
</style>

<script>
    // ================================================================
    // STATE
    // ================================================================
    let map;
    let markers            = [];
    let polylineLayer      = null;
    let currentTargetId    = null;
    let intervalId         = null;
    let currentZoneCircle  = null;
    let isFirstTrackLoad   = true;
    let lastPointCount     = 0;       // Untuk deteksi titik lokasi baru
    let lastUpdateTime     = null;    // Timestamp terakhir data diterima
    let lastUpdateInterval = null;    // Interval untuk update badge "X detik lalu"
    let pingCooldownTimer  = null;    // Cooldown tombol PING
    let pingWaitTimer      = null;    // Countdown "Menunggu respons..."
    let pingWaitInterval   = null;    // Interval countdown
    let isSoundEnabled     = true;    // Toggle suara notifikasi

    let csrfTokenName  = '<?= csrf_header() ?>';
    let csrfTokenValue = '<?= csrf_hash() ?>';

    // ================================================================
    // WEB AUDIO API — Sound Alert (tidak butuh file eksternal)
    // ================================================================
    function playNewPointSound() {
        if (!isSoundEnabled) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1100, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.4, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.4);
        } catch(e) { /* Browser mungkin blokir AudioContext sebelum interaksi user */ }
    }

    // ================================================================
    // LAST UPDATE BADGE — "Diperbarui X detik lalu"
    // ================================================================
    function updateLastUpdateBadge() {
        if (!lastUpdateTime) return;
        const elapsed   = Math.floor((Date.now() - lastUpdateTime) / 1000);
        const dot       = document.getElementById('update-dot');
        const text      = document.getElementById('last-update-text');
        const badge     = document.getElementById('last-update-badge');

        badge.classList.remove('hidden');

        if (elapsed < 5) {
            text.textContent = 'baru saja';
            dot.className = 'w-2 h-2 bg-emerald-500 rounded-full animate-pulse';
        } else if (elapsed < 30) {
            text.textContent = elapsed + ' detik lalu';
            dot.className = 'w-2 h-2 bg-emerald-400 rounded-full';
        } else if (elapsed < 90) {
            text.textContent = elapsed + ' detik lalu';
            dot.className = 'w-2 h-2 bg-amber-400 rounded-full';
        } else {
            text.textContent = Math.floor(elapsed / 60) + ' menit lalu';
            dot.className = 'w-2 h-2 bg-red-400 rounded-full animate-pulse';
        }
    }

    function startLastUpdateInterval() {
        if (lastUpdateInterval) clearInterval(lastUpdateInterval);
        lastUpdateInterval = setInterval(updateLastUpdateBadge, 1000);
    }

    // ================================================================
    // NEW POINT TOAST NOTIFICATION
    // ================================================================
    function showNewPointToast() {
        const toast = document.getElementById('new-point-toast');
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    // ================================================================
    // PING RESPONSE STATUS — Countdown "Menunggu respons..."
    // ================================================================
    function startPingWaiting() {
        const statusEl    = document.getElementById('ping-waiting-status');
        const countdownEl = document.getElementById('ping-countdown');
        let seconds = 30;

        statusEl.classList.remove('hidden');
        countdownEl.textContent = seconds;

        if (pingWaitInterval) clearInterval(pingWaitInterval);
        pingWaitInterval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(pingWaitInterval);
                pingWaitInterval = null;
                statusEl.classList.add('hidden');
            }
        }, 1000);

        if (pingWaitTimer) clearTimeout(pingWaitTimer);
        pingWaitTimer = setTimeout(() => {
            statusEl.classList.add('hidden');
        }, 31000);
    }

    function stopPingWaiting() {
        if (pingWaitInterval) { clearInterval(pingWaitInterval); pingWaitInterval = null; }
        if (pingWaitTimer)    { clearTimeout(pingWaitTimer);     pingWaitTimer = null; }
        document.getElementById('ping-waiting-status').classList.add('hidden');
    }

    // ================================================================
    // PING COOLDOWN — Debounce tombol 10 detik
    // ================================================================
    function startPingCooldown() {
        const btn      = document.getElementById('btn-ping');
        const label    = document.getElementById('ping-btn-label');
        let remaining  = 10;

        btn.disabled = true;
        label.textContent = 'Tunggu (' + remaining + 's)';

        if (pingCooldownTimer) clearInterval(pingCooldownTimer);
        pingCooldownTimer = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(pingCooldownTimer);
                btn.disabled = false;
                label.textContent = 'LACAK SEKARANG';
            } else {
                label.textContent = 'Tunggu (' + remaining + 's)';
            }
        }, 1000);
    }

    // ================================================================
    // INIT MAP
    // ================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const googleStreets = L.tileLayer('https://{s}.google.com/vt?lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 21, subdomains: ['mt0','mt1','mt2','mt3'] });
        const googleHybrid  = L.tileLayer('https://{s}.google.com/vt?lyrs=s,h&x={x}&y={y}&z={z}', { maxZoom: 21, subdomains: ['mt0','mt1','mt2','mt3'] });
        const googleSat     = L.tileLayer('https://{s}.google.com/vt?lyrs=s&x={x}&y={y}&z={z}', { maxZoom: 21, subdomains: ['mt0','mt1','mt2','mt3'] });

        map = window.L.map('map', {
            center: [<?= (string)$zona_default['latitude'] ?>, <?= (string)$zona_default['longitude'] ?>],
            zoom: 16,
            layers: [googleStreets]
        });

        if (window.ResizeObserver) {
            new window.ResizeObserver(() => { if (map) map.invalidateSize(); }).observe(document.getElementById('map'));
        }

        L.control.layers({ "Google Maps Biasa": googleStreets, "Google Satellite": googleHybrid, "Google Terrain": googleSat }, null, { position: 'topright' }).addTo(map);

        window.L.marker([<?= (string)$zona_default['latitude'] ?>, <?= (string)$zona_default['longitude'] ?>])
            .addTo(map).bindPopup("<b>Zona Sekolah (Default)</b><br>Radius: <?= (string)$zona_default['radius'] ?>m").openPopup();

        currentZoneCircle = window.L.circle([<?= (string)$zona_default['latitude'] ?>, <?= (string)$zona_default['longitude'] ?>], {
            color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.15, radius: <?= (string)$zona_default['radius'] ?>
        }).addTo(map);

        // Search filter client-side
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                document.querySelectorAll('.siswa-item').forEach(item => {
                    const nama = item.getAttribute('data-nama');
                    const nis  = item.getAttribute('data-nis');
                    item.style.display = (nama.includes(filter) || nis.includes(filter)) ? 'flex' : 'none';
                });
            });
            searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); });
        }

        // Auto-focus siswa dari URL param
        const initialTargetId = '<?= esc((string)($target_id ?? '')) ?>';
        if (initialTargetId) {
            setTimeout(() => {
                const targetBtn = document.getElementById('btn-siswa-' + initialTargetId);
                if (targetBtn) {
                    targetBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    targetBtn.click();
                }
            }, 500);
        }

        // ==============================================================
        // PAGE VISIBILITY API — Pause/Resume polling jika tab background
        // ==============================================================
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Tab tidak aktif → pause interval untuk hemat resource
                if (intervalId) {
                    clearInterval(intervalId);
                    intervalId = null;
                }
            } else {
                // Tab kembali aktif → resume interval jika ada target
                if (currentTargetId && !intervalId) {
                    fetchLocationArray(); // Langsung fetch sekali
                    intervalId = setInterval(fetchLocationArray, 5000);
                }
            }
        });
    });

    // ================================================================
    // FOCUS SISWA (klik dari list)
    // ================================================================
    function focusSiswa(idSiswa, nama, zonaLat, zonaLng, zonaRadius) {
        if (intervalId) clearInterval(intervalId);
        stopPingWaiting();

        currentTargetId   = idSiswa;
        isFirstTrackLoad  = true;
        lastPointCount    = 0;
        lastUpdateTime    = null;

        // Reset badge last update
        document.getElementById('last-update-badge').classList.add('hidden');

        // Highlight siswa yang dipilih
        document.querySelectorAll('.siswa-item').forEach(el => {
            el.classList.remove('bg-blue-50', 'border-blue-200');
            el.classList.add('border-transparent');
        });
        const activeBtn = document.getElementById('btn-siswa-' + idSiswa);
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent');
            activeBtn.classList.add('bg-blue-50', 'border-blue-200');
        }

        document.getElementById('target-name').textContent = nama.toUpperCase();
        document.getElementById('tracking-status').classList.remove('hidden');
        document.getElementById('btn-ping').classList.remove('hidden');
        document.getElementById('btn-ping').classList.add('flex');

        // Hapus layer sebelumnya
        if (currentZoneCircle) map.removeLayer(currentZoneCircle);
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        if (polylineLayer) { map.removeLayer(polylineLayer); polylineLayer = null; }

        // Gambar zona siswa
        let lat = zonaLat !== null ? parseFloat(zonaLat) : <?= (string)$zona_default['latitude'] ?>;
        let lng = zonaLng !== null ? parseFloat(zonaLng) : <?= (string)$zona_default['longitude'] ?>;
        let rad = zonaRadius !== null ? parseFloat(zonaRadius) : <?= (string)$zona_default['radius'] ?>;

        currentZoneCircle = window.L.circle([lat, lng], {
            color: zonaLat !== null ? '#f59e0b' : '#3b82f6',
            fillColor: zonaLat !== null ? '#f59e0b' : '#3b82f6',
            fillOpacity: 0.15,
            radius: rad
        }).addTo(map);

        pingSiswa();
        intervalId = setInterval(fetchLocationArray, 5000);
        startLastUpdateInterval();
    }

    // ================================================================
    // FETCH LOCATION (setiap 5 detik)
    // ================================================================
    function fetchLocationArray() {
        if (!currentTargetId) return;
        fetch(`<?= base_url('admin/tracking/getLocation/') ?>${currentTargetId}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    const newCount = res.data.length;

                    // Deteksi titik baru
                    if (newCount > lastPointCount && lastPointCount > 0) {
                        showNewPointToast();
                        playNewPointSound();
                        stopPingWaiting(); // Respons diterima, hentikan countdown
                    }
                    lastPointCount = newCount;
                    lastUpdateTime = Date.now();
                    updateLastUpdateBadge();

                    drawRoute(res.data);
                }
            })
            .catch(() => { /* Sinkronisasi berlangsung */ });
    }

    // ================================================================
    // DRAW ROUTE — Gambar marker & polyline di peta
    // ================================================================
    function drawRoute(locations) {
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        if (polylineLayer) map.removeLayer(polylineLayer);

        let latlngs = [];

        locations.forEach((loc, index) => {
            let pos      = [parseFloat(loc.lat), parseFloat(loc.lng)];
            let isLatest = (index === locations.length - 1);
            let color    = isLatest ? '#ef4444' : '#3b82f6';
            let size     = isLatest ? '16px' : '10px';

            latlngs.push(pos);

            let customIcon = window.L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color:${color}; width:${size}; height:${size}; border-radius:50%; border:2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.4);"></div>`,
                iconSize: [parseInt(size), parseInt(size)],
                iconAnchor: [parseInt(size) / 2, parseInt(size) / 2]
            });

            let newMarker = window.L.marker(pos, { icon: customIcon }).addTo(map)
                .bindPopup(`
                    <div style="text-align:center;">
                        <b style="color:${color}; font-size:12px; text-transform:uppercase;">TITIK ${loc.tipe}</b><br>
                        <span style="font-size:10px; color:#666;">Tercatat:<br>${loc.waktu}</span>
                    </div>
                `);

            if (isLatest) newMarker.openPopup();
            markers.push(newMarker);
        });

        if (latlngs.length > 1) {
            polylineLayer = window.L.polyline(latlngs, { color: '#ef4444', weight: 3, dashArray: '5, 8', lineJoin: 'round' }).addTo(map);
        }

        // Smart auto-frame hanya saat pertama kali load
        if (isFirstTrackLoad) {
            let groupLayers = [...markers];
            if (currentZoneCircle) groupLayers.push(currentZoneCircle);
            let group = new window.L.featureGroup(groupLayers);
            map.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 18 });
            isFirstTrackLoad = false;
        }
    }

    // ================================================================
    // PING SISWA — Kirim notifikasi FCM ke HP
    // ================================================================
    function pingSiswa() {
        if (!currentTargetId) return;

        const btnPing = document.getElementById('btn-ping');
        if (btnPing && btnPing.disabled) return; // Blokir jika masih cooldown

        // Mulai cooldown & waiting status
        startPingCooldown();
        startPingWaiting();

        if (typeof toastr !== 'undefined') toastr.info("Mengirim sinyal pelacakan ke HP...");

        fetch(`<?= base_url('admin/tracking/pingSiswa/') ?>${currentTargetId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfTokenName]: csrfTokenValue
            }
        })
        .then(res => {
            if (!res.ok) throw new Error("Gagal / Sesi Kedaluwarsa");
            return res.json();
        })
        .then(data => {
            if (data.csrf_token) csrfTokenValue = data.csrf_token;

            if (data.status === 200) {
                if (typeof toastr !== 'undefined') toastr.success(data.message);
            } else {
                // Jika gagal kirim (mis. FCM error), hentikan countdown tunggu respons
                stopPingWaiting();
                if (typeof toastr !== 'undefined') toastr.error(data.message);
            }
        })
        .catch(err => {
            stopPingWaiting();
            console.error(err);
            if (typeof toastr !== 'undefined') toastr.error("Sesi pengamanan berubah. Silakan Refresh (F5) halaman Anda.");
        });
    }
</script>
<?= $this->endSection() ?>