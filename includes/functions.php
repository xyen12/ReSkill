
<?php

// ==========================================
// INISIALISASI SESSION DAN DATABASE
// ==========================================

// Memulai session jika belum aktif.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menghubungkan aplikasi dengan database.
require_once __DIR__ . '/../config/koneksi.php';


// ==========================================
// FUNGSI KEAMANAN OUTPUT
// ==========================================

/**
 * Mengamankan output agar karakter HTML
 * tidak dieksekusi sebagai kode.
 */
function e($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


// ==========================================
// FUNGSI URL DAN REDIRECT
// ==========================================

/**
 * Membuat URL berdasarkan direktori utama aplikasi.
 */
function url(string $path = ''): string
{
    return '/ReSkill/' . ltrim($path, '/');
}

/**
 * Mengarahkan pengguna ke halaman tertentu.
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}


// ==========================================
// FUNGSI MENGAMBIL DATA PENGGUNA
// ==========================================

/**
 * Mengambil data pengguna yang sedang login.
 * Data disimpan sementara agar tidak perlu
 * mengambilnya berulang kali dalam satu request.
 */
function current_user(): ?array
{
    static $checked = false;
    static $user = null;

    if (!$checked) {

        $checked = true;

        // Mengambil ID pengguna dari session.
        $id = filter_var(
            $_SESSION['user_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($id) {

            global $pdo;

            // Mengambil data pengguna dari database.
            $stmt = $pdo->prepare(
                'SELECT id, name, email, role
                 FROM users
                 WHERE id = ?
                 LIMIT 1'
            );

            $stmt->execute([$id]);
            $user = $stmt->fetch() ?: null;
        }

        // Memperbarui data session jika pengguna ditemukan.
        if ($user) {

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

        } else {

            // Menghapus session jika pengguna tidak ditemukan.
            unset(
                $_SESSION['user_id'],
                $_SESSION['name'],
                $_SESSION['email'],
                $_SESSION['role']
            );
        }
    }

    return $user;
}


// ==========================================
// FUNGSI AUTENTIKASI DAN HAK AKSES
// ==========================================

/**
 * Memeriksa apakah pengguna sudah login.
 */
function is_logged_in(): bool
{
    return current_user() !== null;
}

/**
 * Memeriksa apakah pengguna memiliki role admin.
 */
function is_admin(): bool
{
    $user = current_user();

    return $user !== null && $user['role'] === 'admin';
}

/**
 * Memastikan pengguna sudah login.
 * Jika belum, arahkan ke halaman login.
 */
function require_login(): void
{
    if (!is_logged_in()) {

        set_flash(
            'error',
            'Sesi login tidak valid atau akun sudah tidak tersedia. Silakan masuk kembali.'
        );

        redirect('auth/login.php');
    }
}

/**
 * Memastikan halaman hanya dapat diakses admin.
 */
function require_admin(): void
{
    require_login();

    if (!is_admin()) {

        http_response_code(403);

        exit('Akses hanya tersedia untuk admin.');
    }
}


// ==========================================
// FUNGSI NOTIFIKASI (FLASH MESSAGE)
// ==========================================

/**
 * Menyimpan pesan notifikasi ke dalam session.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Mengambil pesan notifikasi dan menghapusnya
 * agar hanya ditampilkan satu kali.
 */
function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;

    unset($_SESSION['flash']);

    return $flash;
}


// ==========================================
// FUNGSI IKON KATEGORI LIMBAH
// ==========================================

/**
 * Mengembalikan ikon berdasarkan nama kategori.
 */
function category_icon(string $name): string
{
    return [
        'Organik' => '🍃',
        'Anorganik' => '♻️',
        'B3' => '⚠️',
        'Residu' => '🗑️'
    ][$name] ?? '🌱';
}


// ==========================================
// FUNGSI YOUTUBE EMBED
// ==========================================

/**
 * Mengubah URL YouTube menjadi URL embed.
 * Mendukung format youtu.be, watch, embed, dan shorts.
 */
function youtube_embed(string $url): string
{
    if (
        preg_match(
            '~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~',
            $url,
            $matches
        )
    ) {
        return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
    }

    // Mengembalikan string kosong jika URL tidak valid.
    return '';
}
