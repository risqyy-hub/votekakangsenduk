# Sistem Voting Kakang Senduk Ponorogo

Aplikasi pemilihan Kakang dan Senduk Ponorogo berbasis PHP dan MySQL. Aplikasi menyediakan pendaftaran dan login pemilih, pengelolaan kontestan dan periode voting, pemungutan suara, serta klasemen hasil.

## Persyaratan

- PHP dengan ekstensi MySQLi
- MySQL atau MariaDB
- Apache (misalnya melalui XAMPP)

## Menjalankan secara lokal

1. Salin proyek ke folder web server, misalnya `C:\xampp\htdocs\votekakangsenduk`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Impor `database.sql` melalui phpMyAdmin atau MySQL.
4. Sesuaikan pengaturan koneksi database di `config/koneksi.php` bila diperlukan.
5. Buka `http://localhost/votekakangsenduk/` di browser.

## Akun demo

Data contoh tersedia saat mengimpor database:

- Admin: `admin@ponorogo.go.id` / `admin123`
- Pemilih: `budi@gmail.com` / `pemilih123`

Akun dan data tersebut hanya untuk pengembangan/demo. Ganti atau hapus sebelum aplikasi digunakan di lingkungan produksi.
