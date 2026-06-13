<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Files\UploadedFile;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\AbsensiModel;
use App\Models\LogFraudModel;
use App\Models\ZonaModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class Siswa extends BaseController
{
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected AbsensiModel $absensiModel;
    protected LogFraudModel $logFraudModel;
    protected ZonaModel $zonaModel;

    public function __construct()
    {
        $this->siswaModel    = new SiswaModel();
        $this->kelasModel    = new KelasModel();
        $this->absensiModel  = new AbsensiModel();
        $this->logFraudModel = new LogFraudModel();
        $this->zonaModel     = new ZonaModel();
    }

    /**
     * @param int $targetKelasId
     * @return bool
     */
    private function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === (int) session()->get('kelas_id');
        }
        return true;
    }

    /**
     * @param UploadedFile|null $foto
     * @param string|null $fotoLama
     * @return array
     */
    private function handleUploadFoto(?UploadedFile $foto, ?string $fotoLama = null): array
    {
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $aturanFoto = [
                'foto' => [
                    'label'  => 'Foto Profil',
                    'rules'  => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
                ]
            ];

            if (!$this->validate($aturanFoto)) {
                return ['status' => false, 'error' => $this->validator->getError('foto')];
            }

            $namaFoto = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/siswa', $namaFoto);

            if (!empty($fotoLama) && file_exists(FCPATH . 'uploads/siswa/' . $fotoLama)) {
                unlink(FCPATH . 'uploads/siswa/' . $fotoLama);
            }

            return ['status' => true, 'data' => $namaFoto];
        }

        return ['status' => true, 'data' => $fotoLama];
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $isWaliKelas    = (bool) session()->get('is_wali_kelas');
        $kelasSessionId = session()->get('kelas_id');

        $kelasFilter  = $isWaliKelas ? $kelasSessionId : $this->request->getGet('kelas');
        $searchFilter = (string) $this->request->getGet('search');
        $page         = (int) ($this->request->getGet('page') ?? 1);
        $perPage      = 10;

        $sortParam = (string) ($this->request->getGet('sort') ?? 'nama_siswa-asc');
        $sortParts = explode('-', $sortParam);
        $sortCol   = $sortParts[0] ?? 'nama_siswa';
        $sortDir   = $sortParts[1] ?? 'asc';

        $listKelas = $isWaliKelas
            ? $this->kelasModel->where('id_kelas', $kelasSessionId)->findAll()
            : $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        $listZona = $this->zonaModel->orderBy('is_default', 'DESC')->orderBy('nama_zona', 'ASC')->findAll();

        $siswa = $this->siswaModel->getPaginatedSiswa($kelasFilter, $searchFilter, $perPage, $sortCol, $sortDir);

        $data = [
            'title'         => 'Daftar Siswa',
            'siswa'         => $siswa,
            'list_kelas'    => $listKelas,
            'list_zona'     => $listZona,
            'kelas_aktif'   => $kelasFilter,
            'search_aktif'  => $searchFilter,
            'sort_aktif'    => $sortParam,
            'sort_col'      => $sortCol,
            'sort_dir'      => $sortDir,
            'pager_links'   => $this->siswaModel->pager->links('default', 'tailwind_pagination'),
            'total_data'    => $this->siswaModel->pager->getTotal('default'),
            'page'          => $page,
            'perPage'       => $perPage,
            'is_wali_kelas' => $isWaliKelas
        ];

        return view('web/siswa', $data);
    }

    /**
     * @return mixed
     */
    public function store()
    {
        $kelasIdPost = (int) $this->request->getPost('kelas_id');

        if (!$this->checkAksesWaliKelas($kelasIdPost)) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak dapat menambahkan siswa ke kelas lain.');
        }

        $aturanValidasi = [
            'nis'        => ['rules' => 'required|is_unique[siswa.nis]', 'errors' => ['is_unique' => 'NIS sudah terdaftar.']],
            'nama_siswa' => 'required',
            'kelas_id'   => 'required|numeric'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getError('nis') ?: 'Mohon lengkapi semua data wajib (termasuk pemilihan kelas).');
        }

        $uploadFoto = $this->handleUploadFoto($this->request->getFile('foto'));
        if (!$uploadFoto['status']) return redirect()->back()->withInput()->with('error', (string) $uploadFoto['error']);

        $nis = (string) $this->request->getPost('nis');

        $this->siswaModel->insert([
            'nis'          => $nis,
            'nama_siswa'   => (string) $this->request->getPost('nama_siswa'),
            'kelas_id'     => $kelasIdPost,
            'password'     => password_hash($nis, PASSWORD_BCRYPT),
            'foto_profil'  => $uploadFoto['data']
        ]);

        cache()->deleteMatching('list_siswa_dropdown_*');

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function update(string $id)
    {
        $siswaLama = $this->siswaModel->find($id);

        if (!$siswaLama || (!$this->checkAksesWaliKelas((int)$siswaLama['kelas_id']))) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        $kelasIdPost = (int) $this->request->getPost('kelas_id');
        if (!$this->checkAksesWaliKelas($kelasIdPost)) return redirect()->back()->with('error', 'Akses Ditolak.');

        $aturanValidasi = [
            'nis'        => ['rules' => "required|is_unique[siswa.nis,id_siswa,{$id}]", 'errors' => ['is_unique' => 'NIS dipakai siswa lain.']],
            'nama_siswa' => 'required',
            'kelas_id'   => 'required|numeric'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getError('nis') ?: 'Cek kembali isian Anda.');
        }

        $uploadFoto = $this->handleUploadFoto($this->request->getFile('foto'), (string) $siswaLama['foto_profil']);
        if (!$uploadFoto['status']) return redirect()->back()->withInput()->with('error', (string) $uploadFoto['error']);

        $updateData = [
            'nis'          => (string) $this->request->getPost('nis'),
            'nama_siswa'   => (string) $this->request->getPost('nama_siswa'),
            'kelas_id'     => $kelasIdPost,
            'foto_profil'  => $uploadFoto['data']
        ];

        if ($updateData['nis'] !== $siswaLama['nis']) {
            $updateData['password'] = password_hash($updateData['nis'], PASSWORD_BCRYPT);
        }

        $this->siswaModel->update($id, $updateData);

        cache()->deleteMatching('list_siswa_dropdown_*');

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delete(string $id)
    {
        $siswa = $this->siswaModel->find($id);

        if (!$siswa || (!$this->checkAksesWaliKelas((int)$siswa['kelas_id']))) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        $this->siswaModel->db->transStart();
        $this->siswaModel->delete($id);
        $this->siswaModel->db->transComplete();

        if ($this->siswaModel->db->transStatus() === false) {
            return redirect()->to('/admin/siswa')->with('error', 'Gagal menghapus data siswa dari database.');
        }

        if (!empty($siswa['foto_profil']) && file_exists(FCPATH . 'uploads/siswa/' . $siswa['foto_profil'])) {
            unlink(FCPATH . 'uploads/siswa/' . $siswa['foto_profil']);
        }

        cache()->deleteMatching('list_siswa_dropdown_*');

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa beserta foto berhasil dihapus permanen.');
    }

    /**
     * @return mixed
     */
    public function deleteBulk()
    {
        $ids = $this->request->getPost('ids');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data siswa yang dipilih untuk dihapus.');
        }

        $siswaList = $this->siswaModel->whereIn('id_siswa', $ids)->findAll();

        if (empty($siswaList)) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $validIds       = [];
        $photosToDelete = [];

        foreach ($siswaList as $siswa) {
            if (!$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
                continue;
            }

            $validIds[] = $siswa['id_siswa'];
            if (!empty($siswa['foto_profil'])) {
                $photosToDelete[] = $siswa['foto_profil'];
            }
        }

        if (empty($validIds)) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak dapat menghapus data siswa tersebut.');
        }

        $this->siswaModel->db->transStart();
        $this->siswaModel->whereIn('id_siswa', $validIds)->delete();
        $this->siswaModel->db->transComplete();

        if ($this->siswaModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Terjadi kesalahan pada database saat menghapus data massal.');
        }

        foreach ($photosToDelete as $foto) {
            if (file_exists(FCPATH . 'uploads/siswa/' . $foto)) {
                unlink(FCPATH . 'uploads/siswa/' . $foto);
            }
        }

        cache()->deleteMatching('list_siswa_dropdown_*');

        return redirect()->back()->with('success', count($validIds) . ' data siswa berhasil dihapus secara permanen.');
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function resetDevice(string $id)
    {
        $siswa = $this->siswaModel->find($id);
        if (!$siswa || (!$this->checkAksesWaliKelas((int)$siswa['kelas_id']))) {
            return redirect()->to('/admin/siswa')->with('error', 'Akses Ditolak.');
        }

        $this->siswaModel->update($id, ['device_id' => null]);
        return redirect()->to('/admin/siswa')->with('success', 'Perangkat berhasil di-reset.');
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function unblock(string $id)
    {
        $siswa = $this->siswaModel->find($id);
        if (!$siswa || (!$this->checkAksesWaliKelas((int)$siswa['kelas_id']))) {
            return redirect()->to('/admin/siswa')->with('error', 'Akses Ditolak.');
        }

        $this->siswaModel->update($id, [
            'is_blocked'  => 0,
            'fraud_count' => 0
        ]);
        return redirect()->to('/admin/siswa')->with('success', 'Akun siswa berhasil di-unblock dan fraud count di-reset.');
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function block(string $id)
    {
        $siswa = $this->siswaModel->find($id);
        if (!$siswa || (!$this->checkAksesWaliKelas((int)$siswa['kelas_id']))) {
            return redirect()->to('/admin/siswa')->with('error', 'Akses Ditolak.');
        }

        $this->siswaModel->update($id, [
            'is_blocked' => 1
        ]);
        return redirect()->to('/admin/siswa')->with('success', 'Akses absensi dan login siswa berhasil dikunci sementara.');
    }

    /**
     * @return void
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();

        if (session()->get('is_wali_kelas')) {
            $listKelas = $this->kelasModel->where('id_kelas', session()->get('kelas_id'))->findAll();
        } else {
            $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        }

        $totalKelas = count($listKelas);

        if ($totalKelas > 0) {
            $refSheet = $spreadsheet->createSheet();
            $refSheet->setTitle('DataKelas');
            $refRow = 1;
            foreach ($listKelas as $k) {
                $refSheet->setCellValue('A' . $refRow++, $k['nama_kelas']);
            }
            $spreadsheet->getSheetByName('DataKelas')->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA SISWA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->setCellValue('A2', 'Pilih kelas melalui dropdown di kolom C. Jangan mengetik manual di kolom Kelas.');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setARGB('FFD97706');

        $sheet->setCellValue('A3', 'NIS');
        $sheet->setCellValue('B3', 'NAMA LENGKAP');
        $sheet->setCellValue('C3', 'KELAS (Klik untuk pilih)');
        $sheet->getStyle('A3:C3')->getFont()->setBold(true);
        $sheet->getStyle('A3:C3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

        if ($totalKelas > 0) {
            $validation = $sheet->getDataValidation('C4:C500');
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input Salah');
            $validation->setError('Silakan pilih kelas yang tersedia dari daftar!');
            $validation->setPromptTitle('Pilih Kelas');
            $validation->setPrompt('Klik panah di samping untuk memilih kelas.');
            $validation->setFormula1('DataKelas!$A$1:$A$' . $totalKelas);
        }

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Siswa_V2.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * @return void
     */
    public function export()
    {
        $isWaliKelas = session()->get('is_wali_kelas');
        $kelasId     = $isWaliKelas ? session()->get('kelas_id') : $this->request->getGet('kelas');

        $dataSiswa = $this->siswaModel->getSiswaForExport(empty($kelasId) ? null : (int)$kelasId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Nama Lengkap');
        $sheet->setCellValue('D1', 'Kelas');
        $sheet->setCellValue('E1', 'Zona PKL');

        $row = 2;
        foreach ($dataSiswa as $index => $siswa) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValueExplicit('B' . $row, $siswa['nis'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $siswa['nama_siswa']);
            $sheet->setCellValue('D' . $row, $siswa['nama_kelas']);
            $sheet->setCellValue('E' . $row, $siswa['nama_zona'] ?? 'Mengikuti Kelas/Pusat');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Data_Siswa.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * @return mixed
     */
    public function import()
    {
        $file = $this->request->getFile('file_excel');

        if (!$file || !$file->isValid()) {
            $errorMsg = $file ? $file->getErrorString() : 'File tidak terbaca oleh server.';
            return redirect()->back()->with('error', 'Gagal mengunggah file: ' . $errorMsg);
        }

        $extension = strtolower($file->getClientExtension());
        if ($extension !== 'xlsx') {
            return redirect()->back()->with('error', 'Format tidak valid. Harus .xlsx, file Anda terdeteksi sebagai .' . $extension);
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $dataSiswa   = $spreadsheet->getActiveSheet()->toArray();

            $allKelas = $this->kelasModel->findAll();
            $kelasMap = [];
            foreach ($allKelas as $k) {
                $kelasMap[strtolower(trim((string)$k['nama_kelas']))] = $k['id_kelas'];
            }

            $isWaliKelas = (bool) session()->get('is_wali_kelas');
            $waliKelasId = $isWaliKelas ? (int) session()->get('kelas_id') : null;

            $result = $this->siswaModel->processBulkImport($dataSiswa, $kelasMap, $isWaliKelas, $waliKelasId);

            if (!$result['status']) {
                return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat proses import database. Seluruh data dibatalkan secara aman.');
            }

            cache()->deleteMatching('list_siswa_dropdown_*');

            return redirect()->to('/admin/siswa')->with('success', "Berhasil import {$result['inserted']} data siswa. {$result['skipped']} baris dilewati (NIS duplikat/salah kelas).");
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return redirect()->back()->with('error', 'Struktur file Excel rusak atau tidak dapat dibaca: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan internal: ' . $e->getMessage());
        }
    }

    /**
     * @param string $idSiswa
     * @return mixed
     */
    public function detail(string $idSiswa)
    {
        $siswa = $this->siswaModel->getSiswaWithKelas($idSiswa);

        if (!$siswa || (!$this->checkAksesWaliKelas((int)$siswa['kelas_id']))) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        $startDate   = (string) $this->request->getGet('start_date');
        $endDate     = (string) $this->request->getGet('end_date');
        $pageAbsensi = (int) ($this->request->getGet('page_absensi') ?? 1);
        $perPage     = 10;

        $absensi = $this->absensiModel->getRiwayatAbsensiSiswa($idSiswa, $startDate, $endDate, $perPage);
        $pager   = $this->absensiModel->pager;

        $logFraud = $this->logFraudModel->where('siswa_id', $idSiswa)
            ->orderBy('created_at', 'DESC')
            ->findAll(10);

        $stats = $this->absensiModel->getStatistikSiswa($idSiswa);

        $data = [
            'title'        => 'Profil 360: ' . $siswa['nama_siswa'],
            'siswa'        => $siswa,
            'absensi'      => $absensi,
            'logFraud'     => $logFraud,
            'stats'        => $stats,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'pager_links'  => $pager->links('absensi', 'tailwind_pagination'),
            'total_data'   => $pager->getTotal('absensi'),
            'page'         => $pageAbsensi,
            'perPage'      => $perPage
        ];

        return view('web/siswa_detail', $data);
    }
}
