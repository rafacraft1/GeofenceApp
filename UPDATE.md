# Panduan Update Server Produksi (Zero Data Loss)

Panduan ini berisi langkah-langkah Standar Operasional Prosedur (SOP) untuk melakukan proses *deployment* pembaruan (update) aplikasi GeofenceApp ke server produksi menggunakan GitHub, dengan jaminan **100% aman bagi data** (Zero Data Loss).

File `.env` (berisi password database) dan folder `writable/uploads/` (berisi foto profil/absensi) sudah diabaikan oleh Git, sehingga tidak akan terhapus atau tertimpa saat Anda melakukan *pull* dari server.

---

## 🛠️ Langkah-Langkah Update via SSH

Buka terminal/SSH dan akses server produksi Anda, lalu ikuti langkah berurutan di bawah ini:

### 1. Masuk ke Direktori Proyek
Pastikan Anda sudah berada di dalam folder *root* aplikasi (misalnya di dalam `public_html/presensi/` atau sejenisnya).
```bash
cd /path/ke/folder/aplikasi
```

### 2. Tangani Perubahan File Sampah Lokal (Opsional namun Penting)
Terkadang Git di server akan menolak pembaruan *(Aborting)* dengan pesan *error* karena ada file tembolok bawaan sistem (seperti `.gitkeep` atau `index.html` di dalam folder `writable`) yang sedikit berubah formatnya di server.
Untuk memastikan kelancaran *pull*, amankan (buang sementara) jejak file sampah tersebut:
```bash
git stash
```

### 3. Tarik Pembaruan dari GitHub
Tarik seluruh pembaruan (kodingan PHP, aset CSS/JS terbaru) dari repositori utama.
```bash
git pull origin main
```

*(Setelah sukses melakukan pull, Anda bisa membuang file sampah yang diamankan pada langkah ke-2 tadi agar tidak menumpuk)*:
```bash
git stash drop
```

### 4. Eksekusi Migrasi Database
Jika ada penambahan kolom atau tabel baru, jalankan perintah migrasi.
Perintah ini dirancang **sangat cerdas dan aman**. Ia HANYA akan mengeksekusi file migrasi baru (tidak akan menghapus atau me- *reset* data/tabel lama).
```bash
php spark migrate
```
> [!CAUTION]
> **JANGAN PERNAH** menjalankan perintah `php spark migrate:refresh` atau `php spark migrate:rollback` di server produksi! Perintah tersebut akan mengosongkan seluruh tabel di database Anda.

### 5. Bersihkan Cache dan Paksa Relogin (Penting!)
Setelah perombakan UI/UX dan struktur sistem, bersihkan tembolok *(cache)* server agar *browser* pengguna memuat tampilan versi terbaru yang tidak berantakan.
```bash
# Hapus semua tembolok file HTML/Views
rm -rf writable/cache/*

# Hapus semua sesi yang sedang aktif (Memaksa guru & siswa login ulang 1x)
rm -rf writable/session/*
```

---

## ✅ Validasi (Sanity Check)
Setelah 5 langkah di atas selesai:
1. Buka domain aplikasi produksi Anda melalui *browser*.
2. Pastikan halaman **Login** baru sudah muncul sempurna tanpa cacat *layout*.
3. Lakukan *login* menggunakan akun Admin Anda dan pastikan data absensi atau pengguna terdahulu tetap ada dan aman.

Pembaruan telah berhasil diterapkan!
