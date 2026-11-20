
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// ==========================================
// MENGAMBIL DATA VIDEO
// ==========================================

$stmt = $pdo->prepare(
    'SELECT
        v.*,
        c.name AS category
    FROM videos v
    LEFT JOIN waste_categories c ON c.id = v.category_id
    WHERE v.id = ?
    AND v.status = \'published\''
);

$stmt->execute([
    (int) ($_GET['id'] ?? 0)
]);

$v = $stmt->fetch();

// Memeriksa apakah video tersedia.
if (!$v) {
    exit('Video tidak ditemukan.');
}

// ==========================================
// MENYIAPKAN PEMUTAR VIDEO
// ==========================================

// Mengubah tautan YouTube menjadi URL embed.
$embed = youtube_embed($v['video_url']);

// Mengatur judul halaman.
$page_title = $v['title'];

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     HEADER DAN INFORMASI VIDEO
======================================= -->

<div class="page-heading">

    <a
        class="muted"
        href="<?= e(url('modules/videos/index.php')) ?>"
    >
        ← Kembali ke video
    </a>

    <h1>
        <?= e($v['title']) ?>
    </h1>

    <p>

        <span class="tag">
            <?= e($v['category'] ?? 'Umum') ?>
        </span>

        ·

        <?= e($v['duration'] ?? '') ?>

    </p>

</div>

<!-- ======================================
     LAYOUT DETAIL VIDEO
======================================= -->

<div class="detail-layout">

    <!-- ==================================
         PEMUTAR DAN DESKRIPSI VIDEO
    =================================== -->

    <article class="detail-card">

        <?php if ($embed): ?>

            <!-- Menampilkan video YouTube -->
            <iframe
                class="video-frame"
                src="<?= e($embed) ?>"
                title="<?= e($v['title']) ?>"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
            ></iframe>

        <?php else: ?>

            <!-- Ditampilkan jika tautan tidak mendukung embed -->
            <div class="alert alert-error">

                Tautan video tidak mendukung pemutar YouTube.

                <a
                    href="<?= e($v['video_url']) ?>"
                    target="_blank"
                    rel="noopener"
                >
                    Buka sumber video
                </a>.

            </div>

        <?php endif; ?>

        <!-- Judul video -->
        <h2>
            <?= e($v['title']) ?>
        </h2>

        <!-- Deskripsi video -->
        <p>
            <?= e($v['description']) ?>
        </p>

        <!-- Informasi sumber video -->
        <p class="meta">

            Sumber:
            <?= e($v['source_name'] ?? 'Sumber video') ?>

        </p>

    </article>

    <!-- ==================================
         SIDEBAR MATERI EDUKASI
    =================================== -->

    <aside class="detail-card">

        <h3>
            Belajar lebih lanjut
        </h3>

        <p>
            Setelah menonton, kunjungi materi edukasi terkait
            untuk memahami konsep dan langkah praktik secara
            lebih lengkap.
        </p>

        <a
            class="btn btn-primary"
            href="<?= e(url('modules/materials/index.php')) ?>"
        >
            Jelajahi Materi
        </a>

    </aside>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
