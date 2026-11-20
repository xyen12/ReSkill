
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// Mengambil ID materi dari URL.
$id = (int) ($_GET['id'] ?? 0);

// Mengambil materi yang berstatus published.
$stmt = $pdo->prepare(
    "SELECT
        m.*,
        c.name AS category
    FROM educational_materials m
    LEFT JOIN waste_categories c ON c.id = m.category_id
    WHERE m.id = ?
    AND m.status = 'published'"
);

$stmt->execute([$id]);
$m = $stmt->fetch();

// Menampilkan halaman 404 jika materi tidak ditemukan.
if (!$m) {
    http_response_code(404);
    exit('Materi tidak ditemukan.');
}

// ==========================================
// PROSES MENANDAI MATERI SELESAI
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Menyimpan progres belajar pengguna.
    // Jika data sudah ada, status selesai diperbarui menjadi 1.
    $stmt = $pdo->prepare(
        'INSERT INTO learning_progress
            (user_id, material_id, is_completed)
         VALUES (?, ?, 1)
         ON DUPLICATE KEY UPDATE is_completed = 1'
    );

    $stmt->execute([
        $_SESSION['user_id'],
        $id
    ]);

    // Menampilkan notifikasi berhasil.
    set_flash(
        'success',
        'Materi berhasil ditandai selesai.'
    );

    // Memuat ulang halaman detail materi.
    redirect('modules/materials/detail.php?id=' . $id);
}

// ==========================================
// PERSIAPAN KONTEN MATERI
// ==========================================

// Mengambil daftar referensi materi.
$refs = $m['references_text'] ?? '';

// Memisahkan konten berdasarkan heading level 2 (##).
$parts = preg_split(
    '/\n(?=## )/',
    trim($m['content'])
);

// Mengatur judul halaman.
$page_title = $m['title'];

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     HEADER DAN NAVIGASI MATERI
======================================= -->

<div class="page-heading">

    <a
        class="muted"
        href="<?= e(url('modules/materials/index.php')) ?>"
    >
        ← Kembali ke Materi Edukasi
    </a>

    <p>
        <span class="tag">
            <?= e($m['category'] ?? 'Umum') ?>
        </span>
        · Materi Edukasi
    </p>

</div>

<!-- ======================================
     LAYOUT DETAIL MATERI
======================================= -->

<div class="detail-layout">

    <div>

        <!-- ==================================
             INFORMASI UTAMA MATERI
        =================================== -->

        <article
            class="detail-card"
            style="margin-bottom: 18px"
        >

            <div class="heading-hero">

                <div>

                    <h1 style="font-size: 34px; margin: 8px 0">
                        <?= e($m['title']) ?>
                    </h1>

                    <p>
                        <?= e($m['summary']) ?>
                    </p>

                    <div class="meta">

                        ▤ Materi pembelajaran

                        &nbsp; ◷ ±
                        <?= e($m['duration']) ?>
                        menit

                        &nbsp; ♧
                        <?= e($m['level']) ?>

                    </div>

                </div>

                <img
                    src="<?= e(url('assets/images/materials.jpg')) ?>"
                    alt=""
                >

            </div>

        </article>

        <!-- ==================================
             ISI MATERI PEMBELAJARAN
        =================================== -->

        <article class="detail-card article-content">

            <?php foreach ($parts as $part): ?>

                <?php

                // Memisahkan bagian materi menjadi beberapa baris.
                $lines = explode("\n", trim($part));

                // Mengambil baris pertama sebagai judul bagian.
                $heading = trim(array_shift($lines));

                // Menampilkan judul bagian jika tersedia.
                if ($heading !== '') {
                    echo '<h2>' . e(
                        preg_replace('/^##\s*/', '', $heading)
                    ) . '</h2>';
                }

                // Penanda apakah daftar sedang dibuka.
                $list = false;

                ?>

                <?php foreach ($lines as $line): ?>

                    <?php

                    $line = trim($line);

                    // Melewati baris kosong.
                    if ($line === '') {
                        continue;
                    }

                    // Memeriksa apakah baris merupakan daftar menggunakan - atau *.
                    if (preg_match('/^[-*]\s+(.+)/', $line, $mm)) {

                        // Membuka elemen ul jika belum terbuka.
                        if (!$list) {
                            echo '<ul>';
                            $list = true;
                        }

                        // Menampilkan isi daftar.
                        echo '<li>' . e($mm[1]) . '</li>';

                        continue;
                    }

                    // Menutup daftar jika baris berikutnya bukan daftar.
                    if ($list) {
                        echo '</ul>';
                        $list = false;
                    }

                    // Memeriksa apakah baris merupakan daftar bernomor.
                    if (preg_match('/^\d+\.\s+(.+)/', $line, $mm)) {

                        echo '<p>• ' . e($mm[1]) . '</p>';

                    } else {

                        // Menampilkan baris sebagai paragraf biasa.
                        echo '<p>' . e($line) . '</p>';
                    }

                    ?>

                <?php endforeach; ?>

                <?php

                // Menutup daftar jika masih terbuka di akhir bagian.
                if ($list) {
                    echo '</ul>';
                }

                ?>

            <?php endforeach; ?>

            <!-- ==================================
                 DAFTAR PUSTAKA DAN REFERENSI
            =================================== -->

            <?php if ($refs): ?>

                <hr>

                <h2>
                    Daftar Pustaka dan Referensi
                </h2>

                <div class="reference-list">
                    <?= nl2br(e($refs)) ?>
                </div>

            <?php endif; ?>

        </article>

        <!-- ==================================
             TOMBOL AKSI MATERI
        =================================== -->

        <form
            method="post"
            class="inline-actions"
        >

            <button
                class="btn btn-primary"
                type="submit"
            >
                ✓ Tandai Materi Selesai
            </button>

            <a
                class="btn btn-outline"
                href="<?= e(url('modules/materials/index.php')) ?>"
            >
                Materi Lainnya
            </a>

        </form>

    </div>

    <!-- ======================================
         SIDEBAR INFORMASI MATERI
    ======================================= -->

    <aside>

        <!-- Daftar isi materi -->
        <div class="side-card">

            <h3>Daftar Isi</h3>

            <?php foreach ($parts as $i => $part): ?>

                <?php

                // Mengambil judul dari setiap bagian materi.
                $heading = trim(strtok($part, "\n"));

                ?>

                <p>
                    <?= ($i + 1) ?>.
                    <?= e(
                        preg_replace('/^##\s*/', '', $heading)
                    ) ?>
                </p>

            <?php endforeach; ?>

        </div>

        <!-- Informasi tambahan materi -->
        <div
            class="side-card"
            style="margin-top: 16px"
        >

            <h3>Informasi Materi</h3>

            <p>
                Kategori:
                <strong>
                    <?= e($m['category'] ?? 'Umum') ?>
                </strong>
            </p>

            <p>
                Tingkat:
                <strong>
                    <?= e($m['level']) ?>
                </strong>
            </p>

            <p>
                Estimasi:
                <strong>
                    <?= e($m['duration']) ?> menit
                </strong>
            </p>

        </div>

    </aside>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
