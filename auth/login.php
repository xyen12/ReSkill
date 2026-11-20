<?php

require_once __DIR__ . '/../includes/functions.php';

// Jika pengguna sudah login, arahkan ke dashboard.
if (is_logged_in()) {
    redirect(
        is_admin()
            ? 'admin/dashboard.php'
            : 'user/dashboard.php'
    );
}

$error = '';

// ==========================================
// PROSES LOGIN
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil data dari form login.
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Mencari pengguna berdasarkan email.
    $stmt = $pdo->prepare(
        'SELECT * FROM users WHERE email = ?'
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Memeriksa email dan password.
    if ($user && password_verify($pass, $user['password'])) {

        // Memperbarui ID session untuk keamanan.
        session_regenerate_id(true);

        // Menyimpan informasi pengguna ke session.
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Mengarahkan pengguna sesuai dengan role.
        redirect(
            $user['role'] === 'admin'
                ? 'admin/dashboard.php'
                : 'user/dashboard.php'
        );

    } else {

        // Pesan jika email atau password salah.
        $error = 'Email atau password tidak sesuai.';
    }
}

// Mengatur judul halaman.
$page_title = 'Masuk';

require_once __DIR__ . '/../includes/header.php';

?>

<!-- ======================================
     FORM LOGIN
======================================= -->

<div class="auth-wrap">

    <form class="form-card auth-card" method="post">

        <span class="eyebrow">
            SELAMAT DATANG KEMBALI
        </span>

        <h1>Masuk ke ReSkill</h1>

        <p class="muted">
            Lanjutkan perjalanan belajarmu.
        </p>

        <!-- Menampilkan pesan kesalahan -->
        <?php if ($error): ?>

            <div class="alert alert-error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

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
                required
            >
        </label>

        <!-- Tombol login -->
        <button class="btn btn-primary btn-full" type="submit">
            Masuk
        </button>

        <!-- Link menuju halaman pendaftaran -->
        <p class="form-note">
            Belum punya akun?

            <a href="<?= e(url('auth/register.php')) ?>">
                Daftar sekarang
            </a>
        </p>

    </form>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
