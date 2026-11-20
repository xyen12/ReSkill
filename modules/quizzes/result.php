
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL HASIL KUIS
// ==========================================

// Mengambil hasil kuis berdasarkan ID hasil
// dan ID pengguna yang sedang login.
$stmt = $pdo->prepare(
    'SELECT
        r.*,
        q.title
    FROM quiz_results r
    JOIN quizzes q ON q.id = r.quiz_id
    WHERE r.id = ?
    AND r.user_id = ?'
);

$stmt->execute([
    (int) ($_GET['id'] ?? 0),
    $_SESSION['user_id']
]);

$r = $stmt->fetch();

// Memastikan hasil kuis ditemukan dan dimiliki pengguna.
if (!$r) {
    exit('Hasil kuis tidak ditemukan.');
}

// ==========================================
// MENGAMBIL JAWABAN PENGGUNA
// ==========================================

// Mengubah data JSON menjadi array jawaban.
$answers = json_decode(
    $r['answers_json'] ?? '{}',
    true
) ?: [];

// ==========================================
// MENGAMBIL PERTANYAAN DAN PEMBAHASAN
// ==========================================

$stmt = $pdo->prepare(
    'SELECT *
    FROM questions
    WHERE quiz_id = ?
    ORDER BY id'
);

$stmt->execute([$r['quiz_id']]);

$questions = $stmt->fetchAll();

// Mengatur judul halaman.
$page_title = 'Hasil Kuis';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     HEADER HASIL EVALUASI
======================================= -->

<section class="page-heading">

    <span class="eyebrow">
        HASIL EVALUASI
    </span>

    <h1>
        Kuis Selesai!
    </h1>

    <p>
        <?= e($r['title']) ?>
    </p>

</section>

<!-- ======================================
     KARTU HASIL KUIS
======================================= -->

<div
    class="detail-card"
    style="max-width: 850px; margin: auto"
>

    <!-- ==================================
         NILAI AKHIR
    =================================== -->

    <div style="text-align: center">

        <div style="font-size: 64px">
            🏅
        </div>

        <h2
            style="font-size: 48px; color: var(--green); margin: 0"
        >
            <?= e($r['score']) ?>
        </h2>

        <p>
            Nilai akhir
        </p>

        <p>
            <?= (int) $r['correct_count'] ?>
            dari
            <?= (int) $r['total_questions'] ?>
            jawaban benar
        </p>

    </div>

    <!-- ==================================
         PEMBAHASAN JAWABAN
    =================================== -->

    <h2>
        Pembahasan Jawaban
    </h2>

    <?php foreach ($questions as $i => $q): ?>

        <?php
        // Mengambil jawaban pengguna berdasarkan ID pertanyaan.
        $given = $answers[$q['id']] ?? '-';
        ?>

        <div
            style="padding: 12px 0; border-top: 1px solid var(--line)"
        >

            <!-- Pertanyaan -->
            <strong>
                <?= ($i + 1) ?>.
                <?= e($q['question']) ?>
            </strong>

            <!-- Jawaban pengguna dan kunci jawaban -->
            <p>

                Jawaban kamu:
                <?= e($given) ?>

                ·

                Jawaban benar:
                <?= e($q['correct_option']) ?>

            </p>

            <!-- Penjelasan jawaban -->
            <p class="muted">
                <?= e(
                    $q['explanation'] ??
                    'Periksa kembali materi terkait untuk memahami jawaban.'
                ) ?>
            </p>

        </div>

    <?php endforeach; ?>

    <!-- Tombol kembali ke daftar kuis -->
    <a
        class="btn btn-primary"
        href="<?= e(url('modules/quizzes/index.php')) ?>"
    >
        Kembali ke Kuis
    </a>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
