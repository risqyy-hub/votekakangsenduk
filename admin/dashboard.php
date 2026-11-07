<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: admin/dashboard.php
 * Halaman Dashboard Utama Administrator & Visualisasi Chart.js
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

// Validasi otorisasi peran administrator
require_admin();

$admin = get_logged_in_user($conn);
$periode = get_active_periode($conn);

// 1. Data Statistik Ringkasan
$total_kontestan = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM kontestan");
if ($r = $res->fetch_assoc()) $total_kontestan = (int)$r['total'];

$total_pemilih = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'pemilih'");
if ($r = $res->fetch_assoc()) $total_pemilih = (int)$r['total'];

$total_suara_kakang = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM voting WHERE kategori = 'Kakang'");
if ($r = $res->fetch_assoc()) $total_suara_kakang = (int)$r['total'];

$total_suara_senduk = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM voting WHERE kategori = 'Senduk'");
if ($r = $res->fetch_assoc()) $total_suara_senduk = (int)$r['total'];

// 2. Data untuk Chart.js (Kakang)
$chart_kakang_labels = [];
$chart_kakang_data = [];
$res_ck = $conn->query("
    SELECT k.nomor_peserta, k.nama, COUNT(v.id) AS total_suara 
    FROM kontestan k 
    LEFT JOIN voting v ON k.id = v.kontestan_id 
    WHERE k.kategori = 'Kakang' 
    GROUP BY k.id 
    ORDER BY k.nomor_peserta ASC
");
while ($r = $res_ck->fetch_assoc()) {
    $chart_kakang_labels[] = $r['nomor_peserta'] . ' - ' . explode(' ', $r['nama'])[0];
    $chart_kakang_data[] = (int)$r['total_suara'];
}

// 3. Data untuk Chart.js (Senduk)
$chart_senduk_labels = [];
$chart_senduk_data = [];
$res_cs = $conn->query("
    SELECT k.nomor_peserta, k.nama, COUNT(v.id) AS total_suara 
    FROM kontestan k 
    LEFT JOIN voting v ON k.id = v.kontestan_id 
    WHERE k.kategori = 'Senduk' 
    GROUP BY k.id 
    ORDER BY k.nomor_peserta ASC
");
while ($r = $res_cs->fetch_assoc()) {
    $chart_senduk_labels[] = $r['nomor_peserta'] . ' - ' . explode(' ', $r['nama'])[0];
    $chart_senduk_data[] = (int)$r['total_suara'];
}

// 4. Riwayat 8 Suara Terakhir
$recent_votes = [];
$res_rv = $conn->query("
    SELECT v.id, v.created_at, v.kategori, u.nama AS nama_pemilih, k.nomor_peserta, k.nama AS nama_kontestan
    FROM voting v
    JOIN users u ON v.user_id = u.id
    JOIN kontestan k ON v.kontestan_id = k.id
    ORDER BY v.id DESC
    LIMIT 8
");
while ($r = $res_rv->fetch_assoc()) {
    $recent_votes[] = $r;
}

$active_menu = 'dashboard';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - Pemilihan Kakang Senduk Ponorogo</title>
  
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
  <!-- Include Sidebar Navigation -->
  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content Wrapper -->
  <div class="admin-content">
    
    <!-- Topbar Admin -->
    <header class="admin-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary btn-sm d-lg-none" id="toggleSidebarBtn">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h5 class="fw-bold text-dark mb-0">Dashboard Ringkasan</h5>
      </div>

      <div class="d-flex align-items-center gap-3">
        <div class="d-none d-md-block text-end">
          <span class="d-block fw-bold text-dark lh-1"><?= htmlspecialchars($admin['nama']) ?></span>
          <small class="text-muted" style="font-size: 0.75rem;">Administrator Sistem</small>
        </div>
        <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--grad-maroon); display: flex; align-items: center; justify-content: center; color: var(--royal-gold); font-size: 1.2rem; border: 2px solid var(--royal-gold);">
          <i class="bi bi-person-fill"></i>
        </div>
      </div>
    </header>

    <!-- Admin Body Content -->
    <main class="admin-body">

      <!-- Status Periode Pemilihan Aktif -->
      <div class="alert <?= ($periode && $periode['status'] === 'Aktif' && !empty($periode['is_within_time'])) ? 'alert-success border-success' : 'alert-warning border-warning' ?> d-flex justify-content-between align-items-center rounded-4 p-3 mb-4 shadow-sm">
        <div class="d-flex align-items-center gap-3">
          <i class="bi <?= ($periode && $periode['status'] === 'Aktif') ? 'bi-broadcast-pin text-success fs-2' : 'bi-pause-circle text-warning fs-2' ?>"></i>
          <div>
            <h6 class="fw-bold mb-1">
              Periode: <?= htmlspecialchars($periode['nama_periode'] ?? 'Tidak Ada Periode Aktif') ?>
              <span class="badge <?= ($periode && $periode['status'] === 'Aktif') ? 'bg-success' : 'bg-secondary' ?> ms-2">
                <?= $periode['status'] ?? 'Nonaktif' ?>
              </span>
            </h6>
            <small class="text-muted">
              Jadwal: <?= $periode ? format_tanggal_indo($periode['tanggal_mulai']) . ' s/d ' . format_tanggal_indo($periode['tanggal_selesai']) : 'Belum diatur' ?>
            </small>
          </div>
        </div>
        <a href="<?= BASE_URL ?>admin/periode.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
          <i class="bi bi-gear-fill me-1"></i> Atur Periode
        </a>
      </div>

      <!-- 4 Stat Cards Row -->
      <div class="row g-4 mb-4">
        <!-- Total Kontestan -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="stat-card">
            <div class="stat-icon icon-maroon">
              <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-info">
              <h3><?= $total_kontestan ?></h3>
              <p>Total Kontestan</p>
            </div>
          </div>
        </div>

        <!-- Total Pemilih -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="stat-card">
            <div class="stat-icon icon-blue">
              <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="stat-info">
              <h3><?= $total_pemilih ?></h3>
              <p>Pemilih Terdaftar</p>
            </div>
          </div>
        </div>

        <!-- Suara Kakang -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="stat-card">
            <div class="stat-icon icon-gold">
              <i class="bi bi-trophy-fill"></i>
            </div>
            <div class="stat-info">
              <h3><?= $total_suara_kakang ?></h3>
              <p>Suara Kakang Masuk</p>
            </div>
          </div>
        </div>

        <!-- Suara Senduk -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="stat-card">
            <div class="stat-icon icon-emerald">
              <i class="bi bi-award-fill"></i>
            </div>
            <div class="stat-info">
              <h3><?= $total_suara_senduk ?></h3>
              <p>Suara Senduk Masuk</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Chart.js Row -->
      <div class="row g-4 mb-4">
        <!-- Grafik Kakang (Bar Chart) -->
        <div class="col-12 col-lg-6">
          <div class="card card-table h-100 shadow-sm">
            <div class="card-header">
              <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill text-maroon me-2"></i>Perolehan Suara Kakang</h6>
              <span class="badge bg-maroon rounded-pill"><?= $total_suara_kakang ?> Suara</span>
            </div>
            <div class="p-4">
              <canvas id="chartKakang" style="max-height: 280px;"></canvas>
            </div>
          </div>
        </div>

        <!-- Grafik Senduk (Doughnut Chart) -->
        <div class="col-12 col-lg-6">
          <div class="card card-table h-100 shadow-sm">
            <div class="card-header">
              <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart-fill text-gold me-2"></i>Perolehan Suara Senduk</h6>
              <span class="badge bg-gold text-dark rounded-pill"><?= $total_suara_senduk ?> Suara</span>
            </div>
            <div class="p-4">
              <canvas id="chartSenduk" style="max-height: 280px;"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Aktivitas Voting Terbaru Table -->
      <div class="card card-table shadow-sm">
        <div class="card-header">
          <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-maroon me-2"></i>Aktivitas Voting Terkini</h6>
          <a href="<?= BASE_URL ?>admin/hasil-voting.php" class="btn btn-outline-maroon btn-sm">Lihat Seluruh Log</a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
              <tr>
                <th>ID</th>
                <th>Nama Pemilih</th>
                <th>Kategori</th>
                <th>Pilihan Kontestan</th>
                <th class="text-end">Waktu Suara Masuk</th>
              </tr>
            </thead>
            <tbody class="small">
              <?php if (empty($recent_votes)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada aktivitas voting tercatat.</td></tr>
              <?php else: ?>
                <?php foreach ($recent_votes as $rv): ?>
                  <tr>
                    <td><span class="badge bg-light text-dark border">#<?= $rv['id'] ?></span></td>
                    <td class="fw-bold text-dark"><?= htmlspecialchars($rv['nama_pemilih']) ?></td>
                    <td>
                      <span class="badge <?= $rv['kategori'] === 'Kakang' ? 'badge-kakang' : 'badge-senduk' ?> rounded-pill">
                        <?= $rv['kategori'] ?>
                      </span>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark"><?= htmlspecialchars($rv['nomor_peserta']) ?> - <?= htmlspecialchars($rv['nama_kontestan']) ?></span>
                    </td>
                    <td class="text-end text-muted"><?= format_tanggal_indo($rv['created_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  // Toggle Sidebar Mobile
  const toggleBtn = document.getElementById('toggleSidebarBtn');
  const sidebar = document.getElementById('adminSidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('show');
    });
  }

  // Visualisasi Chart.js: Kakang (Bar Chart)
  const ctxKakang = document.getElementById('chartKakang').getContext('2d');
  new Chart(ctxKakang, {
    type: 'bar',
    data: {
      labels: <?= json_encode($chart_kakang_labels) ?>,
      datasets: [{
        label: 'Jumlah Suara',
        data: <?= json_encode($chart_kakang_data) ?>,
        backgroundColor: '#6B0F1A',
        borderColor: '#4A0810',
        borderRadius: 8,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      }
    }
  });

  // Visualisasi Chart.js: Senduk (Doughnut Chart)
  const ctxSenduk = document.getElementById('chartSenduk').getContext('2d');
  new Chart(ctxSenduk, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($chart_senduk_labels) ?>,
      datasets: [{
        data: <?= json_encode($chart_senduk_data) ?>,
        backgroundColor: ['#D4AF37', '#9D174D', '#B45309', '#BE185D', '#4338CA', '#059669'],
        borderWidth: 2,
        borderColor: '#FFFFFF'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });

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
