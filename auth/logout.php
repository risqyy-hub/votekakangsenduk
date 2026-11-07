<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: auth/logout.php
 * Proses Keluar Akun & Pembersihan Session
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

// Hapus seluruh variabel session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Mulai session baru hanya untuk menampung pesan flash
session_start();
set_flash('info', 'Berhasil Keluar', 'Anda telah berhasil keluar dari sistem pemilihan.');

header("Location: " . BASE_URL . "auth/login.php");
exit;
