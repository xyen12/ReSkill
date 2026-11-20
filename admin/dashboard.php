<?php

// Memanggil fungsi bantuan dan koneksi database
require_once __DIR__ . '/../includes/functions.php';

// Memastikan pengguna yang mengakses halaman adalah admin
require_admin();

// Menentukan judul halaman
$page_title = 'Dashboard Admin';

// Menyiapkan array untuk menyimpan jumlah data
$stats = [];

// Daftar tabel yang akan dihitung jumlah datanya
$tables = [
    'users',
    'waste_categories',
    'educational_materials',
    'videos',
    'quizzes',
    'recycling_gallery'
];

// Menghitung jumlah data pada setiap tabel
foreach ($tables as $table) {
    $query = $pdo->query("SELECT COUNT(*) FROM `$table`");

    // Menyimpan hasil perhitungan ke dalam array
    $stats[$table] = (int) $query->fetchColumn();
}

// Memanggil bagian header halaman
require_once __DIR__ . '/../includes/header.php';

?>

<!-- Bagian sambutan administrator -->
<section class="page-heading">

    <span class="eyebrow">RUANG ADMINISTRATOR</span>

    <h1>
        Halo, <?= e($_SESSION['name']) ?>!
    </h1>

    <p>
        Kelola konten dan aktivitas pembelajaran ReSkill
        dari satu tempat.
    </p>

</section>


<!-- Menampilkan ringkasan jumlah data -->
<div class="feature-grid">

    <!-- Jumlah pengguna terdaftar -->
    <article class="feature-card">
        <h2><?= $stats['users'] ?></h2>
        <p>Pengguna terdaftar</p>
    </article>

    <!-- Jumlah materi edukasi -->
    <article class="feature-card">
        <h2><?= $stats['educational_materials'] ?></h2>
        <p>Materi edukasi</p>
    </article>

    <!-- Jumlah video pembelajaran -->
    <article class="feature-card">
        <h2><?= $stats['videos'] ?></h2>
        <p>Video pembelajaran</p>
    </article>

    <!-- Jumlah karya di galeri -->
    <article class="feature-card">
        <h2><?= $stats['recycling_gallery'] ?></h2>
        <p>Karya di galeri</p>
    </article>

</div>


<!-- Bagian menu pengelolaan konten -->
<div class="detail-card" style="margin-top: 20px">

    <h2>Pengelolaan Konten</h2>

    <p class="muted">
        Admin dapat menambah, mengedit, dan menghapus kategori,
        materi, video, kuis, soal, serta mengelola karya galeri pengguna.
    </p>

    <!-- Tombol menuju halaman pengelolaan -->
    <div class="inline-actions">

        <a class="btn btn-primary"
           href="<?= e(url('admin/manage.php')) ?>">
            Kelola Konten
        </a>

        <a class="btn btn-outline"
           href="<?= e(url('admin/moderate.php')) ?>">
            Moderasi Galeri
        </a>

        <a class="btn btn-outline"
           href="<?= e(url('modules/progress/index.php')) ?>">
            Laporan Aktivitas
        </a>

    </div>

</div>


<?php

// Memanggil bagian footer halaman
require_once __DIR__ . '/../includes/footer.php';

?>
