-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Jul 2025 pada 08.03
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `klinik_alma`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `pasien_id` int(11) DEFAULT NULL,
  `dokter_id` int(11) DEFAULT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jam_kunjungan` time NOT NULL,
  `keluhan` text DEFAULT NULL,
  `status` enum('pending','selesai','dibatalkan') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking`
--

INSERT INTO `booking` (`id`, `pasien_id`, `dokter_id`, `tanggal_kunjungan`, `jam_kunjungan`, `keluhan`, `status`) VALUES
(1, 1, 1, '2025-07-30', '08:30:00', 'Stroke\r\n', ''),
(2, 1, 1, '2025-08-01', '10:30:00', 'stroke\r\n', ''),
(3, 1, 1, '2025-08-08', '08:00:00', 'Stroke', 'dibatalkan'),
(4, 1, 2, '2025-07-29', '14:30:00', 'anjay', ''),
(5, 1, 2, '2025-07-26', '12:00:00', 'v', ''),
(6, 1, 1, '2025-07-30', '09:00:00', 'k\r\n', 'dibatalkan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `obat_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `obat_id`, `qty`, `subtotal`) VALUES
(1, 1, 2, 1, 8000.00),
(2, 2, 2, 1, 8000.00),
(3, 2, 4, 1, 3000.00),
(4, 2, 5, 1, 15000.00),
(5, 3, 4, 1, 3000.00),
(6, 3, 2, 1, 8000.00),
(7, 3, 5, 1, 15000.00),
(8, 3, 1, 1, 5000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter`
--

CREATE TABLE `dokter` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `spesialis` varchar(100) NOT NULL,
  `no_str` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokter`
--

INSERT INTO `dokter` (`id`, `user_id`, `spesialis`, `no_str`, `foto`) VALUES
(1, 2, 'Dokter Umum', 'STR001', NULL),
(2, 3, 'Dokter Anak', 'STR002', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_dokter`
--

CREATE TABLE `jadwal_dokter` (
  `id` int(11) NOT NULL,
  `dokter_id` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal_dokter`
--

INSERT INTO `jadwal_dokter` (`id`, `dokter_id`, `hari`, `jam_mulai`, `jam_selesai`, `created_at`) VALUES
(1, 1, 'Senin', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(2, 1, 'Senin', '14:00:00', '17:00:00', '2025-07-22 16:33:59'),
(3, 1, 'Selasa', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(4, 1, 'Rabu', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(5, 1, 'Rabu', '14:00:00', '17:00:00', '2025-07-22 16:33:59'),
(6, 1, 'Kamis', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(7, 1, 'Jumat', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(8, 1, 'Sabtu', '08:00:00', '11:00:00', '2025-07-22 16:33:59'),
(9, 2, 'Senin', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(10, 2, 'Senin', '14:00:00', '17:00:00', '2025-07-22 16:33:59'),
(11, 2, 'Selasa', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(12, 2, 'Rabu', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(13, 2, 'Rabu', '14:00:00', '17:00:00', '2025-07-22 16:33:59'),
(14, 2, 'Kamis', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(15, 2, 'Jumat', '08:00:00', '12:00:00', '2025-07-22 16:33:59'),
(16, 2, 'Sabtu', '08:00:00', '11:00:00', '2025-07-22 16:33:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_praktik`
--

CREATE TABLE `jadwal_praktik` (
  `id` int(11) NOT NULL,
  `dokter_id` int(11) DEFAULT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal_praktik`
--

INSERT INTO `jadwal_praktik` (`id`, `dokter_id`, `hari`, `jam_mulai`, `jam_selesai`) VALUES
(1, 1, 'Senin', '08:00:00', '12:00:00'),
(2, 1, 'Rabu', '08:00:00', '12:00:00'),
(3, 1, 'Jumat', '08:00:00', '12:00:00'),
(4, 2, 'Selasa', '14:00:00', '18:00:00'),
(5, 2, 'Kamis', '14:00:00', '18:00:00'),
(6, 2, 'Sabtu', '09:00:00', '13:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_kunjungan`
--

CREATE TABLE `laporan_kunjungan` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `diagnosa` text DEFAULT NULL,
  `resep` text DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `obat`
--

CREATE TABLE `obat` (
  `id` int(11) NOT NULL,
  `nama_obat` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `jenis` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `obat`
--

INSERT INTO `obat` (`id`, `nama_obat`, `deskripsi`, `harga`, `stok`, `jenis`) VALUES
(1, 'Paracetamol 500mg', 'Obat penurun panas dan pereda nyeri', 5000.00, 99, 'Tablet'),
(2, 'Amoxicillin 500mg', 'Antibiotik untuk infeksi bakteri', 8000.00, 47, 'Kapsul'),
(3, 'OBH Combi', 'Obat batuk dan flu', 12000.00, 75, 'Sirup'),
(4, 'Antangin JRG', 'Obat masuk angin', 3000.00, 198, 'Sachet'),
(5, 'Betadine Solution', 'Antiseptik untuk luka', 15000.00, 28, 'Cairan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pasien`
--

CREATE TABLE `pasien` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pasien`
--

INSERT INTO `pasien` (`id`, `user_id`, `alamat`, `no_hp`, `tanggal_lahir`, `jenis_kelamin`) VALUES
(1, 4, 'jl. kol', '0812', '2025-07-10', 'P');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_pengaturan` varchar(100) NOT NULL,
  `nilai` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_pengaturan`, `nilai`, `deskripsi`) VALUES
(1, 'nama_klinik', 'Klinik Alma Sehat', 'Nama klinik'),
(2, 'alamat_klinik', 'Jl. Kesehatan No. 123, Jakarta', 'Alamat klinik'),
(3, 'telepon_klinik', '021-12345678', 'Nomor telepon klinik'),
(4, 'email_klinik', 'info@klinikalmasehat.com', 'Email klinik'),
(5, 'jam_buka', '08:00 - 20:00', 'Jam operasional klinik'),
(6, 'visi', 'Menjadi klinik terdepan dalam pelayanan kesehatan yang berkualitas dan terjangkau.', 'Visi klinik'),
(7, 'misi', 'Memberikan pelayanan kesehatan terbaik dengan teknologi modern dan tenaga medis profesional.', 'Misi klinik');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `pasien_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('diproses','selesai','dibatalkan') DEFAULT 'diproses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `pasien_id`, `tanggal`, `total_harga`, `status`) VALUES
(1, 1, '2025-07-17', 8000.00, 'diproses'),
(2, 1, '2025-07-23', 26000.00, 'diproses'),
(3, 1, '2025-07-23', 31000.00, 'diproses');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','dokter','pasien') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@klinik.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-07-17 02:15:23'),
(2, 'Dr. Ahmad Santoso', 'ahmad@klinik.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dokter', '2025-07-17 02:15:23'),
(3, 'Dr. Sari Dewi', 'sari@klinik.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dokter', '2025-07-17 02:15:23'),
(4, 'Muhammad Ammar Al Farabi', 'alfarabibravo8@gmail.com', '$2y$10$9zlPeHBEeqL/J1M94e1wpuoItbco3q9piqibTpJ8x9eGsNQTxMuFS', 'pasien', '2025-07-17 03:45:14');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasien_id` (`pasien_id`),
  ADD KEY `dokter_id` (`dokter_id`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `obat_id` (`obat_id`);

--
-- Indeks untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dokter_id` (`dokter_id`);

--
-- Indeks untuk tabel `jadwal_praktik`
--
ALTER TABLE `jadwal_praktik`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dokter_id` (`dokter_id`);

--
-- Indeks untuk tabel `laporan_kunjungan`
--
ALTER TABLE `laporan_kunjungan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasien_id` (`pasien_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `jadwal_praktik`
--
ALTER TABLE `jadwal_praktik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `laporan_kunjungan`
--
ALTER TABLE `laporan_kunjungan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `obat`
--
ALTER TABLE `obat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`pasien_id`) REFERENCES `pasien` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`dokter_id`) REFERENCES `dokter` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`obat_id`) REFERENCES `obat` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD CONSTRAINT `dokter_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD CONSTRAINT `jadwal_dokter_ibfk_1` FOREIGN KEY (`dokter_id`) REFERENCES `dokter` (`id`);

--
-- Ketidakleluasaan untuk tabel `jadwal_praktik`
--
ALTER TABLE `jadwal_praktik`
  ADD CONSTRAINT `jadwal_praktik_ibfk_1` FOREIGN KEY (`dokter_id`) REFERENCES `dokter` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan_kunjungan`
--
ALTER TABLE `laporan_kunjungan`
  ADD CONSTRAINT `laporan_kunjungan_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pasien`
--
ALTER TABLE `pasien`
  ADD CONSTRAINT `pasien_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`pasien_id`) REFERENCES `pasien` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
