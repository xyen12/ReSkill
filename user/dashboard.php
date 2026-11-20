
<?php

require_once __DIR__ . '/../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL STATISTIK PROGRES PENGGUNA
// ==========================================

// Menghitung jumlah materi yang sudah diselesaikan.
$stmt = $pdo->prepare(
    'SELECT COUNT(*)
    FROM learning_progress
    WHERE user_id = ?
    AND is_completed = 1'
);

$stmt->execute([$_SESSION['user_id']]);

$completed = (int) $stmt->fetchColumn();

// ==========================================
// MENGAMBIL JUMLAH MATERI TERSEDIA
// ==========================================

// Menghitung seluruh materi yang sudah dipublikasikan.
$materials = (int) $pdo->query(
    "SELECT COUNT(*)
    FROM educational_materials
    WHERE status = 'published'"
)->fetchColumn();

// ==========================================
// MENGAMBIL JUMLAH VIDEO TERSEDIA
// ==========================================

// Menghitung seluruh video yang sudah dipublikasikan.
$videos = (int) $pdo->query(
    "SELECT COUNT(*)
    FROM videos
    WHERE status = 'published'"
)->fetchColumn();

// Mengatur judul halaman.
$page_title = 'Dashboard';

require_once __DIR__ . '/../includes/header.php';

// ==========================================
// DAFTAR MENU PEMBELAJARAN
// ==========================================

// Menentukan judul, deskripsi, tautan, gambar,
// dan warna untuk setiap menu pembelajaran.
$menus = [
    [
        'Kategori Limbah',
        'Kenali berbagai jenis limbah dan cara penanganannya.',
        'modules/categories/index.php',
        'category-anorganik.png',
        'green'
    ],
    [
        'Materi Edukasi',
        'Baca materi lengkap tentang pengelolaan limbah, termasuk panduan praktik.',
        'modules/materials/index.php',
        'materials.jpg',
        'yellow'
    ],
    [
        'Video',
        'Belajar melalui video edukasi yang menarik.',
        'modules/videos/index.php',
        'video.jpg',
        'pink'
    ],
    [
        'Kuis',
        'Uji pemahamanmu tentang pengelolaan limbah.',
        'modules/quizzes/index.php',
        'quiz-hero-reference.png',
        'blue'
    ],
    [
        'Galeri',
        'Lihat dan bagikan hasil karya daur ulang.',
        'modules/gallery/index.php',
        'gallery.jpg',
        'purple'
    ]
];

?>

<!-- ======================================
     BAGIAN SAMBUTAN DAN STATISTIK
======================================= -->

<section class="welcome-grid">

    <div class="welcome-copy">

        <span class="eyebrow">
            🍃 RUANG BELAJAR
        </span>

        <h1>
            Halo,
            <span>
                <?= e($_SESSION['name']) ?>!
            </span>
        </h1>

        <p>
            Yuk, tambah pengetahuan dan mulai langkah kecil
            untuk mengelola limbah menjadi sesuatu yang bermanfaat.
        </p>

        <!-- ==================================
             KARTU STATISTIK
        =================================== -->

        <div class="stat-row">

            <!-- Statistik materi tersedia -->
            <article class="stat-card">

                <span
                    class="stat-symbol stat-icon stat-icon-material"
                    aria-hidden="true"
                >

                    <svg viewBox="0 0 48 48" role="img">

                        <path
                            d="M10 8.5h22a4 4 0 0 1 4 4v26H14a4 4 0 0 1-4-4v-26Z"
                            fill="#dff5df"
                        />

                        <path
                            d="M10 8.5h22a4 4 0 0 1 4 4v26H14a4 4 0 0 1-4-4v-26Z"
                            fill="none"
                            stroke="#078b45"
                            stroke-width="2.4"
                            stroke-linejoin="round"
                        />

                        <path
                            d="M15 16h15M15 22h15M15 28h10"
                            stroke="#078b45"
                            stroke-width="2.4"
                            stroke-linecap="round"
                        />

                        <path
                            d="M7 13v23a4 4 0 0 0 4 4h24"
                            fill="none"
                            stroke="#7bcf79"
                            stroke-width="2.4"
                            stroke-linecap="round"
                        />

                        <path
                            d="M29 7c3-3 7-2 9 0-2 4-5 6-9 5-1-2-1-3 0-5Z"
                            fill="#8edb76"
                        />

                    </svg>

                </span>

                <div>

                    <small>
                        Materi tersedia
                    </small>

                    <strong>
                        <?= $materials ?>
                    </strong>

                </div>

            </article>

            <!-- Statistik materi selesai -->
            <article class="stat-card">

                <span
                    class="stat-symbol stat-icon stat-icon-completed"
                    aria-hidden="true"
                >

                    <svg viewBox="0 0 48 48" role="img">

                        <circle
                            cx="24"
                            cy="24"
                            r="18"
                            fill="#e4f8e2"
                            stroke="#079447"
                            stroke-width="2.5"
                        />

                        <path
                            d="m15.5 24.5 5.7 5.7 11.8-13"
                            fill="none"
                            stroke="#079447"
                            stroke-width="3.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                        <path
                            d="M33.5 8.5c2.8-2.3 6.2-1.5 7.5.8-1.7 3.1-4.1 4.4-7.3 3.6-.9-1.4-.9-2.8-.2-4.4Z"
                            fill="#9bdc7c"
                        />

                    </svg>

                </span>

                <div>

                    <small>
                        Materi selesai
                    </small>

                    <strong>
                        <?= $completed ?>
                    </strong>

                </div>

            </article>

        </div>

    </div>

    <!-- Ilustrasi dashboard -->
    <div class="welcome-art">

        <img
            src="<?= e(url('assets/images/hero-recycling.jpg')) ?>"
            alt="Ilustrasi tempat sampah daur ulang"
        >

    </div>

</section>

<!-- ======================================
     MENU PEMBELAJARAN
======================================= -->

<section class="learning-section">

    <div class="section-title">

        <span></span>

        <div>

            <h2>
                Mulai Belajar
            </h2>

            <p>
                Pilih aktivitas yang ingin kamu pelajari hari ini.
            </p>

        </div>

    </div>

    <!-- Menampilkan seluruh menu pembelajaran -->
    <div class="learning-grid">

        <?php foreach ($menus as [$t, $d, $l, $im, $tone]): ?>

            <a
                class="learning-card <?= e($tone) ?>"
                href="<?= e(url($l)) ?>"
            >

                <!-- Gambar menu -->
                <div class="learning-image">

                    <img
                        src="<?= e(url('assets/images/' . $im)) ?>"
                        alt=""
                    >

                </div>

                <!-- Informasi menu -->
                <div class="learning-info">

                    <h3>
                        <?= e($t) ?>
                    </h3>

                    <p>
                        <?= e($d) ?>
                    </p>

                </div>

                <!-- Ikon navigasi -->
                <span class="card-arrow">
                    ↗
                </span>

            </a>

        <?php endforeach; ?>

    </div>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
