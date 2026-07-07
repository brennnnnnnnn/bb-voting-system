-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 03:16 PM
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
-- Database: `sistem pengundian jawatankuasa briged putera`
--

-- --------------------------------------------------------

--
-- Table structure for table `calon`
--

CREATE TABLE `calon` (
  `id_Calon` varchar(50) NOT NULL,
  `nama_Calon` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calon`
--

INSERT INTO `calon` (`id_Calon`, `nama_Calon`) VALUES
('C1', 'Brennan Kuan Yew Joe'),
('C2', 'Clayton Tai Kar Poh'),
('C3', 'Cheah Eu Jin');

-- --------------------------------------------------------

--
-- Table structure for table `jawatan`
--

CREATE TABLE `jawatan` (
  `id_Jawatan` varchar(50) NOT NULL,
  `nama_Jawatan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jawatan`
--

INSERT INTO `jawatan` (`id_Jawatan`, `nama_Jawatan`) VALUES
('J1', 'Pengerusi'),
('J2', 'Naib Pengerusi'),
('J3', 'Setiausaha');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_Pengguna` varchar(50) NOT NULL,
  `nama_Pengguna` varchar(50) NOT NULL,
  `kelas_Pengguna` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_Pengguna`, `nama_Pengguna`, `kelas_Pengguna`) VALUES
('D6271', 'Ooi Jing Shen', '4SA3'),
('D6276', 'Choo Yi Jie', '4SB2'),
('D6380', 'George Ong', '4SK2'),
('D6434', 'Bernard Koo', '4SA2');

-- --------------------------------------------------------

--
-- Table structure for table `undian`
--

CREATE TABLE `undian` (
  `id_Undi` varchar(50) NOT NULL,
  `id_Pengguna` varchar(50) NOT NULL,
  `id_Calon` varchar(50) NOT NULL,
  `id_Jawatan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `undian`
--

INSERT INTO `undian` (`id_Undi`, `id_Pengguna`, `id_Calon`, `id_Jawatan`) VALUES
('1', 'D6380', 'C1', 'J1'),
('2', 'D6380', 'C3', 'J2'),
('3', 'D6380', 'C2', 'J2'),
('4', 'D6271', 'C1', 'J2'),
('5', 'D6434', 'C2', 'J2'),
('6', 'D6276', 'C3', 'J3');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calon`
--
ALTER TABLE `calon`
  ADD PRIMARY KEY (`id_Calon`);

--
-- Indexes for table `jawatan`
--
ALTER TABLE `jawatan`
  ADD PRIMARY KEY (`id_Jawatan`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_Pengguna`);

--
-- Indexes for table `undian`
--
ALTER TABLE `undian`
  ADD PRIMARY KEY (`id_Undi`),
  ADD KEY `undian_ibfk_1` (`id_Calon`),
  ADD KEY `undian_ibfk_2` (`id_Jawatan`),
  ADD KEY `undian_ibfk_3` (`id_Pengguna`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `undian`
--
ALTER TABLE `undian`
  ADD CONSTRAINT `undian_ibfk_1` FOREIGN KEY (`id_Calon`) REFERENCES `calon` (`id_Calon`),
  ADD CONSTRAINT `undian_ibfk_2` FOREIGN KEY (`id_Jawatan`) REFERENCES `jawatan` (`id_Jawatan`),
  ADD CONSTRAINT `undian_ibfk_3` FOREIGN KEY (`id_Pengguna`) REFERENCES `pengguna` (`id_Pengguna`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

