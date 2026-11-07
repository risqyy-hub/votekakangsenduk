<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: pages/klasemen.php
 * Halaman Klasemen Real-Time dengan Podium Juara 1-2-3 & AJAX
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

$user = get_logged_in_user($conn);
$periode = get_active_periode($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Klasemen Real-Time - Pemilihan Kakang Senduk Ponorogo</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

  <style>
    .live-pulse {
      display: inline-block;
      width: 10px;
      height: 10px;
      background-color: #10B981;
      border-radius: 50%;
      box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      animation: pulseLive 1.8s infinite;
    }
    @keyframes pulseLive {
      0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
      70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .nav-pills-custom .nav-link {
      color: #64748B;
      font-weight: 700;
      padding: 10px 28px;
      border-radius: 50px;
      transition: all 0.3s;
    }
    .nav-pills-custom .nav-link.active {
      background: var(--grad-maroon);
      color: #FFFFFF;
      box-shadow: 0 4px 15px rgba(107, 15, 26, 0.3);
    }
  </style>
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
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>pages/voting.php">Voting</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>pages/klasemen.php">Klasemen</a></li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <?php if (is_logged_in()): ?>
            <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-outline-maroon px-3 py-2">
              <i class="bi bi-person-check-fill text-gold me-1"></i> <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
            </a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-maroon px-4">Masuk</a>
            <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-gold px-4">Daftar</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Header Klasemen -->
  <section class="py-5 bg-white border-bottom shadow-sm">
    <div class="container">
      <div class="row align-items-center gy-4">
        <div class="col-lg-8">
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-pill mb-2">
            <span class="live-pulse"></span>
            <span class="text-success fw-bold small">REAL-TIME LIVE UPDATE (5s)</span>
          </div>
          <h1 class="display-6 fw-bold text-dark mb-2">Klasemen Perolehan Suara Pemilihan</h1>
          <p class="text-muted mb-0">
            Perolehan suara diperbarui secara otomatis tanpa refresh halaman melalui sinkronisasi database berkala.
          </p>
        </div>

        <div class="col-lg-4 text-lg-end">
          <div class="p-3 bg-clean border rounded-4 shadow-sm text-center text-lg-start">
            <div class="small text-muted mb-1">TOTAL SUARA MASUK</div>
            <h2 class="fw-bold text-maroon mb-1" id="statTotalSuara">0</h2>
            <small class="text-muted d-block" style="font-size: 0.75rem;">
              Terakhir diperbarui: <span id="lastUpdated" class="fw-semibold text-dark">-</span>
            </small>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs Kategori Kakang vs Senduk -->
      <div class="d-flex justify-content-center mt-4">
        <ul class="nav nav-pills nav-pills-custom bg-light p-1 rounded-pill border" id="klasemenTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="kakang-tab" data-bs-toggle="pill" data-bs-target="#kakang-pane" type="button" role="tab">
              <i class="bi bi-person-standing me-1"></i> Klasemen Kakang
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="senduk-tab" data-bs-toggle="pill" data-bs-target="#senduk-pane" type="button" role="tab">
              <i class="bi bi-person-standing-dress me-1"></i> Klasemen Senduk
            </button>
          </li>
        </ul>
      </div>
    </div>
  </section>

  <!-- Klasemen Body Tab Content -->
  <main class="py-5">
    <div class="container">
      <div class="tab-content" id="klasemenTabContent">

        <!-- ================= TAB KAKANG ================= -->
        <div class="tab-pane fade show active" id="kakang-pane" role="tabpanel">
          <!-- PODIUM 1, 2, 3 KAKANG -->
          <div class="podium-section">
            <h4 class="text-center fw-bold text-dark mb-1">Podium Tiga Besar Kakang Ponorogo</h4>
            <p class="text-center text-muted small mb-0">Peringkat 1, 2, dan 3 perolehan suara sementara</p>
            
            <div class="podium-container" id="podiumKakang">
              <!-- Rendered by JS: 2nd place, 1st place, 3rd place -->
              <div class="text-center py-5 text-muted">Memuat podium Kakang...</div>
            </div>
          </div>

          <!-- TABEL PERINGKAT LENGKAP KAKANG -->
          <div class="card card-table shadow-sm mt-4">
            <div class="card-header bg-white">
              <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-ol text-maroon me-2"></i>Peringkat Lengkap Finalis Kakang</h5>
              <span class="badge bg-maroon rounded-pill" id="badgeTotalKakang">0 Suara</span>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 80px;" class="text-center">Rank</th>
                    <th>Kontestan</th>
                    <th>Asal Instansi</th>
                    <th style="width: 280px;">Persentase Suara</th>
                    <th style="width: 130px;" class="text-end">Jumlah Suara</th>
                  </tr>
                </thead>
                <tbody id="tableBodyKakang">
                  <tr><td colspan="5" class="text-center py-4 text-muted">Memuat data tabel...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ================= TAB SENDUK ================= -->
        <div class="tab-pane fade" id="senduk-pane" role="tabpanel">
          <!-- PODIUM 1, 2, 3 SENDUK -->
          <div class="podium-section">
            <h4 class="text-center fw-bold text-dark mb-1">Podium Tiga Besar Senduk Ponorogo</h4>
            <p class="text-center text-muted small mb-0">Peringkat 1, 2, dan 3 perolehan suara sementara</p>
            
            <div class="podium-container" id="podiumSenduk">
              <div class="text-center py-5 text-muted">Memuat podium Senduk...</div>
            </div>
          </div>

          <!-- TABEL PERINGKAT LENGKAP SENDUK -->
          <div class="card card-table shadow-sm mt-4">
            <div class="card-header bg-white">
              <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-ol text-maroon me-2"></i>Peringkat Lengkap Finalis Senduk</h5>
              <span class="badge bg-gold text-dark rounded-pill" id="badgeTotalSenduk">0 Suara</span>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 80px;" class="text-center">Rank</th>
                    <th>Kontestan</th>
                    <th>Asal Instansi</th>
                    <th style="width: 280px;">Persentase Suara</th>
                    <th style="width: 130px;" class="text-end">Jumlah Suara</th>
                  </tr>
                </thead>
                <tbody id="tableBodySenduk">
                  <tr><td colspan="5" class="text-center py-4 text-muted">Memuat data tabel...</td></tr>
                </tbody>
              </table>
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

  <!-- Real-Time AJAX Script -->
  <script>
    const API_URL = '<?= BASE_URL ?>proses/api-klasemen.php';

    function renderPodium(containerId, list) {
      const container = document.getElementById(containerId);
      if (!container) return;

      if (!list || list.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted">Belum ada suara masuk.</div>';
        return;
      }

      // Ambil Top 3 (1st, 2nd, 3rd)
      const first = list[0] || null;
      const second = list[1] || null;
      const third = list[2] || null;

      let html = '';

      // Tampilan Podium Urutan: [2nd (Kiri)] - [1st (Tengah)] - [3rd (Kanan)]
      // 2nd Place (Silver)
      if (second) {
        html += `
          <div class="podium-item second">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown">🥈</span>
              <img src="${second.foto_url}" alt="${second.nama}">
            </div>
            <h6 class="fw-bold mb-0 text-dark">${second.nama}</h6>
            <small class="text-muted d-block mb-2">${second.nomor_peserta} &bull; ${second.asal_instansi}</small>
            <div class="podium-pillar">
              <span class="podium-rank">2</span>
              <span class="small fw-bold">JUARA 2</span>
              <span class="podium-votes-badge">${second.total_suara} Suara (${second.persentase}%)</span>
            </div>
          </div>
        `;
      }

      // 1st Place (Gold - Tengah Tertinggi)
      if (first) {
        html += `
          <div class="podium-item first">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown">👑</span>
              <img src="${first.foto_url}" alt="${first.nama}">
            </div>
            <h5 class="fw-bold mb-0 text-dark">${first.nama}</h5>
            <small class="text-muted d-block mb-2 font-weight-bold">${first.nomor_peserta} &bull; ${first.asal_instansi}</small>
            <div class="podium-pillar">
              <span class="podium-rank">1</span>
              <span class="small fw-bold text-gold">PEMIMPIN KLASEMEN</span>
              <span class="podium-votes-badge">${first.total_suara} Suara (${first.persentase}%)</span>
            </div>
          </div>
        `;
      }

      // 3rd Place (Bronze)
      if (third) {
        html += `
          <div class="podium-item third">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown">🥉</span>
              <img src="${third.foto_url}" alt="${third.nama}">
            </div>
            <h6 class="fw-bold mb-0 text-dark">${third.nama}</h6>
            <small class="text-muted d-block mb-2">${third.nomor_peserta} &bull; ${third.asal_instansi}</small>
            <div class="podium-pillar">
              <span class="podium-rank">3</span>
              <span class="small fw-bold">JUARA 3</span>
              <span class="podium-votes-badge">${third.total_suara} Suara (${third.persentase}%)</span>
            </div>
          </div>
        `;
      }

      container.innerHTML = html;
    }

    function renderTable(tableBodyId, list, isGoldBar = false) {
      const tbody = document.getElementById(tableBodyId);
      if (!tbody) return;

      if (!list || list.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data kontestan.</td></tr>';
        return;
      }

      let html = '';
      list.forEach((item) => {
        let rankBadge = '';
        if (item.rank === 1) {
          rankBadge = '<span class="badge bg-gold text-dark fs-6 px-3 py-1 rounded-pill">🥇 #1</span>';
        } else if (item.rank === 2) {
          rankBadge = '<span class="badge bg-secondary text-white fs-6 px-3 py-1 rounded-pill">🥈 #2</span>';
        } else if (item.rank === 3) {
          rankBadge = '<span class="badge bg-warning bg-opacity-75 text-dark fs-6 px-3 py-1 rounded-pill">🥉 #3</span>';
        } else {
          rankBadge = `<span class="badge bg-light text-dark border fs-6 px-3 py-1 rounded-pill">#${item.rank}</span>`;
        }

        html += `
          <tr>
            <td class="text-center">${rankBadge}</td>
            <td>
              <div class="d-flex align-items-center gap-3">
                <img src="${item.foto_url}" alt="${item.nama}" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;">
                <div>
                  <div class="fw-bold text-dark">${item.nama}</div>
                  <small class="text-muted"><span class="badge bg-dark">${item.nomor_peserta}</span></small>
                </div>
              </div>
            </td>
            <td><span class="text-muted small"><i class="bi bi-mortarboard me-1"></i>${item.asal_instansi}</span></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="progress-vote-container flex-grow-1">
                  <div class="progress-vote-fill ${isGoldBar ? 'gold' : ''}" style="width: ${item.persentase}%;"></div>
                </div>
                <span class="fw-bold small" style="min-width: 45px;">${item.persentase}%</span>
              </div>
            </td>
            <td class="text-end">
              <span class="fw-bold fs-6 text-maroon">${item.total_suara}</span>
              <small class="text-muted d-block" style="font-size: 0.75rem;">Suara Sah</small>
            </td>
          </tr>
        `;
      });

      tbody.innerHTML = html;
    }

    async function fetchKlasemen() {
      try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();

        if (data.status === 'success') {
          // Update Ringkasan Stat
          document.getElementById('statTotalSuara').innerText = data.summary.total_suara;
          document.getElementById('lastUpdated').innerText = data.timestamp;
          document.getElementById('badgeTotalKakang').innerText = data.summary.total_kakang + ' Suara';
          document.getElementById('badgeTotalSenduk').innerText = data.summary.total_senduk + ' Suara';

          // Render Kakang
          renderPodium('podiumKakang', data.kakang);
          renderTable('tableBodyKakang', data.kakang, false);

          // Render Senduk
          renderPodium('podiumSenduk', data.senduk);
          renderTable('tableBodySenduk', data.senduk, true);
        }
      } catch (err) {
        console.error('Gagal mengambil data klasemen:', err);
      }
    }

    // Panggil saat pertama dimuat
    fetchKlasemen();

    // Auto update setiap 5 detik secara realtime
    setInterval(fetchKlasemen, 5000);
  </script>
</body>
</html>
