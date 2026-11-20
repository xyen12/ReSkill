
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// Mengambil data pengguna yang sedang login.
$user = current_user();

// Mengambil ID kuis dari URL.
$quizId = (int) ($_GET['id'] ?? 0);

// ==========================================
// MENGAMBIL DATA KUIS
// ==========================================

$stmt = $pdo->prepare(
    "SELECT *
    FROM quizzes
    WHERE id = ?
    AND status = 'published'"
);

$stmt->execute([$quizId]);

$quiz = $stmt->fetch();

// Memeriksa apakah kuis tersedia.
if (!$quiz) {
    http_response_code(404);
    exit('Kuis tidak ditemukan atau belum dipublikasikan.');
}

// ==========================================
// MENGAMBIL DAFTAR PERTANYAAN
// ==========================================

$stmt = $pdo->prepare(
    'SELECT *
    FROM questions
    WHERE quiz_id = ?
    ORDER BY id'
);

$stmt->execute([$quizId]);

$questions = $stmt->fetchAll();

// Memastikan kuis memiliki pertanyaan.
if (!$questions) {
    exit('Kuis ini belum memiliki soal.');
}

// ==========================================
// PROSES JAWABAN KUIS
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil jawaban yang dikirim pengguna.
    $answers = $_POST['answer'] ?? [];

    $correct = 0;
    $savedAnswers = [];

    // Memeriksa jawaban setiap pertanyaan.
    foreach ($questions as $question) {

        // Mengambil dan mengubah jawaban menjadi huruf kapital.
        $answer = strtoupper(
            trim(
                (string) ($answers[$question['id']] ?? '')
            )
        );

        // Memastikan jawaban hanya A, B, C, atau D.
        if (!in_array($answer, ['A', 'B', 'C', 'D'], true)) {

            set_flash(
                'error',
                'Pastikan semua pertanyaan sudah dijawab.'
            );

            redirect('modules/quizzes/play.php?id=' . $quizId);
        }

        // Menyimpan jawaban pengguna.
        $savedAnswers[$question['id']] = $answer;

        // Membandingkan jawaban dengan kunci jawaban.
        if ($answer === $question['correct_option']) {
            $correct++;
        }
    }

    // ======================================
    // MENGHITUNG NILAI AKHIR
    // ======================================

    // Rumus:
    // Nilai = (Jumlah jawaban benar / Total soal) x 100
    $score = round(
        ($correct / count($questions)) * 100,
        2
    );

    // ======================================
    // MENYIMPAN HASIL KUIS
    // ======================================

    try {

        // Menyimpan hasil kuis ke database.
        // ID pengguna diambil dari akun yang sedang login.
        $insert = $pdo->prepare(
            'INSERT INTO quiz_results
            (
                user_id,
                quiz_id,
                score,
                correct_count,
                total_questions,
                answers_json
            )
            VALUES (?, ?, ?, ?, ?, ?)'
        );

        $insert->execute([
            (int) $user['id'],
            $quizId,
            $score,
            $correct,
            count($questions),
            json_encode($savedAnswers, JSON_UNESCAPED_UNICODE)
        ]);

        // Mengarahkan pengguna ke halaman hasil kuis.
        redirect(
            'modules/quizzes/result.php?id=' .
            (int) $pdo->lastInsertId()
        );

    } catch (PDOException $error) {

        // Mencatat kesalahan database.
        error_log(
            'ReSkill quiz result error: ' . $error->getMessage()
        );

        set_flash(
            'error',
            'Hasil kuis belum dapat disimpan. Sesi akun atau struktur database perlu diperiksa. Silakan masuk kembali.'
        );

        redirect('modules/quizzes/index.php');
    }
}

// Mengatur judul halaman.
$page_title = 'Kerjakan Kuis';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     HEADER PENGERJAAN KUIS
======================================= -->

<div class="quiz-work-heading">

    <a
        class="back-link"
        href="<?= e(url('modules/quizzes/detail.php?id=' . $quizId)) ?>"
    >
        ← Kembali ke petunjuk kuis
    </a>

    <div class="quiz-work-title">

        <div>

            <span class="eyebrow">
                KUIS PEMBELAJARAN
            </span>

            <h1>
                <?= e($quiz['title']) ?>
            </h1>

            <p>
                Pilih satu jawaban yang paling tepat untuk setiap pertanyaan.
            </p>

        </div>

        <!-- Menampilkan jumlah soal -->
        <div class="quiz-total">

            <strong>
                <?= count($questions) ?>
            </strong>

            <span>
                Jumlah soal
            </span>

        </div>

    </div>

</div>

<!-- ======================================
     FORM PENGERJAAN KUIS
======================================= -->

<form
    method="post"
    class="quiz-question-list"
>

    <?php foreach ($questions as $index => $question): ?>

        <section class="quiz-question-card">

            <!-- Informasi nomor pertanyaan -->
            <div class="quiz-question-top">

                <span class="tag">
                    Soal <?= ($index + 1) ?>
                </span>

                <span class="muted">
                    Pertanyaan
                    <?= ($index + 1) ?>
                    dari
                    <?= count($questions) ?>
                </span>

            </div>

            <!-- Isi pertanyaan -->
            <h2>
                <?= e($question['question']) ?>
            </h2>

            <!-- ==================================
                 PILIHAN JAWABAN
            =================================== -->

            <div class="quiz-answers">

                <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>

                    <?php
                    // Menentukan nama kolom berdasarkan pilihan jawaban.
                    $field = 'option_' . strtolower($option);
                    ?>

                    <label class="quiz-answer">

                        <input
                            type="radio"
                            name="answer[<?= $question['id'] ?>]"
                            value="<?= $option ?>"
                            required
                        >

                        <span class="answer-letter">
                            <?= $option ?>
                        </span>

                        <span>
                            <?= e($question[$field]) ?>
                        </span>

                    </label>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endforeach; ?>

    <!-- ======================================
         TOMBOL KIRIM JAWABAN
    ======================================= -->

    <div class="quiz-submit-row">

        <p class="muted">
            Periksa kembali jawabanmu sebelum mengirim.
        </p>

        <button
            class="btn btn-primary"
            type="submit"
        >
            Kirim Jawaban
            <span aria-hidden="true">→</span>
        </button>

    </div>

</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
