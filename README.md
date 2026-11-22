# ReSkill – Platform Edukasi Pengolahan Limbah

Permasalahan sampah dan limbah masih menjadi tantangan lingkungan yang perlu mendapatkan perhatian. Banyak limbah rumah tangga yang sebenarnya masih dapat dimanfaatkan, tetapi belum dikelola dengan baik. Kurangnya pengetahuan mengenai pemilahan dan pengolahan limbah menyebabkan barang yang masih memiliki nilai guna sering kali berakhir sebagai sampah. Padahal, melalui pengolahan yang tepat, limbah dapat diubah menjadi produk baru yang bermanfaat bahkan memiliki nilai ekonomi.
Oleh karena itu, dibutuhkan media edukasi yang dapat membantu masyarakat mengenali jenis limbah, memahami cara pengolahannya, serta memperoleh panduan untuk memanfaatkan limbah secara lebih kreatif dan bertanggung jawab.

## Tujuan Proyek

- Meningkatkan pengetahuan masyarakat mengenai pemilahan dan pengolahan limbah.
- Menyediakan media pembelajaran yang mudah diakses.
- Memberikan panduan pengolahan limbah menjadi produk bernilai ekonomi.
- Mendorong masyarakat untuk berbagi inspirasi dan hasil karya daur ulang.

## Sustainable Development Goals (SDGs)

ReSkill mendukung beberapa tujuan pembangunan berkelanjutan:

- **SDG 12 – Responsible Consumption and Production:** Mendorong pengelolaan limbah dan pemanfaatan kembali bahan yang masih memiliki nilai guna.
- **SDG 4 – Quality Education:** Menyediakan akses pembelajaran mengenai pengelolaan dan pengolahan limbah.

## Fitur Utama

### 1. Manajemen Pengguna
- Registrasi akun.
- Login dan logout.
- Pengelolaan profil pengguna.
- Pembagian hak akses Admin dan User.

### 2. Dashboard
- Menampilkan halaman utama setelah pengguna login.
- Menyediakan navigasi menuju fitur-fitur aplikasi.

### 3. Kategori Limbah
- Menampilkan kategori limbah berdasarkan jenisnya.
- Membantu pengguna mengenali dan mengelompokkan limbah.

### 4. Materi Edukasi
- Menampilkan artikel edukasi mengenai pengelolaan limbah.
- Menyediakan informasi pembelajaran yang dapat dibaca pengguna.

### 5. Tutorial Pengolahan
- Menyediakan panduan pengolahan limbah.
- Menampilkan langkah-langkah pembuatan produk dari limbah.

### 6. Video Pembelajaran
- Menampilkan video edukasi mengenai pengolahan limbah.
- Menyediakan media pembelajaran berbasis visual.

### 7. Kuis dan Evaluasi
- Menyediakan kuis berdasarkan materi pembelajaran.
- Membantu pengguna menguji pemahaman terhadap materi.

### 8. Progres Belajar
- Menyediakan halaman perkembangan aktivitas belajar.
- Menampilkan informasi materi yang dipelajari dan riwayat kuis sesuai data yang tercatat.

### 9. Galeri Daur Ulang
- Menampilkan karya daur ulang dari pengguna.
- Menyediakan fitur unggah karya.
- Mendukung proses moderasi karya oleh admin.

## Hak Akses Pengguna

### Admin
- Melihat ringkasan data pada Dashboard Admin.
- Mengelola kategori limbah.
- Mengelola materi edukasi dan tutorial.
- Mengelola video pembelajaran.
- Mengelola kuis dan pertanyaan.
- Melakukan moderasi karya pada galeri.

### User
- Mengakses materi dan kategori limbah.
- Membaca artikel dan tutorial pengolahan.
- Menonton video pembelajaran.
- Mengerjakan kuis dan evaluasi.
- Melihat progres belajar.
- Melihat dan mengunggah karya pada galeri daur ulang.

## 🛠️ Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| PHP Native | Pengembangan backend aplikasi |
| MySQL | Pengelolaan database |
| HTML | Struktur halaman web |
| CSS | Tampilan dan desain antarmuka |
| JavaScript | Interaksi pada halaman web |
| PDO | Koneksi dan pengelolaan query database |
| XAMPP | Lingkungan server lokal |

## Instalasi dan Konfigurasi

Ikuti langkah berikut untuk menjalankan ReSkill secara lokal.

### 1. Persiapan

Pastikan perangkat telah memiliki:
- XAMPP
- Web browser
- Text editor seperti Visual Studio Code

### 2. Clone Repository

Buka terminal dan jalankan perintah:

```bash
git clone https://github.com/USERNAME/ReSkill.git
```

Ganti `USERNAME` dengan username GitHub pemilik repository.

### 3. Pindahkan Folder Proyek

Letakkan folder ReSkill di dalam direktori:

```text
C:\xampp\htdocs\
```

### 4. Jalankan XAMPP

Aktifkan layanan:
- Apache
- MySQL

### 5. Buat Database

1. Buka `http://localhost/phpmyadmin`.
2. Buat database dengan nama `reskill`.
3. Pilih database tersebut.
4. Import file `database/reskill.sql`.

### 6. Konfigurasi Koneksi Database

Periksa file:

```text
config/koneksi.php
```

Sesuaikan konfigurasi database dengan pengaturan lokal:

```php
$host = 'localhost';
$dbname = 'reskill';
$username = 'root';
$password = '';
```

Sesuaikan nilainya jika konfigurasi MySQL pada perangkat berbeda.

### 7. Jalankan Aplikasi

Buka browser dan akses:

```text
http://localhost/ReSkill/
```

Aplikasi ReSkill siap dijalankan.

## Membuat Akun Admin

1. Buat akun melalui halaman registrasi aplikasi.
2. Buka phpMyAdmin dan pilih database `reskill`.
3. Buka tabel `users`.
4. Cari akun yang ingin dijadikan admin.
5. Ubah nilai kolom `role` menjadi `admin`.
6. Simpan perubahan dan login kembali menggunakan akun tersebut.

Akun yang baru didaftarkan secara normal digunakan sebagai akun pengguna.

## Struktur Proyek

```text
ReSkill/
│
├── admin/
├── assets/
├── config/
├── database/
├── includes/
├── modules/
│   ├── categories/
│   ├── materials/
│   ├── tutorials/
│   ├── videos/
│   ├── quizzes/
│   ├── progress/
│   └── gallery/
│
├── uploads/
├── index.php
└── README.md
```

## Catatan

- Pastikan Apache dan MySQL aktif sebelum menjalankan aplikasi.
- Database harus di-import terlebih dahulu.
- Sesuaikan konfigurasi koneksi database dengan lingkungan lokal.
- Jangan mengunggah password database atau informasi rahasia ke repository publik.

## Tim Pengembang

**ReSkill – Platform Edukasi Pengolahan Limbah**

Proyek ini dikembangkan sebagai bagian dari tugas akhir Praktikum Pemrograman Web I.

