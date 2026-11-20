
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL DATA KATEGORI LIMBAH
// ==========================================

$cats = $pdo->query(
    'SELECT * FROM waste_categories ORDER BY id'
)->fetchAll();

// ==========================================
// MENGAMBIL DATA KARYA GALERI
// ==========================================

// Mengambil karya yang sudah disetujui
// atau karya milik pengguna yang sedang login.
$stmt = $pdo->prepare(
    "SELECT
        g.*,
        u.name,
        c.name AS category,
        (
            SELECT COUNT(*)
            FROM gallery_likes l
            WHERE l.gallery_id = g.id
        ) AS likes
     FROM recycling_gallery g
     JOIN users u ON u.id = g.user_id
     LEFT JOIN waste_categories c ON c.id = g.category_id
     WHERE g.status = 'approved' OR g.user_id = ?
     ORDER BY g.created_at DESC"
);

$stmt->execute([$_SESSION['user_id']]);

$rows = $stmt->fetchAll();

// Mengatur judul halaman.
$page_title = 'Galeri Karya';

require_once __DIR__ . '/../../includes/header.php';

// Menentukan ikon berdasarkan kategori limbah.
$icons = [
    'Organik'   => '🍃',
    'Anorganik' => '🧴',
    'B3'        => '⚠️',
    'Residu'    => '▤'
];

?>

<!-- ======================================
     JUDUL DAN ILUSTRASI HALAMAN
======================================= -->

<section class="page-heading heading-hero">

    <div>

        <span class="eyebrow">
            ▧ Galeri
        </span>

        <h1>Galeri Karya Daur Ulang</h1>

        <p>
            Jelajahi berbagai karya kreatif hasil pengolahan dan
            daur ulang limbah yang inspiratif dari pengguna ReSkill.
        </p>

        <!-- Tombol menuju halaman unggah karya -->
        <a
            class="btn btn-primary"
            href="<?= e(url('modules/gallery/upload.php')) ?>"
        >
            ⇧ Unggah Karya
        </a>

    </div>

    <!-- Ilustrasi galeri -->
    <img
        src="<?= e(url('assets/images/gallery.jpg')) ?>"
        alt="Ilustrasi galeri karya"
    >

</section>

<!-- ======================================
     FILTER DAN PENCARIAN KARYA
======================================= -->

<div class="search-filter">

    <!-- Filter berdasarkan kategori -->
    <div class="filter-pills">

        <button
            class="filter-pill active"
            type="button"
            data-gallery-filter=""
        >
            ▦ Semua
        </button>

        <?php foreach ($cats as $category): ?>

            <button
                class="filter-pill"
                type="button"
                data-gallery-filter="<?= e($category['name']) ?>"
            >
                <?= $icons[$category['name']] ?? '♻️' ?>

                <?= e($category['name']) ?>
            </button>

        <?php endforeach; ?>

    </div>

    <!-- Pencarian berdasarkan nama karya -->
    <div class="search-box">

        <input
            type="search"
            placeholder="Cari karya..."
            data-search=".gallery-card"
        >

    </div>

</div>

<!-- ======================================
     DAFTAR KARYA GALERI
======================================= -->

<div class="gallery-grid">

    <?php foreach ($rows as $gallery): ?>

        <a
            class="gallery-card"
            data-gallery-card
            data-cat="<?= e($gallery['category'] ?? 'Umum') ?>"
            href="<?= e(
                url('modules/gallery/detail.php?id=' . (int) $gallery['id'])
            ) ?>"
        >

            <!-- Foto karya -->
            <img
                src="<?= e(url('uploads/gallery/' . $gallery['photo'])) ?>"
                data-fallback="<?= e(url('assets/images/gallery.jpg')) ?>"
                alt="<?= e($gallery['title']) ?>"
                loading="lazy"
            >

            <!-- Kategori karya -->
            <span class="tag">
                <?= e($gallery['category'] ?? 'Umum') ?>
            </span>

            <!-- Judul karya -->
            <h3>
                <?= e($gallery['title']) ?>
            </h3>

            <!-- Nama pengunggah dan jumlah like -->
            <p>
                ◉ <?= e($gallery['name']) ?>

                <span style="float: right">
                    ♡ <?= (int) $gallery['likes'] ?>
                </span>
            </p>

            <!-- Status karya jika masih menunggu persetujuan -->
            <?php if (
                $gallery['status'] === 'pending' &&
                (int) $gallery['user_id'] === (int) $_SESSION['user_id']
            ): ?>

                <span class="tag">
                    Menunggu persetujuan
                </span>

            <?php endif; ?>

        </a>

    <?php endforeach; ?>

</div>

<!-- ======================================
     TAMPILAN JIKA GALERI MASIH KOSONG
======================================= -->

<?php if (!$rows): ?>

    <div class="empty-state">

        <strong>Galeri masih kosong</strong>

        Jadilah yang pertama membagikan karya daur ulangmu.

    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
