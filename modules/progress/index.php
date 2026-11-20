
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL PROGRES MATERI
// ==========================================

$stmt = $pdo->prepare(
    'SELECT
        lp.*,
        m.title
    FROM learning_progress lp
    JOIN educational_materials m ON m.id = lp.material_id
    WHERE lp.user_id = ?
    ORDER BY lp.updated_at DESC'
);

$stmt->execute([$_SESSION['user_id']]);

$materials = $stmt->fetchAll();

// ==========================================
// MENGAMBIL RIWAYAT KUIS
// ==========================================

$stmt = $pdo->prepare(
    'SELECT
        r.*,
        q.title
    FROM quiz_results r
    JOIN quizzes q ON q.id = r.quiz_id
    WHERE r.user_id = ?
    ORDER BY r.completed_at DESC'
);

$stmt->execute([$_SESSION['user_id']]);

$results = $stmt->fetchAll();

// Mengatur judul halaman.
$page_title = 'Progres Pembelajaran';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     HEADER HALAMAN
======================================= -->

<section class="page-heading">

    <span class="eyebrow">
        PERKEMBANGANMU
    </span>

    <h1>
        Progres Pembelajaran
    </h1>

    <p>
        Pantau materi yang telah dipelajari dan hasil evaluasimu.
    </p>

</section>

<!-- ======================================
     LAYOUT PROGRES PEMBELAJARAN
======================================= -->

<div class="detail-layout">

    <!-- ==================================
         DAFTAR MATERI YANG DIPELAJARI
    =================================== -->

    <section class="detail-card">

        <h2>
            Materi yang Dipelajari
        </h2>

        <?php foreach ($materials as $m): ?>

            <p>

                <strong>
                    <?= e($m['title']) ?>
                </strong>

                <span class="tag">
                    <?= $m['is_completed'] ? 'Selesai' : 'Dalam proses' ?>
                </span>

            </p>

        <?php endforeach; ?>

        <!-- Ditampilkan jika belum ada progres materi -->
        <?php if (!$materials): ?>

            <p class="muted">
                Belum ada materi yang diselesaikan.
            </p>

        <?php endif; ?>

    </section>

    <!-- ==================================
         RIWAYAT HASIL KUIS
    =================================== -->

    <section class="detail-card">

        <h2>
            Riwayat Kuis
        </h2>

        <?php foreach ($results as $r): ?>

            <p>

                <strong>
                    <?= e($r['title']) ?>
                </strong>

                <br>

                Nilai:
                <?= e($r['score']) ?>

                ·

                <?= date(
                    'd M Y',
                    strtotime($r['completed_at'])
                ) ?>

            </p>

        <?php endforeach; ?>

        <!-- Ditampilkan jika belum ada riwayat kuis -->
        <?php if (!$results): ?>

            <p class="muted">
                Belum ada kuis yang dikerjakan.
            </p>

        <?php endif; ?>

    </section>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
