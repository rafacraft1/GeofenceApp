<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\AbsensiModel;
use App\Models\KelasModel;
use App\Services\ExportService;

class Laporan extends BaseController
{
    protected AbsensiModel $absensiModel;
    protected KelasModel $kelasModel;
    protected ExportService $exportService;

    public function __construct()
    {
        $this->absensiModel  = new AbsensiModel();
        $this->kelasModel    = new KelasModel();
        $this->exportService = new ExportService();
    }

    private function getRekapData(string $bulanMulai, string $bulanSelesai, string $tahun, string $kelasId): array
    {
        $this->absensiModel->select('absensi.siswa_id, absensi.kelas_id, absensi.status, COUNT(absensi.id_absensi) as total, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = absensi.kelas_id', 'left')
            ->where('MONTH(absensi.tanggal) >=', $bulanMulai)
            ->where('MONTH(absensi.tanggal) <=', $bulanSelesai)
            ->where('YEAR(absensi.tanggal)', $tahun);

        if (!empty($kelasId)) {
            $this->absensiModel->where('absensi.kelas_id', $kelasId);
        }

        $dataAbsen = $this->absensiModel->groupBy('absensi.siswa_id, absensi.kelas_id, absensi.status')->findAll();
        $rekapMap = [];

        foreach ($dataAbsen as $row) {
            $key = $row['siswa_id'] . '_' . $row['kelas_id'];

            if (!isset($rekapMap[$key])) {
                $rekapMap[$key] = [
                    'nis'        => $row['nis'],
                    'nama_siswa' => $row['nama_siswa'],
                    'nama_kelas' => $row['nama_kelas'] ?? '-',
                    'Hadir'      => 0,
                    'Dispensasi' => 0,
                    'Terlambat'  => 0,
                    'Sakit'      => 0,
                    'Izin'       => 0,
                    'Alpa'       => 0,
                ];
            }
            $rekapMap[$key][$row['status']] = (int) $row['total'];
        }

        $hasilAkhir = [];
        foreach ($rekapMap as $data) {
            $totalKehadiran = $data['Hadir'] + $data['Terlambat'] + $data['Dispensasi'];
            $totalHari      = $totalKehadiran + $data['Sakit'] + $data['Izin'] + $data['Alpa'];
            $persentase     = ($totalHari > 0) ? round(($totalKehadiran / $totalHari) * 100, 2) : 0;

            $data['TotalHari']  = $totalHari;
            $data['Persentase'] = $persentase;
            $hasilAkhir[] = $data;
        }

        usort($hasilAkhir, function ($a, $b) {
            if ($a['nama_kelas'] === $b['nama_kelas']) return $a['nama_siswa'] <=> $b['nama_siswa'];
            return $a['nama_kelas'] <=> $b['nama_kelas'];
        });

        return $hasilAkhir;
    }

    public function index()
    {
        $bulanMulai   = $this->request->getGet('bulan_mulai') ?? date('m');
        $bulanSelesai = $this->request->getGet('bulan_selesai') ?? date('m');
        $tahun        = $this->request->getGet('tahun') ?? date('Y');

        $isWaliKelas  = session()->get('is_wali_kelas');
        $kelasSession = session()->get('kelas_id');
        $kelasId      = $isWaliKelas ? $kelasSession : ($this->request->getGet('kelas') ?? '');

        $rekapData = $this->getRekapData($bulanMulai, $bulanSelesai, $tahun, (string)$kelasId);

        $listKelas = $isWaliKelas
            ? $this->kelasModel->where('id_kelas', $kelasSession)->findAll()
            : $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        $data = [
            'title'        => 'Laporan Kehadiran',
            'rekapData'    => $rekapData,
            'listKelas'    => $listKelas,
            'bulanMulai'   => $bulanMulai,
            'bulanSelesai' => $bulanSelesai,
            'tahun'        => $tahun,
            'kelasId'      => $kelasId
        ];

        return view('web/laporan/index', $data);
    }

    public function export()
    {
        $bulanMulai   = $this->request->getGet('bulan_mulai') ?? date('m');
        $bulanSelesai = $this->request->getGet('bulan_selesai') ?? date('m');
        $tahun        = $this->request->getGet('tahun') ?? date('Y');

        $kelasId = session()->get('is_wali_kelas') ? session()->get('kelas_id') : ($this->request->getGet('kelas') ?? '');

        $rekapData = $this->getRekapData($bulanMulai, $bulanSelesai, $tahun, (string)$kelasId);

        // Mendelegasikan tugas ke ExportService
        $this->exportService->exportLaporan($rekapData, $bulanMulai, $bulanSelesai, $tahun);
    }
}
