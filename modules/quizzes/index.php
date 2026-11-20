
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL DAFTAR KUIS
// ==========================================

// Mengambil seluruh kuis yang sudah dipublikasikan
// beserta jumlah pertanyaan masing-masing kuis.
$rows = $pdo->query(
    "SELECT
        q.*,
        (
            SELECT COUNT(*)
            FROM questions x
            WHERE x.quiz_id = q.id
        ) AS total
    FROM quizzes q
    WHERE q.status = 'published'
    ORDER BY q.id"
)->fetchAll();

// ==========================================
// MENGAMBIL STATISTIK KUIS
// ==========================================

// Menghitung jumlah kuis yang dikerjakan,
// total skor, dan rata-rata skor pengguna.
$stmt = $pdo->prepare(
    'SELECT
        COUNT(*),
        COALESCE(SUM(score), 0),
        COALESCE(AVG(score), 0)
    FROM quiz_results
    WHERE user_id = ?'
);

$stmt->execute([$_SESSION['user_id']]);

[$done, $sum, $avg] = $stmt->fetch(PDO::FETCH_NUM);

// ==========================================
// MENGAMBIL RIWAYAT KUIS TERAKHIR
// ==========================================

// Mengambil maksimal empat hasil kuis terbaru.
$recent = $pdo->prepare(
    'SELECT
        r.*,
        q.title
    FROM quiz_results r
    JOIN quizzes q ON q.id = r.quiz_id
    WHERE r.user_id = ?
    ORDER BY r.completed_at DESC
    LIMIT 4'
);

$recent->execute([$_SESSION['user_id']]);

$history = $recent->fetchAll();

// ==========================================
// MENENTUKAN ILUSTRASI KUIS
// ==========================================

// Memilih ilustrasi, kategori, dan tingkat
// berdasarkan topik pada judul kuis.
function quiz_art(string $title): array
{
    $t = mb_strtolower($title);

    if (str_contains($t, '3r')) {
        return ['quiz-3r-reference.png', 'Umum', 'Sedang'];
    }

    if (str_contains($t, 'organik')) {
        return ['quiz-organic-reference.png', 'Organik', 'Mudah'];
    }

    if (str_contains($t, 'anorganik')) {
        return ['quiz-inorganic-reference.png', 'Anorganik', 'Sedang'];
    }

    if (str_contains($t, 'b3')) {
        return ['quiz-b3-reference.png', 'B3', 'Sedang'];
    }

    if (str_contains($t, 'residu')) {
        return ['quiz-residue-reference.png', 'Residu', 'Mudah'];
    }

    return ['quiz-sorting-reference.png', 'Umum', 'Dasar'];
}

// Mengatur judul halaman.
$page_title = 'Kuis Pembelajaran';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     HERO KUIS PEMBELAJARAN
======================================= -->

<section class="quiz-hero">

    <div class="quiz-hero-copy">

        <span class="eyebrow">
            ☑ Kuis Pembelajaran
        </span>

        <h1>
            Kuis Pembelajaran
        </h1>

        <p>
            Uji pemahamanmu tentang pengelolaan limbah
            melalui kumpulan kuis interaktif dan menyenangkan.
        </p>

    </div>

    <img
        src="<?= e(url('assets/images/quiz-hero-reference.png')) ?>"
        alt="Ilustrasi kuis pengelolaan limbah"
    >

</section>

<!-- ======================================
     LAYOUT DAFTAR KUIS DAN SIDEBAR
======================================= -->

<div class="quiz-layout quiz-page-layout">

    <section class="quiz-main-column">

        <!-- ==================================
             FILTER KATEGORI KUIS
        =================================== -->

        <div
            class="filter-pills quiz-filters"
            aria-label="Filter kategori kuis"
        >

            <button
                class="filter-pill active"
                type="button"
                data-quiz-filter=""
            >
                ▦ Semua
            </button>

            <button
                class="filter-pill"
                type="button"
                data-quiz-filter="Organik"
            >
                🍃 Organik
            </button>

            <button
                class="filter-pill"
                type="button"
                data-quiz-filter="Anorganik"
            >
                ♻ Anorganik
            </button>

            <button
                class="filter-pill"
                type="button"
                data-quiz-filter="B3"
            >
                ⚠ B3
            </button>

            <button
                class="filter-pill"
                type="button"
                data-quiz-filter="Residu"
            >
                ▤ Residu
            </button>

        </div>

        <!-- ==================================
             DAFTAR KARTU KUIS
        =================================== -->

        <div class="quiz-card-grid">

            <?php foreach ($rows as $r): ?>

                <?php
                // Menentukan ilustrasi dan kategori kuis.
                [$art, $category, $level] = quiz_art($r['title']);
                ?>

                <article
                    class="quiz-learning-card"
                    data-quiz-card
                    data-cat="<?= e($category) ?>"
                >

                    <!-- Ilustrasi kuis -->
                    <div class="quiz-card-art">

                        <img
                            src="<?= e(url('assets/images/' . $art)) ?>"
                            alt="Ilustrasi <?= e($category) ?>"
                        >

                    </div>

                    <!-- Informasi kuis -->
                    <div class="quiz-card-content">

                        <span class="tag <?= $category === 'Anorganik' ? 'blue' : ($category === 'B3' ? 'red' : ($category === 'Residu' ? 'purple' : '')) ?>">

                            <?= e($category) ?>

                        </span>

                        <h2>
                            <?= e($r['title']) ?>
                        </h2>

                        <p>
                            <?= e($r['description']) ?>
                        </p>

                        <!-- Informasi tambahan dan tombol -->
                        <div class="quiz-card-bottom">

                            <div class="quiz-card-meta">

                                <span>
                                    ◷ ±
                                    <?= max(5, (int) $r['total']) ?>
                                    menit
                                </span>

                                <span>
                                    ▤
                                    <?= (int) $r['total'] ?>
                                    soal
                                </span>

                                <span class="difficulty <?= strtolower($level) ?>">
                                    <?= e($level) ?>
                                </span>

                            </div>

                            <a
                                class="btn btn-primary"
                                href="<?= e(url('modules/quizzes/detail.php?id=' . (int) $r['id'])) ?>"
                            >
                                Mulai Kuis
                                <span>→</span>
                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

        <!-- Pesan jika belum ada kuis -->
        <?php if (!$rows): ?>

            <div class="empty-state">

                <strong>
                    Belum ada kuis tersedia
                </strong>

                Admin akan menambahkan kuis pembelajaran di halaman ini.

            </div>

        <?php endif; ?>

    </section>

    <!-- ======================================
         SIDEBAR STATISTIK DAN RIWAYAT
    ======================================= -->

    <aside class="quiz-sidebar">

        <!-- Statistik kuis -->
        <section class="side-card quiz-stat-panel">

            <h3>
                ▥ Statistik Kuis
            </h3>

            <div class="quiz-stat-grid">

                <div class="quiz-stat-box green">

                    <span>✓</span>

                    <strong>
                        <?= (int) $done ?>
                    </strong>

                    <small>
                        Kuis Selesai
                    </small>

                </div>

                <div class="quiz-stat-box yellow">

                    <span>🏆</span>

                    <strong>
                        <?= (int) $sum ?>
                    </strong>

                    <small>
                        Skor Total
                    </small>

                </div>

                <div class="quiz-stat-box purple">

                    <span>▥</span>

                    <strong>
                        <?= round((float) $avg) ?>%
                    </strong>

                    <small>
                        Rata-rata Nilai
                    </small>

                </div>

            </div>

        </section>

        <!-- Riwayat kuis terakhir -->
        <section class="side-card quiz-history-panel">

            <div class="side-heading">

                <h3>
                    ◷ Riwayat Kuis Terakhir
                </h3>

                <a href="<?= e(url('modules/progress/index.php')) ?>">
                    Lihat progres →
                </a>

            </div>

            <?php foreach ($history as $h): ?>

                <div class="quiz-history-item">

                    <span class="history-leaf">
                        ✓
                    </span>

                    <div>

                        <strong>
                            <?= e($h['title']) ?>
                        </strong>

                        <small>
                            <?= date(
                                'd M Y',
                                strtotime($h['completed_at'])
                            ) ?>
                        </small>

                    </div>

                    <span class="history-score">
                        <?= round((float) $h['score']) ?>%
                    </span>

                </div>

            <?php endforeach; ?>

            <!-- Pesan jika belum ada riwayat -->
            <?php if (!$history): ?>

                <p class="muted">
                    Belum ada riwayat kuis. Yuk mulai kuis pertamamu!
                </p>

            <?php endif; ?>

        </section>

    </aside>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
