<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use App\Models\KelasModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends BaseController
{
    protected SiswaModel $siswaModel;
    protected AbsensiModel $absensiModel;
    protected KelasModel $kelasModel;

    public function __construct()
    {
        $this->siswaModel   = new SiswaModel();
        $this->absensiModel = new AbsensiModel();
        $this->kelasModel   = new KelasModel();
    }

    private function getRekapData(string $bulanMulai, string $bulanSelesai, string $tahun, string $kelasId): array
    {
        // 1. Ambil Data Siswa beserta Kelas
        $this->siswaModel->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasId)) {
            $this->siswaModel->where('siswa.kelas_id', $kelasId);
        }

        $listSiswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();

        // 2. Ambil Agregasi Data Absensi
        $this->absensiModel->select('absensi.siswa_id, absensi.status, COUNT(absensi.id_absensi) as total')
            ->where('MONTH(absensi.tanggal) >=', $bulanMulai)
            ->where('MONTH(absensi.tanggal) <=', $bulanSelesai)
            ->where('YEAR(absensi.tanggal)', $tahun);

        if (!empty($kelasId)) {
            $this->absensiModel->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
                ->where('siswa.kelas_id', $kelasId);
        }

        $dataAbsen = $this->absensiModel->groupBy('absensi.siswa_id, absensi.status')->findAll();

        // 3. Mapping Data Absensi ke Memori (Efisiensi O(1) Lookups)
        $absenMap = [];
        foreach ($dataAbsen as $row) {
            $absenMap[$row['siswa_id']][$row['status']] = (int) $row['total'];
        }

        // 4. Penggabungan Data Final
        $rekap = [];
        foreach ($listSiswa as $siswa) {
            $id         = $siswa['id_siswa'];
            $hadir      = $absenMap[$id]['Hadir'] ?? 0;
            $terlambat  = $absenMap[$id]['Terlambat'] ?? 0;
            $dispensasi = $absenMap[$id]['Dispensasi'] ?? 0;
            $sakit      = $absenMap[$id]['Sakit'] ?? 0;
            $izin       = $absenMap[$id]['Izin'] ?? 0;
            $alpa       = $absenMap[$id]['Alpa'] ?? 0;

            $totalKehadiran = $hadir + $terlambat + $dispensasi;
            $totalHari      = $totalKehadiran + $sakit + $izin + $alpa;
            $persentase     = ($totalHari > 0) ? round(($totalKehadiran / $totalHari) * 100, 2) : 0;

            $rekap[] = [
                'nis'        => $siswa['nis'],
                'nama_siswa' => $siswa['nama_siswa'],
                'nama_kelas' => $siswa['nama_kelas'] ?? '-',
                'Hadir'      => $hadir,
                'Dispensasi' => $dispensasi,
                'Terlambat'  => $terlambat,
                'Sakit'      => $sakit,
                'Izin'       => $izin,
                'Alpa'       => $alpa,
                'TotalHari'  => $totalHari,
                'Persentase' => $persentase
            ];
        }

        return $rekap;
    }

    public function index()
    {
        $bulanMulai   = $this->request->getGet('bulan_mulai') ?? date('m');
        $bulanSelesai = $this->request->getGet('bulan_selesai') ?? date('m');
        $tahun        = $this->request->getGet('tahun') ?? date('Y');

        $isWaliKelas  = session()->get('is_wali_kelas');
        $kelasSession = session()->get('kelas_id');

        // Penetapan paksa ID Kelas jika Wali Kelas
        $kelasId = $isWaliKelas ? $kelasSession : ($this->request->getGet('kelas') ?? '');

        $rekapData = $this->getRekapData($bulanMulai, $bulanSelesai, $tahun, (string)$kelasId);

        // List dropdown disesuaikan otoritas
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

        // Penetapan paksa ID Kelas untuk file Excel
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
