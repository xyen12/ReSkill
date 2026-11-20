<?php

require_once __DIR__ . '/../../includes/functions.php';

// Memastikan pengguna sudah login.
require_login();

// Mengarahkan pengguna ke halaman Materi Edukasi.
redirect('modules/materials/index.php');
