<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Siswa extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $kelas_filter = $this->request->getGet('kelas');

        // 1. Ambil list kelas untuk Dropdown Filter
        $list_kelas = $this->db->table('siswa')
            ->select('kelas')
            ->groupBy('kelas')
            ->orderBy('kelas', 'ASC')
            ->get()
            ->getResult();

        // 2. Konfigurasi Pagination
        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        // 3. Susun Query Builder
        $builder = $this->db->table('siswa');
        if (!empty($kelas_filter)) {
            $builder->where('kelas', $kelas_filter);
        }

        // 4. Hitung Total Data
        $total_data = $builder->countAllResults(false);

        // 5. Ambil Data dengan Limit & Offset
        $offset = ($page - 1) * $perPage;
        $siswa = $builder->orderBy('kelas', 'ASC')
            ->orderBy('nama_lengkap', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResult();

        $data = [
            'title'       => 'Manajemen Siswa',
            'siswa'       => $siswa,
            'list_kelas'  => $list_kelas,
            'kelas_aktif' => $kelas_filter,
            'pager_links' => $pager->makeLinks($page, $perPage, $total_data, 'default_full'),
            'total_data'  => $total_data,
            'page'        => $page,
            'perPage'     => $perPage
        ];

        return view('web/siswa', $data);
    }

    public function store()
    {
        $nis = $this->request->getPost('nis');

        $cek = $this->db->table('siswa')->where('nis', $nis)->countAllResults();
        if ($cek > 0) {
            return redirect()->back()->with('error', 'Gagal! NIS ' . $nis . ' sudah terdaftar.');
        }

        $this->db->table('siswa')->insert([
            'nis'          => $nis,
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'kelas'        => strtoupper($this->request->getPost('kelas')), // Pastikan uppercase di backend juga
            'created_at'   => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update($id)
    {
        $nis = $this->request->getPost('nis');

        // Cek jika NIS diubah ke NIS yang sudah milik orang lain
        $cek = $this->db->table('siswa')->where('nis', $nis)->where('id !=', $id)->countAllResults();
        if ($cek > 0) {
            return redirect()->back()->with('error', 'Gagal! NIS tersebut sudah digunakan siswa lain.');
        }

        $this->db->table('siswa')->where('id', $id)->update([
            'nis'          => $nis,
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'kelas'        => strtoupper($this->request->getPost('kelas')),
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function reset_device($id)
    {
        $this->db->table('siswa')->where('id', $id)->update(['device_id' => null]);
        return redirect()->back()->with('success', 'Device ID berhasil direset.');
    }

    public function unblock($id)
    {
        $this->db->table('siswa')->where('id', $id)->update(['is_blocked' => 0, 'fraud_count' => 0]);
        return redirect()->back()->with('success', 'Status blokir dibuka & Fraud Count direset.');
    }

    // --- FITUR EXPORT EXCEL ---
    public function export()
    {
        $kelas_filter = $this->request->getGet('kelas');

        $builder = $this->db->table('siswa');
        if (!empty($kelas_filter)) {
            $builder->where('kelas', $kelas_filter);
        }
        $siswa = $builder->orderBy('kelas', 'ASC')->orderBy('nama_lengkap', 'ASC')->get()->getResult();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];

        $sheet->setCellValue('A1', 'DAFTAR SISWA SMKN 1 TGB');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Tanggal Ekspor: ' . date('d-m-Y H:i'));
        $sheet->mergeCells('A2:D2');

        $sheet->setCellValue('A4', 'NO');
        $sheet->setCellValue('B4', 'NIS');
        $sheet->setCellValue('C4', 'NAMA LENGKAP');
        $sheet->setCellValue('D4', 'KELAS');

        $sheet->getStyle('A4:D4')->applyFromArray($styleHeader);

        $row = 5;
        $no = 1;
        foreach ($siswa as $s) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValueExplicit('B' . $row, $s->nis, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $s->nama_lengkap);
            $sheet->setCellValue('D' . $row, $s->kelas);

            $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row++;
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Data_Siswa_' . ($kelas_filter ?: 'Semua') . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // --- FITUR IMPORT EXCEL ---
    public function download_template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'TEMPLATE IMPORT SISWA');
        $sheet->setCellValue('A2', 'Catatan: Jangan mengubah urutan kolom. Mulai isi data dari baris ke-4.');

        $sheet->setCellValue('A4', 'NIS');
        $sheet->setCellValue('B4', 'NAMA LENGKAP');
        $sheet->setCellValue('C4', 'KELAS');

        $sheet->getStyle('A4:C4')->getFont()->setBold(true);
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Template_Import_Siswa.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function import()
    {
        $file = $this->request->getFile('file_excel');

        if (!$file->isValid() || $file->getExtension() !== 'xlsx') {
            return redirect()->back()->with('error', 'Format file tidak valid. Gunakan file .xlsx');
        }

        $spreadsheet = IOFactory::load($file->getTempName());
        $dataSiswa = $spreadsheet->getActiveSheet()->toArray();

        $inserted = 0;
        $skipped = 0;

        foreach ($dataSiswa as $index => $row) {
            if ($index < 4) continue; // Lewati baris 1 sampai 4 (Header)

            $nis = isset($row[0]) ? trim($row[0]) : '';
            $nama = isset($row[1]) ? trim($row[1]) : '';
            $kelas = isset($row[2]) ? strtoupper(trim($row[2])) : '';

            if (empty($nis) || empty($nama)) continue;

            $cek = $this->db->table('siswa')->where('nis', $nis)->countAllResults();
            if ($cek > 0) {
                $skipped++;
                continue;
            }

            $this->db->table('siswa')->insert([
                'nis'          => $nis,
                'nama_lengkap' => $nama,
                'kelas'        => $kelas,
                'created_at'   => date('Y-m-d H:i:s')
            ]);
            $inserted++;
        }

        return redirect()->to('/admin/siswa')->with('success', "$inserted data berhasil diimport. ($skipped dilewati karena duplikat).");
    }
}
