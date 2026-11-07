<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: proses/simpan-voting.php
 * Pemrosesan Penyimpanan Suara dengan Atomic Database Transaction
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

// Pastikan user telah login
if (!is_logged_in()) {
    set_flash('error', 'Akses Ditolak', 'Silakan login terlebih dahulu untuk memberikan suara.');
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

// Hanya menerima request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "pages/voting.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$kontestan_id = isset($_POST['kontestan_id']) ? (int)$_POST['kontestan_id'] : 0;
$kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : '';

// Validasi nilai kategori
if (!in_array($kategori, ['Kakang', 'Senduk'])) {
    set_flash('error', 'Pilihan Tidak Valid', 'Kategori pemilihan yang dipilih tidak dikenali.');
    header("Location: " . BASE_URL . "pages/voting.php");
    exit;
}

if ($kontestan_id <= 0) {
    set_flash('error', 'Pilihan Tidak Valid', 'Kontestan yang dipilih tidak valid.');
    header("Location: " . BASE_URL . "pages/voting.php");
    exit;
}

// Validasi periode voting aktif
$periode = get_active_periode($conn);
if (!$periode || $periode['status'] !== 'Aktif' || empty($periode['is_within_time'])) {
    set_flash('error', 'Voting Ditutup', 'Saat ini periode pemilihan sedang tidak aktif atau di luar jadwal resmi.');
    header("Location: " . BASE_URL . "pages/voting.php");
    exit;
}

// Verifikasi kontestan memang ada dan sesuai kategori yang didaftarkan
$stmt_check = $conn->prepare("SELECT id, nama, nomor_peserta, kategori FROM kontestan WHERE id = ? AND kategori = ? LIMIT 1");
$stmt_check->bind_param("is", $kontestan_id, $kategori);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check->num_rows === 0) {
    $stmt_check->close();
    set_flash('error', 'Kontestan Tidak Sesuai', 'Data kontestan tidak cocok dengan kategori pemilihan.');
    header("Location: " . BASE_URL . "pages/voting.php");
    exit;
}
$kontestan = $res_check->fetch_assoc();
$stmt_check->close();

/**
 * DATABASE TRANSACTION (BEGIN, COMMIT, ROLLBACK)
 * Mencegah Race Condition dan Suara Ganda
 */
$conn->begin_transaction();

try {
    // Kunci baris voting user untuk kategori ini jika sudah ada (Pessimistic Locking)
    $stmt_vote = $conn->prepare("SELECT id FROM voting WHERE user_id = ? AND kategori = ? FOR UPDATE");
    $stmt_vote->bind_param("is", $user_id, $kategori);
    $stmt_vote->execute();
    $res_vote = $stmt_vote->get_result();

    if ($res_vote->num_rows > 0) {
        $stmt_vote->close();
        $conn->rollback();
        set_flash('warning', 'Suara Sudah Pernah Digunakan', "Anda sudah pernah memberikan suara untuk Kategori {$kategori}. Setiap akun hanya berhak 1 suara per kategori.");
        header("Location: " . BASE_URL . "pages/voting.php");
        exit;
    }
    $stmt_vote->close();

    // Simpan suara ke tabel voting
    $stmt_insert = $conn->prepare("INSERT INTO voting (user_id, kontestan_id, kategori, created_at) VALUES (?, ?, ?, NOW())");
    $stmt_insert->bind_param("iis", $user_id, $kontestan_id, $kategori);
    $stmt_insert->execute();
    $stmt_insert->close();

    // Commit seluruh perubahan
    $conn->commit();

    set_flash('success', 'Suara Berhasil Terkirim!', "Terima kasih! Suara Anda untuk {$kontestan['nomor_peserta']} - {$kontestan['nama']} ({$kategori}) telah sah dicatat oleh sistem.");
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    // Jika duplicate key terpicu oleh Unique Constraint
    if ($e->getCode() == 1062) {
        set_flash('warning', 'Peringatan Voting Ganda', "Sistem mendeteksi Anda telah memilih di kategori {$kategori}.");
    } else {
        set_flash('error', 'Gagal Menyimpan Suara', 'Terjadi gangguan koneksi pada database. Silakan coba kembali.');
    }
}

header("Location: " . BASE_URL . "pages/voting.php");
exit;
