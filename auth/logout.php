<?php

require_once __DIR__ . '/../includes/functions.php';

// Mengosongkan seluruh data session.
$_SESSION = [];

// Menghancurkan session pengguna.
session_destroy();

// Mengarahkan pengguna kembali ke halaman utama.
header('Location: ' . url());
exit;

?>
