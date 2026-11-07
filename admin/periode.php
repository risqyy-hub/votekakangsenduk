<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: admin/periode.php
 * Kelola Periode, Jadwal Pemilihan & Saklar Buka/Tutup Voting
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';
require_admin();

$admin = get_logged_in_user($conn);

// Ambil periode terbaru
$stmt = $conn->query("SELECT * FROM periode_voting ORDER BY id DESC LIMIT 1");
$periode = $stmt->fetch_assoc();

$error_message = '';

// Proses Saklar Buka / Tutup Cepat (Toggle Status)
if (isset($_GET['toggle']) && $periode) {
    $new_status = ($periode['status'] === 'Aktif') ? 'Nonaktif' : 'Aktif';
    $stmt_toggle = $conn->prepare("UPDATE periode_voting SET status = ? WHERE id = ?");
    $stmt_toggle->bind_param("si", $new_status, $periode['id']);
    if ($stmt_toggle->execute()) {
        $stmt_toggle->close();
        set_flash('success', 'Status Berubah', "Periode pemilihan sekarang berada dalam status: {$new_status}");
    }
    header("Location: " . BASE_URL . "admin/periode.php");
    exit;
}

// Proses Simpan / Perbarui Form Periode
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_periode = trim($_POST['nama_periode'] ?? '');
    $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
    $status = trim($_POST['status'] ?? 'Aktif');

    if (empty($nama_periode) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
        $error_message = 'Seluruh kolom formulir periode wajib diisi!';
    } elseif (strtotime($tanggal_selesai) <= strtotime($tanggal_mulai)) {
        $error_message = 'Tanggal selesai pemilihan harus lebih baru daripada tanggal mulai!';
    } else {
        if ($periode) {
            // Update periode yang sudah ada
            $stmt_save = $conn->prepare("UPDATE periode_voting SET nama_periode = ?, tanggal_mulai = ?, tanggal_selesai = ?, status = ? WHERE id = ?");
            $stmt_save->bind_param("ssssi", $nama_periode, $tanggal_mulai, $tanggal_selesai, $status, $periode['id']);
        } else {
            // Buat periode baru
            $stmt_save = $conn->prepare("INSERT INTO periode_voting (nama_periode, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, ?)");
            $stmt_save->bind_param("ssss", $nama_periode, $tanggal_mulai, $tanggal_selesai, $status);
        }

        if ($stmt_save->execute()) {
            $stmt_save->close();
            set_flash('success', 'Periode Diperbarui', 'Pengaturan jadwal dan status periode voting berhasil disimpan.');
            header("Location: " . BASE_URL . "admin/periode.php");
            exit;
        } else {
            $error_message = 'Terjadi kesalahan sistem saat menyimpan periode.';
            $stmt_save->close();
        }
    }
}

$active_menu = 'periode';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Periode Voting - Admin Kakang Senduk</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<div class="admin-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="admin-content">
    <header class="admin-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary btn-sm d-lg-none" id="toggleSidebarBtn">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h5 class="fw-bold text-dark mb-0">Kelola Periode & Jadwal Pemilihan</h5>
      </div>
    </header>

    <main class="admin-body">
      <div class="row g-4 justify-content-center">
        <!-- Status & Saklar Cepat Buka/Tutup -->
        <div class="col-12 col-lg-5">
          <div class="card card-table shadow-sm h-100">
            <div class="card-header">
              <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-broadcast text-maroon me-2"></i>Status Kontrol Pemilihan Langsung
              </h6>
            </div>
            <div class="p-4 text-center">
              <?php if ($periode && $periode['status'] === 'Aktif'): ?>
                <div class="mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; border: 3px solid #10B981;">
                  <i class="bi bi-check2-circle"></i>
                </div>
                <h4 class="fw-bold text-success mb-1">Voting Sedang Dibuka</h4>
                <p class="text-muted small mb-4">Pemilih terdaftar dapat menyalurkan hak suaranya di bilik voting.</p>
                
                <a href="<?= BASE_URL ?>admin/periode.php?toggle=1" class="btn btn-danger w-100 py-3 rounded-pill fw-bold" onclick="return confirm('Apakah Anda yakin ingin MENUTUP pemungutan suara sekarang?')">
                  <i class="bi bi-stop-circle-fill me-1"></i> Tutup Pemungutan Suara Sekarang
                </a>
              <?php else: ?>
                <div class="mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; border: 3px solid #EF4444;">
                  <i class="bi bi-pause-circle"></i>
                </div>
                <h4 class="fw-bold text-danger mb-1">Voting Sedang Ditutup</h4>
                <p class="text-muted small mb-4">Pemilih tidak dapat mengirimkan suara baru sampai periode diaktifkan kembali.</p>

                <a href="<?= BASE_URL ?>admin/periode.php?toggle=1" class="btn btn-success w-100 py-3 rounded-pill fw-bold">
                  <i class="bi bi-play-circle-fill me-1"></i> Buka Pemungutan Suara Sekarang
                </a>
              <?php endif; ?>

              <hr class="my-4">
              <div class="text-start small text-muted">
                <div><b>Waktu Mulai:</b> <?= $periode ? format_tanggal_indo($periode['tanggal_mulai']) : '-' ?></div>
                <div class="mt-1"><b>Waktu Selesai:</b> <?= $periode ? format_tanggal_indo($periode['tanggal_selesai']) : '-' ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Pengaturan Periode Lengkap -->
        <div class="col-12 col-lg-7">
          <div class="card card-table shadow-sm">
            <div class="card-header">
              <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-calendar2-range text-maroon me-2"></i>Formulir Pengaturan Jadwal Pemilihan
              </h6>
            </div>
            <div class="p-4">
              <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger d-flex align-items-center rounded-3 mb-4" role="alert">
                  <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2 fs-5"></i>
                  <div><?= htmlspecialchars($error_message) ?></div>
                </div>
              <?php endif; ?>

              <form action="" method="POST">
                <div class="mb-3">
                  <label class="form-label fw-bold small text-muted">Nama Periode Pemilihan</label>
                  <input type="text" name="nama_periode" class="form-control" value="<?= htmlspecialchars($periode['nama_periode'] ?? 'Pemilihan Duta Wisata Kakang Senduk Ponorogo 2026/2027') ?>" required>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">Tanggal & Jam Mulai</label>
                    <input type="datetime-local" name="tanggal_mulai" class="form-control" value="<?= $periode ? date('Y-m-d\TH:i', strtotime($periode['tanggal_mulai'])) : date('Y-m-d\T00:00') ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">Tanggal & Jam Selesai</label>
                    <input type="datetime-local" name="tanggal_selesai" class="form-control" value="<?= $periode ? date('Y-m-d\TH:i', strtotime($periode['tanggal_selesai'])) : date('Y-m-d\T23:59', strtotime('+14 days')) ?>" required>
                  </div>
                </div>

                <div class="mb-4">
                  <label class="form-label fw-bold small text-muted">Status Operasional Periode</label>
                  <select name="status" class="form-select" required>
                    <option value="Aktif" <?= ($periode && $periode['status'] === 'Aktif') ? 'selected' : '' ?>>Aktif (Pemilih Dapat Voting)</option>
                    <option value="Nonaktif" <?= ($periode && $periode['status'] === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif (Voting Dikunci)</option>
                  </select>
                </div>

                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-maroon px-5 py-2">
                    <i class="bi bi-save-fill me-1"></i> Simpan Pengaturan Jadwal
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>assets/js/script.js"></script>

<?php if ($flash): ?>
<script>
  Swal.fire({
    icon: '<?= $flash['type'] ?>',
    title: '<?= addslashes($flash['title']) ?>',
    text: '<?= addslashes($flash['message']) ?>',
    confirmButtonColor: '#6B0F1A'
  });
</script>
<?php endif; ?>
</body>
</html>
