
<?php

require_once __DIR__ . '/../includes/functions.php';

// Jika pengguna sudah login, arahkan ke dashboard.
if (is_logged_in()) {
    redirect('user/dashboard.php');
}

$error = '';

// ==========================================
// PROSES PENDAFTARAN AKUN
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil data dari form pendaftaran.
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Memvalidasi data yang dimasukkan pengguna.
    if (
        !$name ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($pass) < 8
    ) {
        $error = 'Isi nama, email valid, dan password minimal 8 karakter.';

    } else {

        try {

            // Menyiapkan query untuk menyimpan akun baru.
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)'
            );

            // Mengenkripsi password sebelum disimpan.
            $hashedPassword = password_hash(
                $pass,
                PASSWORD_DEFAULT
            );

            // Menyimpan data pengguna ke database.
            $stmt->execute([
                $name,
                $email,
                $hashedPassword
            ]);

            // Menampilkan pesan berhasil dan mengarahkan ke login.
            set_flash('success', 'Akun berhasil dibuat. Silakan masuk.');

            redirect('auth/login.php');

        } catch (PDOException $error) {

            // Menangani kegagalan penyimpanan, misalnya email duplikat.
            $error = 'Email sudah digunakan atau data tidak dapat disimpan.';
        }
    }
}

// Mengatur judul halaman.
$page_title = 'Daftar';

require_once __DIR__ . '/../includes/header.php';

?>

<!-- ======================================
     FORM PENDAFTARAN
======================================= -->

<div class="auth-wrap">

    <form class="form-card auth-card" method="post">

        <span class="eyebrow">
            GABUNG BERSAMA RESKILL
        </span>

        <h1>Buat Akun</h1>

        <p class="muted">
            Mulai belajar mengelola limbah dengan lebih bijak.
        </p>

        <!-- Menampilkan pesan kesalahan -->
        <?php if ($error): ?>

            <div class="alert alert-error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <!-- Input nama lengkap -->
        <label>
            Nama lengkap
            <input
                type="text"
                name="name"
                required
            >
        </label>

        <!-- Input email -->
        <label>
            Email
            <input
                type="email"
                name="email"
                required
            >
        </label>

        <!-- Input password -->
        <label>
            Password
            <input
                type="password"
                name="password"
                minlength="8"
                required
            >
        </label>

        <!-- Tombol daftar -->
        <button class="btn btn-primary btn-full" type="submit">
            Daftar
        </button>

        <!-- Link menuju halaman login -->
        <p class="form-note">
            Sudah punya akun?

            <a href="<?= e(url('auth/login.php')) ?>">
                Masuk
            </a>
        </p>

    </form>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
