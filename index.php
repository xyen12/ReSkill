
<?php

require_once __DIR__ . '/includes/functions.php';

// ==========================================
// PEMERIKSAAN STATUS LOGIN
// ==========================================

// Jika pengguna sudah login, arahkan ke dashboard
// sesuai dengan perannya.
if (is_logged_in()) {

    redirect(
        is_admin()
            ? 'admin/dashboard.php'
            : 'user/dashboard.php'
    );
}

// Mengatur judul halaman.
$page_title = 'Beranda';

require_once __DIR__ . '/includes/header.php';

?>

<!-- ======================================
     HERO SECTION
======================================= -->

<section class="hero">

    <div class="hero-content">

        <span class="eyebrow">
            🍃 Belajar · Pilah · Olah
        </span>

        <h1>
            Mulai langkah kecil untuk lingkungan yang lebih baik.
        </h1>

        <p>
            ReSkill membantu masyarakat mengenal jenis limbah,
            belajar memilah sampah, dan mempraktikkan pengolahan
            limbah melalui materi, video, kuis, serta galeri karya
            yang mudah dipahami.
        </p>

        <!-- Tombol aksi utama -->
        <div class="hero-actions">

            <a
                class="btn btn-primary"
                href="<?= e(url('auth/register.php')) ?>"
            >
                ⊳ Mulai Belajar
            </a>

            <a
                class="btn btn-outline"
                href="<?= e(url('auth/login.php')) ?>"
            >
                ▣ Masuk
            </a>

        </div>

    </div>

    <!-- Ilustrasi utama -->
    <div class="hero-art">

        <img
            src="<?= e(url('assets/images/hero-recycling.jpg')) ?>"
            alt="Ilustrasi pemilahan sampah"
        >

    </div>

</section>

<!-- ======================================
     BANNER AJAKAN
======================================= -->

<div class="action-banner">

    <span class="bulb">
        💡
    </span>

    <div>

        <h3>
            Ubah pengetahuan menjadi aksi
        </h3>

        <p>
            Pelajari pengelolaan limbah dari rumah,
            mulai dari hal sederhana.
        </p>

    </div>

    <span style="margin-left: auto; font-size: 34px">
        🍃
    </span>

</div>

<!-- ======================================
     TENTANG RESKILL
======================================= -->

<section class="section">

    <div class="section-heading">

        <span class="eyebrow">
            Tentang ReSkill
        </span>

        <h2>
            Belajar mengelola limbah dengan lebih bijak
        </h2>

        <p>
            Materi edukasi, video pembelajaran, kuis,
            dan galeri karya dalam satu platform.
        </p>

    </div>

    <!-- ==================================
         DAFTAR FITUR UTAMA
    =================================== -->

    <div class="feature-grid">

        <!-- Fitur materi edukasi -->
        <article class="feature-card">

            <img
                src="<?= e(url('assets/images/materials.jpg')) ?>"
                alt=""
            >

            <h3>
                Materi Edukasi
            </h3>

            <p>
                Kenali jenis limbah, prinsip 3R,
                dan cara pengelolaannya.
            </p>

        </article>

        <!-- Fitur video pembelajaran -->
        <article class="feature-card">

            <img
                src="<?= e(url('assets/images/video.jpg')) ?>"
                alt=""
            >

            <h3>
                Video Pembelajaran
            </h3>

            <p>
                Tonton panduan pengolahan limbah secara praktis.
            </p>

        </article>

        <!-- Fitur kuis dan evaluasi -->
        <article class="feature-card">

            <img
                src="<?= e(url('assets/images/quiz.jpg')) ?>"
                alt=""
            >

            <h3>
                Kuis dan Evaluasi
            </h3>

            <p>
                Uji pemahamanmu tentang pengelolaan limbah.
            </p>

        </article>

        <!-- Fitur galeri karya -->
        <article class="feature-card">

            <img
                src="<?= e(url('assets/images/gallery.jpg')) ?>"
                alt=""
            >

            <h3>
                Galeri Karya
            </h3>

            <p>
                Jelajahi berbagai karya kreatif dari hasil daur ulang.
            </p>

        </article>

    </div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
