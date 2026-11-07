<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: admin/hapus-kontestan.php
 * Pemrosesan Penghapusan Kontestan
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Ambil info foto sebelum dihapus
    $stmt = $conn->prepare("SELECT nama, foto FROM kontestan WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $nama = $row['nama'];
        $foto = $row['foto'];
        $stmt->close();

        // Hapus dari database (Relasi FOREIGN KEY ON DELETE CASCADE akan otomatis membersihkan suara)
        $stmt_del = $conn->prepare("DELETE FROM kontestan WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            $stmt_del->close();

            // Hapus file foto fisik jika hasil unggahan kustom
            $file_path = __DIR__ . '/../uploads/' . $foto;
            if (file_exists($file_path) && strpos($foto, 'kontestan_') === 0) {
                @unlink($file_path);
            }

            set_flash('success', 'Berhasil Dihapus', "Data kontestan {$nama} dan seluruh suara terkait telah berhasil dihapus.");
        } else {
            $stmt_del->close();
            set_flash('error', 'Gagal Menghapus', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    } else {
        $stmt->close();
        set_flash('error', 'Tidak Ditemukan', 'Data kontestan tidak ditemukan.');
    }
}

header("Location: " . BASE_URL . "admin/kontestan.php");
exit;
