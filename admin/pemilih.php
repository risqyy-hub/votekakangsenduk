<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: admin/pemilih.php
 * Daftar Akun Pemilih Terdaftar & Status Hak Suara
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';
require_admin();

$admin = get_logged_in_user($conn);

// Query seluruh pemilih beserta status voting Kakang & Senduk
$query = "
    SELECT 
        u.id, u.nama, u.email, u.no_hp, u.created_at,
        (SELECT k.nama FROM voting v JOIN kontestan k ON v.kontestan_id = k.id WHERE v.user_id = u.id AND v.kategori = 'Kakang' LIMIT 1) AS voted_kakang_nama,
        (SELECT k.nomor_peserta FROM voting v JOIN kontestan k ON v.kontestan_id = k.id WHERE v.user_id = u.id AND v.kategori = 'Kakang' LIMIT 1) AS voted_kakang_nomor,
        (SELECT k.nama FROM voting v JOIN kontestan k ON v.kontestan_id = k.id WHERE v.user_id = u.id AND v.kategori = 'Senduk' LIMIT 1) AS voted_senduk_nama,
        (SELECT k.nomor_peserta FROM voting v JOIN kontestan k ON v.kontestan_id = k.id WHERE v.user_id = u.id AND v.kategori = 'Senduk' LIMIT 1) AS voted_senduk_nomor
    FROM users u
    WHERE u.role = 'pemilih'
    ORDER BY u.id DESC
";
$result = $conn->query($query);
$pemilih_list = [];
while ($row = $result->fetch_assoc()) {
    $pemilih_list[] = $row;
}

$active_menu = 'pemilih';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Pemilih - Admin Panel Kakang Senduk</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
        <h5 class="fw-bold text-dark mb-0">Kelola Data Pemilih Terdaftar</h5>
      </div>
    </header>

    <main class="admin-body">
      <div class="card card-table shadow-sm">
        <div class="card-header">
          <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-person-lines-fill text-maroon me-2"></i>Daftar Pemilih & Penyaluran Hak Suara
          </h6>
          <span class="badge bg-maroon rounded-pill"><?= count($pemilih_list) ?> Pemilih Terdaftar</span>
        </div>

        <div class="p-4 table-responsive">
          <table class="table table-hover align-middle w-100" id="tablePemilih">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;">No</th>
                <th>Nama Pemilih</th>
                <th>Kontak & Email</th>
                <th>Status Suara Kakang</th>
                <th>Status Suara Senduk</th>
                <th class="text-end">Terdaftar Pada</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($pemilih_list as $p): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td>
                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($p['nama']) ?></span>
                    <small class="text-muted">ID User: #<?= $p['id'] ?></small>
                  </td>
                  <td>
                    <div><i class="bi bi-envelope text-muted me-1"></i><?= htmlspecialchars($p['email']) ?></div>
                    <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($p['no_hp']) ?></small>
                  </td>
                  <td>
                    <?php if ($p['voted_kakang_nomor']): ?>
                      <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($p['voted_kakang_nomor']) ?> - <?= htmlspecialchars($p['voted_kakang_nama']) ?>
                      </span>
                    <?php else: ?>
                      <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">
                        <i class="bi bi-clock me-1"></i>Belum Memilih
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($p['voted_senduk_nomor']): ?>
                      <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($p['voted_senduk_nomor']) ?> - <?= htmlspecialchars($p['voted_senduk_nama']) ?>
                      </span>
                    <?php else: ?>
                      <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">
                        <i class="bi bi-clock me-1"></i>Belum Memilih
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end text-muted small">
                    <?= format_tanggal_indo($p['created_at']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/script.js"></script>

<script>
  $(document).ready(function() {
    $('#tablePemilih').DataTable({
      language: {
        search: "Cari Pemilih:",
        lengthMenu: "Tampilkan _MENU_ pemilih",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pemilih",
        infoEmpty: "Tidak ada pemilih tersedia",
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
