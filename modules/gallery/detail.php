
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// Mengambil ID karya dari URL.
$id = (int) ($_GET['id'] ?? 0);

// ==========================================
// MENGAMBIL DETAIL KARYA
// ==========================================

$stmt = $pdo->prepare(
    "SELECT
        g.*,
        u.name,
        c.name AS category
     FROM recycling_gallery g
     JOIN users u ON u.id = g.user_id
     LEFT JOIN waste_categories c ON c.id = g.category_id
     WHERE g.id = ?
       AND (g.status = 'approved' OR g.user_id = ?)"
);

$stmt->execute([$id, $_SESSION['user_id']]);

$gallery = $stmt->fetch();

// Menghentikan proses jika karya tidak ditemukan
// atau tidak dapat diakses oleh pengguna.
if (!$gallery) {
    exit('Karya tidak ditemukan.');
}

// ==========================================
// PROSES MEMBERIKAN LIKE
// ==========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['like'])
) {

    // Menyimpan like pengguna.
    // INSERT IGNORE mencegah duplikasi jika
    // tabel memiliki UNIQUE(user_id, gallery_id).
    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO gallery_likes (user_id, gallery_id)
         VALUES (?, ?)'
    );

    $stmt->execute([
        $_SESSION['user_id'],
        $id
    ]);

    // Memuat ulang halaman detail.
    redirect('modules/gallery/detail.php?id=' . $id);
}

// ==========================================
// MENGAMBIL FOTO KARYA
// ==========================================

// Mengambil foto tambahan dari galeri.
$photoQuery = $pdo->prepare(
    'SELECT photo
     FROM gallery_photos
     WHERE gallery_id = ?
     ORDER BY id'
);

$photoQuery->execute([$id]);

$extraPhotos = $photoQuery->fetchAll(PDO::FETCH_COLUMN);

// Menggabungkan foto utama dan foto tambahan.
$photos = array_merge(
    [$gallery['photo']],
    $extraPhotos
);

// ==========================================
// MENGHITUNG JUMLAH LIKE
// ==========================================

$likeQuery = $pdo->prepare(
    'SELECT COUNT(*)
     FROM gallery_likes
     WHERE gallery_id = ?'
);

$likeQuery->execute([$id]);

$likes = (int) $likeQuery->fetchColumn();

// ==========================================
// MENGAMBIL KOMENTAR
// ==========================================

$commentQuery = $pdo->prepare(
    'SELECT c.*, u.name
     FROM gallery_comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.gallery_id = ?
     ORDER BY c.created_at DESC'
);

$commentQuery->execute([$id]);

$comments = $commentQuery->fetchAll();

// ==========================================
// PROSES MENAMBAHKAN KOMENTAR
// ==========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['comment'])
) {

    // Mengambil dan membersihkan isi komentar.
    $comment = trim($_POST['comment']);

    // Menyimpan komentar jika tidak kosong.
    if ($comment) {

        $stmt = $pdo->prepare(
            'INSERT INTO gallery_comments
             (user_id, gallery_id, comment)
             VALUES (?, ?, ?)'
        );

        $stmt->execute([
            $_SESSION['user_id'],
            $id,
            $comment
        ]);
    }

    // Memuat ulang halaman setelah komentar dikirim.
    redirect('modules/gallery/detail.php?id=' . $id);
}

// Mengatur judul halaman.
$page_title = $gallery['title'];

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     JUDUL DAN NAVIGASI HALAMAN
======================================= -->

<div class="page-heading">

    <a
        class="muted"
        href="<?= e(url('modules/gallery/index.php')) ?>"
    >
        ← Kembali ke Galeri
    </a>

    <h1>Detail Karya</h1>

</div>

<!-- ======================================
     LAYOUT DETAIL KARYA DAN KOMENTAR
======================================= -->

<div class="detail-layout">

    <!-- ==================================
         INFORMASI DETAIL KARYA
    =================================== -->

    <article class="detail-card">

        <!-- Foto utama karya -->
        <img
            data-gallery-main
            class="gallery-main-photo"
            src="<?= e(url('uploads/gallery/' . $photos[0])) ?>"
            data-fallback="<?= e(url('assets/images/gallery.jpg')) ?>"
            alt="<?= e($gallery['title']) ?>"
        >

        <!-- Daftar thumbnail foto -->
        <div class="gallery-thumbs">

            <?php foreach ($photos as $index => $photo): ?>

                <button
                    type="button"
                    class="<?= !$index ? 'selected' : '' ?>"
                    data-gallery-thumb="<?= e(url('uploads/gallery/' . $photo)) ?>"
                >

                    <img
                        src="<?= e(url('uploads/gallery/' . $photo)) ?>"
                        data-fallback="<?= e(url('assets/images/gallery.jpg')) ?>"
                        alt="Foto karya <?= $index + 1 ?>"
                    >

                </button>

            <?php endforeach; ?>

        </div>

        <!-- Judul karya -->
        <h2>
            <?= e($gallery['title']) ?>
        </h2>

        <!-- Informasi kategori, pengunggah, dan tanggal -->
        <p>

            <span class="tag">
                <?= e($gallery['category'] ?? 'Umum') ?>
            </span>

            · Oleh <?= e($gallery['name']) ?>

            · <?= date('d M Y', strtotime($gallery['created_at'])) ?>

        </p>

        <!-- Deskripsi karya -->
        <p>
            <?= nl2br(e($gallery['description'])) ?>
        </p>

        <!-- Tombol like -->
        <form method="post">

            <button
                class="btn btn-outline"
                name="like"
                value="1"
                type="submit"
            >
                ♡ Suka (<?= $likes ?>)
            </button>

        </form>

        <hr>

        <!-- Bahan yang digunakan -->
        <h3>Bahan yang Digunakan</h3>

        <p>
            <?= nl2br(e($gallery['materials'] ?? 'Belum dicantumkan.')) ?>
        </p>

        <!-- Langkah pembuatan -->
        <h3>Langkah Pembuatan</h3>

        <p>
            <?= nl2br(e($gallery['steps'] ?? 'Belum dicantumkan.')) ?>
        </p>

    </article>

    <!-- ==================================
         BAGIAN KOMENTAR
    =================================== -->

    <aside class="detail-card">

        <h3>Komentar</h3>

        <!-- Form untuk mengirim komentar -->
        <form method="post" class="form-card">

            <textarea
                name="comment"
                rows="3"
                placeholder="Tulis komentar..."
                required
            ></textarea>

            <button
                class="btn btn-primary"
                name="send_comment"
                value="1"
                type="submit"
            >
                Kirim Komentar
            </button>

        </form>

        <!-- Menampilkan komentar pengguna -->
        <?php foreach ($comments as $comment): ?>

            <p>

                <strong>
                    <?= e($comment['name']) ?>
                </strong>

                <br>

                <?= e($comment['comment']) ?>

            </p>

            <hr>

        <?php endforeach; ?>

        <!-- Pesan jika belum ada komentar -->
        <?php if (!$comments): ?>

            <p class="muted">
                Belum ada komentar.
            </p>

        <?php endif; ?>

    </aside>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
