<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: admin/kontestan.php
 * Manajemen Data Kontestan (CRUD & DataTables)
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';
require_admin();

$admin = get_logged_in_user($conn);

// Ambil seluruh kontestan beserta perolehan suaranya
$query = "
    SELECT 
        k.*, 
        COUNT(v.id) AS total_suara
    FROM kontestan k
    LEFT JOIN voting v ON k.id = v.kontestan_id
    GROUP BY k.id
    ORDER BY k.kategori DESC, k.nomor_peserta ASC
";
$result = $conn->query($query);
$kontestan_list = [];
while ($row = $result->fetch_assoc()) {
    $kontestan_list[] = $row;
}

$active_menu = 'kontestan';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Kontestan - Admin Panel Kakang Senduk</title>
  
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
        <h5 class="fw-bold text-dark mb-0">Kelola Data Kontestan</h5>
      </div>

      <a href="<?= BASE_URL ?>admin/tambah-kontestan.php" class="btn btn-maroon btn-sm px-3">
        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kontestan Baru
      </a>
    </header>

    <main class="admin-body">
      <div class="card card-table shadow-sm">
        <div class="card-header">
          <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-people-fill text-maroon me-2"></i>Daftar Finalis Kakang & Senduk
          </h6>
          <span class="badge bg-maroon rounded-pill"><?= count($kontestan_list) ?> Kontestan Terdaftar</span>
        </div>

        <div class="p-4 table-responsive">
          <table class="table table-hover align-middle w-100" id="tableKontestan">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;">No</th>
                <th style="width: 70px;">Foto</th>
                <th style="width: 90px;">Nomor</th>
                <th>Nama Lengkap</th>
                <th>Kategori</th>
                <th>Asal Instansi</th>
                <th class="text-center">Total Suara</th>
                <th style="width: 140px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($kontestan_list as $k): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td>
                    <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($k['foto']) ?>" alt="<?= htmlspecialchars($k['nama']) ?>" class="rounded-3 border" style="width: 48px; height: 58px; object-fit: cover;">
                  </td>
                  <td>
                    <span class="badge bg-dark fs-6"><?= htmlspecialchars($k['nomor_peserta']) ?></span>
                  </td>
                  <td>
                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($k['nama']) ?></span>
                    <small class="text-muted"><?= htmlspecialchars($k['ttl']) ?></small>
                  </td>
                  <td>
                    <span class="badge <?= $k['kategori'] === 'Kakang' ? 'badge-kakang' : 'badge-senduk' ?> rounded-pill">
                      <?= htmlspecialchars($k['kategori']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($k['asal_instansi']) ?></td>
                  <td class="text-center">
                    <span class="badge bg-gold text-dark fs-6 px-3"><?= (int)$k['total_suara'] ?></span>
                  </td>
                  <td class="text-center">
                    <div class="d-inline-flex gap-1">
                      <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $k['id'] ?>" target="_blank" class="btn btn-outline-info btn-sm rounded-circle" title="Lihat Profil">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="<?= BASE_URL ?>admin/edit-kontestan.php?id=<?= $k['id'] ?>" class="btn btn-outline-warning btn-sm rounded-circle" title="Edit Data">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" title="Hapus Kontestan" onclick="confirmDelete('<?= BASE_URL ?>admin/hapus-kontestan.php?id=<?= $k['id'] ?>', 'Hapus <?= htmlspecialchars($k['nama']) ?>?')">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/script.js"></script>

<script>
  $(document).ready(function() {
    $('#tableKontestan').DataTable({
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Tidak ada data tersedia",
        paginate: {
          first: "Awal",
          last: "Akhir",
          next: "Selanjutnya",
          previous: "Sebelumnya"
        }
      }
    });
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
