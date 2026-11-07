<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: pages/detail-kontestan.php
 * Halaman Detail Komprehensif Biodata Kontestan
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: " . BASE_URL . "pages/kontestan.php");
    exit;
}

// Prepared Statement mengambil kontestan
$stmt = $conn->prepare("SELECT * FROM kontestan WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('error', 'Tidak Ditemukan', 'Data kontestan yang Anda cari tidak tersedia.');
    header("Location: " . BASE_URL . "pages/kontestan.php");
    exit;
}

$kontestan = $result->fetch_assoc();
$stmt->close();

$user = get_logged_in_user($conn);
$periode = get_active_periode($conn);

// Cek status voting jika sudah login
$has_voted_this_category = false;
$voted_kontestan_id = null;

if (is_logged_in()) {
    $voted_kontestan_id = user_has_voted($conn, $_SESSION['user_id'], $kontestan['kategori']);
    $has_voted_this_category = ($voted_kontestan_id !== false);
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($kontestan['nama']) ?> - Detail Kontestan Kakang Senduk</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="bg-clean">

  <!-- Navbar -->
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
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>index.php">Beranda</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>pages/kontestan.php">Kontestan</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>pages/voting.php">Voting</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>pages/klasemen.php">Klasemen</a></li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <?php if (is_logged_in()): ?>
            <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-outline-maroon px-3 py-2">
              <i class="bi bi-person-check-fill text-gold me-1"></i> <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
            </a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-maroon px-4">Masuk</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Detail Content Section -->
  <main class="py-5">
    <div class="container">
      <div class="mb-4">
        <a href="<?= BASE_URL ?>pages/kontestan.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kontestan
        </a>
      </div>

      <div class="detail-hero-card p-4 p-md-5">
        <div class="row g-5">
          <!-- Kolom Foto & Aksi Voting Cepat -->
          <div class="col-lg-5">
            <div class="detail-img-box position-relative">
              <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($kontestan['foto']) ?>" alt="<?= htmlspecialchars($kontestan['nama']) ?>" class="w-100 h-auto d-block">
              <div class="position-absolute top-0 start-0 m-3">
                <span class="badge bg-dark text-gold fs-6 px-3 py-2 rounded-pill border border-warning shadow">
                  <?= htmlspecialchars($kontestan['nomor_peserta']) ?>
                </span>
              </div>
              <div class="position-absolute top-0 end-0 m-3">
                <span class="badge <?= $kontestan['kategori'] === 'Kakang' ? 'badge-kakang' : 'badge-senduk' ?> fs-6 px-3 py-2 rounded-pill shadow">
                  Kategori <?= htmlspecialchars($kontestan['kategori']) ?>
                </span>
              </div>
            </div>

            <!-- Panel Aksi Voting -->
            <div class="mt-4 p-4 rounded-4 bg-light border text-center">
              <?php if (!is_logged_in()): ?>
                <h6 class="fw-bold text-dark mb-2">Ingin Memilih Kontestan Ini?</h6>
                <p class="text-muted small mb-3">Silakan masuk ke akun pemilih Anda untuk memberikan suara secara sah.</p>
                <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-maroon w-100 py-2">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Masuk untuk Memilih
                </a>
              <?php elseif (!$periode || $periode['status'] !== 'Aktif' || empty($periode['is_within_time'])): ?>
                <div class="alert alert-warning mb-0 small">
                  <i class="bi bi-exclamation-triangle-fill me-1"></i> Periode voting saat ini sedang tidak aktif atau di luar jadwal.
                </div>
              <?php elseif ($has_voted_this_category): ?>
                <?php if ($voted_kontestan_id == $kontestan['id']): ?>
                  <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success">
                    <span class="badge-voted mb-2"><i class="bi bi-check-circle-fill"></i> Pilihan Anda</span>
                    <h6 class="fw-bold text-success mb-0">Suara Anda telah tercatat untuk kontestan ini!</h6>
                  </div>
                <?php else: ?>
                  <div class="p-3 bg-secondary bg-opacity-10 rounded-3 border">
                    <i class="bi bi-lock-fill text-muted fs-4 d-block mb-1"></i>
                    <h6 class="fw-bold text-secondary mb-1">Hak Suara Terpakai</h6>
                    <small class="text-muted">Anda sudah menyalurkan suara untuk kontestan lain pada kategori <?= $kontestan['kategori'] ?>.</small>
                  </div>
                <?php endif; ?>
              <?php else: ?>
                <button type="button" 
                        class="btn btn-gold w-100 py-3 fs-6 fw-bold shadow btn-trigger-vote"
                        data-id="<?= $kontestan['id'] ?>"
                        data-nama="<?= htmlspecialchars($kontestan['nama']) ?>"
                        data-kategori="<?= htmlspecialchars($kontestan['kategori']) ?>"
                        data-nomor="<?= htmlspecialchars($kontestan['nomor_peserta']) ?>">
                  <i class="bi bi-heart-fill me-2 text-danger"></i> Beri Suara untuk <?= htmlspecialchars(explode(' ', $kontestan['nama'])[0]) ?>
                </button>
                <small class="text-muted d-block mt-2">1 akun hanya dapat memilih 1 Kakang dan 1 Senduk.</small>
              <?php endif; ?>
            </div>
          </div>

          <!-- Kolom Informasi Komprehensif -->
          <div class="col-lg-7">
            <span class="section-tag mb-2">Profil Resmi Duta Wisata</span>
            <h1 class="display-6 fw-bold text-dark mb-2"><?= htmlspecialchars($kontestan['nama']) ?></h1>
            <p class="text-muted fs-5 mb-4"><i class="bi bi-mortarboard text-maroon me-2"></i><?= htmlspecialchars($kontestan['asal_instansi']) ?></p>

            <div class="d-flex flex-wrap gap-2 mb-4">
              <div class="info-pill">
                <i class="bi bi-calendar3 text-maroon"></i>
                <span><?= htmlspecialchars($kontestan['ttl']) ?></span>
              </div>
              <div class="info-pill">
                <i class="bi bi-tag-fill text-maroon"></i>
                <span>Nomor: <b><?= htmlspecialchars($kontestan['nomor_peserta']) ?></b></span>
              </div>
              <div class="info-pill">
                <i class="bi bi-gender-ambiguous text-maroon"></i>
                <span>Kategori: <b><?= htmlspecialchars($kontestan['kategori']) ?></b></span>
              </div>
            </div>

            <!-- Visi -->
            <div class="mb-4">
              <h5 class="detail-section-title">
                <i class="bi bi-compass-fill"></i> Visi Kontestan
              </h5>
              <div class="p-3 rounded-3 bg-light border-start border-4 border-maroon">
                <p class="mb-0 fs-6 fst-italic text-dark lh-base">
                  "<?= nl2br(htmlspecialchars($kontestan['visi'])) ?>"
                </p>
              </div>
            </div>

            <!-- Misi -->
            <div class="mb-4">
              <h5 class="detail-section-title">
                <i class="bi bi-list-check"></i> Misi & Program Aksi
              </h5>
              <div class="text-secondary lh-lg ps-2">
                <?= nl2br(htmlspecialchars($kontestan['misi'])) ?>
              </div>
            </div>

            <!-- Prestasi -->
            <div class="mb-4">
              <h5 class="detail-section-title">
                <i class="bi bi-trophy-fill"></i> Rekam Jejak Prestasi
              </h5>
              <div class="text-secondary lh-lg ps-2">
                <?= nl2br(htmlspecialchars($kontestan['prestasi'])) ?>
              </div>
            </div>

            <!-- Deskripsi Personal -->
            <div>
              <h5 class="detail-section-title">
                <i class="bi bi-person-lines-fill"></i> Deskripsi Profil & Advokasi
              </h5>
              <p class="text-secondary lh-base">
                <?= nl2br(htmlspecialchars($kontestan['deskripsi'])) ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer-custom">
    <div class="container text-center text-muted small">
      <p class="mb-0">&copy; <?= date('Y') ?> Pemilihan Duta Wisata Kakang Senduk Ponorogo. Portal Resmi E-Voting.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
