<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Siswa extends Controller
{
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $kelas_filter = $this->request->getGet('kelas');

        $list_kelas = $this->db->table('kelas')
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResultArray();

        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $builder = $this->db->table('siswa')
            ->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelas_filter)) {
            $builder->where('siswa.kelas_id', $kelas_filter);
        }

        $total_data = $builder->countAllResults(false);
        $offset = ($page - 1) * $perPage;

        $siswa = $builder->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->get($perPage, $offset)->getResultArray();

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
        $foto = $this->request->getFile('foto');
        $namaFoto = null;

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $namaFoto = $foto->getRandomName();
            $foto->move('uploads/siswa', $namaFoto);
        }

        $nis = $this->request->getPost('nis');

        $this->db->table('siswa')->insert([
            'nis'          => $nis,
            'nama_siswa'   => $this->request->getPost('nama_siswa'),
            'kelas_id'     => $this->request->getPost('kelas_id'),
            'password'     => password_hash($nis, PASSWORD_BCRYPT),
            'foto_profil'  => $namaFoto,
            'created_at'   => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(string $id)
    {
        $siswaLama = $this->db->table('siswa')->where('id_siswa', $id)->get()->getRow();

        $foto = $this->request->getFile('foto');
        $namaFoto = $siswaLama->foto_profil;

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $namaFoto = $foto->getRandomName();
            $foto->move('uploads/siswa', $namaFoto);

            if (!empty($siswaLama->foto_profil) && file_exists('uploads/siswa/' . $siswaLama->foto_profil)) {
                unlink('uploads/siswa/' . $siswaLama->foto_profil);
            }
        }

        $this->db->table('siswa')->where('id_siswa', $id)->update([
            'nis'          => $this->request->getPost('nis'),
            'nama_siswa'   => $this->request->getPost('nama_siswa'),
            'kelas_id'     => $this->request->getPost('kelas_id'),
            'foto_profil'  => $namaFoto,
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $siswa = $this->db->table('siswa')->where('id_siswa', $id)->get()->getRow();
        if ($siswa) {
            if (!empty($siswa->foto_profil) && file_exists('uploads/siswa/' . $siswa->foto_profil)) {
                unlink('uploads/siswa/' . $siswa->foto_profil);
            }
            $this->db->table('siswa')->where('id_siswa', $id)->delete();
        }

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa beserta foto berhasil dihapus.');
    }

    public function reset_device(string $id)
    {
        $this->db->table('siswa')->where('id_siswa', $id)->update([
            'device_id' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return redirect()->to('/admin/siswa')->with('success', 'Perangkat berhasil di-reset.');
    }

    public function unblock(string $id)
    {
        $this->db->table('siswa')->where('id_siswa', $id)->update([
            'is_blocked'  => 0,
            'fraud_count' => 0,
            'updated_at'  => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Akun siswa berhasil di-unblock dan fraud count di-reset.');
    }

    public function download_template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA SISWA');
        $sheet->setCellValue('A3', 'NIS');
        $sheet->setCellValue('B3', 'NAMA LENGKAP');
        $sheet->setCellValue('C3', 'ID KELAS (Lihat di menu Manajemen Kelas)');

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Siswa.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function export()
    {
        $kelas_id = $this->request->getGet('kelas');
        $builder = $this->db->table('siswa')
            ->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelas_id)) {
            $builder->where('siswa.kelas_id', $kelas_id);
        }

        $dataSiswa = $builder->orderBy('kelas.nama_kelas', 'ASC')->orderBy('siswa.nama_siswa', 'ASC')->get()->getResultArray();

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
            $sheet->setCellValue('C' . $row, $siswa['nama_siswa']);
            $sheet->setCellValue('D' . $row, $siswa['nama_kelas']);
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
            if ($index < 4) continue;

            $nis = isset($row[0]) ? trim($row[0]) : '';
            $nama = isset($row[1]) ? trim($row[1]) : '';
            $kelas_id = isset($row[2]) ? (int)trim($row[2]) : 0;

            if (empty($nis) || empty($nama) || empty($kelas_id)) continue;

            $cek = $this->db->table('siswa')->where('nis', $nis)->countAllResults();
            if ($cek > 0) {
                $skipped++;
                continue;
            }

            $this->db->table('siswa')->insert([
                'nis'          => $nis,
                'nama_siswa'   => $nama,
                'kelas_id'     => $kelas_id,
                'password'     => password_hash($nis, PASSWORD_BCRYPT),
                'created_at'   => date('Y-m-d H:i:s')
            ]);
            $inserted++;
        }

        return redirect()->to('/admin/siswa')->with('success', "Berhasil import $inserted data baru. $skipped data dilewati (NIS duplikat).");
    }

    // FASE 3: Fungsi Detail Profil 360
    public function detail(string $id_siswa)
    {
        $siswa = $this->db->table('siswa')
            ->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('id_siswa', $id_siswa)
            ->get()->getRowArray();

        if (!$siswa) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan.');
        }

        // Ambil riwayat absen 10 hari terakhir
        $absensi = $this->db->table('absensi')
            ->where('siswa_id', $id_siswa)
            ->orderBy('tanggal', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // Ambil riwayat fraud
        $logFraud = $this->db->table('log_fraud')
            ->where('siswa_id', $id_siswa)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        // Statistik Total
        $statHadir = $this->db->table('absensi')->where(['siswa_id' => $id_siswa, 'status' => 'Hadir'])->countAllResults();
        $statTelat = $this->db->table('absensi')->where(['siswa_id' => $id_siswa, 'status' => 'Terlambat'])->countAllResults();
        $statSakit = $this->db->table('absensi')->where(['siswa_id' => $id_siswa, 'status' => 'Sakit'])->countAllResults();
        $statIzin  = $this->db->table('absensi')->where(['siswa_id' => $id_siswa, 'status' => 'Izin'])->countAllResults();
        $statAlpa  = $this->db->table('absensi')->where(['siswa_id' => $id_siswa, 'status' => 'Alpa'])->countAllResults();

        $data = [
            'title'    => 'Profil 360: ' . $siswa['nama_siswa'],
            'siswa'    => $siswa,
            'absensi'  => $absensi,
            'logFraud' => $logFraud,
            'stats'    => [
                'hadir'     => $statHadir,
                'terlambat' => $statTelat,
                'sakit'     => $statSakit,
                'izin'      => $statIzin,
                'alpa'      => $statAlpa,
                'total'     => $statHadir + $statTelat + $statSakit + $statIzin + $statAlpa
            ]
        ];

        return view('web/siswa_detail', $data);
    }
}
