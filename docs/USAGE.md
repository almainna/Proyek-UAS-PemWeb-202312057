# 📝 Usage Guide - Klinik Alma Sehat

**Proyek Ujian Akhir Semester - Pemrograman Web**  
**Alma Inna (202312057) - Sekolah Tinggi Teknologi Bontang**  
**Email: flowerrdaaisy@gmail.com**

---

## Overview

Klinik Alma Sehat adalah aplikasi web yang memungkinkan klinik untuk mengelola pasien, dokter, jadwal, konsultasi, dan transaksi obat. Sistem ini dibagi menjadi tiga pengguna utama: **Administrator**, **Dokter**, dan **Pasien**.

## Getting Started

### Accessing the Application
1. Buka browser web
2. Akses URL aplikasi: `http://localhost/alma/`
3. Anda akan melihat halaman login utama

### User Roles

#### Administrator
- Manajemen penuh sistem
- Menambahkan/menghapus dokter, pasien, dan pengguna
- Pengaturan pelayanan medis dan obat
- Pengawasan semua aktivitas pengguna

#### Dokter
- Melihat dan mengelola jadwal praktik
- Melihat daftar pasien
- Mengedit catatan medis setelah konsultasi
- Melihat riwayat konsultasi dan obat yang diresepkan

#### Pasien
- Mendaftar dan login
- Melihat dan memesan jadwal konsultasi dokter
- Membeli obat secara online
- Melihat riwayat konsultasi dan pembelian obat

## User Registration and Login

### Registration (Pasien)
1. Klik tombol **"Daftar"** di halaman utama
2. Isi form registrasi:
   - **Username:** Nama pengguna unik
   - **Email:** Alamat email valid
   - **Password:** Minimal 6 karakter
   - **Confirm Password:** Konfirmasi password
3. Klik **"Daftar"**
4. Jika berhasil, Anda akan diarahkan ke halaman login

### Login
1. Masukkan **Email** dan **Password**
2. Klik **"Login"**
3. Anda akan diarahkan ke dashboard sesuai dengan peran:
   - Admin → Admin Dashboard
   - Dokter → Doctor Dashboard
   - Pasien → User Dashboard

### Default Test Accounts

#### Admin Account
- **Email:** admin@klinik.com
- **Password:** password

#### Doctor Accounts
**Dokter 1:**
- **Email:** ahmad@klinik.com
- **Password:** password

**Dokter 2:**
- **Email:** sari@klinik.com
- **Password:** password

 🔒 **Password Security:** Semua password menggunakan bcrypt hash untuk keamanan maksimal

## Patient Features

### 1. User Dashboard
Setelah login sebagai pasien, Anda akan melihat:

#### Welcome Section
- Pesan selamat datang personal
- Ringkasan akun

#### Appointment Summary
- Konsultasi aktif dan mendatang
- Riwayat booking sebelumnya

#### Recent Transactions
- Riwayat pembelian obat terbaru
- Status setiap transaksi

### 2. Booking Consultations

#### Step 1: Select Doctor
1. Pilih dokter dari daftar yang tersedia
2. Klik **"Pilih Jadwal"**

#### Step 2: Booking Form
Isi form booking:
- **Consultation Date:** Tanggal konsultasi
- **Time Slot:** Pilih waktu konsultasi
- **Notes:** Catatan tambahan (opsional)

#### Step 3: Confirmation
- Review detail booking
- Klik **"Konfirmasi Booking"**

#### Step 4: Booking Status
Setelah booking berhasil:
- Status: **"Pending"** (menunggu konfirmasi admin)
- Anda akan menerima notifikasi
- Booking dapat dilihat di dashboard

### 3. Medicine Purchase

#### Step 1: Browse Medicines
- Klik pada menu **"Apotek"**
- Telusuri obat yang tersedia
- Tambahkan item ke keranjang

#### Step 2: Checkout
- Klik **"Keranjang"** di navbar
- Review item dan jumlah
- Klik **"Checkout"**

#### Step 3: Payment
- Pilih metode pembayaran
- Klik **"Bayar Sekarang"**

### 4. Profile Management
Akses melalui dropdown profil → **"Profil Saya"**

#### View Profile
- Lihat informasi personal
- Email, username, nama lengkap

#### Edit Profile
1. Klik **"Edit Profil"**
2. Update informasi yang diperlukan:
   - Email
   - Username
   - Password
3. Klik **"Simpan Perubahan"**

#### Change Password
1. Klik **"Ubah Password"**
2. Masukkan:
   - Password lama
   - Password baru
   - Konfirmasi password baru
3. Klik **"Ubah Password"**

### 5. Consultation History
Lihat di dashboard atau menu riwayat:

#### Booking Information
- **Doctor Info:** Nama, spesialisasi
- **Consultation Date:** Tanggal konsultasi
- **Status:** Status konsultasi
- **Booking Date:** Tanggal booking dibuat

#### Consultation Status
- **Pending:** Menunggu konfirmasi
- **Confirmed:** Dikonfirmasi oleh admin
- **Completed:** Konsultasi selesai
- **Cancelled:** Dibatalkan

## Administrator Features

### 1. Admin Dashboard
Akses setelah login sebagai admin:

#### System Statistics
- **Total Users:** Jumlah pengguna terdaftar
- **Total Appointments:** Jumlah booking konsultasi
- **Total Medicines:** Stock obat tersedia

#### Quick Actions
- Link cepat ke modul manajemen
- Statistik real-time
- Recent activities

### 2. User Management
Akses melalui **Admin → Users**

#### View Users
- Daftar semua pengguna
- Informasi: email, role, status
- Filter berdasarkan role atau status

#### Add New User
1. Klik **"Add New User"**
2. Isi form:
   - Email
   - Password
   - Full name
   - Role (Admin/Dokter/Pasien)
3. Klik **"Save"**

#### Edit User
1. Klik **"Edit"** pada pengguna yang dipilih
2. Update informasi yang diperlukan
3. Klik **"Update"**

#### Delete User
1. Klik **"Delete"** pada pengguna yang dipilih
2. Konfirmasi penghapusan
3. Pengguna akan dihapus dari sistem

### 3. Doctor Management
Akses melalui **Admin → Doctors**

#### View Doctors
- Daftar semua dokter
- Informasi: email, spesialisasi, status
- Filter berdasarkan spesialisasi atau status

#### Add New Doctor
1. Klik **"Add New Doctor"**
2. Isi form:
   - Email
   - Password
   - Full name
   - Speciality
3. Klik **"Save"**

#### Edit Doctor
1. Klik **"Edit"** pada dokter yang dipilih
2. Update informasi yang diperlukan
3. Klik **"Update"**

#### Delete Doctor
1. Klik **"Delete"** pada dokter yang dipilih
2. Konfirmasi penghapusan
3. Dokter akan dihapus dari sistem

### 4. Medicine Management
Akses melalui **Admin → Medicines**

#### View Medicines
- Daftar semua obat
- Informasi: nama, stock, harga
- Filter berdasarkan kategori atau status

#### Add New Medicine
1. Klik **"Add New Medicine"**
2. Isi form:
   - Medicine name
   - Quantity
   - Price
   - Description
3. Klik **"Save"**

#### Edit Medicine
1. Klik **"Edit"** pada obat yang dipilih
2. Update informasi yang diperlukan
3. Klik **"Update"**

#### Delete Medicine
1. Klik **"Delete"** pada obat yang dipilih
2. Konfirmasi penghapusan
3. Obat akan dihapus dari sistem

### 5. Booking Management
Akses melalui **Admin → Bookings**

#### View All Bookings
- Daftar semua booking
- Filter berdasarkan status atau tanggal
- Search berdasarkan user atau dokter

#### Booking Actions
**Confirm Booking:**
1. Pilih booking dengan status "Pending"
2. Klik **"Confirm"**
3. Status berubah menjadi "Confirmed"

**Complete Booking:**
1. Pilih booking dengan status "Confirmed"
2. Klik **"Complete"**
3. Status berubah menjadi "Completed"

**Cancel Booking:**
1. Pilih booking yang akan dibatalkan
2. Klik **"Cancel"**
3. Berikan alasan pembatalan
4. Status berubah menjadi "Cancelled"

### 6. System Logs
Akses melalui **Admin → Logs**

#### Activity Monitoring
- Login/logout activities
- Booking activities
- System changes

#### Log Information
- **User:** Siapa yang melakukan aksi
- **Action:** Jenis aksi yang dilakukan
- **Timestamp:** Kapan aksi dilakukan
- **IP Address:** Alamat IP pengguna
- **Details:** Detail tambahan

### 7. Settings
Akses melalui **Admin → Settings**

#### System Configuration
- Site name and description
- Contact information
- Email settings
- Backup settings

#### Security Settings
- Password policies
- Session timeout
- Login attempt limits

## Navigation Guide

### Main Navigation (All Users)
- **Home:** Halaman utama
- **Apotek:** Katalog obat (untuk pasien)
- **Login/Register:** Untuk guest users

### Patient Navigation
- **Dashboard:** User dashboard
- **Appointments:** Rencana konsultasi
- **Profile Dropdown:**
  - Profil Saya
  - Logout

### Admin Navigation
- **Dashboard Admin:** Admin dashboard
- **Admin Dropdown:**
  - Users
  - Doctors
  - Medicines
  - Bookings
  - Logs
  - Settings
- **Profile Dropdown:**
  - Logout

## Best Practices

### For Patients

#### Booking Tips
1. **Plan Ahead:** Pesan jadwal jauh-jauh hari
2. **Check Availability:** Pastikan dokter tersedia di tanggal yang diinginkan
3. **Read Description:** Baca detail dokter dan spesialisasi
4. **Contact Admin:** Hubungi admin jika ada pertanyaan

#### Profile Management
1. **Keep Information Updated:** Selalu update informasi kontak
2. **Strong Password:** Gunakan password yang kuat
3. **Regular Check:** Cek dashboard secara berkala untuk update

### For Administrators

#### User Management
1. **Regular Monitoring:** Monitor aktivitas pengguna secara berkala
2. **Quick Response:** Respon cepat terhadap booking request
3. **Data Backup:** Lakukan backup data secara rutin

#### Doctor Management
1. **Accurate Information:** Pastikan informasi dokter akurat
2. **Status Updates:** Update status dokter secara real-time
3. **Schedule Management:** Jadwalkan dokter dengan baik

#### Security
1. **Regular Password Change:** Ganti password admin secara berkala
2. **Monitor Logs:** Periksa log sistem untuk aktivitas mencurigakan
3. **User Verification:** Verifikasi identitas pengguna baru

## Troubleshooting

### Common Issues

#### Login Problems
**Problem:** Cannot login  
**Solution:**
1. Check username and password  
2. Ensure account is active  
3. Clear browser cache  
4. Contact administrator

#### Booking Issues
**Problem:** Cannot complete booking  
**Solution:**
1. Check doctor availability  
2. Ensure dates are valid  
3. Check if you're logged in  
4. Try refreshing the page

#### Profile Update Failed
**Problem:** Cannot update profile  
**Solution:**
1. Check required fields  
2. Ensure email format is correct  
3. Check password requirements  
4. Try again later

### Error Messages

#### "Database Connection Failed"
- Check internet connection
- Contact system administrator
- Try refreshing the page

#### "Access Denied"
- Ensure you're logged in
- Check if you have proper permissions
- Contact administrator if needed

#### "Session Expired"
- Login again
- Check if cookies are enabled
- Clear browser cache

## Support and Contact

### Getting Help
1. **Check Documentation:** Review this user guide
2. **Contact Administrator:** Use contact form or email
3. **Report Issues:** Report bugs or problems
4. **Feature Requests:** Suggest new features

### System Maintenance
- **Scheduled Maintenance:** Usually announced in advance
- **Emergency Maintenance:** May occur without notice
- **Backup Schedule:** Daily automatic backups

### Updates and Changes
- **Feature Updates:** New features added regularly
- **Security Updates:** Applied automatically
- **User Notifications:** Important changes will be announced

---

**Note:** This user guide is regularly updated. Please check for the latest version periodically.
