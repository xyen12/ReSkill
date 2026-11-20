
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL DATA KATEGORI LIMBAH
// ==========================================

$rows = $pdo->query(
    'SELECT * FROM waste_categories ORDER BY id'
)->fetchAll();

// Menentukan ilustrasi untuk setiap kategori.
$art = [
    'Organik'   => 'category-organik.png',
    'Anorganik' => 'category-anorganik.png',
    'B3'        => 'category-b3.png',
    'Residu'    => 'category-residu.png'
];

// Menentukan warna tampilan setiap kategori.
$tone = [
    'Organik'   => 'organic',
    'Anorganik' => 'inorganic',
    'B3'        => 'hazard',
    'Residu'    => 'residue'
];

// Mengatur judul halaman.
$page_title = 'Kategori Limbah';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     JUDUL DAN ILUSTRASI HALAMAN
======================================= -->

<section class="page-heading heading-hero">

    <div>

        <span class="eyebrow">
            🍃 Kategori Limbah
        </span>

        <h1>Pilih Kategori Limbah</h1>

        <p>
            Kenali berbagai jenis limbah dan pilih satu atau lebih
            kategori untuk mempelajari cara pengelolaannya.
        </p>

    </div>

    <!-- Ilustrasi halaman kategori -->
    <img
        src="<?= e(url('assets/images/category.jpg')) ?>"
        alt="Ilustrasi kategori limbah"
    >

</section>

<!-- ======================================
     FORM PEMILIHAN KATEGORI
======================================= -->

<form
    method="get"
    action="<?= e(url('modules/materials/index.php')) ?>"
    id="categoryForm"
>

    <div class="category-grid">

        <!-- Menampilkan seluruh kategori dari database -->
        <?php foreach ($rows as $r): ?>

            <?php $name = $r['name']; ?>

            <label class="category-card <?= e($tone[$name] ?? 'organic') ?>">

                <!-- Checkbox pemilihan kategori -->
                <input
                    type="checkbox"
                    name="category[]"
                    value="<?= (int) $r['id'] ?>"
                    class="category-choice"
                >

                <!-- Ilustrasi kategori -->
                <div class="category-art" aria-hidden="true">

                    <img
                        src="<?= e(url(
                            'assets/images/' .
                            ($art[$name] ?? 'category-organik.png')
                        )) ?>"
                        alt=""
                    >

                </div>

                <!-- Informasi kategori -->
                <h3>
                    <?= e($name) ?>
                </h3>

                <p>
                    <?= e($r['description']) ?>
                </p>

                <span class="tag">
                    Pilih kategori
                </span>

            </label>

        <?php endforeach; ?>

    </div>

    <!-- ==================================
         BAR INFORMASI KATEGORI TERPILIH
    =================================== -->

    <div class="selection-bar">

        <div class="selection-count">

            ☷ Kategori Dipilih:

            <span class="count-pill" id="selectedCount">
                0
            </span>

        </div>

        <!-- Tombol menuju halaman materi -->
        <button
            class="btn btn-primary"
            type="submit"
            id="viewMaterials"
            disabled
        >
            Lihat Materi →
        </button>

    </div>

</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
