<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: admin/edit-kontestan.php
 * Formulir Ubah / Perbarui Data Kontestan
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: " . BASE_URL . "admin/kontestan.php");
    exit;
}

// Ambil data kontestan
$stmt = $conn->prepare("SELECT * FROM kontestan WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    set_flash('error', 'Tidak Ditemukan', 'Data kontestan tidak ditemukan.');
    header("Location: " . BASE_URL . "admin/kontestan.php");
    exit;
}
$kontestan = $res->fetch_assoc();
$stmt->close();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_peserta = trim($_POST['nomor_peserta'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kategori = trim($_POST['kategori'] ?? 'Kakang');
    $ttl = trim($_POST['ttl'] ?? '');
    $asal_instansi = trim($_POST['asal_instansi'] ?? '');
    $visi = trim($_POST['visi'] ?? '');
    $misi = trim($_POST['misi'] ?? '');
    $prestasi = trim($_POST['prestasi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($nomor_peserta) || empty($nama) || empty($ttl) || empty($asal_instansi) || empty($visi) || empty($misi) || empty($prestasi) || empty($deskripsi)) {
        $error_message = 'Seluruh kolom formulir wajib diisi dengan lengkap!';
    } elseif (!in_array($kategori, ['Kakang', 'Senduk'])) {
        $error_message = 'Kategori harus berupa Kakang atau Senduk!';
    } else {
        $foto_filename = $kontestan['foto']; // Nilai default tetap foto lama

        // Cek jika ada unggahan foto baru
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto'];
            $allowed_ext = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $file_size = $file['size'];

            if (!in_array($ext, $allowed_ext)) {
                $error_message = 'Format file foto tidak diizinkan! Gunakan format: JPG, PNG, WEBP, atau SVG.';
            } elseif ($file_size > 3 * 1024 * 1024) {
                $error_message = 'Ukuran file foto maksimal adalah 3 MB!';
            } else {
                $new_filename = 'kontestan_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $upload_path = __DIR__ . '/../uploads/' . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Hapus foto lama jika bukan foto bawaan svg seeder
                    $old_file = __DIR__ . '/../uploads/' . $kontestan['foto'];
                    if (file_exists($old_file) && strpos($kontestan['foto'], 'kontestan_') === 0) {
                        @unlink($old_file);
                    }
                    $foto_filename = $new_filename;
                } else {
                    $error_message = 'Gagal menyimpan foto baru.';
                }
            }
        }

        if (empty($error_message)) {
            $stmt_update = $conn->prepare("UPDATE kontestan SET nomor_peserta = ?, nama = ?, kategori = ?, ttl = ?, foto = ?, asal_instansi = ?, visi = ?, misi = ?, prestasi = ?, deskripsi = ? WHERE id = ?");
            $stmt_update->bind_param("ssssssssssi", $nomor_peserta, $nama, $kategori, $ttl, $foto_filename, $asal_instansi, $visi, $misi, $prestasi, $deskripsi, $id);

            if ($stmt_update->execute()) {
                $stmt_update->close();
                set_flash('success', 'Berhasil Diperbarui', "Data kontestan {$nomor_peserta} - {$nama} berhasil diperbarui.");
                header("Location: " . BASE_URL . "admin/kontestan.php");
                exit;
            } else {
                $error_message = 'Gagal menyimpan pembaruan ke database.';
                $stmt_update->close();
            }
        }
    }
}

$active_menu = 'kontestan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Kontestan - <?= htmlspecialchars($kontestan['nama']) ?></title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        <h5 class="fw-bold text-dark mb-0">Ubah Data Kontestan</h5>
      </div>

      <a href="<?= BASE_URL ?>admin/kontestan.php" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
      </a>
    </header>

    <main class="admin-body">
      <div class="card card-table shadow-sm" style="max-width: 900px; margin: 0 auto;">
        <div class="card-header">
          <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-pencil-square text-maroon me-2"></i>Perbarui Data: <?= htmlspecialchars($kontestan['nama']) ?>
          </h6>
        </div>

        <div class="p-4">
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger d-flex align-items-center rounded-3 mb-4" role="alert">
              <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2 fs-5"></i>
              <div><?= htmlspecialchars($error_message) ?></div>
            </div>
          <?php endif; ?>

          <form action="" method="POST" enctype="multipart/form-data">
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label fw-bold small text-muted">Nomor Peserta</label>
                <input type="text" name="nomor_peserta" class="form-control" value="<?= htmlspecialchars($kontestan['nomor_peserta']) ?>" required>
              </div>

              <div class="col-md-5">
                <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($kontestan['nama']) ?>" required>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-bold small text-muted">Kategori</label>
                <select name="kategori" class="form-select" required>
                  <option value="Kakang" <?= $kontestan['kategori'] === 'Kakang' ? 'selected' : '' ?>>Kakang</option>
                  <option value="Senduk" <?= $kontestan['kategori'] === 'Senduk' ? 'selected' : '' ?>>Senduk</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Tempat & Tanggal Lahir</label>
                <input type="text" name="ttl" class="form-control" value="<?= htmlspecialchars($kontestan['ttl']) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Asal Sekolah / Instansi / Kampus</label>
                <input type="text" name="asal_instansi" class="form-control" value="<?= htmlspecialchars($kontestan['asal_instansi']) ?>" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold small text-muted">Ganti Foto Kontestan (Kosongkan jika tetap)</label>
              <div class="d-flex align-items-center gap-3 mb-2">
                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($kontestan['foto']) ?>" alt="Foto Sekarang" class="rounded-3 border" style="width: 70px; height: 85px; object-fit: cover;">
                <span class="small text-muted">Foto saat ini: <code><?= htmlspecialchars($kontestan['foto']) ?></code></span>
              </div>
              <input type="file" name="foto" id="fotoInput" class="form-control" accept=".jpg,.jpeg,.png,.svg,.webp">
              <small class="text-muted">Biarkan kosong jika tidak ingin mengganti file foto.</small>
              <div class="mt-2" id="previewBox" style="display: none;">
                <img id="imagePreview" src="#" alt="Pratinjau Foto Baru" class="rounded-3 border" style="max-height: 180px;">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold small text-muted">Visi Kontestan</label>
              <textarea name="visi" class="form-control" rows="2" required><?= htmlspecialchars($kontestan['visi']) ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold small text-muted">Misi & Rencana Aksi</label>
              <textarea name="misi" class="form-control" rows="3" required><?= htmlspecialchars($kontestan['misi']) ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold small text-muted">Prestasi</label>
              <textarea name="prestasi" class="form-control" rows="3" required><?= htmlspecialchars($kontestan['prestasi']) ?></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold small text-muted">Deskripsi Profil & Advokasi</label>
              <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($kontestan['deskripsi']) ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="<?= BASE_URL ?>admin/kontestan.php" class="btn btn-outline-secondary px-4">Batal</a>
              <button type="submit" class="btn btn-maroon px-5">
                <i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const fotoInput = document.getElementById('fotoInput');
  const previewBox = document.getElementById('previewBox');
  const imagePreview = document.getElementById('imagePreview');

  fotoInput.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        imagePreview.setAttribute('src', e.target.result);
        previewBox.style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      previewBox.style.display = 'none';
    }
  });
</script>
</body>
</html>
