
<?php

// ==========================================
// KONFIGURASI DATABASE
// ==========================================

// Menentukan alamat server dan nama database.
$host = 'localhost';
$dbname = 'reskill';

// Mengatur username dan password MySQL.
$username = 'root';
$password = '';

// ==========================================
// PROSES KONEKSI DATABASE
// ==========================================

try {

    // Membuat koneksi menggunakan PDO.
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            // Mengaktifkan mode exception jika terjadi kesalahan.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // Mengatur hasil query agar berbentuk associative array.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Menonaktifkan emulasi prepared statement.
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    // Menampilkan pesan jika koneksi database gagal.
    exit(
        'Koneksi database gagal. Periksa config/koneksi.php ' .
        'dan pastikan database reskill sudah di-import.'
    );
}
