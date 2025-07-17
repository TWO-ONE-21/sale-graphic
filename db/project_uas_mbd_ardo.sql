-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 17, 2025 at 09:37 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_uas_mbd_ardo`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_catat_penjualan` (IN `in_user_id` INT, IN `in_id_produk` VARCHAR(15), IN `in_jumlah` INT)   BEGIN
    DECLARE v_harga DOUBLE;
    DECLARE v_total_harga DOUBLE;

    SELECT harga INTO v_harga FROM produk WHERE id = in_id_produk;

    SET v_total_harga = v_harga * in_jumlah;

    INSERT INTO penjualan (id_produk, user_id, jumlah, total_harga)
    VALUES (in_id_produk, in_user_id, in_jumlah, v_total_harga);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL,
  `id_produk` varchar(15) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `total_harga` double NOT NULL,
  `tgl_penjualan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id`, `id_produk`, `user_id`, `jumlah`, `total_harga`, `tgl_penjualan`) VALUES
(1, 'P001', 2, 1, 15000000, '2025-07-09 03:00:00'),
(2, 'P002', 2, 2, 1500000, '2025-07-09 03:05:00'),
(3, 'P003', 2, 1, 1200000, '2025-07-10 04:30:00'),
(4, 'P001', 2, 12, 180000000, '2025-07-17 19:12:00');

--
-- Triggers `penjualan`
--
DELIMITER $$
CREATE TRIGGER `trg_setelah_penjualan_perbarui_stok` AFTER INSERT ON `penjualan` FOR EACH ROW BEGIN
    -- Kurangi stok di tabel produk sesuai jumlah yang terjual
    UPDATE produk 
    SET stok = stok - NEW.jumlah 
    WHERE id = NEW.id_produk;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` varchar(15) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `stok` int(11) NOT NULL,
  `harga` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama_produk`, `stok`, `harga`) VALUES
('P001', 'Laptop Pro 14', 38, 15000000),
('P002', 'Mouse Nirkabel MX', 150, 750000),
('P003', 'Papan Ketik Mekanikal', 80, 1200000),
('P004', 'Monitor 24 inch UHD', 40, 4500000);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `peran` enum('admin','kasir') NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `terakhir_masuk` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `nama_lengkap`, `peran`, `dibuat_pada`, `terakhir_masuk`) VALUES
(1, 'admin', '8bf1fc09a1464b8c5739048283aa52e2', 'Admin', 'admin', '2025-07-17 17:41:15', NULL),
(2, 'ardorianda', '8bf1fc09a1464b8c5739048283aa52e2', 'Ardo Rianda', 'kasir', '2025-07-17 17:41:15', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_masuk`
-- (See below for the actual view)
--
CREATE TABLE `v_masuk` (
`id` int(11)
,`username` varchar(50)
,`password` varchar(255)
,`nama_lengkap` varchar(100)
,`peran` enum('admin','kasir')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_ringkasan_penjualan`
-- (See below for the actual view)
--
CREATE TABLE `v_ringkasan_penjualan` (
`nama_produk` varchar(100)
,`total_jumlah_terjual` decimal(32,0)
,`total_pendapatan` double
);

-- --------------------------------------------------------

--
-- Structure for view `v_masuk`
--
DROP TABLE IF EXISTS `v_masuk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_masuk`  AS SELECT `user`.`id` AS `id`, `user`.`username` AS `username`, `user`.`password` AS `password`, `user`.`nama_lengkap` AS `nama_lengkap`, `user`.`peran` AS `peran` FROM `user` ;

-- --------------------------------------------------------

--
-- Structure for view `v_ringkasan_penjualan`
--
DROP TABLE IF EXISTS `v_ringkasan_penjualan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ringkasan_penjualan`  AS SELECT `p`.`nama_produk` AS `nama_produk`, sum(`j`.`jumlah`) AS `total_jumlah_terjual`, sum(`j`.`total_harga`) AS `total_pendapatan` FROM (`penjualan` `j` join `produk` `p` on(`j`.`id_produk` = `p`.`id`)) GROUP BY `p`.`nama_produk` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `penjualan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
