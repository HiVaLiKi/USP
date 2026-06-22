-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2026 at 10:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `warehouse_id`, `name`) VALUES
(1, 1, 'Storage Room A'),
(2, 1, 'Garage');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `barcode_digits` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `critical_minimum` int(11) DEFAULT 0,
  `location_rack` varchar(50) DEFAULT NULL,
  `location_shelf` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `facility_id`, `name`, `barcode_digits`, `quantity`, `critical_minimum`, `location_rack`, `location_shelf`, `image_path`) VALUES
(1, 1, 'Coca-Cola 500ml', '5449000000439', 18, 10, 'Rack 2', 'Shelf B', NULL),
(2, 1, 'Fanta', 'Ratatatatata', 12, 5, 'A', '2', NULL),
(3, 2, 'Daska', '0', 1, 0, 'Poda', 'V sredata', NULL),
(4, 2, 'Nigger & faggot birds', 'kaka', 2, 2, 'darvo', '', 'uploads/1781912065_220424meme5-618x416.png');

-- --------------------------------------------------------

--
-- Table structure for table `item_history`
--

CREATE TABLE `item_history` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_history`
--

INSERT INTO `item_history` (`id`, `item_id`, `action_type`, `quantity_change`, `timestamp`) VALUES
(1, 1, 'restock', 3, '2026-06-19 21:57:03'),
(2, 1, 'consume', -5, '2026-06-19 21:57:09'),
(3, 1, 'restock', 8, '2026-06-19 21:57:28'),
(4, 1, 'consume', -2, '2026-06-19 21:57:33'),
(5, 1, 'edit', 0, '2026-06-19 21:59:58'),
(6, 1, 'edit', 0, '2026-06-19 22:00:13'),
(7, 1, 'edit', 0, '2026-06-19 22:00:19'),
(8, 1, 'restock', 1, '2026-06-19 22:05:03'),
(9, 1, 'consume', -2, '2026-06-19 22:05:07'),
(10, 1, 'edit', 0, '2026-06-19 22:05:26'),
(11, 1, 'edit', 0, '2026-06-19 22:05:32'),
(12, 1, 'edit', 0, '2026-06-19 22:05:47'),
(13, 2, 'add', 9, '2026-06-19 22:12:15'),
(14, 1, 'consume', -13, '2026-06-19 22:41:08'),
(15, 1, 'restock', 8, '2026-06-19 22:58:16'),
(16, 1, 'restock', 8, '2026-06-19 22:58:24'),
(17, 2, 'consume', -6, '2026-06-19 22:59:17'),
(18, 3, 'add', 1, '2026-06-19 23:20:36'),
(19, 2, 'restock', 9, '2026-06-19 23:29:49'),
(20, 4, 'add', 2, '2026-06-19 23:34:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', 'admin123', '2026-06-19 21:27:14'),
(2, 'john_doe', 'password123', '2026-06-19 22:21:52'),
(3, 'sarah_smith', 'password123', '2026-06-19 22:21:52'),
(4, 'mike_warehouse', 'password123', '2026-06-19 22:21:52');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `user_id`, `name`) VALUES
(1, 1, 'Main Warehouse');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `item_history`
--
ALTER TABLE `item_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `item_history`
--
ALTER TABLE `item_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `facilities`
--
ALTER TABLE `facilities`
  ADD CONSTRAINT `facilities_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_history`
--
ALTER TABLE `item_history`
  ADD CONSTRAINT `item_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
