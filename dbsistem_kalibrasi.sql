/*
 Navicat Premium Dump SQL

 Source Server         : databaseDS
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : dbsistem_kalibrasi

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 19/01/2026 15:19:40
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tbl_invoice
-- ----------------------------
DROP TABLE IF EXISTS `tbl_invoice`;
CREATE TABLE `tbl_invoice`  (
  `id_invoice` int NOT NULL AUTO_INCREMENT,
  `id_penawaran` int NOT NULL,
  `nomor_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_invoice` date NOT NULL,
  `tanggal_jatuh_tempo` date NULL DEFAULT NULL,
  `total_tagihan` decimal(18, 2) NOT NULL,
  `status_pembayaran` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_file_invoice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lokasi_file_invoice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan_invoice` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id_invoice`) USING BTREE,
  INDEX `fk_invoice_penawaran`(`id_penawaran` ASC) USING BTREE,
  CONSTRAINT `fk_invoice_penawaran` FOREIGN KEY (`id_penawaran`) REFERENCES `tbl_penawaran` (`id_penawaran`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tbl_invoice
-- ----------------------------
INSERT INTO `tbl_invoice` VALUES (1, 5, 'INV-001/2026', '2026-01-18', '2026-01-23', 500000.00, 'belum dibayar', 'invoice.jpg', 'file-invoice/invoice.jpg', 'segera', '2026-01-18 14:47:18');

-- ----------------------------
-- Table structure for tbl_konten_website
-- ----------------------------
DROP TABLE IF EXISTS `tbl_konten_website`;
CREATE TABLE `tbl_konten_website`  (
  `id_konten` int NOT NULL AUTO_INCREMENT,
  `id_admin` int NOT NULL,
  `jenis_konten` enum('profil','layanan','kontak','berita') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `judul` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `isi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `created_at` datetime NULL DEFAULT current_timestamp,
  `updated_at` datetime NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_konten`) USING BTREE,
  INDEX `fk_konten_admin`(`id_admin` ASC) USING BTREE,
  CONSTRAINT `fk_konten_admin` FOREIGN KEY (`id_admin`) REFERENCES `tbl_users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_konten_website
-- ----------------------------

-- ----------------------------
-- Table structure for tbl_pelanggan
-- ----------------------------
DROP TABLE IF EXISTS `tbl_pelanggan`;
CREATE TABLE `tbl_pelanggan`  (
  `id_pelanggan` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `alamat` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT current_timestamp,
  `updated_at` datetime NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pelanggan`) USING BTREE,
  UNIQUE INDEX `id_user`(`id_user` ASC) USING BTREE,
  CONSTRAINT `fk_pelanggan_user` FOREIGN KEY (`id_user`) REFERENCES `tbl_users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_pelanggan
-- ----------------------------
INSERT INTO `tbl_pelanggan` VALUES (4, 6, 'Purwakarta', '08898764323', '2026-01-13 22:44:42', '2026-01-13 22:44:42');

-- ----------------------------
-- Table structure for tbl_penawaran
-- ----------------------------
DROP TABLE IF EXISTS `tbl_penawaran`;
CREATE TABLE `tbl_penawaran`  (
  `id_penawaran` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_admin` int NOT NULL,
  `tanggal_penawaran` datetime NOT NULL DEFAULT current_timestamp,
  `total_biaya` decimal(12, 2) NULL DEFAULT NULL,
  `rincian` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `status_penawaran` enum('dikirim','diterima','ditolak','negosiasi') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'dikirim',
  `created_at` datetime NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id_penawaran`) USING BTREE,
  UNIQUE INDEX `id_pengajuan`(`id_pengajuan` ASC) USING BTREE,
  INDEX `fk_penawaran_admin`(`id_admin` ASC) USING BTREE,
  CONSTRAINT `fk_penawaran_admin` FOREIGN KEY (`id_admin`) REFERENCES `tbl_users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_penawaran_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `tbl_pengajuan_kalibrasi` (`id_pengajuan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_penawaran
-- ----------------------------
INSERT INTO `tbl_penawaran` VALUES (4, 14, 7, '2026-01-14 00:00:00', 2000000.00, 'kali', 'dikirim', '2026-01-14 05:26:22');
INSERT INTO `tbl_penawaran` VALUES (5, 15, 7, '2026-01-14 00:00:00', 500000.00, 'aa', 'diterima', '2026-01-14 05:35:23');

-- ----------------------------
-- Table structure for tbl_pengajuan_alat
-- ----------------------------
DROP TABLE IF EXISTS `tbl_pengajuan_alat`;
CREATE TABLE `tbl_pengajuan_alat`  (
  `id_alat` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nama_alat` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `merk_tipe` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `kapasitas` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `jumlah_unit` int NOT NULL DEFAULT 1,
  `parameter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `titik_ukur` int NULL DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id_alat`) USING BTREE,
  INDEX `fk_alat_pengajuan`(`id_pengajuan` ASC) USING BTREE,
  CONSTRAINT `fk_alat_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `tbl_pengajuan_kalibrasi` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_pengajuan_alat
-- ----------------------------
INSERT INTO `tbl_pengajuan_alat` VALUES (6, 14, 'timbangan', 'CAS ER-14', '20kg', 1, 'Berat', 1, 'hati hati');
INSERT INTO `tbl_pengajuan_alat` VALUES (7, 15, 'timbangan', 'CAS ER-10', '20kg', 1, 'Berat', 1, 'hati hati');

-- ----------------------------
-- Table structure for tbl_pengajuan_kalibrasi
-- ----------------------------
DROP TABLE IF EXISTS `tbl_pengajuan_kalibrasi`;
CREATE TABLE `tbl_pengajuan_kalibrasi`  (
  `id_pengajuan` int NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int NOT NULL,
  `tanggal_pengajuan` datetime NOT NULL DEFAULT current_timestamp,
  `status_pengajuan` enum('dikirim','diproses','selesai','ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'dikirim',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `created_at` datetime NULL DEFAULT current_timestamp,
  `updated_at` datetime NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengajuan`) USING BTREE,
  INDEX `fk_pengajuan_pelanggan`(`id_pelanggan` ASC) USING BTREE,
  INDEX `idx_pengajuan_status`(`status_pengajuan` ASC) USING BTREE,
  INDEX `idx_pengajuan_tanggal`(`tanggal_pengajuan` ASC) USING BTREE,
  CONSTRAINT `fk_pengajuan_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_pengajuan_kalibrasi
-- ----------------------------
INSERT INTO `tbl_pengajuan_kalibrasi` VALUES (14, 4, '2026-01-14 05:25:41', 'dikirim', 'baruu', '2026-01-14 05:25:41', '2026-01-14 05:25:41');
INSERT INTO `tbl_pengajuan_kalibrasi` VALUES (15, 4, '2026-01-14 05:34:29', 'selesai', 'ada baru', '2026-01-14 05:34:29', '2026-01-14 12:59:16');

-- ----------------------------
-- Table structure for tbl_pesan_cs
-- ----------------------------
DROP TABLE IF EXISTS `tbl_pesan_cs`;
CREATE TABLE `tbl_pesan_cs`  (
  `id_pesan` int NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int NOT NULL,
  `kontak` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `waktu_kirim` datetime NOT NULL,
  `balasan_otomatis` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `status_baca_admin` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_pesan`) USING BTREE,
  INDEX `fk_pesan_cs`(`id_pelanggan` ASC) USING BTREE,
  CONSTRAINT `fk_pesan_cs` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_pesan_cs
-- ----------------------------

-- ----------------------------
-- Table structure for tbl_sertifikat
-- ----------------------------
DROP TABLE IF EXISTS `tbl_sertifikat`;
CREATE TABLE `tbl_sertifikat`  (
  `id_sertifikat` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nomor_sertifikat` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `nama_file_sertifikat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lokasi_file_sertifikat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan_sertifikat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `dibuat_pada` datetime NOT NULL,
  PRIMARY KEY (`id_sertifikat`) USING BTREE,
  INDEX `fk_sertifikat_pengajuan`(`id_pengajuan` ASC) USING BTREE,
  CONSTRAINT `fk_sertifikat_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `tbl_pengajuan_kalibrasi` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tbl_sertifikat
-- ----------------------------
INSERT INTO `tbl_sertifikat` VALUES (1, 15, 'CERT-001/2026', '2026-01-18', 'invoice.jpg', 'file-sertifikat/invoice.jpg', '-', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for tbl_users
-- ----------------------------
DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE `tbl_users`  (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('pelanggan','admin','owner') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pelanggan',
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT current_timestamp,
  `updated_at` datetime NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_users
-- ----------------------------
INSERT INTO `tbl_users` VALUES (6, 'lala', 'lala@gmail.com', '$2y$10$BupA.uWLk2KJ1DOHV7NLq.AYh5o.iAULv5jo2NAmSadOSH9CVPG72', 'pelanggan', '', '2026-01-13 22:44:42', '2026-01-14 03:04:13');
INSERT INTO `tbl_users` VALUES (7, 'risaa', 'risa@gmail.com', '$2y$10$BupA.uWLk2KJ1DOHV7NLq.AYh5o.iAULv5jo2NAmSadOSH9CVPG72', 'admin', '', '2026-01-13 23:12:59', '2026-01-14 03:03:25');
INSERT INTO `tbl_users` VALUES (10, 'alinda', 'alinda@gmail.com', '$2y$10$BupA.uWLk2KJ1DOHV7NLq.AYh5o.iAULv5jo2NAmSadOSH9CVPG72', 'owner', '', '2026-01-13 23:19:14', '2026-01-14 03:04:18');
INSERT INTO `tbl_users` VALUES (11, 'pelanggan', 'pelanggan@gmail.com', '$2y$10$BupA.uWLk2KJ1DOHV7NLq.AYh5o.iAULv5jo2NAmSadOSH9CVPG72', 'pelanggan', NULL, '2026-01-14 03:01:46', '2026-01-14 03:01:46');
INSERT INTO `tbl_users` VALUES (12, 'pelanggan2', 'pelanggan2@gmail.com', '$2y$10$CvteSTKqymqFarqKuk2B.eQAur.CiaFCdTjXnTtSnxkxoxfP6ifPu', 'pelanggan', NULL, '2026-01-14 03:10:55', '2026-01-14 03:10:55');

SET FOREIGN_KEY_CHECKS = 1;
