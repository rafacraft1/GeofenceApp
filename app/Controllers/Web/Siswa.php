<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Siswa extends Controller
{
    // 1. Tipe data $db sudah dideklarasikan secara spesifik
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $kelas_filter = $this->request->getGet('kelas');

        // Ambil list kelas untuk Dropdown Filter
        $list_kelas = $this->db->table('siswa')
            ->select('kelas')
            ->groupBy('kelas')
            ->orderBy('kelas', 'ASC')
            ->get()
            ->getResult();

        // Konfigurasi Pagination
        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        // Susun Query Builder
        $builder = $this->db->table('siswa');
        if (!empty($kelas_filter)) {
            $builder->where('kelas', $kelas_filter);
        }

        // Hitung Total Data
        $total_data = $builder->countAllResults(false);

        // Ambil Data dengan Limit & Offset
        $offset = ($page - 1) * $perPage;
        $siswa = $builder->orderBy('kelas', 'ASC')
            ->orderBy('nama_lengkap', 'ASC')
            ->get($perPage, $offset)->getResult();

        $data = [
            'title'       => 'Daftar Siswa',
            'siswa'       => $siswa,
            'list_kelas'  => $list_kelas,
            'kelas_aktif' => $kelas_filter,
            'pager_links' => $pager->makeLinks($page, $perPage, $total_data, 'default_full'),
            'page'        => $page,
            'perPage'     => $perPage,
            'total_data'  => $total_data
        ];

        return view('web/siswa', $data);
    }

    public function store()
    {
        $this->db->table('siswa')->insert([
            'nis'          => $this->request->getPost('nis'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'kelas'        => strtoupper($this->request->getPost('kelas')),
            'created_at'   => date('Y-m-d H:i:s')
        ]);
        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    // 2. Menambahkan 'string' pada parameter $id
    public function update(string $id)
    {
        $this->db->table('siswa')->where('id', $id)->update([
            'nis'          => $this->request->getPost('nis'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'kelas'        => strtoupper($this->request->getPost('kelas')),
        ]);
        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil diperbarui.');
    }

    // 3. Menambahkan 'string' pada parameter $id
    public function reset_device(string $id)
    {
        $this->db->table('siswa')->where('id', $id)->update([
            'device_id' => null,
            'api_token' => null
        ]);
        return redirect()->to('/admin/siswa')->with('success', 'Perangkat berhasil di-reset.');
    }

    // 4. Menambahkan 'string' pada parameter $id
    public function unblock(string $id)
    {
        $this->db->table('siswa')->where('id', $id)->update([
            'is_blocked'  => 0,
            'fraud_count' => 0
        ]);
        return redirect()->to('/admin/siswa')->with('success', 'Akun siswa berhasil di-unblock.');
    }

    public function download_template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA SISWA');
        $sheet->setCellValue('A3', 'NIS');
        $sheet->setCellValue('B3', 'NAMA LENGKAP');
        $sheet->setCellValue('C3', 'KELAS');

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Siswa.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function export()
    {
        $kelas = $this->request->getGet('kelas');
        $builder = $this->db->table('siswa');

        if (!empty($kelas)) {
            $builder->where('kelas', $kelas);
        }

        $dataSiswa = $builder->orderBy('kelas', 'ASC')->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Nama Lengkap');
        $sheet->setCellValue('D1', 'Kelas');

        $row = 2;
        foreach ($dataSiswa as $index => $siswa) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $siswa['nis']);
            $sheet->setCellValue('C' . $row, $siswa['nama_lengkap']);
            $sheet->setCellValue('D' . $row, $siswa['kelas']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Data_Siswa.xlsx"');
        header('Cache-Control: max-age=0');
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

        return redirect()->to('/admin/siswa')->with('success', "Berhasil import $inserted data baru. $skipped data dilewati (NIS duplikat).");
    }
}
