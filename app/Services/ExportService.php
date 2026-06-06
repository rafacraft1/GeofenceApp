<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExportService
{
    /**
     * Generate & Download Template Import Siswa
     */
    public function downloadTemplateSiswa(array $listKelas)
    {
        $spreadsheet = new Spreadsheet();
        $totalKelas  = count($listKelas);

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
        $sheet->getStyle('A3:C3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

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

        $this->outputExcel($spreadsheet, 'Template_Import_Siswa_V2.xlsx');
    }

    /**
     * Generate & Download Data Siswa
     */
    public function exportDataSiswa(array $dataSiswa)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Nama Lengkap');
        $sheet->setCellValue('D1', 'Kelas');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $row = 2;
        foreach ($dataSiswa as $index => $siswa) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValueExplicit('B' . $row, $siswa['nis'], DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $siswa['nama_siswa']);
            $sheet->setCellValue('D' . $row, $siswa['nama_kelas']);
            $row++;
        }

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $this->outputExcel($spreadsheet, 'Data_Siswa_Geofence.xlsx');
    }

    /**
     * Generate & Download Rekapitulasi Absensi (Laporan)
     */
    public function exportLaporan(array $rekapData, string $bulanMulai, string $bulanSelesai, string $tahun)
    {
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
            $sheet->setCellValueExplicit('B' . $row, $data['nis'], DataType::TYPE_STRING);
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

        $this->outputExcel($spreadsheet, "Rekap_Absensi_{$tahun}_{$bulanMulai}-{$bulanSelesai}.xlsx");
    }

    /**
     * Utility untuk eksekusi download file Excel
     */
    private function outputExcel(Spreadsheet $spreadsheet, string $filename)
    {
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
