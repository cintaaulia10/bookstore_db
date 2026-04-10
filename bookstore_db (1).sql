-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2026 at 03:47 PM
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
-- Database: `bookstore_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `nama`, `email`, `password`) VALUES
(1, 'admin', 'admin@gmail.com', '0192023a7bbd73250516f069df18b500');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `penulis` varchar(150) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) DEFAULT 10,
  `id_kategori` int(11) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `judul`, `penulis`, `harga`, `deskripsi`, `stok`, `id_kategori`, `gambar`) VALUES
(1, 'Laskar Pelangi', 'Andrea Hirata', 85000, 'Laskar Pelangi adalah novel bestseller karya Andrea Hirata yang mengisahkan perjuangan 10 anak dari Belitung dalam meraih pendidikan melawan keterbatasan.', 7, 1, 'laskar_pelangi.jpg'),
(2, 'Bumi', 'Tere Liye', 75000, 'Bumi adalah novel fiksi ilmiah karya Tere Liye tentang petualangan Raib yang bisa menghilang dan teman-temannya menjelajahi dunia paralel penuh misteri.', 5, 1, 'bumi.jpg'),
(3, 'Dilan 1990', 'Pidi Baiq', 80000, 'Dilan 1990 adalah novel roman karya Pidi Baiq yang bercerita tentang kisah cinta khas remaja SMA antara Dilan dan Milea di Bandung tahun 1990.', 4, 1, 'dilan.jpg'),
(4, 'Perahu Kertas', 'Dee Lestari', 78000, 'Perahu Kertas adalah novel karya Dee Lestari tentang perjalanan hidup Kugy dan Keenan yang berjuang meraih mimpi dan cinta di tengah perbedaan.', 6, 1, 'perahu_kertas.jpg'),
(5, 'Atomic Habits', 'James Clear', 90000, 'Atomic Habits adalah buku panduan praktis karya James Clear tentang membangun kebiasaan baik dan menghilangkan kebiasaan buruk dengan perubahan kecil konsisten.', 8, 2, 'atomic_habits.jpg'),
(6, 'Filosofi Teras', 'Henry Manampiring', 85000, 'Filosofi Teras adalah buku pengantar stoikisme karya Henry Manampiring yang mengajarkan cara mengelola emosi dan menjalani hidup lebih tenang di era modern.', 8, 2, 'filosofi_teras.jpg'),
(7, 'Sebuah Seni Untuk Bersikap Bodo Amat', 'Mark Manson', 88000, 'Sebuah Seni Untuk Bersikap Bodo Amat mengajak kita untuk tidak memedulikan hal yang tidak penting dan fokus pada apa yang benar-benar berarti dalam hidup.', 9, 2, 'sebuah_seni.jpg'),
(8, 'Nanti Kita Cerita Tentang Hari Ini', 'Marchella FP', 70000, 'Nanti Kita Cerita Tentang Hari Ini adalah novel tentang perjalanan hidup sekelompok anak muda yang tinggal bersama di sebuah rumah mencari jati diri.', 10, 2, 'nanti_kita.jpg'),
(9, 'Timun Mas', 'Cerita Rakyat', 40000, 'Timun Mas adalah cerita rakyat Jawa Tengah tentang gadis yang lahir dari buah timun ajaib dan harus melawan raksasa jahat dengan bantuan benda sakti.', 10, 3, 'timun_mas.jpg'),
(10, 'Malin Kundang', 'Cerita Rakyat', 42000, 'Malin Kundang adalah cerita rakyat Sumatera Barat tentang anak durhaka yang dikutuk menjadi batu karena tidak mengakui ibunya sendiri.', 10, 3, 'malin_kundang.jpg'),
(11, 'Bawang Merah Bawang Putih', 'Cerita Rakyat', 45000, 'Bawang Merah Bawang Putih adalah cerita rakyat tentang dua saudari dengan sifat berbeda yang mengajarkan bahwa kebaikan hati akan membawa kebahagiaan.', 10, 3, 'bawang_merah.jpg'),
(12, 'Dongeng Si Kancil', 'Cerita Rakyat', 35000, 'Dongeng Si Kancil adalah cerita rakyat tentang kecerdikan Si Kancil yang berhasil mengelabui buaya untuk menyeberangi sungai dengan cerdik.', 10, 3, 'dongeng_kancil.jpg'),
(13, 'Buku Pintar Matematika', 'Tim Penyusun', 55000, 'Buku Pintar Matematika adalah buku panduan lengkap matematika untuk pelajar SD hingga SMA dengan rumus penting dan contoh soal mudah dipahami.', 10, 4, 'pintar_mtk.jpg'),
(14, 'Buku Pintar Bahasa Inggris', 'Tim Penyusun', 55000, 'Buku Pintar Bahasa Inggris adalah buku panduan belajar bahasa Inggris praktis untuk pemula hingga mahir dengan materi grammar dan percakapan.', 10, 4, 'pintar_inggris.jpg'),
(15, 'Bahasa Indonesia', 'Tim Penyusun', 50000, 'Bahasa Indonesia adalah buku pelajaran bahasa Indonesia yang lengkap dengan materi tata bahasa, ejaan, dan sastra Indonesia untuk pelajar.', 10, 4, 'b_indo.jpg'),
(16, 'Buku Agama Islam', 'Tim Penyusun', 60000, 'Buku Agama Islam adalah buku pelajaran agama Islam yang membahas dasar-dasar keislaman, ibadah, akhlak, dan sejarah Islam untuk semua kalangan.', 10, 4, 'buku_agama.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `id_pesanan` int(11) DEFAULT NULL,
  `id_buku` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga_satuan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `id_pesanan`, `id_buku`, `jumlah`, `harga_satuan`) VALUES
(1, 2, 3, 1, 80000),
(2, 2, 1, 1, 85000),
(3, 4, 2, 1, 75000),
(4, 4, 5, 1, 90000),
(5, 5, 6, 1, 85000),
(6, 5, 3, 1, 80000),
(7, 6, 2, 1, 75000),
(8, 7, 2, 1, 75000),
(9, 7, 7, 1, 88000),
(10, 7, 4, 1, 78000),
(11, 8, 3, 1, 80000),
(12, 8, 6, 1, 85000),
(13, 9, 3, 1, 80000),
(14, 10, 5, 1, 90000),
(15, 11, 5, 1, 90000);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `created_at`) VALUES
(1, 'horor', '2026-04-09 16:40:23'),
(2, 'Non-Fiksi', '2026-04-09 16:40:23'),
(3, 'Anak-Anak', '2026-04-09 16:40:23'),
(4, 'Pendidikan', '2026-04-09 16:40:23'),
(5, 'Horor', '2026-04-10 08:49:03'),
(6, 'www', '2026-04-10 09:15:47'),
(7, 'ddd', '2026-04-10 09:18:32'),
(8, 'motivasi', '2026-04-10 09:25:00'),
(9, 'motivasi', '2026-04-10 12:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_buku` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `total_harga` int(11) DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `alamat_pengiriman` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `gambar_bukti` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `id_user`, `total_harga`, `metode_pembayaran`, `status`, `alamat_pengiriman`, `no_hp`, `gambar_bukti`, `tanggal`) VALUES
(2, 2, 165000, 'bayar_ditempat', 'pending', NULL, NULL, NULL, '2026-04-09 21:39:46'),
(3, 2, 165000, 'ewallet', 'pending', 'Jl. Pondok Bambu', '086745324567', '1775806092_69d8a68c12348.jpg', '2026-04-10 02:28:12'),
(4, 2, 165000, 'ewallet', 'pending', 'Jl. Pondok Bambu', '086745324567', '1775806248_69d8a72806ee5.jpg', '2026-04-10 02:30:48'),
(5, 4, 165000, 'ewallet', 'pending', 'Jl. Cipinang Muara', '086745324567', '1775813719_69d8c45717bf8.jpg', '2026-04-10 04:35:19'),
(6, 4, 75000, 'ewallet', 'pending', 'Jl. Cipinang Muara', '086745324567', '1775819501_69d8daed09fd7.jpg', '2026-04-10 06:11:41'),
(7, 4, 241000, 'ewallet', 'pending', 'Jl.Pondok Bambu', '086745324567', '1775822647_69d8e737bc20f.jpg', '2026-04-10 07:04:07'),
(8, 4, 165000, 'transfer', 'pending', 'Bekasi', '086745324567', '', '2026-04-10 07:44:14'),
(9, 4, 80000, 'ewallet', 'pending', 'bekasi', '086745324567', '1775825114_69d8f0dab9014.jpg', '2026-04-10 07:45:14'),
(10, 4, 90000, 'ewallet', 'pending', 'bekasi', '086745324567', '1775825191_69d8f12774661.jpg', '2026-04-10 07:46:31'),
(11, 4, 90000, 'ewallet', 'pending', 'ciracas', '086745324567', '1775825291_69d8f18b8ee2b.jpg', '2026-04-10 07:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `pesan_kontak`
--

CREATE TABLE `pesan_kontak` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `subjek` varchar(200) NOT NULL,
  `pesan` text NOT NULL,
  `status` enum('belum_dibaca','sudah_dibaca') DEFAULT 'belum_dibaca',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `alamat`, `no_hp`, `created_at`) VALUES
(1, 'User', 'user@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Bandung', '08123456789', '2026-04-09 14:33:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@bookstore.com', '0192023a7bbd73250516f069df18b500', 'admin', '2026-04-09 20:55:01', '2026-04-09 20:55:01'),
(2, 'Fani', 'fani@gmail.com', 'ee61d621f12489791ce28b31409daee4', 'user', '2026-04-09 22:45:04', '2026-04-09 22:45:04'),
(3, 'Rina', 'Rina@gmail.com', '3aea9516d222934e35dd30f142fda18c', 'user', '2026-04-10 08:26:25', '2026-04-10 08:26:25'),
(4, 'Angel', 'angel@gmail.com', 'f4f068e71e0d87bf0ad51e6214ab84e9', 'user', '2026-04-10 08:38:06', '2026-04-10 08:38:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_buku` (`id_buku`),
  ADD KEY `keranjang_ibfk_1` (`id_user`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_ibfk_1` (`id_user`);

--
-- Indexes for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `buku_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`);

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD CONSTRAINT `pesan_kontak_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
