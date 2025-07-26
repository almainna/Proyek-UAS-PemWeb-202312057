# 📖 Installation Guide - Klinik Alma Sehat

**Proyek Ujian Akhir Semester - Pemrograman Web**  
**Alma Inna (202312057) - Sekolah Tinggi Teknologi Bontang**  
**Email: flowerrdaaisy@gmail.com**

---

## Prerequisites

Sebelum memulai instalasi, pastikan sistem Anda memenuhi persyaratan berikut:

### System Requirements
- **Operating System:** Windows 10/11, macOS 10.15+, atau Linux (Ubuntu 18.04+)
- **Web Server:** Apache 2.4+ atau Nginx 1.18+
- **PHP:** Version 7.4 atau lebih tinggi
- **Database:** MySQL 5.7+ atau MariaDB 10.5+
- **Memory:** Minimum 512MB RAM
- **Storage:** Minimum 100MB disk space

### Required PHP Extensions
```bash
php-pdo
php-pdo-mysql
php-session
php-json
php-mbstring
php-openssl
php-curl
php-gd (optional, untuk manipulasi gambar)
```

## Installation Methods

### Method 1: XAMPP (Recommended for Development)

#### Step 1: Download and Install XAMPP
1. Kunjungi [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Download XAMPP untuk sistem operasi Anda
3. Install XAMPP dengan pengaturan default
4. Jalankan XAMPP Control Panel

#### Step 2: Start Services
```bash
# Melalui XAMPP Control Panel, start:
- Apache
- MySQL
```

#### Step 3: Download Project
```bash
# Option A: Download ZIP
# Download project dari repository dan extract ke folder htdocs

# Option B: Git Clone (jika Git tersedia)
cd C:\xampp\htdocs\  # Windows
cd /Applications/XAMPP/htdocs/  # macOS
cd /opt/lampp/htdocs/  # Linux

git clone <repository-url> alma
```

#### Step 4: Database Setup
1. Buka browser dan akses `http://localhost/phpmyadmin`
2. Klik "New" untuk membuat database baru
3. Nama database: `klinik_alma`
4. Collation: `utf8mb4_unicode_ci`
5. Klik "Create"
6. Pilih database yang baru dibuat
7. Klik tab "Import"
8. Pilih file `database/klinik_alma.sql` dari folder project
9. Klik "Go" untuk import

#### Step 5: Configuration
Edit file `config/database.php`:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Kosong untuk XAMPP default
define('DB_NAME', 'klinik_alma');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

#### Step 6: Setup Upload Directories
```bash
# Create directories untuk file upload
mkdir uploads
mkdir uploads/doctors
chmod 755 uploads
chmod 755 uploads/doctors
```

#### Step 7: Access Application
Buka browser dan akses: `http://localhost/alma/`

## Post-Installation Setup

### 1. Verify Installation
Akses aplikasi melalui browser dan pastikan:
- Halaman login dapat diakses
- Database connection berhasil
- Tidak ada error PHP

### 2. Test Default Accounts
Login dengan akun default:

**Admin Account:**
- Email: `admin@klinik.com`
- Password: `password`

**Doctor Accounts:**
- Email: `ahmad@klinik.com`
- Password: `password`
- Email: `sari@klinik.com`
- Password: `password`

> 🔒 **Password Security:** Semua password menggunakan bcrypt hash untuk keamanan maksimal

### 3. Security Configuration

#### Change Default Passwords
```sql
-- Update admin password
UPDATE users SET password = '$2y$10$newhashedpassword' WHERE email = 'admin@klinik.com';
```

#### File Permissions (Linux)
```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/alma/

# Set proper permissions
sudo find /var/www/html/alma/ -type d -exec chmod 755 {} \;
sudo find /var/www/html/alma/ -type f -exec chmod 644 {} \;
```

## Troubleshooting

### Common Installation Issues

#### 1. Database Connection Failed
**Error:** `Connection failed: SQLSTATE[HY000] [1045] Access denied`

**Solution:**
```bash
# Check MySQL service
sudo systemctl status mysql

# Reset MySQL password
sudo mysql -u root -p
ALTER USER 'root'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;
```

#### 2. Permission Denied
**Error:** `Permission denied` atau `403 Forbidden`

**Solution:**
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/alma/
sudo chmod -R 755 /var/www/html/alma/
```

#### 3. PHP Extensions Missing
**Error:** `Call to undefined function PDO()`

**Solution:**
```bash
# Install missing PHP extensions
sudo apt install php7.4-mysql php7.4-pdo -y
sudo systemctl restart apache2
```

## Verification Checklist

Setelah instalasi selesai, pastikan semua item berikut berfungsi:

- [ ] Aplikasi dapat diakses melalui browser
- [ ] Database connection berhasil
- [ ] Login admin berfungsi
- [ ] Login doctor berfungsi
- [ ] Registrasi pasien berfungsi
- [ ] Dashboard dapat diakses
- [ ] Navigasi antar halaman berfungsi
- [ ] Upload file berfungsi (jika ada)
- [ ] Session management berfungsi
- [ ] Error handling berfungsi

## Next Steps

Setelah instalasi berhasil:

1. Baca [USAGE.md](USAGE.md) untuk panduan penggunaan
2. Baca [DATABASE.md](DATABASE.md) untuk memahami struktur database
3. Baca [DEPLOYMENT.md](DEPLOYMENT.md) untuk deployment ke production
4. Customize aplikasi sesuai kebutuhan
5. Setup backup dan monitoring

## Support

Jika mengalami masalah selama instalasi:

1. Periksa log error di `/var/log/apache2/error.log`
2. Periksa log PHP error
3. Pastikan semua service berjalan
4. Verifikasi konfigurasi database
5. Periksa file permissions

Untuk bantuan lebih lanjut, silakan hubungi developer atau buat issue di repository.
