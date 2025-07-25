# Klinik Alma Sehat - Aplikasi Web Dinamis

Aplikasi web klinik yang lengkap dengan fitur manajemen dokter, pasien, obat, booking konsultasi, dan penjualan obat online.

## 🚀 Fitur Utama

### 👥 Untuk Pasien
- **Registrasi & Login** - Daftar akun baru dan login
- **Booking Konsultasi** - Reservasi jadwal dengan dokter
- **Apotek Online** - Beli obat dengan sistem keranjang belanja
- **Riwayat Konsultasi** - Lihat riwayat booking dan hasil konsultasi
- **Riwayat Pembelian** - Lihat riwayat transaksi obat
- **Profil Management** - Edit data profil pribadi

### 👨‍⚕️ Untuk Dokter
- **Dashboard Dokter** - Lihat jadwal dan pasien
- **Manajemen Jadwal** - Atur jadwal praktik
- **Konsultasi Pasien** - Lihat daftar pasien yang booking
- **Laporan Kunjungan** - Input diagnosa dan resep

### 👨‍💼 Untuk Admin
- **Dashboard Admin** - Statistik dan overview sistem
- **Manajemen User** - CRUD data admin, dokter, dan pasien
- **Manajemen Dokter** - CRUD data dokter dan jadwal praktik
- **Manajemen Obat** - CRUD data obat dan stok
- **Manajemen Booking** - Kelola booking konsultasi
- **Manajemen Transaksi** - Kelola transaksi penjualan obat
- **Laporan** - Laporan penjualan, kunjungan, dan aktivitas
- **Pengaturan Sistem** - Konfigurasi info klinik

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+ (Native PHP, PDO)
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework CSS**: Bootstrap 5.3
- **Icons**: Font Awesome 6.0
- **Server**: Apache (XAMPP)

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache Web Server
- Browser modern (Chrome, Firefox, Safari, Edge)

## 🔧 Instalasi

### 1. Clone atau Download Project
```bash
git clone [repository-url]
# atau download dan extract ke folder xampp/htdocs/alma
```

### 2. Setup Database
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Import file `database/klinik_alma.sql`
3. Database `klinik_alma` akan terbuat otomatis dengan data sample

### 3. Konfigurasi Database
Edit file `config/database.php` jika diperlukan:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'klinik_alma');
```

### 4. Setup Folder Upload
Buat folder untuk upload file:
```
mkdir uploads
mkdir uploads/doctors
chmod 755 uploads
chmod 755 uploads/doctors
```

### 5. Test Database Connection
Akses: `http://localhost/alma/test_db.php`
- Untuk memverifikasi koneksi database
- Memeriksa status semua tabel
- Melihat data sample yang tersedia

### 6. Quick Setup (Windows)
Jalankan file `setup.bat` untuk panduan lengkap

### 7. Akses Aplikasi
Buka browser dan akses: `http://localhost/alma`

## 👤 Akun Default

### Admin
- **Email**: admin@klinik.com
- **Password**: password

### Dokter
- **Email**: ahmad@klinik.com
- **Password**: password
- **Email**: sari@klinik.com
- **Password**: password

### Pasien
Daftar akun baru melalui halaman registrasi

## 📁 Struktur Folder

```
alma/
├── admin/                  # Dashboard dan fitur admin
├── assets/                 # CSS, JS, dan asset lainnya
│   ├── css/
│   └── js/
├── config/                 # Konfigurasi database dan sistem
├── database/               # File SQL database
├── dokter/                 # Dashboard dan fitur dokter
├── includes/               # Template header dan footer
├── pasien/                 # Dashboard dan fitur pasien
├── uploads/                # Folder upload file
├── index.php              # Halaman beranda
├── login.php              # Halaman login
├── register.php           # Halaman registrasi
├── dokter.php             # Halaman daftar dokter
├── obat.php               # Halaman daftar obat
├── booking.php            # Halaman booking konsultasi
├── keranjang.php          # Halaman keranjang belanja
└── README.md              # Dokumentasi ini
```

## 🗄️ Database Schema

### Tabel Utama
- **users** - Data pengguna (admin, dokter, pasien)
- **dokter** - Data dokter dan spesialisasi
- **pasien** - Data pasien
- **jadwal_praktik** - Jadwal praktik dokter
- **obat** - Data obat dan stok
- **booking** - Data booking konsultasi
- **transaksi** - Data transaksi penjualan obat
- **detail_transaksi** - Detail item transaksi
- **laporan_kunjungan** - Laporan hasil konsultasi
- **pengaturan** - Pengaturan sistem

## 🔐 Sistem Autentikasi

- **Session Management** - Menggunakan PHP Session
- **Password Hashing** - Menggunakan PHP password_hash()
- **Role-based Access** - Admin, Dokter, Pasien
- **Route Protection** - Middleware untuk proteksi halaman

## 🛡️ Keamanan

- **SQL Injection Protection** - Menggunakan PDO Prepared Statements
- **XSS Protection** - Input sanitization dan output escaping
- **CSRF Protection** - Token validation untuk form
- **Password Security** - Hashing dengan bcrypt
- **File Upload Security** - Validasi tipe dan ukuran file

## 📱 Responsive Design

- **Mobile First** - Desain responsif untuk semua device
- **Bootstrap Grid** - Layout yang fleksibel
- **Touch Friendly** - Interface yang mudah digunakan di mobile

## 🎨 UI/UX Features

- **Modern Design** - Interface yang clean dan modern
- **Loading States** - Feedback visual untuk user
- **Notifications** - Alert dan notifikasi real-time
- **Form Validation** - Validasi client-side dan server-side
- **Search & Filter** - Pencarian dan filter data
- **Pagination** - Pembagian halaman untuk data besar

## 📊 Laporan

### Laporan Admin
- Laporan penjualan obat (harian, bulanan, tahunan)
- Laporan kunjungan pasien
- Laporan aktivitas dokter
- Laporan stok obat
- Export ke Excel/PDF (opsional)

### Laporan Dokter
- Jadwal praktik
- Daftar pasien
- Riwayat konsultasi

### Laporan Pasien
- Riwayat booking
- Riwayat pembelian obat
- Hasil konsultasi

## 🔄 API Endpoints

- `get_doctor_schedule.php` - Mendapatkan jadwal dokter
- `get_available_slots.php` - Mendapatkan slot waktu tersedia
- `process_checkout.php` - Memproses checkout keranjang

## 🚀 Fitur Lanjutan

- **Real-time Notifications** - Notifikasi booking dan transaksi
- **Email Integration** - Konfirmasi booking via email
- **Google Maps** - Lokasi klinik
- **Print Reports** - Cetak laporan
- **Data Export** - Export data ke Excel/PDF

## 🐛 Troubleshooting

### Error Database Connection
- Pastikan MySQL service berjalan
- Cek konfigurasi database di `config/database.php`
- Pastikan database `klinik_alma` sudah dibuat

### Error File Upload
- Pastikan folder `uploads/` memiliki permission write
- Cek setting `upload_max_filesize` di php.ini

### Error Session
- Pastikan session.save_path dapat diakses
- Restart Apache service

## 📞 Support

Jika mengalami masalah atau butuh bantuan:
1. Cek dokumentasi ini terlebih dahulu
2. Periksa log error di Apache/PHP
3. Pastikan semua requirement terpenuhi

## 📝 License

Project ini dibuat untuk keperluan edukasi dan pembelajaran.

## 🤝 Contributing

Kontribusi selalu diterima! Silakan:
1. Fork repository
2. Buat branch fitur baru
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

---

**Klinik Alma Sehat** - Melayani kesehatan Anda dengan sepenuh hati! 💙