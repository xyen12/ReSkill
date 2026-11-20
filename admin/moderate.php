
<?php

require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Membuat token CSRF untuk melindungi proses moderasi.
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['admin_csrf'];

// ==========================================
// PROSES MODERASI KARYA GALERI
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Memeriksa token keamanan.
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        set_flash('error', 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
        redirect('admin/moderate.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    try {

        // Memastikan ID karya valid.
        if (!$id || $id < 1) {
            throw new RuntimeException('Karya yang dipilih tidak valid.');
        }

        // Menentukan status berdasarkan aksi administrator.
        if ($action === 'approve') {
            $status = 'approved';
            $message = 'Karya disetujui.';

        } elseif ($action === 'reject') {
            $status = 'rejected';
            $message = 'Karya ditolak.';

        } else {
            throw new RuntimeException('Aksi moderasi tidak dikenali.');
        }

        // Memperbarui status karya yang masih menunggu persetujuan.
        $stmt = $pdo->prepare(
            'UPDATE recycling_gallery
             SET status = ?
             WHERE id = ? AND status = ?'
        );

        $stmt->execute([$status, $id, 'pending']);

        if ($stmt->rowCount() > 0) {
            set_flash('success', $message);
        } else {
            set_flash('error', 'Karya tidak ditemukan atau sudah dimoderasi.');
        }

    } catch (Throwable $error) {

        // Mencatat kesalahan untuk pemeriksaan administrator.
        error_log('ReSkill moderation: ' . $error->getMessage());

        set_flash(
            'error',
            $error instanceof RuntimeException
                ? $error->getMessage()
                : 'Terjadi kesalahan saat memproses moderasi karya.'
        );
    }

    redirect('admin/moderate.php');
}

// ==========================================
// MENGAMBIL KARYA YANG MENUNGGU PERSETUJUAN
// ==========================================

$rows = $pdo->query(
    "SELECT g.*, u.name
     FROM recycling_gallery g
     JOIN users u ON u.id = g.user_id
     WHERE g.status = 'pending'
     ORDER BY g.created_at"
)->fetchAll();

// Mengatur judul halaman.
$page_title = 'Moderasi Galeri';

require_once __DIR__ . '/../includes/header.php';

?>

<!-- ======================================
     JUDUL HALAMAN
======================================= -->

<section class="page-heading">
    <h1>Moderasi Galeri</h1>

    <p>
        Tinjau karya yang dikirim pengguna sebelum ditampilkan secara publik.
    </p>
</section>

<!-- ======================================
     DAFTAR KARYA YANG MENUNGGU PERSETUJUAN
======================================= -->

<div class="gallery-grid">

    <?php foreach ($rows as $r): ?>

        <article class="content-card">

            <!-- Foto karya pengguna -->
            <img
                class="cover"
                src="<?= e(url('uploads/gallery/' . $r['photo'])) ?>"
                alt="<?= e($r['title']) ?>"
            >

            <!-- Informasi karya -->
            <h3><?= e($r['title']) ?></h3>

            <p><?= e($r['name']) ?></p>

            <!-- Tombol moderasi -->
            <div class="inline-actions">

                <!-- Menyetujui karya -->
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">

                    <button class="btn btn-primary" type="submit">
                        Setujui
                    </button>
                </form>

                <!-- Menolak karya -->
                <form
                    method="post"
                    onsubmit="return confirm('Tolak karya ini?');"
                >
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">

                    <button class="btn btn-outline" type="submit">
                        Tolak
                    </button>
                </form>

            </div>

        </article>

    <?php endforeach; ?>

    <!-- Pesan jika tidak ada karya yang menunggu -->
    <?php if (!$rows): ?>

        <p>Tidak ada karya yang menunggu persetujuan.</p>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
