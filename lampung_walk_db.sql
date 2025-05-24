-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 05:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lampung_walk_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$8KHFh9LK8VgIrJKB7V8QOOtKbZCgYb5Yd4GxRKQwC5QZzFqQSXXXi', 'Administrator', 'admin@lampungwalk.com', 'admin', NULL, '2025-05-04 17:47:45', '2025-05-04 17:47:45');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `wahana_id` int(11) NOT NULL,
  `jumlah_tiket` int(11) NOT NULL DEFAULT 1,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `wahana_id`, `jumlah_tiket`, `harga_satuan`, `subtotal`, `created_at`) VALUES
(29, 29, 3, 1, 45000.00, 45000.00, '2025-05-24 14:58:29'),
(30, 30, 1, 1, 50000.00, 50000.00, '2025-05-24 15:03:28'),
(31, 31, 2, 1, 35000.00, 35000.00, '2025-05-24 15:05:40'),
(32, 32, 1, 1, 50000.00, 50000.00, '2025-05-24 15:08:19'),
(33, 33, 1, 1, 50000.00, 50000.00, '2025-05-24 15:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `username`, `nama_lengkap`, `email`, `telepon`, `password`, `role`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 'rain', 'rain', 'rain@gmail.com', NULL, '$2y$10$D2im1zT/xlOeBYqYRQFh3ea61siZFt1Vw8vMtJb49yrHGostWD8.6', 'user', NULL, '2025-05-04 20:26:26', '2025-05-04 20:26:26'),
(2, 'rainadmin', 'rainadmin', 'rainadmin@gmail.com', NULL, '$2y$10$WjSJpc4jTsvfFZoesppX.efgC7MgwLVdx55y7wDDQb/nkIR3Tsx0u', 'user', NULL, '2025-05-04 20:37:21', '2025-05-24 08:29:40'),
(3, 'rainganteng', 'rainganteng', 'rain7@gmail.com', NULL, '$2y$10$i86lfBHCcuOY/OB/mgeCaO21JY44tCMXe83VksBGme.Udt.rz0u6O', 'user', NULL, '2025-05-24 08:30:29', '2025-05-24 08:30:29');

-- --------------------------------------------------------

--
-- Table structure for table `pesan_kontak`
--

CREATE TABLE `pesan_kontak` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `pesan` text NOT NULL,
  `status` enum('baru','dibaca','ditanggapi') DEFAULT 'baru',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiket`
--

CREATE TABLE `tiket` (
  `id` int(11) NOT NULL,
  `kode_tiket` varchar(50) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `wahana_id` int(11) NOT NULL,
  `status` enum('aktif','digunakan','kadaluarsa','dibatalkan','terpakai') DEFAULT 'aktif',
  `qr_code` text DEFAULT NULL,
  `tanggal_berlaku` date NOT NULL,
  `tanggal_kadaluarsa` date NOT NULL,
  `tanggal_digunakan` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tiket`
--

INSERT INTO `tiket` (`id`, `kode_tiket`, `transaksi_id`, `wahana_id`, `status`, `qr_code`, `tanggal_berlaku`, `tanggal_kadaluarsa`, `tanggal_digunakan`, `created_at`, `updated_at`) VALUES
(11, 'LW-829-7396-001', 29, 3, 'aktif', NULL, '2025-05-24', '2025-06-23', NULL, '2025-05-24 15:02:42', '2025-05-24 15:02:42'),
(12, 'LW-540-7768-001', 31, 2, 'aktif', NULL, '2025-05-24', '2025-06-23', NULL, '2025-05-24 15:07:44', '2025-05-24 15:07:44'),
(13, 'LW-819-6613-001', 32, 1, 'aktif', NULL, '2025-05-24', '2025-06-23', NULL, '2025-05-24 15:08:38', '2025-05-24 15:08:38'),
(14, 'LW-030-5111-001', 33, 1, 'aktif', NULL, '2025-05-24', '2025-06-23', NULL, '2025-05-24 15:10:52', '2025-05-24 15:10:52');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `tanggal_pembelian` datetime NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status_pembayaran` enum('pending','paid','failed','refunded','expired','processing') DEFAULT 'pending',
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `xendit_invoice_id` varchar(255) DEFAULT NULL,
  `xendit_invoice_url` text DEFAULT NULL,
  `xendit_external_id` varchar(255) DEFAULT NULL,
  `payment_status_detail` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `pelanggan_id`, `tanggal_pembelian`, `tanggal_kunjungan`, `total_harga`, `status_pembayaran`, `metode_pembayaran`, `xendit_invoice_id`, `xendit_invoice_url`, `xendit_external_id`, `payment_status_detail`, `created_at`, `updated_at`) VALUES
(29, 'TRX-20250524215829-7396', 3, '2025-05-24 21:58:29', '2025-05-24', 45000.00, 'paid', 'e_wallet', '6831de9a7e386de4cd724728', 'https://checkout-staging.xendit.co/web/6831de9a7e386de4cd724728', 'LAMPUNG_WALK_TRX-20250524215829-7396_1748098714', '{\"status_check\":true,\"xendit_status\":\"PAID\",\"paid_amount\":45000,\"paid_at\":\"2025-05-24T14:58:38.906Z\",\"payment_method\":\"EWALLET\"}', '2025-05-24 14:58:29', '2025-05-24 14:58:48'),
(30, 'TRX-20250524220328-9899', 3, '2025-05-24 22:03:28', '2025-05-24', 50000.00, 'paid', 'e_wallet', '6831dff37e386de4cd7248f5', 'https://checkout-staging.xendit.co/web/6831dff37e386de4cd7248f5', 'LAMPUNG_WALK_TRX-20250524220328-9899_1748099058', '{\"status_check\":true,\"xendit_status\":\"PAID\",\"paid_amount\":50000,\"paid_at\":\"2025-05-24T15:04:22.601Z\",\"payment_method\":\"EWALLET\"}', '2025-05-24 15:03:28', '2025-05-24 15:04:32'),
(31, 'TRX-20250524220540-7768', 3, '2025-05-24 22:05:40', '2025-05-24', 35000.00, 'paid', 'e_wallet', '6831e04b7e386de4cd724948', 'https://checkout-staging.xendit.co/web/6831e04b7e386de4cd724948', 'LAMPUNG_WALK_TRX-20250524220540-7768_1748099147', '{\"status_check\":true,\"xendit_status\":\"PAID\",\"paid_amount\":35000,\"paid_at\":\"2025-05-24T15:05:51.997Z\",\"payment_method\":\"EWALLET\"}', '2025-05-24 15:05:40', '2025-05-24 15:06:02'),
(32, 'TRX-20250524220819-6613', 3, '2025-05-24 22:08:19', '2025-05-24', 50000.00, 'paid', 'e_wallet', '6831e0e97e386de4cd7249d9', 'https://checkout-staging.xendit.co/web/6831e0e97e386de4cd7249d9', 'LAMPUNG_WALK_TRX-20250524220819-6613_1748099304', '{\"status_check\":true,\"xendit_status\":\"PAID\",\"paid_amount\":50000,\"paid_at\":\"2025-05-24T15:08:29.190Z\",\"payment_method\":\"EWALLET\"}', '2025-05-24 15:08:19', '2025-05-24 15:08:38'),
(33, 'TRX-20250524221030-5111', 3, '2025-05-24 22:10:30', '2025-05-24', 50000.00, 'paid', 'e_wallet', '6831e16ee76ff92c161223f8', 'https://checkout-staging.xendit.co/web/6831e16ee76ff92c161223f8', 'LAMPUNG_WALK_TRX-20250524221030-5111_1748099437', '{\"status_check\":true,\"xendit_status\":\"PAID\",\"paid_amount\":50000,\"paid_at\":\"2025-05-24T15:10:42.272Z\",\"payment_method\":\"EWALLET\"}', '2025-05-24 15:10:30', '2025-05-24 15:10:52');

-- --------------------------------------------------------

--
-- Table structure for table `wahana`
--

CREATE TABLE `wahana` (
  `id` int(11) NOT NULL,
  `kode_wahana` varchar(50) NOT NULL,
  `nama_wahana` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `jam_buka` time DEFAULT '09:00:00',
  `jam_tutup` time DEFAULT '18:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wahana`
--

INSERT INTO `wahana` (`id`, `kode_wahana`, `nama_wahana`, `deskripsi`, `harga`, `gambar`, `status`, `jam_buka`, `jam_tutup`, `created_at`, `updated_at`) VALUES
(1, 'waterpark', 'Waterpark', 'Rasakan keseruan di Water Slide dengan lintasan 100 meter dan ketinggian 15 meter, serta kolam ombak yang seru!', 50000.00, 'assets/img/waterpark.jpg', 'aktif', '09:00:00', '18:00:00', '2025-05-04 17:47:45', '2025-05-04 17:47:45'),
(2, 'studio', 'Studio 3D', 'Nikmati pengalaman visual yang mengagumkan dengan teknologi terkini!', 35000.00, 'assets/img/studio.jpg', 'aktif', '09:00:00', '18:00:00', '2025-05-04 17:47:45', '2025-05-04 17:47:45'),
(3, 'sport', 'Sport Center', 'Fasilitas olahraga modern untuk segala usia dan kebutuhan!', 45000.00, 'assets/img/sport.jpg', 'aktif', '09:00:00', '18:00:00', '2025-05-04 17:47:45', '2025-05-04 17:47:45');

-- --------------------------------------------------------

--
-- Table structure for table `xendit_invoices`
--

CREATE TABLE `xendit_invoices` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `invoice_id` varchar(255) NOT NULL,
  `invoice_url` text NOT NULL,
  `external_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `wahana_id` (`wahana_id`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tiket`
--
ALTER TABLE `tiket`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_tiket` (`kode_tiket`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `wahana_id` (`wahana_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `pelanggan_id` (`pelanggan_id`),
  ADD KEY `idx_xendit_invoice_id` (`xendit_invoice_id`),
  ADD KEY `idx_xendit_external_id` (`xendit_external_id`),
  ADD KEY `idx_status_pembayaran` (`status_pembayaran`);

--
-- Indexes for table `wahana`
--
ALTER TABLE `wahana`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_wahana` (`kode_wahana`);

--
-- Indexes for table `xendit_invoices`
--
ALTER TABLE `xendit_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiket`
--
ALTER TABLE `tiket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `wahana`
--
ALTER TABLE `wahana`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `xendit_invoices`
--
ALTER TABLE `xendit_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`wahana_id`) REFERENCES `wahana` (`id`);

--
-- Constraints for table `tiket`
--
ALTER TABLE `tiket`
  ADD CONSTRAINT `tiket_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tiket_ibfk_2` FOREIGN KEY (`wahana_id`) REFERENCES `wahana` (`id`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `xendit_invoices`
--
ALTER TABLE `xendit_invoices`
  ADD CONSTRAINT `xendit_invoices_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
