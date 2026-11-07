<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: auth/login.php
 * Halaman Masuk Akun (Admin & Pemilih)
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

// Jika sudah login, redirect sesuai peran
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: " . BASE_URL . "admin/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "index.php");
    }
    exit;
}

$error_message = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $email_val = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    if (empty($email) || empty($password)) {
        $error_message = 'Email dan Password wajib diisi lengkap!';
    } else {
        // Prepared Statement untuk mencegah SQL Injection
        $stmt = $conn->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verifikasi Password Hash
            if (password_verify($password, $user['password'])) {
                // Regenerate session id untuk keamanan session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                set_flash('success', 'Selamat Datang!', 'Berhasil masuk sebagai ' . htmlspecialchars($user['nama']));

                if ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "admin/dashboard.php");
                } else {
                    header("Location: " . BASE_URL . "pages/voting.php");
                }
                exit;
            } else {
                $error_message = 'Password yang Anda masukkan salah!';
            }
        } else {
            $error_message = 'Akun dengan email tersebut tidak ditemukan!';
        }
        $stmt->close();
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk Akun - Pemilihan Kakang Senduk Ponorogo</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <!-- Custom Modern Cultural CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="bg-clean">

  <!-- Minimal Glass Header -->
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
      <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
      </a>
    </div>
  </header>

  <!-- Login Form Wrapper -->
  <main class="auth-wrapper">
    <div class="auth-card">
      <div class="text-center mb-4">
        <div class="mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 16px; background: var(--grad-maroon); display: flex; align-items: center; justify-content: center; color: var(--royal-gold); font-size: 1.8rem; box-shadow: 0 8px 20px rgba(107, 15, 26, 0.25);">
          <i class="bi bi-person-circle"></i>
        </div>
        <h3 class="fw-bold text-dark mb-1">Selamat Datang</h3>
        <p class="text-muted small">Silakan masuk untuk menyalurkan hak suara Anda</p>
      </div>

      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger d-flex align-items-center rounded-3 mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2 fs-5"></i>
          <div><?= htmlspecialchars($error_message) ?></div>
        </div>
      <?php endif; ?>

      <form action="" method="POST" novalidate>
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?= $email_val ?>" required autofocus>
          <label for="email"><i class="bi bi-envelope me-1 text-muted"></i> Alamat Email</label>
        </div>

        <div class="form-floating mb-3 position-relative">
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
          <label for="password"><i class="bi bi-lock me-1 text-muted"></i> Password</label>
          <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none me-2" id="togglePassword" style="z-index: 5;">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label small text-muted" for="rememberMe">
              Ingat saya
            </label>
          </div>
          <a href="#" onclick="Swal.fire('Informasi Akun Demo', 'Akun Pengujian:<br><b>Admin:</b> admin@ponorogo.go.id / admin123<br><b>Pemilih:</b> budi@gmail.com / pemilih123', 'info'); return false;" class="small text-decoration-none text-maroon fw-semibold">
            Bantuan Akun?
          </a>
        </div>

        <button type="submit" class="btn btn-maroon w-100 py-3 rounded-pill fw-bold fs-6 mb-3 shadow-sm">
          <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
        </button>

        <div class="text-center">
          <p class="small text-muted mb-0">
            Belum memiliki akun pemilih? 
            <a href="<?= BASE_URL ?>auth/register.php" class="text-maroon fw-bold text-decoration-none">Daftar di sini</a>
          </p>
        </div>
      </form>
    </div>
  </main>

  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Toggle Password Visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePassword && passwordInput) {
      togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.classList.toggle('bi-eye');
        eyeIcon.classList.toggle('bi-eye-slash');
      });
    }

    // Flash Message Trigger
    <?php if ($flash): ?>
    Swal.fire({
      icon: '<?= $flash['type'] ?>',
      title: '<?= addslashes($flash['title']) ?>',
      text: '<?= addslashes($flash['message']) ?>',
      confirmButtonColor: '#6B0F1A'
    });
    <?php endif; ?>
  </script>
</body>
</html>
