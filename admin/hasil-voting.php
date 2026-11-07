<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: admin/hasil-voting.php
 * Hasil Rekapitulasi Suara & Fitur Export Excel/PDF DataTables
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';
require_admin();

$admin = get_logged_in_user($conn);

// 1. Total Suara
$total_kakang = 0;
$res_k = $conn->query("SELECT COUNT(*) AS total FROM voting WHERE kategori = 'Kakang'");
if ($r = $res_k->fetch_assoc()) $total_kakang = (int)$r['total'];

$total_senduk = 0;
$res_s = $conn->query("SELECT COUNT(*) AS total FROM voting WHERE kategori = 'Senduk'");
if ($r = $res_s->fetch_assoc()) $total_senduk = (int)$r['total'];

$total_semua = $total_kakang + $total_senduk;

// 2. Rekapitulasi Kakang
$rekap_kakang = [];
$res_rk = $conn->query("
    SELECT k.nomor_peserta, k.nama, k.asal_instansi, COUNT(v.id) AS total_suara
    FROM kontestan k
    LEFT JOIN voting v ON k.id = v.kontestan_id
    WHERE k.kategori = 'Kakang'
    GROUP BY k.id
    ORDER BY total_suara DESC, k.nomor_peserta ASC
");
$rank_k = 1;
while ($row = $res_rk->fetch_assoc()) {
    $row['rank'] = $rank_k++;
    $row['persen'] = ($total_kakang > 0) ? round(($row['total_suara'] / $total_kakang) * 100, 1) : 0;
    $rekap_kakang[] = $row;
}

// 3. Rekapitulasi Senduk
$rekap_senduk = [];
$res_rs = $conn->query("
    SELECT k.nomor_peserta, k.nama, k.asal_instansi, COUNT(v.id) AS total_suara
    FROM kontestan k
    LEFT JOIN voting v ON k.id = v.kontestan_id
    WHERE k.kategori = 'Senduk'
    GROUP BY k.id
    ORDER BY total_suara DESC, k.nomor_peserta ASC
");
$rank_s = 1;
while ($row = $res_rs->fetch_assoc()) {
    $row['rank'] = $rank_s++;
    $row['persen'] = ($total_senduk > 0) ? round(($row['total_suara'] / $total_senduk) * 100, 1) : 0;
    $rekap_senduk[] = $row;
}

// 4. Seluruh Log Audit Transaksi Voting
$log_voting = [];
$res_log = $conn->query("
    SELECT v.id, v.created_at, v.kategori, u.nama AS nama_pemilih, u.email AS email_pemilih,
           k.nomor_peserta, k.nama AS nama_kontestan
    FROM voting v
    JOIN users u ON v.user_id = u.id
    JOIN kontestan k ON v.kontestan_id = k.id
    ORDER BY v.id DESC
");
while ($row = $res_log->fetch_assoc()) {
    $log_voting[] = $row;
}

$active_menu = 'hasil-voting';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekapitulasi Hasil Voting - Admin Kakang Senduk</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- DataTables & Buttons CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
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
        <h5 class="fw-bold text-dark mb-0">Hasil Rekapitulasi & Laporan Resmi</h5>
      </div>

      <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>pages/klasemen.php" target="_blank" class="btn btn-outline-gold btn-sm px-3">
          <i class="bi bi-trophy-fill me-1"></i> Buka Klasemen Publik
        </a>
      </div>
    </header>

    <main class="admin-body">

      <!-- Summary Stats Banner -->
      <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
          <div class="stat-card">
            <div class="stat-icon icon-maroon"><i class="bi bi-inbox-fill"></i></div>
            <div class="stat-info">
              <h3><?= $total_semua ?></h3>
              <p>Total Seluruh Suara Masuk</p>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="stat-card">
            <div class="stat-icon icon-gold"><i class="bi bi-person-standing"></i></div>
            <div class="stat-info">
              <h3><?= $total_kakang ?></h3>
              <p>Total Suara Kategori Kakang</p>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="stat-card">
            <div class="stat-icon icon-emerald"><i class="bi bi-person-standing-dress"></i></div>
            <div class="stat-info">
              <h3><?= $total_senduk ?></h3>
              <p>Total Suara Kategori Senduk</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Rekap Kakang & Senduk Side by Side -->
      <div class="row g-4 mb-4">
        <!-- Rekap Kakang -->
        <div class="col-12 col-lg-6">
          <div class="card card-table shadow-sm h-100">
            <div class="card-header">
              <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-award-fill text-maroon me-2"></i>Rekap Suara Kakang Ponorogo
              </h6>
              <span class="badge bg-maroon rounded-pill"><?= $total_kakang ?> Suara</span>
            </div>
            <div class="p-3 table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px;" class="text-center">Rank</th>
                    <th>Nomor & Nama</th>
                    <th>Instansi</th>
                    <th class="text-end">Suara</th>
                    <th class="text-end">Persen</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rekap_kakang as $rk): ?>
                    <tr>
                      <td class="text-center fw-bold">#<?= $rk['rank'] ?></td>
                      <td>
                        <b><?= htmlspecialchars($rk['nomor_peserta']) ?></b> - <?= htmlspecialchars($rk['nama']) ?>
                      </td>
                      <td class="small text-muted"><?= htmlspecialchars($rk['asal_instansi']) ?></td>
                      <td class="text-end fw-bold text-maroon"><?= $rk['total_suara'] ?></td>
                      <td class="text-end fw-bold"><?= $rk['persen'] ?>%</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Rekap Senduk -->
        <div class="col-12 col-lg-6">
          <div class="card card-table shadow-sm h-100">
            <div class="card-header">
              <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-award-fill text-gold me-2"></i>Rekap Suara Senduk Ponorogo
              </h6>
              <span class="badge bg-gold text-dark rounded-pill"><?= $total_senduk ?> Suara</span>
            </div>
            <div class="p-3 table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px;" class="text-center">Rank</th>
                    <th>Nomor & Nama</th>
                    <th>Instansi</th>
                    <th class="text-end">Suara</th>
                    <th class="text-end">Persen</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rekap_senduk as $rs): ?>
                    <tr>
                      <td class="text-center fw-bold">#<?= $rs['rank'] ?></td>
                      <td>
                        <b><?= htmlspecialchars($rs['nomor_peserta']) ?></b> - <?= htmlspecialchars($rs['nama']) ?>
                      </td>
                      <td class="small text-muted"><?= htmlspecialchars($rs['asal_instansi']) ?></td>
                      <td class="text-end fw-bold text-maroon"><?= $rs['total_suara'] ?></td>
                      <td class="text-end fw-bold"><?= $rs['persen'] ?>%</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Audit Log Voting Lengkap dengan Export PDF & Excel -->
      <div class="card card-table shadow-sm">
        <div class="card-header">
          <div>
            <h6 class="fw-bold mb-0 text-dark">
              <i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i>Log Audit Transaksi Voting Lengkap
            </h6>
            <small class="text-muted">Gunakan tombol di bawah untuk mencetak atau mengekspor data ke format Excel dan PDF.</small>
          </div>
        </div>

        <div class="p-4 table-responsive">
          <table class="table table-hover align-middle w-100" id="tableLogVoting">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;">ID</th>
                <th>Nama Pemilih</th>
                <th>Email Pemilih</th>
                <th>Kategori</th>
                <th>Kontestan Dipilih</th>
                <th class="text-end">Waktu Voting</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($log_voting as $log): ?>
                <tr>
                  <td><span class="badge bg-light text-dark border">#<?= $log['id'] ?></span></td>
                  <td class="fw-bold text-dark"><?= htmlspecialchars($log['nama_pemilih']) ?></td>
                  <td><?= htmlspecialchars($log['email_pemilih']) ?></td>
                  <td>
                    <span class="badge <?= $log['kategori'] === 'Kakang' ? 'badge-kakang' : 'badge-senduk' ?> rounded-pill">
                      <?= $log['kategori'] ?>
                    </span>
                  </td>
                  <td>
                    <b><?= htmlspecialchars($log['nomor_peserta']) ?></b> - <?= htmlspecialchars($log['nama_kontestan']) ?>
                  </td>
                  <td class="text-end text-muted small"><?= format_tanggal_indo($log['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables & Export Extensions CDN -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
  $(document).ready(function() {
    $('#tableLogVoting').DataTable({
      dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"Bf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>',
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="bi bi-file-earmark-excel me-1"></i> Export Excel',
          className: 'btn btn-success btn-sm rounded-pill px-3',
          title: 'Rekapitulasi Voting Kakang Senduk Ponorogo'
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="bi bi-file-earmark-pdf me-1"></i> Export PDF',
          className: 'btn btn-danger btn-sm rounded-pill px-3',
          title: 'Laporan Resmi Suara Pemilihan Kakang Senduk'
        },
        {
          extend: 'print',
          text: '<i class="bi bi-printer me-1"></i> Cetak Dokumen',
          className: 'btn btn-secondary btn-sm rounded-pill px-3'
        }
      ],
      language: {
        search: "Cari Data Log:",
        lengthMenu: "Tampilkan _MENU_ baris",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ log",
        infoEmpty: "Belum ada transaksi voting",
        paginate: {
          first: "Awal",
          last: "Akhir",
          next: "Selanjutnya",
          previous: "Sebelumnya"
        }
      }
    });
  });
</script>
</body>
</html>
