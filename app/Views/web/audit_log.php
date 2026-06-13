<?php

/**
 * @var string $title
 * @var array $logs
 * @var string $pager_links
 * @var int $total_data
 * @var int $page
 * @var int $perPage
 * @var string $modul_aktif
 * @var string $action_aktif
 * @var string $start_date
 * @var string $end_date
 * @var string $search
 */
?>
<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><?= esc($title) ?></h1>
        <p class="text-sm text-slate-500 mt-1">Pantau seluruh riwayat perubahan data pada sistem.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
    <div class="p-5 border-b border-slate-100 bg-slate-50/50 rounded-t-xl">
        <form action="" method="get" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-search text-slate-400"></i>
                    </div>
                    <input type="text" name="search" value="<?= esc($search) ?>" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Cari nama admin atau IP...">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Aksi</label>
                <select name="action" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Semua Aksi</option>
                    <option value="INSERT" <?= $action_aktif === 'INSERT' ? 'selected' : '' ?>>INSERT</option>
                    <option value="UPDATE" <?= $action_aktif === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
                    <option value="DELETE" <?= $action_aktif === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Mulai</label>
                <input type="date" name="start_date" value="<?= esc($start_date) ?>" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Akhir</label>
                <div class="flex gap-2">
                    <input type="date" name="end_date" value="<?= esc($end_date) ?>" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm px-4 py-2.5 transition-colors focus:ring-4 focus:ring-blue-200">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    <?php if ($search || $action_aktif || $start_date || $end_date): ?>
                        <a href="<?= base_url('admin/audit-log') ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium rounded-lg text-sm px-4 py-2.5 transition-colors">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold">Waktu & IP</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Pengguna</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Modul Target</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Aksi</th>
                    <th scope="col" class="px-6 py-4 font-semibold text-right">Detail Forensik</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <i class="fa-solid fa-shield-halved text-4xl mb-3 text-slate-300 block"></i>
                            Tidak ada rekaman log yang ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="bg-white border-b border-slate-100 hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800"><?= date('d M Y', strtotime((string)$log['created_at'])) ?></div>
                                <div class="text-xs text-slate-500"><?= date('H:i:s', strtotime((string)$log['created_at'])) ?> • <?= esc($log['ip_address']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800"><?= esc($log['user_name'] ?? 'Sistem / Guest') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-slate-100 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded-md border border-slate-200 uppercase tracking-wider">
                                    <?= esc($log['module']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($log['action'] === 'INSERT'): ?>
                                    <span class="bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold px-2.5 py-1 rounded-md flex items-center w-max gap-1.5"><i class="fa-solid fa-plus w-3"></i> INSERT</span>
                                <?php elseif ($log['action'] === 'UPDATE'): ?>
                                    <span class="bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold px-2.5 py-1 rounded-md flex items-center w-max gap-1.5"><i class="fa-solid fa-pen text-[10px] w-3"></i> UPDATE</span>
                                <?php elseif ($log['action'] === 'DELETE'): ?>
                                    <span class="bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold px-2.5 py-1 rounded-md flex items-center w-max gap-1.5"><i class="fa-solid fa-trash text-[10px] w-3"></i> DELETE</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-700 border border-gray-200 text-xs font-bold px-2.5 py-1 rounded-md flex items-center w-max gap-1.5"><i class="fa-solid fa-bolt text-[10px] w-3"></i> <?= esc($log['action']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php
                                $old = htmlspecialchars((string)($log['old_values'] ?? '{}'), ENT_QUOTES, 'UTF-8');
                                $new = htmlspecialchars((string)($log['new_values'] ?? '{}'), ENT_QUOTES, 'UTF-8');
                                ?>
                                <button onclick="openForensicModal('<?= $old ?>', '<?= $new ?>', '<?= esc($log['action']) ?>', '<?= esc($log['module']) ?>')" class="text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border border-blue-100">
                                    Lihat Data JSON
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_data > $perPage): ?>
        <div class="p-5 border-t border-slate-100 bg-slate-50/50 rounded-b-xl flex items-center justify-between">
            <div class="text-sm text-slate-500">
                Menampilkan <span class="font-medium text-slate-800"><?= count($logs) ?></span> dari <span class="font-medium text-slate-800"><?= $total_data ?></span> log.
            </div>
            <div><?= $pager_links ?></div>
        </div>
    <?php endif; ?>
</div>

<div id="forensicModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0">
    <div class="relative w-full max-w-4xl max-h-full transition-all transform scale-95" id="forensicModalContent">
        <div class="relative bg-white rounded-xl shadow-2xl">
            <div class="flex items-start justify-between p-5 border-b border-slate-100 rounded-t-xl bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 tracking-tight" id="modalTitle">Komparasi Data Forensik</h3>
                    <p class="text-xs text-slate-500 mt-1 font-mono" id="modalSubtitle">Module</p>
                </div>
                <button type="button" onclick="closeForensicModal()" class="text-slate-400 bg-transparent hover:bg-slate-200 hover:text-slate-900 rounded-lg text-sm p-2 ml-auto inline-flex items-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Data Lama / Dihapus</span>
                    </div>
                    <div class="bg-slate-900 rounded-lg border border-slate-800 overflow-hidden flex-grow shadow-inner">
                        <pre class="p-4 text-xs font-mono text-rose-300 overflow-x-auto h-full max-h-[50vh] scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent" id="oldDataPre"></pre>
                    </div>
                </div>

                <div class="flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Data Baru / Dimasukkan</span>
                    </div>
                    <div class="bg-slate-900 rounded-lg border border-slate-800 overflow-hidden flex-grow shadow-inner">
                        <pre class="p-4 text-xs font-mono text-emerald-300 overflow-x-auto h-full max-h-[50vh] scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent" id="newDataPre"></pre>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end p-5 border-t border-slate-100 rounded-b-xl bg-white">
                <button onclick="closeForensicModal()" type="button" class="text-slate-700 bg-white border border-slate-300 focus:ring-4 focus:outline-none focus:ring-slate-100 hover:bg-slate-50 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">Tutup Jendela</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openForensicModal(oldJson, newJson, action, module) {
        const modal = document.getElementById('forensicModal');
        const modalContent = document.getElementById('forensicModalContent');

        document.getElementById('modalTitle').textContent = `Inspeksi Aksi: ${action}`;
        document.getElementById('modalSubtitle').textContent = `Modul: ${module.toUpperCase()}`;

        try {
            document.getElementById('oldDataPre').textContent = JSON.stringify(JSON.parse(oldJson), null, 4);
        } catch (e) {
            document.getElementById('oldDataPre').textContent = 'Tidak ada data lama.';
        }
        try {
            document.getElementById('newDataPre').textContent = JSON.stringify(JSON.parse(newJson), null, 4);
        } catch (e) {
            document.getElementById('newDataPre').textContent = 'Tidak ada data baru.';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
    }

    function closeForensicModal() {
        const modal = document.getElementById('forensicModal');
        const modalContent = document.getElementById('forensicModalContent');

        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
</script>
<?= $this->endSection() ?>