
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// FILTER KATEGORI
// ==========================================

// Mengambil kategori yang dipilih dari URL.
$selected = $_GET['category'] ?? [];

// Memastikan data kategori berbentuk array.
if (!is_array($selected)) {
    $selected = [$selected];
}

// Mengubah nilai kategori menjadi integer dan
// menghapus nilai yang kosong.
$selected = array_values(
    array_filter(
        array_map('intval', $selected)
    )
);

// Mengambil seluruh kategori limbah.
$cats = $pdo->query(
    'SELECT * FROM waste_categories ORDER BY id'
)->fetchAll();

// ==========================================
// MENGAMBIL DATA MATERI
// ==========================================

// Query dasar untuk mengambil materi yang sudah dipublikasikan.
$sql = "
    SELECT
        m.*,
        c.name AS category
    FROM educational_materials m
    LEFT JOIN waste_categories c ON c.id = m.category_id
    WHERE m.status = 'published'
";

$args = [];

// Menambahkan filter kategori jika pengguna memilih kategori.
if ($selected) {

    // Membuat placeholder sesuai jumlah kategori yang dipilih.
    $marks = implode(
        ',',
        array_fill(0, count($selected), '?')
    );

    $sql .= ' AND m.category_id IN (' . $marks . ')';

    // Menyiapkan parameter untuk query.
    $args = $selected;
}

// Mengurutkan materi berdasarkan tanggal terbaru.
$sql .= ' ORDER BY m.created_at DESC';

// Menjalankan query menggunakan prepared statement.
$st = $pdo->prepare($sql);
$st->execute($args);

$rows = $st->fetchAll();

// Mengatur judul halaman.
$page_title = 'Materi Edukasi';

require_once __DIR__ . '/../../includes/header.php';

// ==========================================
// IKON DAN ILUSTRASI KATEGORI
// ==========================================

// Menentukan ikon berdasarkan nama kategori.
$icons = [
    'Organik' => '🍃',
    'Anorganik' => '🧴',
    'B3' => '⚠️',
    'Residu' => '▤'
];

// Menentukan gambar berdasarkan kategori materi.
$materialArt = [
    'Organik' => 'category-organik.png',
    'Anorganik' => 'category-anorganik.png',
    'B3' => 'category-b3.png',
    'Residu' => 'category-residu.png'
];

?>

<!-- ======================================
     HEADER HALAMAN MATERI EDUKASI
======================================= -->

<section class="page-heading heading-hero">

    <div>

        <span class="eyebrow">
            ♧ Materi Edukasi
        </span>

        <h1>
            Materi
            <span style="color: var(--green)">
                Edukasi
            </span>
        </h1>

        <p>
            Pelajari berbagai materi tentang pengelolaan limbah,
            mulai dari pengenalan, pemilahan, hingga daur ulang.
        </p>

    </div>

    <img
        src="<?= e(url('assets/images/materials.jpg')) ?>"
        alt="Ilustrasi buku edukasi pengelolaan limbah"
    >

</section>

<!-- ======================================
     FILTER KATEGORI DAN PENCARIAN
======================================= -->

<div class="search-filter">

    <!-- Tombol filter kategori -->
    <div class="filter-pills">

        <!-- Menampilkan semua materi -->
        <a
            class="filter-pill <?= !$selected ? 'active' : '' ?>"
            href="<?= e(url('modules/materials/index.php')) ?>"
        >
            ▦ Semua
        </a>

        <!-- Menampilkan tombol setiap kategori -->
        <?php foreach ($cats as $c): ?>

            <a
                class="filter-pill <?= in_array((int) $c['id'], $selected, true) ? 'active' : '' ?>"
                href="<?= e(url('modules/materials/index.php?category[]=' . (int) $c['id'])) ?>"
            >
                <?= $icons[$c['name']] ?? '♻️' ?>
                <?= e($c['name']) ?>
            </a>

        <?php endforeach; ?>

    </div>

    <!-- Input pencarian materi -->
    <div class="search-box">

        <input
            type="search"
            placeholder="Cari materi..."
            data-search=".material-item"
        >

    </div>

</div>

<!-- ======================================
     DAFTAR KARTU MATERI
======================================= -->

<div class="content-grid">

    <?php foreach ($rows as $r): ?>

        <article class="content-card material-item">

            <!-- Gambar materi berdasarkan kategori -->
            <img
                class="cover"
                src="<?= e(
                    url(
                        'assets/images/' .
                        ($materialArt[$r['category'] ?? ''] ?? 'materials.jpg')
                    )
                ) ?>"
                alt=""
            >

            <!-- Kategori materi -->
            <span class="tag">
                <?= e($r['category'] ?? 'Umum') ?>
            </span>

            <!-- Judul materi -->
            <h3>
                <?= e($r['title']) ?>
            </h3>

            <!-- Ringkasan materi -->
            <p>
                <?= e(
                    $r['summary'] ??
                    mb_substr(strip_tags($r['content']), 0, 140) . '…'
                ) ?>
            </p>

            <!-- Informasi jumlah bagian dan durasi -->
            <div class="meta">

                ▤
                <?= max(
                    1,
                    (int) ceil(
                        mb_strlen(strip_tags($r['content'])) / 1800
                    )
                ) ?>
                bagian

                &nbsp; ◷

                <?= e($r['duration'] ?? 5) ?>
                menit

            </div>

            <!-- Tombol menuju halaman detail materi -->
            <a
                class="btn btn-primary"
                href="<?= e(
                    url('modules/materials/detail.php?id=' . (int) $r['id'])
                ) ?>"
            >
                Baca Materi →
            </a>

        </article>

    <?php endforeach; ?>

</div>

<!-- ======================================
     KONDISI JIKA MATERI TIDAK DITEMUKAN
======================================= -->

<?php if (!$rows): ?>

    <div class="empty-state">

        <strong>
            Materi belum ditemukan
        </strong>

        Coba pilih kategori lain atau lihat semua materi.

    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
