# ReSkill

Aplikasi edukasi pengelolaan limbah berbasis PHP Native dan MySQL.

## Menjalankan aplikasi
1. Ekstrak folder `ReSkill` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL melalui XAMPP.
3. Import `database/reskill.sql` melalui phpMyAdmin.
4. Sesuaikan konfigurasi database di `config/koneksi.php` bila diperlukan.
5. Buka `http://localhost/ReSkill/`.

## Membuat akun admin
1. Daftar akun melalui halaman registrasi.
2. Buka phpMyAdmin, pilih database `reskill`, lalu tabel `users`.
3. Ubah nilai kolom `role` akun tersebut dari `user` menjadi `admin`.
4. Login ulang. Admin akan diarahkan ke dashboard admin.

## Pengelolaan admin
Pada menu **Kelola Konten**, admin dapat menambah, mengedit, dan menghapus:
- Kategori limbah
- Materi edukasi
- Video pembelajaran
- Kuis dan soal kuis
- Karya galeri pengguna (termasuk menghapus foto terkait)

Penghapusan kuis juga menghapus soal dan riwayat hasil kuis terkait karena relasi database menggunakan `ON DELETE CASCADE`. Penghapusan materi menghapus catatan progres belajar terkait. Kategori yang dihapus akan dilepas dari konten terkait tanpa menghapus kontennya.
