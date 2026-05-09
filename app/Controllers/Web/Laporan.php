<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends BaseController
{
    private function getRekapData(string $bulanMulai, string $bulanSelesai, string $tahun, string $kelasId): array
    {
        $builderSiswa = $this->db->table('siswa')
            ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasId)) {
            $builderSiswa->where('siswa.kelas_id', $kelasId);
        }

        $listSiswa = $builderSiswa->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->get()->getResultArray();

        $builderAbsen = $this->db->table('absensi')
            ->select('siswa_id, status, COUNT(id_absensi) as total')
            ->where('MONTH(tanggal) >=', $bulanMulai)
            ->where('MONTH(tanggal) <=', $bulanSelesai)
            ->where('YEAR(tanggal)', $tahun);

        if (!empty($kelasId)) {
            $builderAbsen->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
                ->where('siswa.kelas_id', $kelasId);
        }

        $dataAbsen = $builderAbsen->groupBy('siswa_id, status')->get()->getResultArray();

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
                'TotalHari'  => 0,
                'Persentase' => 0,
            ];
        }

        foreach ($dataAbsen as $ab) {
            $sId = $ab['siswa_id'];
            $status = $ab['status'];
            if (isset($rekap[$sId]) && isset($rekap[$sId][$status])) {
                $rekap[$sId][$status] = (int) $ab['total'];
            }
        }

        foreach ($rekap as $id => $data) {
            $hadirAktif = $data['Hadir'] + $data['Terlambat'];
            $absenAktif = $data['Sakit'] + $data['Izin'] + $data['Alpa'];
            $totalHari  = $hadirAktif + $absenAktif;

            $rekap[$id]['TotalHari']  = $totalHari;
            $rekap[$id]['Persentase'] = $totalHari > 0 ? round(($hadirAktif / $totalHari) * 100) : 0;
        }

        return $rekap;
    }

    public function index()
    {
        $bulanMulai   = (string) ($this->request->getGet('bulan_mulai') ?? date('m'));
        $bulanSelesai = (string) ($this->request->getGet('bulan_selesai') ?? $bulanMulai);
        $tahun        = (string) ($this->request->getGet('tahun') ?? date('Y'));
        $kelasId      = (string) ($this->request->getGet('kelas') ?? '');

        $listKelas = $this->db->table('kelas')->orderBy('nama_kelas', 'ASC')->get()->getResultArray();
        $rekapData = $this->getRekapData($bulanMulai, $bulanSelesai, $tahun, $kelasId);

        $data = [
            'title'        => 'Rekapitulasi Kehadiran',
            'listKelas'    => $listKelas,
            'bulanMulai'   => $bulanMulai,
            'bulanSelesai' => $bulanSelesai,
            'tahun'        => $tahun,
            'kelasId'      => $kelasId,
            'rekapData'    => array_values($rekapData)
        ];

        return view('web/laporan/index', $data);
    }

    public function export()
    {
        $bulanMulai   = (string) $this->request->getGet('bulan_mulai');
        $bulanSelesai = (string) $this->request->getGet('bulan_selesai');
        $tahun        = (string) $this->request->getGet('tahun');
        $kelasId      = (string) $this->request->getGet('kelas');

        $rekapData = $this->getRekapData($bulanMulai, $bulanSelesai, $tahun, $kelasId);

        $namaKelasStr = 'Semua_Kelas';
        if (!empty($kelasId)) {
            $kelasInfo = $this->db->table('kelas')->where('id_kelas', $kelasId)->get()->getRowArray();
            if ($kelasInfo) $namaKelasStr = str_replace(' ', '_', (string) $kelasInfo['nama_kelas']);
        }

        $namaBulanMulai   = date('F', mktime(0, 0, 0, (int)$bulanMulai, 10));
        $namaBulanSelesai = date('F', mktime(0, 0, 0, (int)$bulanSelesai, 10));

        $periodeStr = ($bulanMulai === $bulanSelesai) ? $namaBulanMulai : "{$namaBulanMulai}_sd_{$namaBulanSelesai}";
        $fileName   = "Rekap_Absensi_{$namaKelasStr}_{$periodeStr}_{$tahun}.xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'REKAPITULASI KEHADIRAN SISWA');
        $sheet->setCellValue('A2', "Periode: $namaBulanMulai - $namaBulanSelesai $tahun");
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');

        $headers = ['No', 'NIS', 'Nama Lengkap', 'Kelas', 'Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpa', 'Total Hari', '% Kehadiran'];
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
            $sheet->setCellValue('F' . $row, $data['Terlambat']);
            $sheet->setCellValue('G' . $row, $data['Sakit']);
            $sheet->setCellValue('H' . $row, $data['Izin']);
            $sheet->setCellValue('I' . $row, $data['Alpa']);
            $sheet->setCellValue('J' . $row, $data['TotalHari']);
            $sheet->setCellValue('K' . $row, $data['Persentase'] . '%');
            $row++;
        }

        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
