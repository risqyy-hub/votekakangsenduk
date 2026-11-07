<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: index.php
 * Halaman Beranda Utama
 * ============================================================
 */

require_once __DIR__ . '/config/koneksi.php';

$periode = get_active_periode($conn);
$user = get_logged_in_user($conn);

// Hitung total kontestan
$total_kakang = 0;
$total_senduk = 0;
$res_kakang = $conn->query("SELECT COUNT(*) AS total FROM kontestan WHERE kategori = 'Kakang'");
if ($r = $res_kakang->fetch_assoc()) $total_kakang = (int)$r['total'];

$res_senduk = $conn->query("SELECT COUNT(*) AS total FROM kontestan WHERE kategori = 'Senduk'");
if ($r = $res_senduk->fetch_assoc()) $total_senduk = (int)$r['total'];

// Hitung total suara masuk
$total_suara = 0;
$res_suara = $conn->query("SELECT COUNT(*) AS total FROM voting");
if ($r = $res_suara->fetch_assoc()) $total_suara = (int)$r['total'];

// Ambil 4 kontestan acak/unggulan untuk preview
$preview_kontestan = $conn->query("SELECT * FROM kontestan ORDER BY nomor_peserta ASC LIMIT 4");

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pemilihan Duta Wisata Kakang Senduk Ponorogo 2026</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

  <!-- Navbar Glassmorphism -->
  <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
      <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
        <div class="brand-logo">
          <i class="bi bi-award-fill"></i>
        </div>
        <div>
          <span class="d-block lh-1 text-maroon" style="font-size: 1.15rem; font-weight: 800;">KAKANG SENDUK</span>
          <small class="text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.15em;">Bumi Reog Ponorogo</small>
        </div>
      </a>

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" href="<?= BASE_URL ?>index.php"><i class="bi bi-house-door me-1"></i> Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>pages/kontestan.php"><i class="bi bi-people me-1"></i> Kontestan</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>pages/voting.php"><i class="bi bi-check2-square me-1"></i> Voting</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>pages/klasemen.php"><i class="bi bi-bar-chart-line me-1"></i> Klasemen</a>
          </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <?php if (is_logged_in()): ?>
            <div class="dropdown">
              <button class="btn btn-outline-maroon dropdown-toggle px-3 py-2" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle text-gold me-1"></i> <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mt-2">
                <li><h6 class="dropdown-header small text-muted">Peran: <?= ucfirst($_SESSION['user_role']) ?></h6></li>
                <?php if (is_admin()): ?>
                  <li><a class="dropdown-item rounded-3" href="<?= BASE_URL ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2 text-maroon"></i> Panel Admin</a></li>
                  <li><hr class="dropdown-divider"></li>
                <?php endif; ?>
                <li><a class="dropdown-item rounded-3 text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-maroon px-4">Masuk</a>
            <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-gold px-4">Daftar</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section Modern Cultural Elegance -->
  <section class="hero-section">
    <div class="container position-relative" style="z-index: 2;">
      <div class="row align-items-center gy-5">
        <div class="col-lg-7">
          <div class="hero-badge">
            <i class="bi bi-stars"></i> Official E-Voting Portal 2026/2027
          </div>
          <h1 class="hero-title">
            Wujudkan Kejayaan Budaya Bersama <span class="text-gradient-gold font-serif">Kakang & Senduk</span> Ponorogo
          </h1>
          <p class="hero-subtitle">
            Salurkan aspirasi dan suara Anda untuk memilih figur Duta Wisata terbaik yang akan mengharumkan keagungan seni Reog Ponorogo di kancah nasional dan internasional.
          </p>

          <div class="d-flex flex-wrap gap-3">
            <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-gold btn-lg px-4 py-3 shadow-lg">
              <i class="bi bi-check-circle-fill me-1"></i> Voting Sekarang
            </a>
            <a href="<?= BASE_URL ?>pages/kontestan.php" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill">
              <i class="bi bi-person-lines-fill me-1"></i> Profil Kontestan
            </a>
            <a href="<?= BASE_URL ?>pages/klasemen.php" class="btn btn-outline-gold btn-lg px-4 py-3 rounded-pill">
              <i class="bi bi-trophy-fill me-1"></i> Klasemen Live
            </a>
          </div>
        </div>

        <!-- Countdown Widget -->
        <div class="col-lg-5">
          <div class="countdown-box">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 border-secondary border-opacity-25">
              <div>
                <span class="badge bg-gold text-dark fw-bold mb-1">JADWAL PEMILIHAN</span>
                <h5 class="text-white mb-0 fw-bold"><?= htmlspecialchars($periode['nama_periode'] ?? 'Periode Pemilihan') ?></h5>
              </div>
              <i class="bi bi-clock-history fs-2 text-gold"></i>
            </div>

            <?php if ($periode && $periode['status'] === 'Aktif'): ?>
              <p class="text-light small mb-3">Waktu pemilihan akan ditutup dalam:</p>
              <div class="countdown-grid" id="votingCountdown" data-target="<?= date('Y-m-d\TH:i:s', strtotime($periode['tanggal_selesai'])) ?>">
                <div class="countdown-unit">
                  <div class="countdown-number" id="days">00</div>
                  <div class="countdown-label">Hari</div>
                </div>
                <div class="countdown-unit">
                  <div class="countdown-number" id="hours">00</div>
                  <div class="countdown-label">Jam</div>
                </div>
                <div class="countdown-unit">
                  <div class="countdown-number" id="minutes">00</div>
                  <div class="countdown-label">Menit</div>
                </div>
                <div class="countdown-unit">
                  <div class="countdown-number" id="seconds">00</div>
                  <div class="countdown-label">Detik</div>
                </div>
              </div>
              <div class="text-center mt-3 text-warning small" id="countdownStatus">
                <i class="bi bi-shield-fill-check me-1"></i> Periode Sedang Aktif Hingga <?= format_tanggal_indo($periode['tanggal_selesai']) ?>
              </div>
            <?php else: ?>
              <div class="alert alert-warning mb-0 text-center rounded-3">
                <i class="bi bi-exclamation-circle-fill me-1"></i> Saat ini tidak ada periode voting yang aktif.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Quick Stats Counter Section -->
  <section class="py-5 bg-white border-bottom">
    <div class="container">
      <div class="row g-4 text-center">
        <div class="col-6 col-md-3">
          <div class="p-3">
            <div class="text-maroon fs-1 fw-bold"><?= $total_kakang ?></div>
            <p class="text-muted fw-semibold mb-0">Finalis Kakang</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3">
            <div class="text-maroon fs-1 fw-bold"><?= $total_senduk ?></div>
            <p class="text-muted fw-semibold mb-0">Finalis Senduk</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3">
            <div class="text-gold fs-1 fw-bold"><?= $total_suara ?></div>
            <p class="text-muted fw-semibold mb-0">Suara Terverifikasi</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3">
            <div class="text-success fs-1 fw-bold"><i class="bi bi-patch-check-fill"></i></div>
            <p class="text-muted fw-semibold mb-0">Sistem Aman & Adil</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Preview Kontestan Section -->
  <section class="py-5 my-3">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Duta Budaya & Pariwisata</span>
        <h2 class="section-title">Finalis <span>Kakang & Senduk</span> Ponorogo</h2>
        <div class="gold-divider"></div>
        <p class="section-subtitle">Kenali visi, misi, dan latar belakang para generasi muda berbakat pilihan Ponorogo.</p>
      </div>

      <div class="row g-4">
        <?php while ($row = $preview_kontestan->fetch_assoc()): ?>
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="card-contestant">
              <div class="image-wrapper">
                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($row['foto']) ?>" alt="<?= htmlspecialchars($row['nama']) ?>">
                <span class="nomor-badge"><?= htmlspecialchars($row['nomor_peserta']) ?></span>
                <span class="category-badge <?= $row['kategori'] === 'Kakang' ? 'badge-kakang' : 'badge-senduk' ?>">
                  <?= htmlspecialchars($row['kategori']) ?>
                </span>
              </div>
              <div class="card-body">
                <h5 class="contestant-name"><?= htmlspecialchars($row['nama']) ?></h5>
                <div class="contestant-instansi">
                  <i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($row['asal_instansi']) ?>
                </div>
                <div class="contestant-quote">
                  "<?= htmlspecialchars($row['visi']) ?>"
                </div>
                <div class="card-footer-action">
                  <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $row['id'] ?>" class="btn btn-outline-maroon btn-sm">
                    <i class="bi bi-info-circle"></i> Detail
                  </a>
                  <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-maroon btn-sm">
                    <i class="bi bi-heart-fill"></i> Vote
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <div class="text-center mt-5">
        <a href="<?= BASE_URL ?>pages/kontestan.php" class="btn btn-outline-maroon btn-lg px-5">
          Lihat Seluruh Finalis (<?= $total_kakang + $total_senduk ?> Kontestan) <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- Panduan Alur Voting 3 Langkah -->
  <section class="py-5 bg-white border-top border-bottom">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Mudah, Cepat & Terpercaya</span>
        <h2 class="section-title">Cara Memberikan <span>Suara Anda</span></h2>
        <div class="gold-divider"></div>
        <p class="section-subtitle">Tiga langkah mudah untuk menentukan masa depan Duta Wisata Ponorogo.</p>
      </div>

      <div class="row g-4 text-center">
        <div class="col-md-4">
          <div class="p-4 rounded-4 bg-clean h-100 border">
            <div class="mx-auto mb-3" style="width: 70px; height: 70px; border-radius: 50%; background: var(--grad-maroon); display:flex; align-items:center; justify-content:center; color: var(--royal-gold); font-size: 1.8rem; box-shadow: 0 8px 20px rgba(107, 15, 26, 0.2);">
              1
            </div>
            <h5 class="fw-bold mb-2">Daftar & Masuk Akun</h5>
            <p class="text-muted small">Registrasikan nama dan email aktif Anda. Sistem menerapkan kebijakan 1 akun untuk 1 suara Kakang dan 1 suara Senduk.</p>
          </div>
        </div>

        <div class="col-md-4">
          <div class="p-4 rounded-4 bg-clean h-100 border">
            <div class="mx-auto mb-3" style="width: 70px; height: 70px; border-radius: 50%; background: var(--grad-gold); display:flex; align-items:center; justify-content:center; color: #2E1A02; font-size: 1.8rem; box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);">
              2
            </div>
            <h5 class="fw-bold mb-2">Pelajari Profil Kontestan</h5>
            <p class="text-muted small">Eksplorasi latar belakang pendidikan, visi misi, prestasi daerah, dan motivasi tiap kontestan Kakang dan Senduk.</p>
          </div>
        </div>

        <div class="col-md-4">
          <div class="p-4 rounded-4 bg-clean h-100 border">
            <div class="mx-auto mb-3" style="width: 70px; height: 70px; border-radius: 50%; background: var(--dark-charcoal); display:flex; align-items:center; justify-content:center; color: #FFFFFF; font-size: 1.8rem; box-shadow: 0 8px 20px rgba(30, 41, 59, 0.2);">
              3
            </div>
            <h5 class="fw-bold mb-2">Kirim & Pantau Klasemen</h5>
            <p class="text-muted small">Konfirmasi pilihan Anda melalui sistem aman berbasis transaksi database dan saksikan update skor klasemen podium secara real-time.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer Modern Ponorogo -->
  <footer class="footer-custom">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--grad-maroon); display:flex; align-items:center; justify-content:center; color: var(--royal-gold); border: 1px solid var(--royal-gold);">
              <i class="bi bi-award-fill"></i>
            </div>
            <h5 class="mb-0 text-white">KAKANG SENDUK PONOROGO</h5>
          </div>
          <p class="small text-muted">
            Platform Pemilihan Duta Wisata resmi Kabupaten Ponorogo. Mengusung transparansi, integritas, dan modernitas untuk kemajuan pariwisata Bumi Reog.
          </p>
        </div>

        <div class="col-lg-2 col-md-6">
          <h5>Tautan Cepat</h5>
          <ul class="list-unstyled small d-flex flex-column gap-2">
            <li><a href="<?= BASE_URL ?>index.php">Beranda</a></li>
            <li><a href="<?= BASE_URL ?>pages/kontestan.php">Daftar Kontestan</a></li>
            <li><a href="<?= BASE_URL ?>pages/voting.php">Bilik Voting</a></li>
            <li><a href="<?= BASE_URL ?>pages/klasemen.php">Klasemen Real-time</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6">
          <h5>Penyelenggara</h5>
          <p class="small text-muted mb-1"><i class="bi bi-geo-alt-fill text-gold me-2"></i>Dinas Kebudayaan, Pariwisata, Pemuda dan Olahraga Kabupaten Ponorogo</p>
          <p class="small text-muted"><i class="bi bi-envelope-fill text-gold me-2"></i>disbudparpora@ponorogo.go.id</p>
        </div>

        <div class="col-lg-3 col-md-6">
          <h5>Keamanan Sistem</h5>
          <p class="small text-muted">
            Dilengkapi perlindungan SQL Injection Prepared Statements, Enkripsi Bcrypt, dan Atomic Transaction untuk integritas pemilihan yang adil.
          </p>
        </div>
      </div>

      <div class="footer-bottom text-center">
        <p class="mb-0 text-muted">&copy; <?= date('Y') ?> Pemilihan Kakang Senduk Ponorogo. Seluruh hak cipta dilindungi undang-undang.</p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
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
