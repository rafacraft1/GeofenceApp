<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends BaseController
{
    /**
     * Fungsi Private untuk mengambil dan merangkum data (Mencegah N+1 Query Problem)
     */
    private function getRekapData(string $bulan, string $tahun, string $kelas_id): array
    {
        // 1. Ambil daftar siswa (difilter berdasarkan kelas jika ada)
        $builderSiswa = $this->db->table('siswa')
            ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelas_id)) {
            $builderSiswa->where('siswa.kelas_id', $kelas_id);
        }

        $listSiswa = $builderSiswa->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->get()->getResultArray();

        // 2. Ambil data agregat absensi pada bulan & tahun terpilih
        $builderAbsen = $this->db->table('absensi')
            ->select('siswa_id, status, COUNT(id_absensi) as total')
            ->where('MONTH(tanggal)', $bulan)
            ->where('YEAR(tanggal)', $tahun);

        if (!empty($kelas_id)) {
            $builderAbsen->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
                ->where('siswa.kelas_id', $kelas_id);
        }

        $dataAbsen = $builderAbsen->groupBy('siswa_id, status')->get()->getResultArray();

        // 3. Mapping data absensi ke masing-masing siswa (Proses O(N) yang sangat cepat)
        $rekap = [];
        foreach ($listSiswa as $s) {
            $rekap[$s['id_siswa']] = [
                'nis'        => $s['nis'],
                'nama_siswa' => $s['nama_siswa'],
                'nama_kelas' => $s['nama_kelas'] ?? '-',
                'Hadir'      => 0,
                'Terlambat'  => 0,
                'Sakit'      => 0,
                'Izin'       => 0,
                'Alpa'       => 0,
            ];
        }

        foreach ($dataAbsen as $ab) {
            $sId = $ab['siswa_id'];
            $status = $ab['status'];
            if (isset($rekap[$sId]) && isset($rekap[$sId][$status])) {
                $rekap[$sId][$status] = (int) $ab['total'];
            }
        }

        return $rekap;
    }

    public function index()
    {
        $bulan    = (string) ($this->request->getGet('bulan') ?? date('m'));
        $tahun    = (string) ($this->request->getGet('tahun') ?? date('Y'));
        $kelas_id = (string) ($this->request->getGet('kelas') ?? '');

        $list_kelas = $this->db->table('kelas')->orderBy('nama_kelas', 'ASC')->get()->getResultArray();

        $rekapData = $this->getRekapData($bulan, $tahun, $kelas_id);

        $data = [
            'title'      => 'Rekapitulasi Kehadiran',
            'list_kelas' => $list_kelas,
            'bulan'      => $bulan,
            'tahun'      => $tahun,
            'kelas_id'   => $kelas_id,
            'rekapData'  => $rekapData
        ];

        return view('web/laporan/index', $data);
    }

    public function export()
    {
        $bulan    = (string) $this->request->getGet('bulan');
        $tahun    = (string) $this->request->getGet('tahun');
        $kelas_id = (string) $this->request->getGet('kelas');

        if (empty($bulan) || empty($tahun)) {
            return redirect()->back()->with('error', 'Parameter bulan dan tahun tidak valid.');
        }

        $rekapData = $this->getRekapData($bulan, $tahun, $kelas_id);

        $namaKelasStr = 'Semua_Kelas';
        if (!empty($kelas_id)) {
            $kelasInfo = $this->db->table('kelas')->where('id_kelas', $kelas_id)->get()->getRowArray();
            if ($kelasInfo) $namaKelasStr = str_replace(' ', '_', (string) $kelasInfo['nama_kelas']);
        }

        $namaBulan = date('F', mktime(0, 0, 0, (int)$bulan, 10));
        $fileName  = "Rekap_Absensi_{$namaKelasStr}_{$namaBulan}_{$tahun}.xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Judul Laporan
        $sheet->setCellValue('A1', 'REKAPITULASI KEHADIRAN SISWA');
        $sheet->setCellValue('A2', 'Bulan: ' . $namaBulan . ' ' . $tahun);
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

        // Header Tabel
        $headers = ['No', 'NIS', 'Nama Lengkap', 'Kelas', 'Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpa'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }

        // Isi Data
        $row = 5;
        $no = 1;
        foreach ($rekapData as $data) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $data['nis']);
            $sheet->setCellValue('C' . $row, $data['nama_siswa']);
            $sheet->setCellValue('D' . $row, $data['nama_kelas']);
            $sheet->setCellValue('E' . $row, $data['Hadir']);
            $sheet->setCellValue('F' . $row, $data['Terlambat']);
            $sheet->setCellValue('G' . $row, $data['Sakit']);
            $sheet->setCellValue('H' . $row, $data['Izin']);
            $sheet->setCellValue('I' . $row, $data['Alpa']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
