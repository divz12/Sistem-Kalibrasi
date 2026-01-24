-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql210.infinityfree.com
-- Generation Time: Jan 22, 2026 at 08:47 PM
-- Server version: 11.4.9-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40963722_dbsistem_kalibrasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_invoice`
--

CREATE TABLE `tbl_invoice` (
  `id_invoice` int(11) NOT NULL,
  `id_penawaran` int(11) NOT NULL,
  `nomor_invoice` varchar(100) NOT NULL,
  `tanggal_invoice` date NOT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `total_tagihan` decimal(18,2) NOT NULL,
  `status_pembayaran` varchar(50) NOT NULL,
  `nama_file_invoice` varchar(255) NOT NULL,
  `lokasi_file_invoice` varchar(255) NOT NULL,
  `keterangan_invoice` text DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_invoice`
--

INSERT INTO `tbl_invoice` (`id_invoice`, `id_penawaran`, `nomor_invoice`, `tanggal_invoice`, `tanggal_jatuh_tempo`, `total_tagihan`, `status_pembayaran`, `nama_file_invoice`, `lokasi_file_invoice`, `keterangan_invoice`, `dibuat_pada`) VALUES
(5, 12, 'INV-20260121-0012', '2026-01-21', '2026-02-04', '500000.00', 'sudah dibayar', 'invoice_INV-20260121-0012.pdf', 'InvoiceFile/invoice_INV-20260121-0012.pdf', 'Invoice otomatis dari penawaran diterima', '2026-01-21 18:49:17'),
(6, 13, 'INV-20260122-0013', '2026-01-22', '2026-02-05', '200000.00', 'sudah dibayar', 'invoice_INV-20260122-0013.pdf', 'InvoiceFile/invoice_INV-20260122-0013.pdf', 'Invoice otomatis dari penawaran diterima', '2026-01-21 23:15:37'),
(7, 14, 'INV-20260122-0014', '2026-01-22', '2026-02-05', '900000.00', 'sudah dibayar', 'invoice_INV-20260122-0014.pdf', 'InvoiceFile/invoice_INV-20260122-0014.pdf', 'Invoice otomatis dari penawaran diterima', '2026-01-21 23:27:01'),
(8, 15, 'INV-20260122-0015', '2026-01-22', '2026-02-05', '9999999999.99', 'sudah dibayar', 'invoice_INV-20260122-0015.pdf', 'InvoiceFile/invoice_INV-20260122-0015.pdf', 'Invoice otomatis dari penawaran diterima', '2026-01-21 23:31:37'),
(9, 16, 'INV-20260122-0016', '2026-01-22', '2026-02-05', '12.00', 'sudah dibayar', 'invoice_INV-20260122-0016.pdf', 'InvoiceFile/invoice_INV-20260122-0016.pdf', 'Invoice otomatis dari penawaran diterima', '2026-01-21 23:42:01'),
(10, 18, 'INV-20260122-0018', '2026-01-22', '2026-02-05', '200000.00', 'sudah dibayar', 'invoice_INV-20260122-0018.pdf', 'InvoiceFile/invoice_INV-20260122-0018.pdf', 'Invoice otomatis dari penawaran diterima', '2026-01-22 01:05:25');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pelanggan`
--

CREATE TABLE `tbl_pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tbl_pelanggan`
--

INSERT INTO `tbl_pelanggan` (`id_pelanggan`, `id_user`, `alamat`, `no_hp`, `created_at`, `updated_at`) VALUES
(5, 14, 'Jl. Raya Bogor, Kota Bogor', '0812345678', '2026-01-21 18:38:50', '2026-01-21 22:06:26'),
(6, 15, 'CIkembang', '99847938475', '2026-01-22 00:58:08', '2026-01-22 00:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penawaran`
--

CREATE TABLE `tbl_penawaran` (
  `id_penawaran` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `tanggal_penawaran` date NOT NULL DEFAULT current_timestamp(),
  `total_biaya` decimal(12,2) DEFAULT NULL,
  `rincian` text DEFAULT NULL,
  `status_penawaran` enum('dikirim','diterima','ditolak','negosiasi') NOT NULL DEFAULT 'dikirim',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tbl_penawaran`
--

INSERT INTO `tbl_penawaran` (`id_penawaran`, `id_pengajuan`, `id_admin`, `tanggal_penawaran`, `total_biaya`, `rincian`, `status_penawaran`, `created_at`) VALUES
(12, 21, 13, '2026-01-22', '500000.00', '-', 'diterima', '2026-01-21 18:45:48'),
(13, 22, 13, '2026-01-30', '200000.00', 'SA', 'diterima', '2026-01-21 23:14:01'),
(14, 23, 13, '2026-01-22', '900000.00', 'a', 'diterima', '2026-01-21 23:26:51'),
(15, 24, 13, '2026-01-30', '9999999999.99', 'as', 'diterima', '2026-01-21 23:31:29'),
(16, 25, 13, '2026-01-24', '12.00', '0', 'diterima', '2026-01-21 23:41:54'),
(17, 26, 13, '2026-01-22', '1000000.00', 'ada', 'negosiasi', '2026-01-22 01:00:55'),
(18, 27, 13, '2026-01-22', '200000.00', '-', 'diterima', '2026-01-22 01:02:41');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengajuan_alat`
--

CREATE TABLE `tbl_pengajuan_alat` (
  `id_alat` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `nama_alat` varchar(150) NOT NULL,
  `merk_tipe` varchar(150) DEFAULT NULL,
  `kapasitas` varchar(50) DEFAULT NULL,
  `jumlah_unit` int(11) NOT NULL DEFAULT 1,
  `parameter` varchar(255) DEFAULT NULL,
  `titik_ukur` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tbl_pengajuan_alat`
--

INSERT INTO `tbl_pengajuan_alat` (`id_alat`, `id_pengajuan`, `nama_alat`, `merk_tipe`, `kapasitas`, `jumlah_unit`, `parameter`, `titik_ukur`, `keterangan`) VALUES
(13, 21, 'timbangan digital', 'AXLO', '100kg', 1, 'Berat', 1, ''),
(14, 22, 'asdasdasda', 'asdasda', '12', 1, '12', 12, ''),
(15, 23, 'qwsaa', 'asdad', '2', 11, 'Berat', 123, ''),
(16, 24, 'timbang', 'asdf', '12', 1, 'ab', 12, ''),
(17, 25, 'timbang', 'axio', '10kg', 1, 'berat', 1, ''),
(18, 26, 'timbangan digital', 'AXLO', '12kg', 1, 'Berat', 1, ''),
(19, 27, 'Preaserure Test', 'PA05', '', 1, 'Tekanan', 1, 'Perlu di akurasi');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengajuan_kalibrasi`
--

CREATE TABLE `tbl_pengajuan_kalibrasi` (
  `id_pengajuan` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `tanggal_pengajuan` datetime NOT NULL DEFAULT current_timestamp(),
  `status_pengajuan` enum('dikirim','diproses','selesai','ditolak') NOT NULL DEFAULT 'dikirim',
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tbl_pengajuan_kalibrasi`
--

INSERT INTO `tbl_pengajuan_kalibrasi` (`id_pengajuan`, `id_pelanggan`, `tanggal_pengajuan`, `status_pengajuan`, `catatan`, `created_at`, `updated_at`) VALUES
(21, 5, '2025-01-21 18:43:57', 'selesai', 'ini penawaran pertama', '2026-01-21 18:43:57', '2026-01-22 01:19:51'),
(22, 5, '2026-01-21 23:10:38', 'selesai', '', '2026-01-21 23:10:38', '2026-01-21 23:17:13'),
(23, 5, '2026-01-21 23:26:27', 'selesai', '', '2026-01-21 23:26:27', '2026-01-21 23:27:21'),
(24, 5, '2026-01-21 23:30:30', 'selesai', '', '2026-01-21 23:30:30', '2026-01-21 23:32:13'),
(25, 5, '2026-01-21 23:36:01', 'selesai', 'percobaan', '2026-01-21 23:36:01', '2026-01-21 23:42:32'),
(26, 5, '2026-01-22 00:57:49', 'diproses', '', '2026-01-22 00:57:49', '2026-01-22 00:59:12'),
(27, 6, '2026-01-22 01:02:08', 'selesai', 'Kalibrasi Presure Tools', '2026-01-22 01:02:08', '2026-01-22 01:07:48');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pesan_cs`
--

CREATE TABLE `tbl_pesan_cs` (
  `id_pesan` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `kontak` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `waktu_kirim` datetime NOT NULL,
  `balasan_otomatis` text NOT NULL,
  `status_baca_admin` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tbl_pesan_cs`
--

INSERT INTO `tbl_pesan_cs` (`id_pesan`, `id_pelanggan`, `kontak`, `pesan`, `waktu_kirim`, `balasan_otomatis`, `status_baca_admin`) VALUES
(5, NULL, 'lala@gmail.com', 'Nama: lala\nEmail: lala@gmail.com\nSubjek: percobaan\n\nhhh', '2026-01-20 01:32:05', 'Terima kasih, pesan kamu sudah kami terima. Admin/CS akan segera membalas.', 0),
(6, NULL, 'lala@gmail.com', 'Nama: lala\nEmail: lala@gmail.com\nSubjek: percobaan\n\nhai', '2026-01-20 01:47:48', 'Terima kasih, pesan kamu sudah kami terima. Admin/CS akan segera membalas.', 0),
(7, NULL, 'lala@gmail.com', 'Nama: lali\nEmail: lala@gmail.com\nSubjek: percobaan\n\napa', '2026-01-20 01:52:47', 'Terima kasih, pesan kamu sudah kami terima. Admin/CS akan segera membalas.', 0),
(8, NULL, 'lala@gmail.com', 'Nama: lala\nEmail: lala@gmail.com\nSubjek: percobaan\n\nhalo', '2026-01-20 01:54:15', 'Terima kasih, pesan kamu sudah kami terima. Admin/CS akan segera membalas.', 0),
(9, NULL, 'lala@gmail.com', 'Nama: lala\nEmail: lala@gmail.com\nSubjek: percobaan\n\nhaloa', '2026-01-20 02:07:27', 'Terima kasih, pesan kamu sudah kami terima. Admin/CS akan segera membalas.', 0),
(10, NULL, 'lala@gmail.com', 'Nama: lala\nEmail: lala@gmail.com\nSubjek: percobaan\n\nhola', '2026-01-20 02:10:37', 'Terima kasih, pesan kamu sudah kami terima. Admin/CS akan segera membalas.', 0),
(11, NULL, 'lala@gmail.com', 'Nama: lala\nEmail: lala@gmail.com\nSubjek: percobaan\n\nlagi', '2026-01-20 02:14:48', 'Terima kasih, pesan kamu sudah kami terima. Admin/CS akan segera membalas.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sertifikat`
--

CREATE TABLE `tbl_sertifikat` (
  `id_sertifikat` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `nomor_sertifikat` varchar(100) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `nama_file_sertifikat` varchar(255) NOT NULL,
  `lokasi_file_sertifikat` varchar(255) NOT NULL,
  `keterangan_sertifikat` text DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_sertifikat`
--

INSERT INTO `tbl_sertifikat` (`id_sertifikat`, `id_pengajuan`, `nomor_sertifikat`, `tanggal_terbit`, `nama_file_sertifikat`, `lokasi_file_sertifikat`, `keterangan_sertifikat`, `dibuat_pada`) VALUES
(3, 27, 'CERT1234', '2026-01-22', 'user.jpg', 'file-sertifikat/user.jpg', '-', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pelanggan','admin','owner') NOT NULL DEFAULT 'pelanggan',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id_user`, `nama`, `email`, `password`, `role`, `foto`, `created_at`, `updated_at`) VALUES
(7, 'risaa', 'risa@gmail.com', '$2y$10$BupA.uWLk2KJ1DOHV7NLq.AYh5o.iAULv5jo2NAmSadOSH9CVPG72', 'admin', '', '2026-01-13 23:12:59', '2026-01-14 03:03:25'),
(10, 'owner', 'owner@gmail.com', '$2y$10$BupA.uWLk2KJ1DOHV7NLq.AYh5o.iAULv5jo2NAmSadOSH9CVPG72', 'owner', '', '2026-01-13 23:19:14', '2026-01-21 18:11:19'),
(13, 'admin', 'admin@gmail.com', '$2y$10$BupA.uWLk2KJ1DOHV7NLq.AYh5o.iAULv5jo2NAmSadOSH9CVPG72', 'admin', '', '2026-01-21 18:05:18', '2026-01-21 18:06:52'),
(14, 'pelanggan', 'pelanggan@gmail.com', '$2y$10$22Yavqtlru5nYJCp7iwsl.3syYZWft26Xl6Vuj6quiRx92qlGvDzO', 'pelanggan', 'user.jpg', '2026-01-21 18:37:29', '2026-01-21 22:06:26'),
(15, 'salman', 'salman@king.com', '$2y$10$vRkU8Pf6e1KGc/FuszqUVefSsIOZyDyMYCPDdKf61VNV0fSpkVoRu', 'pelanggan', 'share info pelatihan.jpeg', '2026-01-22 00:57:08', '2026-01-22 00:58:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_invoice`
--
ALTER TABLE `tbl_invoice`
  ADD PRIMARY KEY (`id_invoice`),
  ADD KEY `fk_invoice_penawaran` (`id_penawaran`);

--
-- Indexes for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`) USING BTREE,
  ADD UNIQUE KEY `id_user` (`id_user`) USING BTREE;

--
-- Indexes for table `tbl_penawaran`
--
ALTER TABLE `tbl_penawaran`
  ADD PRIMARY KEY (`id_penawaran`) USING BTREE,
  ADD UNIQUE KEY `id_pengajuan` (`id_pengajuan`) USING BTREE,
  ADD KEY `fk_penawaran_admin` (`id_admin`) USING BTREE;

--
-- Indexes for table `tbl_pengajuan_alat`
--
ALTER TABLE `tbl_pengajuan_alat`
  ADD PRIMARY KEY (`id_alat`) USING BTREE,
  ADD KEY `fk_alat_pengajuan` (`id_pengajuan`) USING BTREE;

--
-- Indexes for table `tbl_pengajuan_kalibrasi`
--
ALTER TABLE `tbl_pengajuan_kalibrasi`
  ADD PRIMARY KEY (`id_pengajuan`) USING BTREE,
  ADD KEY `fk_pengajuan_pelanggan` (`id_pelanggan`) USING BTREE,
  ADD KEY `idx_pengajuan_status` (`status_pengajuan`) USING BTREE,
  ADD KEY `idx_pengajuan_tanggal` (`tanggal_pengajuan`) USING BTREE;

--
-- Indexes for table `tbl_pesan_cs`
--
ALTER TABLE `tbl_pesan_cs`
  ADD PRIMARY KEY (`id_pesan`) USING BTREE,
  ADD KEY `fk_pesan_cs` (`id_pelanggan`) USING BTREE;

--
-- Indexes for table `tbl_sertifikat`
--
ALTER TABLE `tbl_sertifikat`
  ADD PRIMARY KEY (`id_sertifikat`),
  ADD KEY `fk_sertifikat_pengajuan` (`id_pengajuan`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id_user`) USING BTREE,
  ADD UNIQUE KEY `email` (`email`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_invoice`
--
ALTER TABLE `tbl_invoice`
  MODIFY `id_invoice` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_penawaran`
--
ALTER TABLE `tbl_penawaran`
  MODIFY `id_penawaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_pengajuan_alat`
--
ALTER TABLE `tbl_pengajuan_alat`
  MODIFY `id_alat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_pengajuan_kalibrasi`
--
ALTER TABLE `tbl_pengajuan_kalibrasi`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tbl_pesan_cs`
--
ALTER TABLE `tbl_pesan_cs`
  MODIFY `id_pesan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_sertifikat`
--
ALTER TABLE `tbl_sertifikat`
  MODIFY `id_sertifikat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_invoice`
--
ALTER TABLE `tbl_invoice`
  ADD CONSTRAINT `fk_invoice_penawaran` FOREIGN KEY (`id_penawaran`) REFERENCES `tbl_penawaran` (`id_penawaran`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  ADD CONSTRAINT `fk_pelanggan_user` FOREIGN KEY (`id_user`) REFERENCES `tbl_users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_penawaran`
--
ALTER TABLE `tbl_penawaran`
  ADD CONSTRAINT `fk_penawaran_admin` FOREIGN KEY (`id_admin`) REFERENCES `tbl_users` (`id_user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penawaran_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `tbl_pengajuan_kalibrasi` (`id_pengajuan`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_pengajuan_alat`
--
ALTER TABLE `tbl_pengajuan_alat`
  ADD CONSTRAINT `fk_alat_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `tbl_pengajuan_kalibrasi` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_pengajuan_kalibrasi`
--
ALTER TABLE `tbl_pengajuan_kalibrasi`
  ADD CONSTRAINT `fk_pengajuan_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_pesan_cs`
--
ALTER TABLE `tbl_pesan_cs`
  ADD CONSTRAINT `fk_pesan_cs` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_sertifikat`
--
ALTER TABLE `tbl_sertifikat`
  ADD CONSTRAINT `fk_sertifikat_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `tbl_pengajuan_kalibrasi` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
