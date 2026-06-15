======================================================================
GEOFENCE APP - PANDUAN PENGATURAN CRONJOB (OTOMATISASI SISTEM)
======================================================================

Aplikasi ini membutuhkan beberapa tugas terjadwal (Cronjob) agar 
fitur Absensi, Keamanan, dan Backup berjalan secara otomatis.

Silakan akses Panel Kontrol Server Anda (CPanel/Plesk/Terminal Linux)
dan masukkan konfigurasi berikut ke menu Cronjob:

----------------------------------------------------------------------
1. AUTO-ALPA (Generate Status Bolos Otomatis)
----------------------------------------------------------------------
Fungsi: Memeriksa siswa yang belum absen dan memberikannya status 'Alpa'
        berdasarkan zona dan jadwal masing-masing.

Jadwal: Setiap jam 12:00 dan 16:00 (Rekomendasi)
Perintah:
/usr/local/bin/php /home/username/public_html/spark absen:generate-alpa

----------------------------------------------------------------------
2. BACKUP DATA ABSENSI HARIAN (Snapshot Bulanan)
----------------------------------------------------------------------
Fungsi: Mengunci data absensi hari ini ke file CSV/JSON di folder
        public/download/ agar data tetap abadi meski kelas/zona berubah.

Jadwal: Setiap malam jam 23:55
Perintah:
/usr/local/bin/php /home/username/public_html/spark absen:backup-harian

----------------------------------------------------------------------
CATATAN PENTING:
----------------------------------------------------------------------
1. Ganti "/home/username/public_html/" dengan path direktori root 
   aplikasi Anda yang sebenarnya.
2. Pastikan user web server memiliki akses tulis (write permission) 
   ke folder 'writable/' dan 'public/download/'.
3. Untuk mengetahui path PHP Anda di server, jalankan perintah: 
   "which php" melalui terminal.
4. Jangan menghapus atau mengubah file 'app/Commands/BackupAbsensi.php' 
   jika Anda tidak ingin sistem backup otomatis berhenti.

----------------------------------------------------------------------
Dibuat untuk: GeofenceApp (Multi-Zona/PKL Edition)
======================================================================