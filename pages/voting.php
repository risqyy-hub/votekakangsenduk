<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: pages/voting.php
 * Bilik Pemilihan Online Kakang & Senduk
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

// Wajibkan pemilih login
require_login();

$user_id = (int)$_SESSION['user_id'];
$user = get_logged_in_user($conn);
$periode = get_active_periode($conn);

// Cek status suara pemilih saat ini
$user_votes = get_user_votes($conn, $user_id);
$voted_kakang_id = $user_votes['Kakang'];
$voted_senduk_id = $user_votes['Senduk'];

// Ambil daftar kontestan Kakang & Senduk
$kontestan_kakang = [];
$res_kakang = $conn->query("SELECT * FROM kontestan WHERE kategori = 'Kakang' ORDER BY nomor_peserta ASC");
while ($row = $res_kakang->fetch_assoc()) {
    $kontestan_kakang[] = $row;
}

$kontestan_senduk = [];
$res_senduk = $conn->query("SELECT * FROM kontestan WHERE kategori = 'Senduk' ORDER BY nomor_peserta ASC");
while ($row = $res_senduk->fetch_assoc()) {
    $kontestan_senduk[] = $row;
}

// Cek apakah voting aktif secara periode dan rentang waktu
$is_period_valid = ($periode && $periode['status'] === 'Aktif' && !empty($periode['is_within_time']));

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bilik Voting - Pemilihan Kakang Senduk Ponorogo</title>
  
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
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>pages/kontestan.php">Kontestan</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>pages/voting.php">Voting</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>pages/klasemen.php">Klasemen</a></li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <div class="dropdown">
            <button class="btn btn-outline-maroon dropdown-toggle px-3 py-2" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-check-fill text-gold me-1"></i> <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mt-2">
              <li><h6 class="dropdown-header small text-muted"><?= htmlspecialchars($user['email']) ?></h6></li>
              <?php if (is_admin()): ?>
                <li><a class="dropdown-item rounded-3" href="<?= BASE_URL ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2 text-maroon"></i> Panel Admin</a></li>
                <li><hr class="dropdown-divider"></li>
              <?php endif; ?>
              <li><a class="dropdown-item rounded-3 text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Page Header & Status Ringkasan Voting -->
  <section class="py-5 bg-white border-bottom shadow-sm">
    <div class="container">
      <div class="row align-items-center gy-4">
        <div class="col-lg-7">
          <span class="section-tag mb-2">Bilik Pemilihan Suara Elektronik</span>
          <h1 class="display-6 fw-bold text-dark mb-2">Tentukan Pilihan Anda Sekarang</h1>
          <p class="text-muted mb-0">
            Halo <b><?= htmlspecialchars($user['nama']) ?></b>, setiap pemilih terdaftar memiliki 2 hak suara: <b>1 suara Kakang</b> dan <b>1 suara Senduk</b>.
          </p>
        </div>

        <!-- Kartu Status Pemilih -->
        <div class="col-lg-5">
          <div class="p-3 rounded-4 bg-clean border shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
              <span class="small fw-bold text-muted">STATUS HAK SUARA ANDA</span>
              <span class="badge bg-maroon">Terverifikasi</span>
            </div>
            <div class="row g-2 text-center">
              <div class="col-6">
                <div class="p-2 rounded-3 <?= $voted_kakang_id ? 'bg-success bg-opacity-10 border border-success' : 'bg-white border' ?>">
                  <small class="d-block text-muted" style="font-size: 0.75rem;">KATEGORI KAKANG</small>
                  <?php if ($voted_kakang_id): ?>
                    <span class="fw-bold text-success small"><i class="bi bi-check-circle-fill me-1"></i>Sudah Memilih</span>
                  <?php else: ?>
                    <span class="fw-bold text-warning small"><i class="bi bi-hourglass-split me-1"></i>Belum Memilih</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 rounded-3 <?= $voted_senduk_id ? 'bg-success bg-opacity-10 border border-success' : 'bg-white border' ?>">
                  <small class="d-block text-muted" style="font-size: 0.75rem;">KATEGORI SENDUK</small>
                  <?php if ($voted_senduk_id): ?>
                    <span class="fw-bold text-success small"><i class="bi bi-check-circle-fill me-1"></i>Sudah Memilih</span>
                  <?php else: ?>
                    <span class="fw-bold text-warning small"><i class="bi bi-hourglass-split me-1"></i>Belum Memilih</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Alert Periode Tidak Aktif -->
      <?php if (!$is_period_valid): ?>
        <div class="alert alert-danger d-flex align-items-center rounded-4 mt-4" role="alert">
          <i class="bi bi-exclamation-octagon-fill flex-shrink-0 fs-3 me-3"></i>
          <div>
            <h5 class="alert-heading fw-bold mb-1">Periode Pemilihan Sedang Ditutup</h5>
            <p class="mb-0 small">
              <?php if (!$periode || $periode['status'] !== 'Aktif'): ?>
                Status periode saat ini sedang nonaktif oleh Administrator.
              <?php elseif (!empty($periode['is_ended'])): ?>
                Waktu pemungutan suara telah berakhir pada <?= format_tanggal_indo($periode['tanggal_selesai']) ?>.
              <?php elseif (!empty($periode['is_upcoming'])): ?>
                Pemungutan suara baru akan dibuka pada <?= format_tanggal_indo($periode['tanggal_mulai']) ?>.
              <?php endif; ?>
              Silakan pantau perkembangan skor di halaman <a href="<?= BASE_URL ?>pages/klasemen.php" class="fw-bold text-danger">Klasemen Real-Time</a>.
            </p>
          </div>
        </div>
      <?php endif; ?>

      <!-- Alert Jika Sudah Selesai Memilih Keduanya -->
      <?php if ($voted_kakang_id && $voted_senduk_id): ?>
        <div class="alert alert-success d-flex align-items-center rounded-4 mt-4 border-2 border-success" role="alert">
          <i class="bi bi-check-circle-fill flex-shrink-0 fs-3 me-3 text-success"></i>
          <div>
            <h5 class="alert-heading fw-bold mb-1">Terima Kasih Atas Partisipasi Anda!</h5>
            <p class="mb-0 small">
              Anda telah menyalurkan seluruh hak suara secara lengkap untuk Kategori Kakang dan Senduk. Saksikan perkembangan suara terkini secara live di <a href="<?= BASE_URL ?>pages/klasemen.php" class="fw-bold text-success text-decoration-underline">Halaman Klasemen Real-Time</a>.
            </p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Section 1: Pemilihan Kakang -->
  <section class="py-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <span class="badge bg-maroon px-3 py-2 rounded-pill mb-1">KATEGORI 1</span>
          <h2 class="fw-bold text-dark mb-0">Pemilihan <span class="text-maroon">Kakang</span> Ponorogo</h2>
        </div>
        <div>
          <?php if ($voted_kakang_id): ?>
            <span class="badge bg-success fs-6 py-2 px-3 rounded-pill"><i class="bi bi-shield-check me-1"></i> Suara Anda Tercatat</span>
          <?php else: ?>
            <span class="badge bg-warning text-dark fs-6 py-2 px-3 rounded-pill"><i class="bi bi-hand-index-thumb me-1"></i> Pilih 1 Kontestan</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="row g-4">
        <?php foreach ($kontestan_kakang as $k): 
          $is_chosen = ($voted_kakang_id == $k['id']);
          $is_disabled = ($voted_kakang_id && !$is_chosen) || !$is_period_valid;
        ?>
          <div class="col-12 col-md-4">
            <div class="card-contestant <?= $is_chosen ? 'border-success border-3 shadow-lg' : '' ?>" style="<?= $is_disabled ? 'opacity: 0.65;' : '' ?>">
              <div class="image-wrapper">
                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($k['foto']) ?>" alt="<?= htmlspecialchars($k['nama']) ?>">
                <span class="nomor-badge"><?= htmlspecialchars($k['nomor_peserta']) ?></span>
                <?php if ($is_chosen): ?>
                  <span class="category-badge bg-success text-white">
                    <i class="bi bi-check-circle-fill me-1"></i> Pilihan Anda
                  </span>
                <?php else: ?>
                  <span class="category-badge badge-kakang">Kakang</span>
                <?php endif; ?>
              </div>
              <div class="card-body">
                <h4 class="contestant-name"><?= htmlspecialchars($k['nama']) ?></h4>
                <div class="contestant-instansi">
                  <i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($k['asal_instansi']) ?>
                </div>
                <div class="contestant-quote">
                  "<?= htmlspecialchars($k['visi']) ?>"
                </div>

                <div class="mt-auto pt-3 border-top">
                  <?php if ($is_chosen): ?>
                    <button class="btn btn-success w-100 py-2" disabled>
                      <i class="bi bi-patch-check-fill me-1"></i> Suara Telah Diberikan
                    </button>
                  <?php elseif ($voted_kakang_id): ?>
                    <button class="btn btn-outline-secondary w-100 py-2" disabled>
                      <i class="bi bi-lock-fill me-1"></i> Terkunci
                    </button>
                  <?php elseif (!$is_period_valid): ?>
                    <button class="btn btn-secondary w-100 py-2" disabled>
                      <i class="bi bi-pause-circle me-1"></i> Voting Ditutup
                    </button>
                  <?php else: ?>
                    <div class="row g-2">
                      <div class="col-5">
                        <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $k['id'] ?>" class="btn btn-outline-secondary w-100 btn-sm">
                          Detail
                        </a>
                      </div>
                      <div class="col-7">
                        <button type="button" 
                                class="btn btn-maroon w-100 btn-sm btn-trigger-vote"
                                data-id="<?= $k['id'] ?>"
                                data-nama="<?= htmlspecialchars($k['nama']) ?>"
                                data-kategori="<?= htmlspecialchars($k['kategori']) ?>"
                                data-nomor="<?= htmlspecialchars($k['nomor_peserta']) ?>">
                          <i class="bi bi-check2-circle me-1"></i> Pilih Kakang
                        </button>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Section 2: Pemilihan Senduk -->
  <section class="py-5 bg-white border-top border-bottom">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <span class="badge bg-gold text-dark px-3 py-2 rounded-pill mb-1">KATEGORI 2</span>
          <h2 class="fw-bold text-dark mb-0">Pemilihan <span class="text-maroon">Senduk</span> Ponorogo</h2>
        </div>
        <div>
          <?php if ($voted_senduk_id): ?>
            <span class="badge bg-success fs-6 py-2 px-3 rounded-pill"><i class="bi bi-shield-check me-1"></i> Suara Anda Tercatat</span>
          <?php else: ?>
            <span class="badge bg-warning text-dark fs-6 py-2 px-3 rounded-pill"><i class="bi bi-hand-index-thumb me-1"></i> Pilih 1 Kontestan</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="row g-4">
        <?php foreach ($kontestan_senduk as $s): 
          $is_chosen_senduk = ($voted_senduk_id == $s['id']);
          $is_disabled_senduk = ($voted_senduk_id && !$is_chosen_senduk) || !$is_period_valid;
        ?>
          <div class="col-12 col-md-4">
            <div class="card-contestant <?= $is_chosen_senduk ? 'border-success border-3 shadow-lg' : '' ?>" style="<?= $is_disabled_senduk ? 'opacity: 0.65;' : '' ?>">
              <div class="image-wrapper">
                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($s['foto']) ?>" alt="<?= htmlspecialchars($s['nama']) ?>">
                <span class="nomor-badge"><?= htmlspecialchars($s['nomor_peserta']) ?></span>
                <?php if ($is_chosen_senduk): ?>
                  <span class="category-badge bg-success text-white">
                    <i class="bi bi-check-circle-fill me-1"></i> Pilihan Anda
                  </span>
                <?php else: ?>
                  <span class="category-badge badge-senduk">Senduk</span>
                <?php endif; ?>
              </div>
              <div class="card-body">
                <h4 class="contestant-name"><?= htmlspecialchars($s['nama']) ?></h4>
                <div class="contestant-instansi">
                  <i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($s['asal_instansi']) ?>
                </div>
                <div class="contestant-quote">
                  "<?= htmlspecialchars($s['visi']) ?>"
                </div>

                <div class="mt-auto pt-3 border-top">
                  <?php if ($is_chosen_senduk): ?>
                    <button class="btn btn-success w-100 py-2" disabled>
                      <i class="bi bi-patch-check-fill me-1"></i> Suara Telah Diberikan
                    </button>
                  <?php elseif ($voted_senduk_id): ?>
                    <button class="btn btn-outline-secondary w-100 py-2" disabled>
                      <i class="bi bi-lock-fill me-1"></i> Terkunci
                    </button>
                  <?php elseif (!$is_period_valid): ?>
                    <button class="btn btn-secondary w-100 py-2" disabled>
                      <i class="bi bi-pause-circle me-1"></i> Voting Ditutup
                    </button>
                  <?php else: ?>
                    <div class="row g-2">
                      <div class="col-5">
                        <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $s['id'] ?>" class="btn btn-outline-secondary w-100 btn-sm">
                          Detail
                        </a>
                      </div>
                      <div class="col-7">
                        <button type="button" 
                                class="btn btn-maroon w-100 btn-sm btn-trigger-vote"
                                data-id="<?= $s['id'] ?>"
                                data-nama="<?= htmlspecialchars($s['nama']) ?>"
                                data-kategori="<?= htmlspecialchars($s['kategori']) ?>"
                                data-nomor="<?= htmlspecialchars($s['nomor_peserta']) ?>">
                          <i class="bi bi-check2-circle me-1"></i> Pilih Senduk
                        </button>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <footer class="footer-custom">
    <div class="container text-center text-muted small">
      <p class="mb-0">&copy; <?= date('Y') ?> Pemilihan Duta Wisata Kakang Senduk Ponorogo. Portal Resmi E-Voting.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
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
