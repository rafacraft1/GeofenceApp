<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\AbsensiModel;
use App\Models\KelasModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends BaseController
{
    protected AbsensiModel $absensiModel;
    protected KelasModel $kelasModel;

    public function __construct()
    {
        $this->absensiModel = new AbsensiModel();
        $this->kelasModel   = new KelasModel();
    }

    /**
     * REFAKTORISASI SCD (Slowly Changing Dimension):
     * Query ini memastikan riwayat kelas tidak bergeser walaupun siswa sudah naik kelas.
     */
    private function getRekapData(string $bulanMulai, string $bulanSelesai, string $tahun, string $kelasId): array
    {
        // 1. Ambil Data Agregasi Kehadiran langsung dari tabel Absensi (Group by Siswa, Kelas Historis, dan Status)
        // Perhatikan: JOIN tabel kelas sekarang dikunci ke absensi.kelas_id
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

        // 2. Mapping Data (O(1) Lookups)
        $rekapMap = [];

        foreach ($dataAbsen as $row) {
            $key = $row['siswa_id'] . '_' . $row['kelas_id']; // Kunci unik gabungan siswa dan kelas historisnya

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

        // 3. Kalkulasi Persentase Akhir
        $hasilAkhir = [];
        foreach ($rekapMap as $data) {
            $hadir      = $data['Hadir'];
            $terlambat  = $data['Terlambat'];
            $dispensasi = $data['Dispensasi'];
            $sakit      = $data['Sakit'];
            $izin       = $data['Izin'];
            $alpa       = $data['Alpa'];

            $totalKehadiran = $hadir + $terlambat + $dispensasi;
            $totalHari      = $totalKehadiran + $sakit + $izin + $alpa;
            $persentase     = ($totalHari > 0) ? round(($totalKehadiran / $totalHari) * 100, 2) : 0;

            $data['TotalHari']  = $totalHari;
            $data['Persentase'] = $persentase;

            $hasilAkhir[] = $data;
        }

        // 4. Sortir secara alfabetis berdasarkan nama kelas lalu nama siswa
        usort($hasilAkhir, function ($a, $b) {
            if ($a['nama_kelas'] === $b['nama_kelas']) {
                return $a['nama_siswa'] <=> $b['nama_siswa'];
            }
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

        $kelasId = $isWaliKelas ? $kelasSession : ($this->request->getGet('kelas') ?? '');

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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'REKAPITULASI KEHADIRAN SISWA');
        $sheet->setCellValue('A2', "PERIODE: Bulan $bulanMulai s/d $bulanSelesai Tahun $tahun");
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);

        $headers = ['No', 'NIS', 'Nama Lengkap', 'Kelas', 'Hadir', 'Dispensasi', 'Terlambat', 'Sakit', 'Izin', 'Alpa', 'Total Hari', 'Persentase'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $col++;
        }

        $row = 5;
        $no = 1;
        foreach ($rekapData as $data) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValueExplicit('B' . $row, $data['nis'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $data['nama_siswa']);
            $sheet->setCellValue('D' . $row, $data['nama_kelas']);
            $sheet->setCellValue('E' . $row, $data['Hadir']);
            $sheet->setCellValue('F' . $row, $data['Dispensasi']);
            $sheet->setCellValue('G' . $row, $data['Terlambat']);
            $sheet->setCellValue('H' . $row, $data['Sakit']);
            $sheet->setCellValue('I' . $row, $data['Izin']);
            $sheet->setCellValue('J' . $row, $data['Alpa']);
            $sheet->setCellValue('K' . $row, $data['TotalHari']);
            $sheet->setCellValue('L' . $row, $data['Persentase'] . '%');
            $row++;
        }

        foreach (range('A', 'L') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Rekap_Absensi_' . $tahun . '_' . $bulanMulai . '-' . $bulanSelesai . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
