<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: pages/kontestan.php
 * Daftar Finalis Kakang & Senduk dengan Fitur Live Search & Filter
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

$user = get_logged_in_user($conn);
$periode = get_active_periode($conn);

// Ambil seluruh data kontestan diurutkan berdasarkan nomor peserta
$query = "SELECT * FROM kontestan ORDER BY kategori DESC, nomor_peserta ASC";
$result = $conn->query($query);
$kontestan_list = [];
while ($row = $result->fetch_assoc()) {
    $kontestan_list[] = $row;
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Kontestan - Pemilihan Kakang Senduk Ponorogo</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
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
            <a class="nav-link" href="<?= BASE_URL ?>index.php"><i class="bi bi-house-door me-1"></i> Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="<?= BASE_URL ?>pages/kontestan.php"><i class="bi bi-people me-1"></i> Kontestan</a>
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

  <!-- Page Header Title -->
  <section class="py-5 bg-white border-bottom shadow-sm">
    <div class="container text-center">
      <span class="section-tag">Finalis Resmi 2026</span>
      <h1 class="display-6 fw-bold text-dark mb-2">Profil Kontestan <span class="text-maroon">Kakang & Senduk</span></h1>
      <p class="text-muted mx-auto" style="max-width: 650px;">
        Telusuri profil lengkap, gagasan pariwisata, dan rekam jejak prestasi masing-masing finalis sebelum Anda menentukan pilihan terbaik.
      </p>

      <!-- Live Search & Filter Bar -->
      <div class="row justify-content-center mt-4 g-3">
        <div class="col-md-5">
          <div class="search-input-group">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="searchContestant" class="form-control" placeholder="Cari nama, instansi, atau nomor peserta...">
          </div>
        </div>
        <div class="col-md-auto d-flex justify-content-center">
          <div class="filter-tabs">
            <button class="filter-btn active" data-filter="all">Semua (<?= count($kontestan_list) ?>)</button>
            <button class="filter-btn" data-filter="Kakang">Kakang</button>
            <button class="filter-btn" data-filter="Senduk">Senduk</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contestant Grid Section -->
  <section class="py-5">
    <div class="container">
      <div class="row g-4" id="contestantsContainer">
        <?php foreach ($kontestan_list as $k): ?>
          <div class="col-12 col-sm-6 col-lg-4 contestant-item"
               data-category="<?= htmlspecialchars($k['kategori']) ?>"
               data-name="<?= htmlspecialchars($k['nama']) ?>"
               data-instansi="<?= htmlspecialchars($k['asal_instansi']) ?>"
               data-nomor="<?= htmlspecialchars($k['nomor_peserta']) ?>">
            <div class="card-contestant">
              <div class="image-wrapper">
                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($k['foto']) ?>" alt="<?= htmlspecialchars($k['nama']) ?>">
                <span class="nomor-badge"><?= htmlspecialchars($k['nomor_peserta']) ?></span>
                <span class="category-badge <?= $k['kategori'] === 'Kakang' ? 'badge-kakang' : 'badge-senduk' ?>">
                  <?= htmlspecialchars($k['kategori']) ?>
                </span>
              </div>
              <div class="card-body">
                <h4 class="contestant-name"><?= htmlspecialchars($k['nama']) ?></h4>
                <div class="contestant-instansi">
                  <i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($k['asal_instansi']) ?>
                </div>
                <div class="contestant-quote">
                  "<?= htmlspecialchars($k['visi']) ?>"
                </div>
                <div class="card-footer-action">
                  <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $k['id'] ?>" class="btn btn-outline-maroon btn-sm">
                    <i class="bi bi-person-badge"></i> Detail
                  </a>
                  <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-maroon btn-sm">
                    <i class="bi bi-check2-circle"></i> Beri Suara
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- State Kosong jika pencarian tidak ditemukan -->
      <div id="emptyContestants" class="text-center py-5" style="display: none;">
        <div class="mx-auto mb-3 text-muted" style="font-size: 3.5rem;">
          <i class="bi bi-search"></i>
        </div>
        <h4 class="fw-bold text-dark">Kontestan Tidak Ditemukan</h4>
        <p class="text-muted">Coba ubah kata kunci pencarian atau ganti filter kategori.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer-custom">
    <div class="container text-center text-muted small">
      <p class="mb-0">&copy; <?= date('Y') ?> Pemilihan Duta Wisata Kakang Senduk Ponorogo. Portal Resmi E-Voting.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
