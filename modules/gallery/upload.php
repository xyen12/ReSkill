
<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// Mengambil data pengguna yang sedang login.
$user = current_user();

// Mengambil daftar kategori limbah.
$categories = $pdo->query(
    'SELECT * FROM waste_categories ORDER BY id'
)->fetchAll();

// Menyiapkan variabel untuk pesan kesalahan.
$errors = [];

// Menyimpan nilai form agar tidak hilang jika validasi gagal.
$old = [
    'title' => '',
    'description' => '',
    'materials' => '',
    'steps' => '',
    'category_id' => ''
];

// ==========================================
// PROSES UNGGAH KARYA
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil seluruh data dari form.
    foreach ($old as $key => $_) {
        $old[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    // ======================================
    // VALIDASI FOTO
    // ======================================

    // Mengambil maksimal tiga foto yang dipilih pengguna.
    $files = $_FILES['photos'] ?? null;
    $validFiles = [];

    if (!$files || empty($files['name'][0])) {

        $errors[] = 'Pilih minimal satu foto karya terlebih dahulu.';

    } else {

        // Menghitung jumlah foto yang dipilih.
        $fileCount = count(array_filter($files['name']));

        // Memeriksa batas maksimal foto.
        if ($fileCount > 3) {
            $errors[] = 'Foto karya maksimal 3 file.';
        }

        // Memeriksa setiap foto yang diunggah.
        for ($i = 0; $i < min($fileCount, 3); $i++) {

            // Memeriksa status proses upload.
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {

                $errors[] = 'Salah satu foto gagal diunggah. Periksa ukuran file dan pengaturan upload PHP.';
                continue;
            }

            // Memeriksa ukuran foto maksimal 5 MB.
            if ($files['size'][$i] > 5 * 1024 * 1024) {

                $errors[] = 'Ukuran setiap foto maksimal 5 MB.';
                continue;
            }

            // Mengambil ekstensi file.
            $extension = strtolower(
                pathinfo($files['name'][$i], PATHINFO_EXTENSION)
            );

            // Memeriksa apakah file benar-benar gambar.
            $imageInfo = @getimagesize($files['tmp_name'][$i]);

            if (
                !in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ||
                !$imageInfo ||
                !in_array(
                    $imageInfo['mime'],
                    ['image/jpeg', 'image/png', 'image/webp'],
                    true
                )
            ) {

                $errors[] = 'Gunakan foto JPG, JPEG, PNG, atau WEBP yang valid.';
                continue;
            }

            // Menyimpan informasi foto yang lolos validasi.
            $validFiles[] = [
                'tmp' => $files['tmp_name'][$i],
                'extension' => $extension
            ];
        }
    }

    // ======================================
    // VALIDASI DATA FORM
    // ======================================

    if (
        $old['title'] === '' ||
        $old['description'] === '' ||
        $old['materials'] === '' ||
        $old['steps'] === '' ||
        !$old['category_id']
    ) {

        $errors[] = 'Lengkapi semua kolom yang bertanda bintang.';
    }

    // ======================================
    // PROSES PENYIMPANAN DATA
    // ======================================

    if (!$errors) {

        // Menentukan lokasi penyimpanan foto.
        $uploadDir = __DIR__ . '/../../uploads/gallery/';

        // Membuat folder jika belum tersedia.
        if (
            !is_dir($uploadDir) &&
            !mkdir($uploadDir, 0775, true) &&
            !is_dir($uploadDir)
        ) {

            $errors[] = 'Folder penyimpanan foto tidak dapat dibuat.';

        } elseif (!is_writable($uploadDir)) {

            $errors[] = 'Folder uploads/gallery tidak memiliki izin tulis.';

        } else {

            $savedFiles = [];

            try {

                // ----------------------------------
                // MEMINDAHKAN FOTO KE FOLDER UPLOAD
                // ----------------------------------

                foreach ($validFiles as $file) {

                    // Membuat nama file acak agar tidak terjadi
                    // benturan nama dengan file yang sudah ada.
                    $filename = bin2hex(random_bytes(16))
                        . '.'
                        . $file['extension'];

                    // Memindahkan foto ke folder penyimpanan.
                    if (
                        !move_uploaded_file(
                            $file['tmp'],
                            $uploadDir . $filename
                        )
                    ) {

                        throw new RuntimeException(
                            'Foto gagal dipindahkan ke folder penyimpanan.'
                        );
                    }

                    $savedFiles[] = $filename;
                }

                // ----------------------------------
                // MENYIMPAN DATA KE DATABASE
                // ----------------------------------

                // Memulai transaksi database.
                $pdo->beginTransaction();

                // Menyimpan informasi karya dan foto utama.
                $insert = $pdo->prepare(
                    'INSERT INTO recycling_gallery
                    (
                        user_id,
                        category_id,
                        title,
                        description,
                        materials,
                        steps,
                        photo
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)'
                );

                $insert->execute([
                    (int) $user['id'],
                    (int) $old['category_id'],
                    $old['title'],
                    $old['description'],
                    $old['materials'],
                    $old['steps'],
                    $savedFiles[0]
                ]);

                // Mengambil ID karya yang baru dibuat.
                $galleryId = (int) $pdo->lastInsertId();

                // ----------------------------------
                // MENYIMPAN FOTO TAMBAHAN
                // ----------------------------------

                // Foto pertama menjadi foto utama.
                // Foto berikutnya disimpan sebagai galeri tambahan.
                if (count($savedFiles) > 1) {

                    $extraInsert = $pdo->prepare(
                        'INSERT INTO gallery_photos (gallery_id, photo)
                         VALUES (?, ?)'
                    );

                    foreach (array_slice($savedFiles, 1) as $filename) {

                        $extraInsert->execute([
                            $galleryId,
                            $filename
                        ]);
                    }
                }

                // Menyelesaikan transaksi database.
                $pdo->commit();

                // Menampilkan notifikasi berhasil.
                set_flash(
                    'success',
                    'Karya berhasil dikirim dan sedang menunggu persetujuan admin.'
                );

                // Mengarahkan pengguna kembali ke galeri.
                redirect('modules/gallery/index.php');

            } catch (Throwable $error) {

                // Membatalkan transaksi jika terjadi kesalahan.
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                // Menghapus foto yang sudah terlanjur diunggah.
                foreach ($savedFiles as $filename) {
                    @unlink($uploadDir . $filename);
                }

                // Mencatat kesalahan untuk pemeriksaan.
                error_log(
                    'ReSkill gallery upload error: ' . $error->getMessage()
                );

                $errors[] = 'Karya belum berhasil disimpan. Periksa kembali akun dan struktur tabel galeri.';
            }
        }
    }
}

// Mengatur judul halaman.
$page_title = 'Unggah Karya';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- ======================================
     JUDUL HALAMAN UNGGAH
======================================= -->

<div class="upload-heading">

    <a
        class="back-link"
        href="<?= e(url('modules/gallery/index.php')) ?>"
    >
        ← Kembali ke Galeri
    </a>

    <span class="eyebrow">
        BAGIKAN KREATIVITASMU
    </span>

    <h1>Unggah Karya</h1>

    <p>
        Bagikan hasil karya daur ulangmu untuk menginspirasi pengguna lain.
    </p>

</div>

<!-- ======================================
     MENAMPILKAN PESAN KESALAHAN
======================================= -->

<?php foreach ($errors as $error): ?>

    <div class="alert alert-error">
        <?= e($error) ?>
    </div>

<?php endforeach; ?>

<!-- ======================================
     FORM UNGGAH KARYA
======================================= -->

<form
    class="form-card upload-form"
    method="post"
    enctype="multipart/form-data"
>

    <div class="upload-columns">

        <!-- ==================================
             BAGIAN FOTO KARYA
        =================================== -->

        <div class="upload-photo-column">

            <label for="photoInput">
                Foto Karya
                <span class="required-mark">*</span>
            </label>

            <!-- Area pemilihan foto -->
            <label
                class="upload-dropzone"
                for="photoInput"
            >

                <span class="upload-cloud">
                    ⇧
                </span>

                <strong>
                    Klik untuk memilih foto
                </strong>

                <span>
                    Format JPG, JPEG, PNG, WEBP ·
                    Maks. 5 MB per foto · Maks. 3 foto
                </span>

                <!-- Input file -->
                <input
                    id="photoInput"
                    type="file"
                    name="photos[]"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    required
                    data-preview="#photoPreview"
                >

            </label>

            <!-- Tempat menampilkan preview foto -->
            <div
                id="photoPreview"
                class="upload-previews"
                aria-live="polite"
            ></div>

        </div>

        <!-- ==================================
             BAGIAN INFORMASI KARYA
        =================================== -->

        <div class="upload-fields">

            <!-- Nama karya -->
            <label>
                Nama Karya
                <span class="required-mark">*</span>

                <input
                    name="title"
                    value="<?= e($old['title']) ?>"
                    required
                    placeholder="Contoh: Pot Gantung dari Botol Plastik"
                >
            </label>

            <!-- Kategori limbah -->
            <label>
                Kategori Limbah
                <span class="required-mark">*</span>

                <select name="category_id" required>

                    <option value="">
                        Pilih kategori
                    </option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?= $category['id'] ?>"
                            <?= ((string) $category['id'] === $old['category_id']) ? 'selected' : '' ?>
                        >
                            <?= e($category['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </label>

            <!-- Deskripsi karya -->
            <label>
                Deskripsi Karya
                <span class="required-mark">*</span>

                <textarea
                    name="description"
                    rows="5"
                    required
                    placeholder="Ceritakan karya yang dibuat..."
                ><?= e($old['description']) ?></textarea>
            </label>

        </div>

    </div>

    <!-- ======================================
         BAHAN YANG DIGUNAKAN
    ======================================= -->

    <label>
        Bahan yang Digunakan
        <span class="required-mark">*</span>

        <textarea
            name="materials"
            rows="3"
            required
            placeholder="Tuliskan bahan yang digunakan..."
        ><?= e($old['materials']) ?></textarea>
    </label>

    <!-- ======================================
         LANGKAH PEMBUATAN
    ======================================= -->

    <label>
        Langkah Pembuatan
        <span class="required-mark">*</span>

        <textarea
            name="steps"
            rows="4"
            required
            placeholder="Tuliskan langkah pembuatan secara berurutan..."
        ><?= e($old['steps']) ?></textarea>
    </label>

    <!-- Informasi proses persetujuan karya -->
    <p class="upload-note">
        Karya akan ditinjau oleh admin sebelum ditampilkan di galeri.
    </p>

    <!-- ======================================
         TOMBOL AKSI FORM
    ======================================= -->

    <div class="inline-actions upload-actions">

        <a
            class="btn btn-outline"
            href="<?= e(url('modules/gallery/index.php')) ?>"
        >
            Batal
        </a>

        <button
            class="btn btn-primary"
            type="submit"
        >
            ➤ Kirim Karya
        </button>

    </div>

</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
