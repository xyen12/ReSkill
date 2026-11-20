
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL DAFTAR VIDEO
// ==========================================

// Mengambil video yang sudah dipublikasikan
// beserta nama kategori masing-masing.
$rows = $pdo->query(
    "SELECT
        v.*,
        c.name AS category
    FROM videos v
    LEFT JOIN waste_categories c ON c.id = v.category_id
    WHERE v.status = 'published'
    ORDER BY v.created_at DESC"
)->fetchAll();

// ==========================================
// MENGAMBIL DAFTAR KATEGORI
// ==========================================

$cats = $pdo->query(
    'SELECT * FROM waste_categories ORDER BY id'
)->fetchAll();

// Mengatur judul halaman.
$page_title = 'Video Pembelajaran';

require_once __DIR__ . '/../../includes/header.php';

// ==========================================
// IKON KATEGORI
// ==========================================

// Menentukan ikon berdasarkan nama kategori.
$icons = [
    'Organik' => '🍃',
    'Anorganik' => '🧴',
    'B3' => '⚠️',
    'Residu' => '▤'
];

?>

<!-- ======================================
     HEADER VIDEO PEMBELAJARAN
======================================= -->

<section class="page-heading heading-hero">

    <div>

        <span class="eyebrow">
            ▷ Video Edukasi
        </span>

        <h1>
            Video Pembelajaran
        </h1>

        <p>
            Tonton berbagai video edukasi seputar pengelolaan limbah,
            mulai dari pemilahan, pengolahan, hingga daur ulang.
        </p>

    </div>

    <img
        src="<?= e(url('assets/images/video.jpg')) ?>"
        alt="Ilustrasi video edukasi"
    >

</section>

<!-- ======================================
     FILTER KATEGORI DAN PENCARIAN
======================================= -->

<div class="search-filter">

    <!-- Tombol filter kategori -->
    <div class="filter-pills">

        <!-- Menampilkan semua video -->
        <button
            class="filter-pill active"
            type="button"
            data-video-filter=""
        >
            ▦ Semua
        </button>

        <!-- Menampilkan tombol setiap kategori -->
        <?php foreach ($cats as $c): ?>

            <button
                class="filter-pill"
                type="button"
                data-video-filter="<?= e($c['name']) ?>"
            >

                <?= $icons[$c['name']] ?? '♻️' ?>

                <?= e($c['name']) ?>

            </button>

        <?php endforeach; ?>

    </div>

    <!-- Input pencarian video -->
    <div class="search-box">

        <input
            type="search"
            placeholder="Cari video..."
            data-search=".video-item"
        >

    </div>

</div>

<!-- ======================================
     DAFTAR KARTU VIDEO
======================================= -->

<div class="content-grid">

    <?php foreach ($rows as $r): ?>

        <article
            class="content-card video-item"
            data-cat="<?= e($r['category'] ?? 'Umum') ?>"
        >

            <!-- Gambar thumbnail video -->
            <a
                class="video-thumb"
                href="<?= e(url('modules/videos/detail.php?id=' . (int) $r['id'])) ?>"
            >

                <img
                    class="cover"
                    src="<?= e(url('assets/images/video.jpg')) ?>"
                    alt=""
                >

            </a>

            <!-- Kategori video -->
            <span class="tag">
                <?= e($r['category'] ?? 'Umum') ?>
            </span>

            <!-- Judul video -->
            <h3>
                <?= e($r['title']) ?>
            </h3>

            <!-- Deskripsi video -->
            <p>
                <?= e($r['description']) ?>
            </p>

            <!-- Informasi video -->
            <div class="meta">

                ▶ Video edukasi

                &nbsp; ◷

                <?= e($r['duration'] ?? '') . ' menit' ?>

            </div>

            <!-- Tombol menonton video -->
            <a
                class="btn btn-primary"
                href="<?= e(url('modules/videos/detail.php?id=' . (int) $r['id'])) ?>"
            >
                ▶ Tonton Video
            </a>

        </article>

    <?php endforeach; ?>

</div>

<!-- ======================================
     KONDISI JIKA VIDEO BELUM TERSEDIA
======================================= -->

<?php if (!$rows): ?>

    <div class="empty-state">

        <strong>
            Video sedang disiapkan
        </strong>

        Admin akan menambahkan video pembelajaran di halaman ini.

    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
