<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: config/koneksi.php
 * Konfigurasi Database, Session, Base URL, dan Helper Functions
 * ============================================================
 */

// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set zona waktu ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database MySQL
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_voting_kakang_senduk');
define('DB_PORT', 3306);

// Aktifkan reporting error mode exception untuk MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Jika database belum dibuat atau koneksi gagal
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'>
            <h2 style='color:#6B0F1A;'>Gagal Terhubung ke Database!</h2>
            <p>Pastikan MySQL di XAMPP telah aktif dan database <b>" . DB_NAME . "</b> telah diimpor dari file <code>database.sql</code>.</p>
            <p style='color:#666;'>Detail Error: " . htmlspecialchars($e->getMessage()) . "</p>
         </div>");
}

// Deteksi Otomatis BASE_URL Project
if (!defined('BASE_URL')) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $protocol = $is_https ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Mengambil root folder proyek dari SCRIPT_NAME
    $raw_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($raw_dir === '.' || $raw_dir === './') {
        $raw_dir = '';
    }
    $script_dir = '/' . trim($raw_dir, '/');
    // Hilangkan folder sub-bagian jika diakses dari dalam folder auth, pages, admin, atau proses
    $root_path = preg_replace('/(\/(admin|pages|auth|proses).*)$/i', '', $script_dir);
    $root_path = trim($root_path, '/');

    if (!empty($root_path)) {
        define('BASE_URL', $protocol . $host . '/' . $root_path . '/');
    } else {
        define('BASE_URL', $protocol . $host . '/');
    }
}

/**
 * ============================================================
 * HELPER FUNCTIONS (KEAMANAN, AUTENTIKASI, & DATA)
 * ============================================================
 */

/**
 * Cek apakah user telah login
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Cek apakah user adalah administrator
 * @return bool
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Wajibkan login untuk mengakses halaman
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash'] = [
            'type' => 'warning',
            'title' => 'Akses Terbatas',
            'message' => 'Silakan login terlebih dahulu untuk mengakses fitur ini.'
        ];
        header("Location: " . BASE_URL . "auth/login.php");
        exit;
    }
}

/**
 * Wajibkan peran admin untuk mengakses halaman
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'title' => 'Akses Ditolak',
            'message' => 'Anda tidak memiliki hak akses sebagai Administrator.'
        ];
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

/**
 * Ambil data user yang sedang login
 * @param mysqli $conn
 * @return array|null
 */
function get_logged_in_user($conn) {
    if (!is_logged_in()) {
        return null;
    }
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, nama, email, no_hp, role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Sanitasi string input untuk mencegah XSS
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Ambil data periode voting aktif saat ini
 * @param mysqli $conn
 * @return array|null
 */
function get_active_periode($conn) {
    $now = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("SELECT * FROM periode_voting WHERE status = 'Aktif' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($periode = $result->fetch_assoc()) {
        // Cek apakah waktu saat ini berada di dalam rentang
        $mulai = $periode['tanggal_mulai'];
        $selesai = $periode['tanggal_selesai'];
        $periode['is_within_time'] = ($now >= $mulai && $now <= $selesai);
        $periode['is_ended'] = ($now > $selesai);
        $periode['is_upcoming'] = ($now < $mulai);
        return $periode;
    }
    return null;
}

/**
 * Cek apakah pemilih sudah memberikan suara pada kategori tertentu
 * @param mysqli $conn
 * @param int $user_id
 * @param string $kategori 'Kakang' | 'Senduk'
 * @return int|bool Mengembalikan kontestan_id jika sudah memilih, atau false jika belum
 */
function user_has_voted($conn, $user_id, $kategori) {
    $user_id = (int)$user_id;
    $stmt = $conn->prepare("SELECT kontestan_id FROM voting WHERE user_id = ? AND kategori = ? LIMIT 1");
    $stmt->bind_param("is", $user_id, $kategori);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return (int)$row['kontestan_id'];
    }
    return false;
}

/**
 * Ambil semua suara yang telah diberikan oleh pemilih (Kakang & Senduk)
 * @param mysqli $conn
 * @param int $user_id
 * @return array ['Kakang' => kontestan_id|null, 'Senduk' => kontestan_id|null]
 */
function get_user_votes($conn, $user_id) {
    $user_id = (int)$user_id;
    $votes = ['Kakang' => null, 'Senduk' => null];
    $stmt = $conn->prepare("SELECT kategori, kontestan_id FROM voting WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $votes[$row['kategori']] = (int)$row['kontestan_id'];
    }
    return $votes;
}

/**
 * Format Tanggal & Jam ke format Indonesia
 * @param string $datetime
 * @param bool $show_time
 * @return string
 */
function format_tanggal_indo($datetime, $show_time = true) {
    if (empty($datetime)) return '-';
    $timestamp = strtotime($datetime);
    $bulan_indo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $hari = date('d', $timestamp);
    $bulan = $bulan_indo[(int)date('m', $timestamp)];
    $tahun = date('Y', $timestamp);
    $waktu = date('H:i', $timestamp) . ' WIB';

    if ($show_time) {
        return "$hari $bulan $tahun, $waktu";
    }
    return "$hari $bulan $tahun";
}

/**
 * Flash Notification Helper
 */
function set_flash($type, $title, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'title' => $title,
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
