<?php

require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Membuat token CSRF untuk melindungi proses pengelolaan data.
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['admin_csrf'];

// Mengambil seluruh kategori limbah.
$categories = $pdo->query(
    'SELECT * FROM waste_categories ORDER BY name'
)->fetchAll();

// Mengambil informasi data yang akan diedit.
$editType = (string) ($_GET['edit'] ?? '');
$editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

$editableTypes = [
    'category',
    'material',
    'video',
    'quiz',
    'question'
];

$editRow = null;

// Mengambil data berdasarkan jenis dan ID yang dipilih.
if ($editId && in_array($editType, $editableTypes, true)) {

    $tables = [
        'category'  => 'waste_categories',
        'material'  => 'educational_materials',
        'video'     => 'videos',
        'quiz'      => 'quizzes',
        'question'  => 'questions'
    ];

    $stmt = $pdo->prepare(
        'SELECT * FROM `' . $tables[$editType] . '` WHERE id = ?'
    );

    $stmt->execute([$editId]);
    $editRow = $stmt->fetch() ?: null;

    if (!$editRow) {
        set_flash('error', 'Data yang akan diedit tidak ditemukan.');
        redirect('admin/manage.php');
    }
}

// Memproses permintaan POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Memeriksa token CSRF.
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        set_flash(
            'error',
            'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.'
        );

        redirect('admin/manage.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $type = (string) ($_POST['type'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

    try {

        // ==========================================
        // PROSES MENGHAPUS DATA
        // ==========================================
        if ($action === 'delete') {

            // Daftar tabel yang boleh dihapus.
            $deleteTables = [
                'category' => 'waste_categories',
                'material' => 'educational_materials',
                'video'    => 'videos',
                'quiz'     => 'quizzes',
                'question' => 'questions',
                'gallery'  => 'recycling_gallery'
            ];

            if (!isset($deleteTables[$type]) || !$id) {
                throw new RuntimeException(
                    'Data yang dipilih tidak valid.'
                );
            }

            // Penghapusan khusus untuk karya galeri.
            if ($type === 'gallery') {

                // Mengambil foto utama.
                $photoQuery = $pdo->prepare(
                    'SELECT photo FROM recycling_gallery WHERE id = ?'
                );

                $photoQuery->execute([$id]);
                $mainPhoto = $photoQuery->fetchColumn();

                // Mengambil foto tambahan.
                $extraQuery = $pdo->prepare(
                    'SELECT photo FROM gallery_photos WHERE gallery_id = ?'
                );

                $extraQuery->execute([$id]);
                $photos = $extraQuery->fetchAll(PDO::FETCH_COLUMN);

                // Menghapus data karya dari database.
                $pdo->prepare(
                    'DELETE FROM recycling_gallery WHERE id = ?'
                )->execute([$id]);

                // Menghapus file foto dari folder uploads/gallery.
                foreach (
                    array_merge($mainPhoto ? [$mainPhoto] : [], $photos)
                    as $photo
                ) {
                    $safeName = basename((string) $photo);
                    $path = __DIR__ . '/../uploads/gallery/' . $safeName;

                    if (is_file($path)) {
                        @unlink($path);
                    }
                }

            } else {

                // Menghapus data selain galeri.
                $pdo->prepare(
                    'DELETE FROM `' . $deleteTables[$type] . '` WHERE id = ?'
                )->execute([$id]);
            }

            set_flash('success', 'Data berhasil dihapus.');

        // ==========================================
        // PROSES MENAMBAH DAN MENGEDIT DATA
        // ==========================================
        } elseif ($action === 'save') {

            // --------------------------------------
            // KATEGORI LIMBAH
            // --------------------------------------
            if ($type === 'category') {

                $name = trim((string) ($_POST['name'] ?? ''));
                $description = trim((string) ($_POST['description'] ?? ''));

                if ($name === '') {
                    throw new RuntimeException(
                        'Nama kategori wajib diisi.'
                    );
                }

                if ($id) {
                    $stmt = $pdo->prepare(
                        'UPDATE waste_categories
                         SET name = ?, description = ?
                         WHERE id = ?'
                    );
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO waste_categories (name, description)
                         VALUES (?, ?)'
                    );
                }

                $params = [$name, $description];

                if ($id) {
                    $params[] = $id;
                }

                $stmt->execute($params);

            // --------------------------------------
            // MATERI EDUKASI
            // --------------------------------------
            } elseif ($type === 'material') {

                $values = [
                    ($_POST['category_id'] ?? '') ?: null,
                    trim((string) ($_POST['title'] ?? '')),
                    trim((string) ($_POST['summary'] ?? '')),
                    trim((string) ($_POST['content'] ?? '')),
                    (string) ($_POST['material_type'] ?? 'article'),
                    max(1, (int) ($_POST['duration'] ?? 5)),
                    (string) ($_POST['level'] ?? 'Dasar'),
                    trim((string) ($_POST['references_text'] ?? '')),
                    (string) ($_POST['status'] ?? 'published')
                ];

                if ($values[1] === '' || $values[3] === '') {
                    throw new RuntimeException(
                        'Judul dan isi materi wajib diisi.'
                    );
                }

                if (
                    !in_array($values[4], ['article', 'tutorial'], true) ||
                    !in_array($values[8], ['draft', 'published'], true)
                ) {
                    throw new RuntimeException(
                        'Pilihan materi tidak valid.'
                    );
                }

                if ($id) {
                    $values[] = $id;

                    $stmt = $pdo->prepare(
                        'UPDATE educational_materials
                         SET category_id = ?, title = ?, summary = ?,
                             content = ?, material_type = ?, duration = ?,
                             level = ?, references_text = ?, status = ?
                         WHERE id = ?'
                    );

                } else {
                    $values[] = (int) $_SESSION['user_id'];

                    $stmt = $pdo->prepare(
                        'INSERT INTO educational_materials
                         (category_id, title, summary, content, material_type,
                          duration, level, references_text, status, created_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                }

                $stmt->execute($values);

            // --------------------------------------
            // VIDEO PEMBELAJARAN
            // --------------------------------------
            } elseif ($type === 'video') {

                $values = [
                    ($_POST['category_id'] ?? '') ?: null,
                    trim((string) ($_POST['title'] ?? '')),
                    trim((string) ($_POST['description'] ?? '')),
                    trim((string) ($_POST['video_url'] ?? '')),
                    trim((string) ($_POST['source_name'] ?? '')),
                    max(0, (int) ($_POST['duration'] ?? 0)),
                    (string) ($_POST['status'] ?? 'published')
                ];

                if (
                    $values[1] === '' ||
                    !filter_var($values[3], FILTER_VALIDATE_URL)
                ) {
                    throw new RuntimeException(
                        'Judul dan URL video yang valid wajib diisi.'
                    );
                }

                if (!in_array($values[6], ['draft', 'published'], true)) {
                    throw new RuntimeException(
                        'Status video tidak valid.'
                    );
                }

                if ($id) {
                    $values[] = $id;

                    $stmt = $pdo->prepare(
                        'UPDATE videos
                         SET category_id = ?, title = ?, description = ?,
                             video_url = ?, source_name = ?, duration = ?,
                             status = ?
                         WHERE id = ?'
                    );

                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO videos
                         (category_id, title, description, video_url,
                          source_name, duration, status)
                         VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                }

                $stmt->execute($values);

            // --------------------------------------
            // KUIS PEMBELAJARAN
            // --------------------------------------
            } elseif ($type === 'quiz') {

                $values = [
                    trim((string) ($_POST['title'] ?? '')),
                    trim((string) ($_POST['description'] ?? '')),
                    (string) ($_POST['level'] ?? 'Dasar'),
                    (string) ($_POST['status'] ?? 'published')
                ];

                if (
                    $values[0] === '' ||
                    !in_array($values[3], ['draft', 'published'], true)
                ) {
                    throw new RuntimeException(
                        'Judul kuis wajib diisi dan status harus valid.'
                    );
                }

                if ($id) {
                    $values[] = $id;

                    $stmt = $pdo->prepare(
                        'UPDATE quizzes
                         SET title = ?, description = ?, level = ?, status = ?
                         WHERE id = ?'
                    );

                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO quizzes (title, description, level, status)
                         VALUES (?, ?, ?, ?)'
                    );
                }

                $stmt->execute($values);

            // --------------------------------------
            // SOAL KUIS
            // --------------------------------------
            } elseif ($type === 'question') {

                $values = [
                    (int) ($_POST['quiz_id'] ?? 0),
                    trim((string) ($_POST['question'] ?? '')),
                    trim((string) ($_POST['option_a'] ?? '')),
                    trim((string) ($_POST['option_b'] ?? '')),
                    trim((string) ($_POST['option_c'] ?? '')),
                    trim((string) ($_POST['option_d'] ?? '')),
                    (string) ($_POST['correct_option'] ?? 'A'),
                    trim((string) ($_POST['explanation'] ?? ''))
                ];

                if (
                    !$values[0] ||
                    $values[1] === '' ||
                    in_array('', array_slice($values, 2, 4), true) ||
                    !in_array($values[6], ['A', 'B', 'C', 'D'], true)
                ) {
                    throw new RuntimeException(
                        'Lengkapi kuis, pertanyaan, semua pilihan, dan jawaban benar.'
                    );
                }

                if ($id) {
                    $values[] = $id;

                    $stmt = $pdo->prepare(
                        'UPDATE questions
                         SET quiz_id = ?, question = ?, option_a = ?,
                             option_b = ?, option_c = ?, option_d = ?,
                             correct_option = ?, explanation = ?
                         WHERE id = ?'
                    );

                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO questions
                         (quiz_id, question, option_a, option_b, option_c,
                          option_d, correct_option, explanation)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                }

                $stmt->execute($values);

            } else {
                throw new RuntimeException(
                    'Jenis data tidak dikenali.'
                );
            }

            set_flash(
                'success',
                $id
                    ? 'Perubahan berhasil disimpan.'
                    : 'Data baru berhasil ditambahkan.'
            );

        } else {
            throw new RuntimeException('Aksi tidak dikenali.');
        }

    } catch (Throwable $error) {

        // Mencatat kesalahan untuk pemeriksaan administrator.
        error_log('ReSkill admin manage: ' . $error->getMessage());

        set_flash(
            'error',
            $error instanceof RuntimeException
                ? $error->getMessage()
                : 'Data gagal diproses. Periksa apakah data masih digunakan oleh bagian lain.'
        );
    }

    redirect('admin/manage.php');
}

// ==========================================
// MENGAMBIL DATA UNTUK DITAMPILKAN
// ==========================================

$materials = $pdo->query(
    'SELECT m.*, c.name AS category_name
     FROM educational_materials m
     LEFT JOIN waste_categories c ON c.id = m.category_id
     ORDER BY m.created_at DESC'
)->fetchAll();

$videos = $pdo->query(
    'SELECT v.*, c.name AS category_name
     FROM videos v
     LEFT JOIN waste_categories c ON c.id = v.category_id
     ORDER BY v.created_at DESC'
)->fetchAll();

$quizzes = $pdo->query(
    'SELECT q.*,
            (SELECT COUNT(*) FROM questions x WHERE x.quiz_id = q.id)
            AS question_count
     FROM quizzes q
     ORDER BY q.created_at DESC'
)->fetchAll();

$questions = $pdo->query(
    'SELECT x.*, q.title AS quiz_title
     FROM questions x
     JOIN quizzes q ON q.id = x.quiz_id
     ORDER BY x.id DESC'
)->fetchAll();

$gallery = $pdo->query(
    'SELECT g.*, u.name AS user_name, c.name AS category_name
     FROM recycling_gallery g
     JOIN users u ON u.id = g.user_id
     LEFT JOIN waste_categories c ON c.id = g.category_id
     ORDER BY g.created_at DESC'
)->fetchAll();

$quizOptions = $pdo->query(
    'SELECT id, title FROM quizzes ORDER BY title'
)->fetchAll();

// Mengatur judul halaman dan memanggil header.
$page_title = 'Kelola Konten';

require_once __DIR__ . '/../includes/header.php';

// Fungsi untuk mengambil nilai data edit dengan aman.
$val = static function (string $key, $default = '') use ($editRow) {
    return e($editRow[$key] ?? $default);
};

?>

<!-- ======================================
     JUDUL HALAMAN
======================================= -->

<section class="page-heading">
    <span class="eyebrow">RUANG ADMINISTRATOR</span>

    <h1>Kelola Konten ReSkill</h1>

    <p>
        Tambah, perbarui, dan hapus data kategori, materi,
        video, kuis, soal, serta karya galeri.
    </p>
</section>

<!-- ======================================
     FORM EDIT DATA
======================================= -->

<?php if ($editRow): ?>

<section class="form-card admin-editor" id="editor">

    <div class="admin-section-head">
        <div>
            <span class="eyebrow">MODE EDIT</span>

            <h2>
                Edit
                <?= e([
                    'category' => 'Kategori',
                    'material' => 'Materi',
                    'video' => 'Video',
                    'quiz' => 'Kuis',
                    'question' => 'Soal Kuis'
                ][$editType]) ?>
            </h2>
        </div>

        <a class="btn btn-outline" href="<?= e(url('admin/manage.php')) ?>">
            Batal
        </a>
    </div>

    <form method="post">

        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="type" value="<?= e($editType) ?>">
        <input type="hidden" name="id" value="<?= $editId ?>">

        <?php if ($editType === 'category'): ?>

            <label>
                Nama kategori
                <input name="name" value="<?= $val('name') ?>" required>
            </label>

            <label>
                Deskripsi
                <textarea name="description" rows="3"><?= $val('description') ?></textarea>
            </label>

        <?php elseif ($editType === 'material'): ?>

            <label>
                Judul materi
                <input name="title" value="<?= $val('title') ?>" required>
            </label>

            <label>
                Kategori
                <select name="category_id">
                    <option value="">Umum</option>

                    <?php foreach ($categories as $c): ?>
                        <option
                            value="<?= $c['id'] ?>"
                            <?= ((string) ($editRow['category_id'] ?? '') === (string) $c['id']) ? 'selected' : '' ?>
                        >
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Jenis
                <select name="material_type">
                    <option value="article" <?= $val('material_type') === 'article' ? 'selected' : '' ?>>
                        Materi edukasi
                    </option>

                    <option value="tutorial" <?= $val('material_type') === 'tutorial' ? 'selected' : '' ?>>
                        Panduan praktik
                    </option>
                </select>
            </label>

            <label>
                Ringkasan
                <input name="summary" value="<?= $val('summary') ?>">
            </label>

            <label>
                Isi materi
                <textarea name="content" rows="8" required><?= $val('content') ?></textarea>
            </label>

            <label>
                Durasi (menit)
                <input type="number" name="duration" min="1" value="<?= $val('duration', 5) ?>">
            </label>

            <label>
                Tingkat
                <select name="level">
                    <?php foreach (['Dasar', 'Menengah', 'Lanjutan'] as $level): ?>
                        <option <?= $val('level') === $level ? 'selected' : '' ?>>
                            <?= $level ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Referensi
                <textarea name="references_text" rows="3"><?= $val('references_text') ?></textarea>
            </label>

            <label>
                Status
                <select name="status">
                    <option value="published" <?= $val('status') === 'published' ? 'selected' : '' ?>>
                        Dipublikasikan
                    </option>

                    <option value="draft" <?= $val('status') === 'draft' ? 'selected' : '' ?>>
                        Draft
                    </option>
                </select>
            </label>

        <?php elseif ($editType === 'video'): ?>

            <label>
                Judul video
                <input name="title" value="<?= $val('title') ?>" required>
            </label>

            <label>
                Kategori
                <select name="category_id">
                    <option value="">Umum</option>

                    <?php foreach ($categories as $c): ?>
                        <option
                            value="<?= $c['id'] ?>"
                            <?= ((string) ($editRow['category_id'] ?? '') === (string) $c['id']) ? 'selected' : '' ?>
                        >
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Deskripsi
                <textarea name="description" rows="3"><?= $val('description') ?></textarea>
            </label>

            <label>
                URL video
                <input type="url" name="video_url" value="<?= $val('video_url') ?>" required>
            </label>

            <label>
                Sumber video
                <input name="source_name" value="<?= $val('source_name') ?>">
            </label>

            <label>
                Durasi (menit)
                <input type="number" name="duration" min="0" value="<?= $val('duration', 0) ?>">
            </label>

            <label>
                Status
                <select name="status">
                    <option value="published" <?= $val('status') === 'published' ? 'selected' : '' ?>>
                        Dipublikasikan
                    </option>

                    <option value="draft" <?= $val('status') === 'draft' ? 'selected' : '' ?>>
                        Draft
                    </option>
                </select>
            </label>

        <?php elseif ($editType === 'quiz'): ?>

            <label>
                Judul kuis
                <input name="title" value="<?= $val('title') ?>" required>
            </label>

            <label>
                Deskripsi
                <textarea name="description" rows="3"><?= $val('description') ?></textarea>
            </label>

            <label>
                Tingkat
                <select name="level">
                    <?php foreach (['Dasar', 'Menengah', 'Lanjutan'] as $level): ?>
                        <option <?= $val('level') === $level ? 'selected' : '' ?>>
                            <?= $level ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Status
                <select name="status">
                    <option value="published" <?= $val('status') === 'published' ? 'selected' : '' ?>>
                        Dipublikasikan
                    </option>

                    <option value="draft" <?= $val('status') === 'draft' ? 'selected' : '' ?>>
                        Draft
                    </option>
                </select>
            </label>

        <?php else: ?>

            <label>
                Kuis
                <select name="quiz_id" required>
                    <?php foreach ($quizOptions as $q): ?>
                        <option
                            value="<?= $q['id'] ?>"
                            <?= ((string) $editRow['quiz_id'] === (string) $q['id']) ? 'selected' : '' ?>
                        >
                            <?= e($q['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Pertanyaan
                <textarea name="question" rows="3" required><?= $val('question') ?></textarea>
            </label>

            <?php foreach (['a', 'b', 'c', 'd'] as $o): ?>
                <label>
                    Pilihan <?= strtoupper($o) ?>
                    <input name="option_<?= $o ?>" value="<?= $val('option_' . $o) ?>" required>
                </label>
            <?php endforeach; ?>

            <label>
                Jawaban benar
                <select name="correct_option">
                    <?php foreach (['A', 'B', 'C', 'D'] as $o): ?>
                        <option <?= $val('correct_option') === $o ? 'selected' : '' ?>>
                            <?= $o ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Pembahasan
                <textarea name="explanation" rows="3"><?= $val('explanation') ?></textarea>
            </label>

        <?php endif; ?>

        <button class="btn btn-primary" type="submit">
            Simpan Perubahan
        </button>

    </form>
</section>

<?php endif; ?>

<!-- ======================================
     FORM TAMBAH DATA
======================================= -->

<div class="admin-management-grid">

    <!-- Tambah kategori -->
    <section class="form-card">
        <h2>Tambah Kategori Limbah</h2>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="type" value="category">

            <label>
                Nama kategori
                <input name="name" required placeholder="Contoh: Organik">
            </label>

            <label>
                Deskripsi
                <textarea name="description" rows="2"></textarea>
            </label>

            <button class="btn btn-primary">Tambah Kategori</button>
        </form>
    </section>

    <!-- Tambah materi edukasi -->
    <section class="form-card">
        <h2>Tambah Materi Edukasi</h2>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="type" value="material">

            <label>
                Judul materi
                <input name="title" required>
            </label>

            <label>
                Kategori
                <select name="category_id">
                    <option value="">Umum</option>

                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Jenis
                <select name="material_type">
                    <option value="article">Materi edukasi</option>
                    <option value="tutorial">Panduan praktik</option>
                </select>
            </label>

            <label>
                Ringkasan
                <input name="summary" required>
            </label>

            <label>
                Isi materi
                <textarea name="content" rows="5" required></textarea>
            </label>

            <label>
                Durasi (menit)
                <input type="number" name="duration" min="1" value="5">
            </label>

            <label>
                Tingkat
                <select name="level">
                    <option>Dasar</option>
                    <option>Menengah</option>
                    <option>Lanjutan</option>
                </select>
            </label>

            <label>
                Referensi
                <textarea name="references_text" rows="2"></textarea>
            </label>

            <label>
                Status
                <select name="status">
                    <option value="published">Dipublikasikan</option>
                    <option value="draft">Draft</option>
                </select>
            </label>

            <button class="btn btn-primary">Tambah Materi</button>
        </form>
    </section>

    <!-- Tambah video -->
    <section class="form-card">
        <h2>Tambah Video</h2>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="type" value="video">

            <label>
                Judul video
                <input name="title" required>
            </label>

            <label>
                Kategori
                <select name="category_id">
                    <option value="">Umum</option>

                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Deskripsi
                <textarea name="description" rows="2"></textarea>
            </label>

            <label>
                URL video
                <input
                    type="url"
                    name="video_url"
                    required
                    placeholder="https://www.youtube.com/watch?v=..."
                >
            </label>

            <label>
                Sumber video
                <input name="source_name">
            </label>

            <label>
                Durasi (menit)
                <input type="number" name="duration" min="0" value="5">
            </label>

            <label>
                Status
                <select name="status">
                    <option value="published">Dipublikasikan</option>
                    <option value="draft">Draft</option>
                </select>
            </label>

            <button class="btn btn-primary">Tambah Video</button>
        </form>
    </section>

    <!-- Tambah kuis -->
    <section class="form-card">
        <h2>Tambah Kuis</h2>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="type" value="quiz">

            <label>
                Judul kuis
                <input name="title" required>
            </label>

            <label>
                Deskripsi
                <textarea name="description" rows="2"></textarea>
            </label>

            <label>
                Tingkat
                <select name="level">
                    <option>Dasar</option>
                    <option>Menengah</option>
                    <option>Lanjutan</option>
                </select>
            </label>

            <label>
                Status
                <select name="status">
                    <option value="published">Dipublikasikan</option>
                    <option value="draft">Draft</option>
                </select>
            </label>

            <button class="btn btn-primary">Tambah Kuis</button>
        </form>
    </section>

    <!-- Tambah soal kuis -->
    <section class="form-card">
        <h2>Tambah Soal Kuis</h2>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="type" value="question">

            <label>
                Pilih kuis
                <select name="quiz_id" required>
                    <?php foreach ($quizOptions as $q): ?>
                        <option value="<?= $q['id'] ?>">
                            <?= e($q['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Pertanyaan
                <textarea name="question" rows="3" required></textarea>
            </label>

            <?php foreach (['a', 'b', 'c', 'd'] as $o): ?>
                <label>
                    Pilihan <?= strtoupper($o) ?>
                    <input name="option_<?= $o ?>" required>
                </label>
            <?php endforeach; ?>

            <label>
                Jawaban benar
                <select name="correct_option">
                    <option>A</option>
                    <option>B</option>
                    <option>C</option>
                    <option>D</option>
                </select>
            </label>

            <label>
                Pembahasan
                <textarea name="explanation" rows="2"></textarea>
            </label>

            <button class="btn btn-primary">Tambah Soal</button>
        </form>
    </section>

</div>

<!-- ======================================
     DAFTAR DATA YANG DIKELOLA
======================================= -->

<?php

$sections = [
    [
        'title' => 'Kategori Limbah',
        'type' => 'category',
        'rows' => $categories,
        'columns' => [
            'name' => 'Nama kategori',
            'description' => 'Deskripsi'
        ]
    ],
    [
        'title' => 'Materi Edukasi',
        'type' => 'material',
        'rows' => $materials,
        'columns' => [
            'title' => 'Judul',
            'category_name' => 'Kategori',
            'material_type' => 'Jenis',
            'status' => 'Status'
        ]
    ],
    [
        'title' => 'Video Pembelajaran',
        'type' => 'video',
        'rows' => $videos,
        'columns' => [
            'title' => 'Judul',
            'category_name' => 'Kategori',
            'source_name' => 'Sumber',
            'status' => 'Status'
        ]
    ],
    [
        'title' => 'Kuis Pembelajaran',
        'type' => 'quiz',
        'rows' => $quizzes,
        'columns' => [
            'title' => 'Judul',
            'level' => 'Tingkat',
            'question_count' => 'Jumlah soal',
            'status' => 'Status'
        ]
    ],
    [
        'title' => 'Soal Kuis',
        'type' => 'question',
        'rows' => $questions,
        'columns' => [
            'quiz_title' => 'Nama kuis',
            'question' => 'Pertanyaan',
            'correct_option' => 'Jawaban'
        ]
    ]
];

foreach ($sections as $section):

?>

<section class="admin-panel admin-list">

    <div class="admin-section-head">
        <h2><?= e($section['title']) ?></h2>

        <span class="tag">
            <?= count($section['rows']) ?> data
        </span>
    </div>

    <?php if (!$section['rows']): ?>

        <p class="muted">Belum ada data.</p>

    <?php else: ?>

        <div class="admin-table-wrap">

            <table class="admin-table">

                <thead>
                    <tr>
                        <?php foreach ($section['columns'] as $label): ?>
                            <th><?= e($label) ?></th>
                        <?php endforeach; ?>

                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($section['rows'] as $row): ?>

                        <tr>

                            <?php foreach ($section['columns'] as $field => $label): ?>
                                <td>
                                    <?= e(substr((string) ($row[$field] ?? '-'), 0, 90)) ?>
                                </td>
                            <?php endforeach; ?>

                            <td>
                                <div class="admin-actions">

                                    <!-- Tombol edit -->
                                    <a
                                        class="btn btn-outline btn-small"
                                        href="?edit=<?= $section['type'] ?>&id=<?= $row['id'] ?>#editor"
                                    >
                                        Edit
                                    </a>

                                    <!-- Tombol hapus -->
                                    <form
                                        method="post"
                                        onsubmit="return confirm('Yakin ingin menghapus data ini? Jika menghapus kuis, soal dan riwayat hasil kuis terkait juga akan terhapus. Tindakan ini tidak dapat dibatalkan.');"
                                    >
                                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="type" value="<?= $section['type'] ?>">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                        <button class="btn btn-danger btn-small" type="submit">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>

<?php endforeach; ?>

<!-- ======================================
     DAFTAR KARYA GALERI PENGGUNA
======================================= -->

<section class="admin-panel admin-list">

    <div class="admin-section-head">
        <h2>Karya Galeri Pengguna</h2>

        <a class="btn btn-outline" href="<?= e(url('admin/moderate.php')) ?>">
            Buka Moderasi
        </a>
    </div>

    <?php if (!$gallery): ?>

        <p class="muted">Belum ada karya yang diunggah.</p>

    <?php else: ?>

        <div class="admin-table-wrap">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Nama karya</th>
                        <th>Pengunggah</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($gallery as $g): ?>

                        <tr>
                            <td><?= e($g['title']) ?></td>
                            <td><?= e($g['user_name']) ?></td>
                            <td><?= e($g['category_name'] ?? 'Umum') ?></td>
                            <td><?= e($g['status']) ?></td>

                            <td>
                                <form
                                    method="post"
                                    onsubmit="return confirm('Hapus karya beserta foto, komentar, dan suka terkait?');"
                                >
                                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="type" value="gallery">
                                    <input type="hidden" name="id" value="<?= $g['id'] ?>">

                                    <button class="btn btn-danger btn-small">
                                        Hapus karya
                                    </button>
                                </form>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
