
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL DATA KUIS
// ==========================================

// Mengambil data kuis berdasarkan ID dari URL.
// Hanya kuis dengan status published yang dapat diakses.
$stmt = $pdo->prepare(
    "SELECT
        q.*,
        (
            SELECT COUNT(*)
            FROM questions
            WHERE quiz_id = q.id
        ) AS total
    FROM quizzes q
    WHERE q.id = ?
    AND q.status = 'published'"
);

$stmt->execute([
    (int) ($_GET['id'] ?? 0)
]);

$q = $stmt->fetch();

// Memeriksa apakah kuis tersedia.
if (!$q) {
    exit('Kuis tidak ditemukan.');
}

// Mengatur judul halaman.
$page_title = 'Petunjuk Kuis';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     INFORMASI KUIS
======================================= -->

<section class="page-heading">

    <a
        class="muted"
        href="<?= e(url('modules/quizzes/index.php')) ?>"
    >
        ← Kembali ke daftar kuis
    </a>

    <h1>
        <?= e($q['title']) ?>
    </h1>

    <p>
        <?= e($q['description']) ?>
    </p>

</section>

<!-- ======================================
     PETUNJUK PENGERJAAN KUIS
======================================= -->

<div
    class="detail-card"
    style="max-width: 760px; margin: 0 auto"
>

    <h2>
        Petunjuk Pengerjaan
    </h2>

    <ul>

        <li>
            Kuis terdiri dari
            <?= (int) $q['total'] ?>
            pertanyaan pilihan ganda.
        </li>

        <li>
            Pilih satu jawaban yang paling tepat pada setiap soal.
        </li>

        <li>
            Nilai akan dihitung otomatis setelah jawaban dikirim.
        </li>

        <li>
            Kamu dapat melihat pembahasan setelah menyelesaikan kuis.
        </li>

    </ul>

    <!-- Tombol menuju halaman pengerjaan kuis -->
    <a
        class="btn btn-primary"
        href="<?= e(url('modules/quizzes/play.php?id=' . (int) $q['id'])) ?>"
    >
        Mulai Kuis
    </a>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
