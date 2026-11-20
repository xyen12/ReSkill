
<?php

// Mengatur judul halaman.
$page_title = $page_title ?? 'ReSkill';

// Mengambil notifikasi sementara dari session.
$flash = get_flash();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <!-- Pengaturan dasar halaman -->
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= e($page_title) ?> | ReSkill</title>

    <!-- Menghubungkan Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <!-- Menghubungkan file CSS utama -->
    <link
        rel="stylesheet"
        href="<?= e(url('assets/css/style.css')) ?>"
    >

</head>

<body>

    <!-- ======================================
         HEADER DAN NAVIGASI
    ======================================= -->

    <header class="site-header">

        <div class="nav-inner">

            <!-- Logo dan identitas ReSkill -->
            <a
                class="brand"
                href="<?= e(url(
                    is_logged_in()
                        ? (is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php')
                        : ''
                )) ?>"
            >

                <img
                    src="<?= e(url('assets/images/logo-mark.jpg')) ?>"
                    alt="Logo ReSkill"
                >

                <span>
                    <strong>ReSkill</strong>
                    <small>Edukasi Pengelolaan Limbah</small>
                </span>

            </a>

            <!-- ==================================
                 NAVIGASI PENGGUNA YANG SUDAH LOGIN
            =================================== -->

            <?php if (is_logged_in()): ?>

                <div class="account-tools">

                    <!-- Tombol notifikasi dan progres -->
                    <a
                        class="icon-button notification"
                        href="<?= e(url('modules/progress/index.php')) ?>"
                        aria-label="Notifikasi dan progres"
                    >
                        ♧
                        <i></i>
                    </a>

                    <!-- Menu profil pengguna -->
                    <div class="profile-wrap">

                        <button
                            class="profile-button"
                            type="button"
                            data-profile-toggle
                            aria-label="Buka menu profil"
                        >

                            <!-- Avatar berdasarkan huruf pertama nama -->
                            <span class="avatar">
                                <?= e(strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1))) ?>
                            </span>

                            <span class="profile-name">
                                <?= e($_SESSION['name'] ?? 'Pengguna') ?>
                            </span>

                            <span>⌄</span>

                        </button>

                        <!-- Isi menu profil -->
                        <div class="profile-menu" data-profile-menu>

                            <strong>
                                <?= e($_SESSION['name'] ?? 'Pengguna') ?>
                            </strong>

                            <small>
                                <?= is_admin() ? 'Administrator' : 'Pengguna ReSkill' ?>
                            </small>

                            <a href="<?= e(url('modules/progress/index.php')) ?>">
                                Progres belajar
                            </a>

                            <!-- Menu tambahan khusus admin -->
                            <?php if (is_admin()): ?>

                                <a href="<?= e(url('admin/dashboard.php')) ?>">
                                    Kelola konten
                                </a>

                            <?php endif; ?>

                            <!-- Tombol keluar -->
                            <a
                                class="logout-link"
                                href="<?= e(url('auth/logout.php')) ?>"
                            >
                                Keluar ↗
                            </a>

                        </div>

                    </div>

                </div>

            <!-- ==================================
                 NAVIGASI PENGUNJUNG
            =================================== -->

            <?php else: ?>

                <nav class="public-nav">

                    <a class="active" href="<?= e(url()) ?>">
                        ⌂ Beranda
                    </a>

                    <a href="<?= e(url('auth/login.php')) ?>">
                        Masuk
                    </a>

                    <a
                        class="nav-cta"
                        href="<?= e(url('auth/register.php')) ?>"
                    >
                        Daftar
                    </a>

                </nav>

            <?php endif; ?>

        </div>

    </header>

    <!-- ======================================
         KONTEN UTAMA HALAMAN
    ======================================= -->

    <main class="container">

        <!-- Menampilkan notifikasi jika tersedia -->
        <?php if ($flash): ?>

            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>

        <?php endif; ?>
