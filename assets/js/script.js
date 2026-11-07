/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: assets/js/script.js
 * Client-Side Interaction, SweetAlert2 Handlers, Search & Filters
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function () {
  // Inisialisasi Countdown Timer jika ada element di halaman
  initCountdownTimer();

  // Inisialisasi Filter dan Pencarian Kontestan
  initContestantFilters();

  // Inisialisasi Tombol Voting Konfirmasi SweetAlert2
  initVotingButtons();
});

/**
 * ------------------------------------------------------------
 * 1. COUNTDOWN TIMER HERO SECTION
 * ------------------------------------------------------------
 */
function initCountdownTimer() {
  const timerElement = document.getElementById('votingCountdown');
  if (!timerElement) return;

  const targetDateStr = timerElement.getAttribute('data-target');
  if (!targetDateStr) return;

  const targetDate = new Date(targetDateStr).getTime();

  function updateTimer() {
    const now = new Date().getTime();
    const distance = targetDate - now;

    if (distance < 0) {
      document.getElementById('days').innerText = '00';
      document.getElementById('hours').innerText = '00';
      document.getElementById('minutes').innerText = '00';
      document.getElementById('seconds').innerText = '00';
      const statusText = document.getElementById('countdownStatus');
      if (statusText) statusText.innerText = 'Periode Voting Telah Berakhir';
      return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    const pad = (num) => String(num).padStart(2, '0');

    const elDays = document.getElementById('days');
    const elHours = document.getElementById('hours');
    const elMinutes = document.getElementById('minutes');
    const elSeconds = document.getElementById('seconds');

    if (elDays) elDays.innerText = pad(days);
    if (elHours) elHours.innerText = pad(hours);
    if (elMinutes) elMinutes.innerText = pad(minutes);
    if (elSeconds) elSeconds.innerText = pad(seconds);
  }

  updateTimer();
  setInterval(updateTimer, 1000);
}

/**
 * ------------------------------------------------------------
 * 2. LIVE SEARCH & FILTER TAB KONTESTAN
 * ------------------------------------------------------------
 */
function initContestantFilters() {
  const searchInput = document.getElementById('searchContestant');
  const filterBtns = document.querySelectorAll('.filter-btn');
  const contestantCards = document.querySelectorAll('.contestant-item');
  const emptyState = document.getElementById('emptyContestants');

  if (!contestantCards.length) return;

  let currentCategory = 'all';
  let currentSearch = '';

  function filterCards() {
    let visibleCount = 0;

    contestantCards.forEach((card) => {
      const cardCategory = card.getAttribute('data-category');
      const cardName = card.getAttribute('data-name').toLowerCase();
      const cardInstansi = card.getAttribute('data-instansi').toLowerCase();
      const cardNomor = card.getAttribute('data-nomor').toLowerCase();

      const matchCategory = (currentCategory === 'all' || cardCategory === currentCategory);
      const matchSearch = (
        cardName.includes(currentSearch) ||
        cardInstansi.includes(currentSearch) ||
        cardNomor.includes(currentSearch)
      );

      if (matchCategory && matchSearch) {
        card.style.display = 'block';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  // Event Listener Search Input
  if (searchInput) {
    searchInput.addEventListener('input', function (e) {
      currentSearch = e.target.value.toLowerCase().trim();
      filterCards();
    });
  }

  // Event Listener Tab Filter Kategori
  filterBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      filterBtns.forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
      currentCategory = this.getAttribute('data-filter');
      filterCards();
    });
  });
}

/**
 * ------------------------------------------------------------
 * 3. SWEETALERT2 VOTING CONFIRMATION & SELECTION
 * ------------------------------------------------------------
 */
function initVotingButtons() {
  // Tombol voting langsung dengan konfirmasi
  document.querySelectorAll('.btn-trigger-vote').forEach((button) => {
    button.addEventListener('click', function (e) {
      e.preventDefault();

      const kontestanId = this.getAttribute('data-id');
      const kontestanNama = this.getAttribute('data-nama');
      const kontestanKategori = this.getAttribute('data-kategori');
      const kontestanNomor = this.getAttribute('data-nomor');

      Swal.fire({
        title: 'Konfirmasi Pilihan Voting',
        html: `Apakah Anda yakin ingin memberikan suara kepada:<br>
               <strong style="color: #6B0F1A; font-size: 1.2rem;">${kontestanNomor} - ${kontestanNama}</strong><br>
               <span class="badge bg-maroon mt-2">Kategori ${kontestanKategori}</span><br>
               <small class="text-muted d-block mt-3">Perhatian: Pilihan suara Anda bersifat final dan tidak dapat diubah setelah dikonfirmasi.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6B0F1A',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Kirim Suara!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
          popup: 'rounded-4 shadow-lg border-2',
          confirmButton: 'btn btn-maroon px-4 py-2',
          cancelButton: 'btn btn-secondary px-4 py-2'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          // Buat form dinamis untuk submit voting
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = window.BASE_URL ? window.BASE_URL + 'proses/simpan-voting.php' : '../proses/simpan-voting.php';

          const inputId = document.createElement('input');
          inputId.type = 'hidden';
          inputId.name = 'kontestan_id';
          inputId.value = kontestanId;

          const inputKategori = document.createElement('input');
          inputKategori.type = 'hidden';
          inputKategori.name = 'kategori';
          inputKategori.value = kontestanKategori;

          form.appendChild(inputId);
          form.appendChild(inputKategori);
          document.body.appendChild(form);
          form.submit();
        }
      });
    });
  });
}

/**
 * ------------------------------------------------------------
 * 4. SWEETALERT CONFIRM DELETE FOR ADMIN
 * ------------------------------------------------------------
 */
function confirmDelete(url, title = 'Hapus Data Ini?') {
  Swal.fire({
    title: title,
    text: 'Data yang dihapus tidak dapat dipulihkan kembali!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#DC2626',
    cancelButtonColor: '#64748B',
    confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus!',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    customClass: {
      popup: 'rounded-4 shadow-lg',
      confirmButton: 'btn btn-danger px-4 py-2',
      cancelButton: 'btn btn-secondary px-4 py-2'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = url;
    }
  });
}
