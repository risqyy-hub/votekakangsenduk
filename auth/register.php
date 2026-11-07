<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: auth/register.php
 * Halaman Pendaftaran Akun Pemilih Baru
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

// Jika sudah login, redirect
if (is_logged_in()) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$error_message = '';
$nama = '';
$email = '';
$no_hp = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Validasi dasar
    if (empty($nama) || empty($email) || empty($no_hp) || empty($password) || empty($konfirmasi_password)) {
        $error_message = 'Seluruh kolom formulir wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format alamat email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password minimal harus 6 karakter!';
    } elseif ($password !== $konfirmasi_password) {
        $error_message = 'Konfirmasi password tidak cocok dengan password yang dimasukkan!';
    } else {
        // Cek keunikan email menggunakan Prepared Statement
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error_message = 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk.';
            $stmt_check->close();
        } else {
            $stmt_check->close();

            // Hash password secara aman
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $role = 'pemilih';

            $stmt_insert = $conn->prepare("INSERT INTO users (nama, email, no_hp, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $nama, $email, $no_hp, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                $new_user_id = $stmt_insert->insert_id;
                $stmt_insert->close();

                // Auto login setelah registrasi berhasil
                session_regenerate_id(true);
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['user_nama'] = $nama;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;

                set_flash('success', 'Registrasi Berhasil!', 'Selamat datang, ' . htmlspecialchars($nama) . '. Anda dapat langsung memberikan suara voting.');
                header("Location: " . BASE_URL . "pages/voting.php");
                exit;
            } else {
                $error_message = 'Terjadi kesalahan sistem saat menyimpan data pendaftaran.';
                $stmt_insert->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun Pemilih - Pemilihan Kakang Senduk Ponorogo</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="bg-clean">

  <header class="py-3 px-4 border-bottom bg-white shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
      <a href="<?= BASE_URL ?>index.php" class="navbar-brand text-decoration-none">
        <div class="brand-logo">
          <i class="bi bi-award-fill"></i>
        </div>
        <div>
          <span class="d-block lh-1 text-maroon" style="font-size: 1.15rem; font-weight: 800;">KAKANG SENDUK</span>
          <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.15em;">Kabupaten Ponorogo</small>
        </div>
      </a>
      <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sudah Punya Akun? Masuk
      </a>
    </div>
  </header>

  <main class="auth-wrapper py-5">
    <div class="auth-card" style="max-width: 520px;">
      <div class="text-center mb-4">
        <div class="mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 16px; background: var(--grad-gold); display: flex; align-items: center; justify-content: center; color: #2D1A02; font-size: 1.8rem; box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);">
          <i class="bi bi-person-plus-fill"></i>
        </div>
        <h3 class="fw-bold text-dark mb-1">Registrasi Pemilih</h3>
        <p class="text-muted small">Daftarkan akun resmi untuk memilih Kakang & Senduk favorit Anda</p>
      </div>

      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger d-flex align-items-center rounded-3 mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2 fs-5"></i>
          <div><?= htmlspecialchars($error_message) ?></div>
        </div>
      <?php endif; ?>

      <form action="" method="POST" novalidate>
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Lengkap" value="<?= htmlspecialchars($nama) ?>" required>
          <label for="nama"><i class="bi bi-person me-1 text-muted"></i> Nama Lengkap</label>
        </div>

        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?= htmlspecialchars($email) ?>" required>
          <label for="email"><i class="bi bi-envelope me-1 text-muted"></i> Alamat Email</label>
        </div>

        <div class="form-floating mb-3">
          <input type="tel" class="form-control" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($no_hp) ?>" required>
          <label for="no_hp"><i class="bi bi-telephone me-1 text-muted"></i> Nomor WhatsApp / HP</label>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <div class="form-floating">
              <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
              <label for="password"><i class="bi bi-lock me-1 text-muted"></i> Password</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating">
              <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi Password" required>
              <label for="konfirmasi_password"><i class="bi bi-shield-check me-1 text-muted"></i> Konfirmasi</label>
            </div>
          </div>
        </div>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="termsCheck" required checked>
          <label class="form-check-label small text-muted" for="termsCheck">
            Saya menyatakan data diri yang diisi adalah benar dan menyetujui ketentuan pemilihan 1 akun = 1 suara Kakang & 1 suara Senduk.
          </label>
        </div>

        <button type="submit" class="btn btn-maroon w-100 py-3 rounded-pill fw-bold fs-6 mb-3 shadow-sm">
          <i class="bi bi-check2-circle me-1"></i> Buat Akun & Mulai Memilih
        </button>

        <div class="text-center">
          <p class="small text-muted mb-0">
            Sudah terdaftar sebelumnya? 
            <a href="<?= BASE_URL ?>auth/login.php" class="text-maroon fw-bold text-decoration-none">Masuk ke Akun</a>
          </p>
        </div>
      </form>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
