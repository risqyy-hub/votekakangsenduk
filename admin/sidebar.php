<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: admin/sidebar.php
 * Komponen Sidebar Admin Navigasi Bersama
 * ============================================================
 */

// $active_menu ditentukan oleh masing-masing halaman pemanggil
$active_menu = $active_menu ?? 'dashboard';
?>
<!-- Sidebar Navigation -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-header">
    <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--grad-maroon); display: flex; align-items: center; justify-content: center; color: var(--royal-gold); border: 1.5px solid var(--royal-gold);">
      <i class="bi bi-shield-lock-fill fs-5"></i>
    </div>
    <div>
      <h6 class="fw-bold mb-0 text-white">PANEL ADMIN</h6>
      <small class="text-muted" style="font-size: 0.7rem; letter-spacing: 0.05em;">Bumi Reog Ponorogo</small>
    </div>
  </div>

  <ul class="sidebar-menu">
    <li class="sidebar-menu-item">
      <a href="<?= BASE_URL ?>admin/dashboard.php" class="<?= $active_menu === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <li class="sidebar-menu-item">
      <a href="<?= BASE_URL ?>admin/kontestan.php" class="<?= $active_menu === 'kontestan' ? 'active' : '' ?>">
        <i class="bi bi-people-fill"></i>
        <span>Data Kontestan</span>
      </a>
    </li>

    <li class="sidebar-menu-item">
      <a href="<?= BASE_URL ?>admin/pemilih.php" class="<?= $active_menu === 'pemilih' ? 'active' : '' ?>">
        <i class="bi bi-person-lines-fill"></i>
        <span>Data Pemilih</span>
      </a>
    </li>

    <li class="sidebar-menu-item">
      <a href="<?= BASE_URL ?>admin/periode.php" class="<?= $active_menu === 'periode' ? 'active' : '' ?>">
        <i class="bi bi-calendar2-range-fill"></i>
        <span>Kelola Periode</span>
      </a>
    </li>

    <li class="sidebar-menu-item">
      <a href="<?= BASE_URL ?>admin/hasil-voting.php" class="<?= $active_menu === 'hasil-voting' ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-bar-graph-fill"></i>
        <span>Hasil & Rekapitulasi</span>
      </a>
    </li>

    <li class="mt-4 pt-3 border-top border-secondary border-opacity-25">
      <small class="text-muted text-uppercase px-3 fw-bold" style="font-size: 0.7rem;">Navigasi Cepat</small>
    </li>

    <li class="sidebar-menu-item mt-2">
      <a href="<?= BASE_URL ?>index.php" target="_blank">
        <i class="bi bi-box-arrow-up-right"></i>
        <span>Lihat Website</span>
      </a>
    </li>

    <li class="sidebar-menu-item">
      <a href="<?= BASE_URL ?>pages/klasemen.php" target="_blank">
        <i class="bi bi-trophy-fill"></i>
        <span>Klasemen Live</span>
      </a>
    </li>

    <li class="sidebar-menu-item mt-auto">
      <a href="<?= BASE_URL ?>auth/logout.php" class="text-danger">
        <i class="bi bi-box-arrow-left"></i>
        <span>Keluar Sistem</span>
      </a>
    </li>
  </ul>
</aside>
