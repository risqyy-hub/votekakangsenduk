-- ============================================================
-- DATABASE VOTING PEMILIHAN KAKANG SENDUK PONOROGO
-- Modern Cultural Elegance Theme
-- Versi: 1.0.0
-- ============================================================

CREATE DATABASE IF NOT EXISTS `db_voting_kakang_senduk` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `db_voting_kakang_senduk`;

-- ------------------------------------------------------------
-- 1. TABEL: users
-- Menyimpan data admin dan pemilih terdaftar
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `voting`;
DROP TABLE IF EXISTS `kontestan`;
DROP TABLE IF EXISTS `periode_voting`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `no_hp` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'pemilih') NOT NULL DEFAULT 'pemilih',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. TABEL: periode_voting
-- Mengatur periode aktif dan jadwal pemilihan
-- ------------------------------------------------------------
CREATE TABLE `periode_voting` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_periode` VARCHAR(100) NOT NULL,
  `tanggal_mulai` DATETIME NOT NULL,
  `tanggal_selesai` DATETIME NOT NULL,
  `status` ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. TABEL: kontestan
-- Menyimpan biodata lengkap kontestan Kakang dan Senduk
-- ------------------------------------------------------------
CREATE TABLE `kontestan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_peserta` VARCHAR(10) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `kategori` ENUM('Kakang', 'Senduk') NOT NULL,
  `ttl` VARCHAR(100) NOT NULL,
  `foto` VARCHAR(255) NOT NULL,
  `asal_instansi` VARCHAR(150) NOT NULL,
  `visi` TEXT NOT NULL,
  `misi` TEXT NOT NULL,
  `prestasi` TEXT NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. TABEL: voting
-- Menyimpan suara masuk dengan batasan 1 user hanya 1 Kakang & 1 Senduk
-- ------------------------------------------------------------
CREATE TABLE `voting` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `kontestan_id` INT NOT NULL,
  `kategori` ENUM('Kakang', 'Senduk') NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_kategori` (`user_id`, `kategori`),
  CONSTRAINT `fk_voting_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_voting_kontestan` FOREIGN KEY (`kontestan_id`) REFERENCES `kontestan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DUMMY DATA SEEDER
-- ============================================================

-- Data Users (Password hash: admin123 untuk admin, pemilih123 untuk pemilih)
-- Hash BCRYPT:
-- 'admin123'   -> $2y$12$R4KyczFpnYCavnMoRghrZObSB60h6mHyrXQQHm.bm1CUVYGlUJrrK
-- 'pemilih123' -> $2y$12$KOKWtnuIbLZdIAeERv5Gc.c7B1Ih/oFyEmk04GrRIWyZ0Je3hQHL2
INSERT INTO `users` (`id`, `nama`, `email`, `no_hp`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator Disbudparpora', 'admin@ponorogo.go.id', '081234567890', '$2y$12$R4KyczFpnYCavnMoRghrZObSB60h6mHyrXQQHm.bm1CUVYGlUJrrK', 'admin', NOW()),
(2, 'Budi Santoso', 'budi@gmail.com', '081298765431', '$2y$12$KOKWtnuIbLZdIAeERv5Gc.c7B1Ih/oFyEmk04GrRIWyZ0Je3hQHL2', 'pemilih', NOW()),
(3, 'Siti Rahmawati', 'siti@gmail.com', '081298765432', '$2y$12$KOKWtnuIbLZdIAeERv5Gc.c7B1Ih/oFyEmk04GrRIWyZ0Je3hQHL2', 'pemilih', NOW()),
(4, 'Dimas Pratama Putra', 'dimas@gmail.com', '081298765433', '$2y$12$KOKWtnuIbLZdIAeERv5Gc.c7B1Ih/oFyEmk04GrRIWyZ0Je3hQHL2', 'pemilih', NOW());

-- Data Periode Voting Aktif (Aktif 14 hari ke depan)
INSERT INTO `periode_voting` (`id`, `nama_periode`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`) VALUES
(1, 'Pemilihan Duta Wisata Kakang Senduk Ponorogo 2026/2027', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY), 'Aktif', NOW());

-- Data Kontestan (3 Kakang & 3 Senduk)
INSERT INTO `kontestan` (`id`, `nomor_peserta`, `nama`, `kategori`, `ttl`, `foto`, `asal_instansi`, `visi`, `misi`, `prestasi`, `deskripsi`, `created_at`) VALUES
(1, 'K-01', 'Raditya Bagus Dewantara', 'Kakang', 'Ponorogo, 14 Mei 2004', 'kakang-01.svg', 'SMAN 1 Ponorogo', 
 'Mewujudkan generasi muda Ponorogo yang berkarakter, berbudaya luhur, adaptif, serta mampu mempromosikan pariwisata daerah di panggung global.',
 '1. Mengembangkan platform digital kreatif untuk mengampanyekan Reog Ponorogo kepada Gen-Z.\n2. Mengadakan lokakarya pelestarian seni tradisional di sekolah-sekolah.\n3. Berkolaborasi dengan UMKM ekonomi kreatif lokal Ponorogo.',
 '- Juara 1 Duta Bahasa Pelajar Ponorogo 2024\n- Delegasi Budaya Festival Nasional Reyog Ponorogo 2025\n- Juara 2 Pidato Bahasa Inggris se-Jawa Timur',
 'Raditya adalah pemuda yang memiliki kecintaan mendalam terhadap seni tari Warok dan sejarah Ponorogo. Memiliki integritas tinggi, kepemimpinan aktif, dan visi global dalam mempromosikan pariwisata Ponorogo.',
 NOW()),

(2, 'K-02', 'Arya Wijaya Kusuma', 'Kakang', 'Ponorogo, 22 Agustus 2003', 'kakang-02.svg', 'Universitas Darussalam Gontor',
 'Menjadikan warisan adiluhung Ponorogo sebagai inspirasi pariwisata edukasi, religi, dan budaya kelas dunia.',
 '1. Menginisiasi program Kakang Senduk Mengajar di desa-desa wisata Ponorogo.\n2. Mendorong digitalisasi arsip sejarah dan situs budaya daerah.\n3. Membangun jejaring pemuda pelopor pariwisata lintas kabupaten se-Karesidenan Madiun.',
 '- Duta Kampus Berprestasi 2024\n- Juara 1 Karya Tulis Ilmiah Pariwisata Daerah 2025\n- Pemakalah Seminar Kebudayaan Nasional',
 'Arya aktif dalam penulisan ilmiah dan advokasi desa wisata. Dikenal berwibawa, fasih berbahasa Arab dan Inggris, serta berkomitmen memperkuat citra Ponorogo Kota Budaya.',
 NOW()),

(3, 'K-03', 'Bayu Pratama Putra', 'Kakang', 'Ponorogo, 10 November 2002', 'kakang-03.svg', 'Universitas Muhammadiyah Ponorogo',
 'Mewujudkan sinergi pemuda dan teknologi informasi menuju Ponorogo Smart Cultural Heritage Tourism.',
 '1. Memproduksi konten sinematografi pariwisata interaktif untuk destinasi Telaga Ngebel dan Waduk Bendo.\n2. Menggerakkan komunitas sadar kebersihan lingkungan wisata.\n3. Mengedukasi penerapan Sapta Pesona kepada para pelaku usaha wisata.',
 '- Juara 1 Lomba Fotografi & Videografi Pariwisata Jawa Timur 2024\n- Finalis Pemuda Pelopor Ponorogo Bidang Inovasi Digital 2025',
 'Bayu berjiwa dinamis, memiliki kemampuan storytelling visual yang kuat, serta berdedikasi mengangkat pesona alam dan kuliner otentik Ponorogo.',
 NOW()),

(4, 'S-01', 'Anindya Putri Larasati', 'Senduk', 'Ponorogo, 18 Maret 2005', 'senduk-01.svg', 'SMAN 2 Ponorogo',
 'Menguatkan representasi wanita muda Ponorogo yang anggun, cerdas, berbudaya, dan berdaya saing internasional.',
 '1. Memperkenalkan filosofi tari Jathil dan Reog kepada generasi muda melalui media digital interaktif.\n2. Menggerakkan program ramah anak dan literasi budaya di sanggar-sanggar seni Ponorogo.\n3. Menjadi representasi keramahan (Hospitality) khas masyarakat Ponorogo bagi para wisatawan.',
 '- Juara 1 Tari Tradisional Remaja Tingkat Jawa Timur 2024\n- Duta Lingkungan Hidup Remaja Ponorogo 2023\n- Juara 1 Storytelling Bahasa Inggris Tingkat Karesidenan',
 'Anindya merupakan penari berbakat yang memadukan keanggunan tata krama Jawa dengan wawasan intelektual yang luas serta komunikasi publik yang prima.',
 NOW()),

(5, 'S-02', 'Citra Lestari Wibowo', 'Senduk', 'Ponorogo, 05 Juli 2003', 'senduk-02.svg', 'IAIN Ponorogo',
 'Mengoptimalkan potensi ecotourism dan ekonomi kreatif berbasis kearifan lokal melalui pemberdayaan pemudi daerah.',
 '1. Memfasilitasi inkubasi UMKM cinderamata dan kopi khas pegunungan Ponorogo.\n2. Menjalin kemitraan dengan pegiat pariwisata nasional untuk paket wisata edukasi budaya.\n3. Mengembangkan program pemandu wisata ramah keluarga di desa wisata.',
 '- Juara 1 Duta Gender & Pemberdayaan Perempuan 2024\n- Penerima Beasiswa Kepemimpinan Mahasiswa Nasional 2024\n- Juara Favorit Festival Busana Etnik Ponorogo',
 'Citra berkarakter ramah, tekun, dan membumi. Memiliki konsistensi tinggi dalam mengedukasi masyarakat mengenai keberlanjutan lingkungan dan pariwisata desa.',
 NOW()),

(6, 'S-03', 'Dinda Kirana Maharani', 'Senduk', 'Ponorogo, 12 Januari 2004', 'senduk-03.svg', 'STKIP PGRI Ponorogo',
 'Mendukung penetapan Ponorogo sebagai Jaringan Kota Kreatif Dunia (UCCN) UNESCO melalui inovasi generasi emas.',
 '1. Mendorong modernisasi desain kriya dan batik motif khas Reyog Ponorogo tanpa menghilangkan nilai pakem.\n2. Membuka ruang kolaborasi virtual bagi seniman muda dan wisatawan internasional.\n3. Mengedukasi pentingnya hak cipta dan perlindungan warisan budaya daerah.',
 '- Best Delegate Model United Nations Youth Ponorogo 2024\n- Desainer Muda Terbaik Fashion Week Budaya Nusantara 2025\n- Fasilitator Forum Pemuda Kreatif Ponorogo',
 'Dinda memiliki kepribadian elegan dan dinamis, fasih dalam advokasi seni diplomasi budaya, dan gigih memajukan batik serta kriya khas Bumi Reog.',
 NOW());

-- Data Voting Dummy (Memberi perolehan awal untuk pengujian klasemen & podium)
INSERT INTO `voting` (`user_id`, `kontestan_id`, `kategori`, `created_at`) VALUES
(2, 1, 'Kakang', DATE_SUB(NOW(), INTERVAL 5 HOUR)), -- Budi memilih K-01
(2, 4, 'Senduk', DATE_SUB(NOW(), INTERVAL 5 HOUR)), -- Budi memilih S-01
(3, 1, 'Kakang', DATE_SUB(NOW(), INTERVAL 3 HOUR)), -- Siti memilih K-01
(3, 5, 'Senduk', DATE_SUB(NOW(), INTERVAL 3 HOUR)), -- Siti memilih S-02
(4, 2, 'Kakang', DATE_SUB(NOW(), INTERVAL 1 HOUR)), -- Dimas memilih K-02
(4, 4, 'Senduk', DATE_SUB(NOW(), INTERVAL 1 HOUR)); -- Dimas memilih S-01
