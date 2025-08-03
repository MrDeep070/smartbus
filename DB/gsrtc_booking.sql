-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2025 at 01:46 PM
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
-- Database: `gsrtc_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `seats` text NOT NULL COMMENT 'JSON array of seat numbers',
  `amount_paid` decimal(10,2) NOT NULL,
  `gst_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('confirmed','cancelled','pending') DEFAULT 'confirmed',
  `cancellation_fee` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `bus_id`, `seats`, `amount_paid`, `gst_amount`, `discount`, `booking_date`, `status`, `cancellation_fee`) VALUES
(1, 2, 1, '[\"2\"]', 210.00, 10.00, 0.00, '2025-07-27 14:28:28', 'confirmed', 0.00),
(3, 3, 1, '[\"16\"]', 262.50, 12.50, 0.00, '2025-08-03 09:09:28', 'cancelled', 0.00),
(4, 3, 1, '[\"12\"]', 242.50, 12.50, 20.00, '2025-08-03 09:10:40', 'cancelled', 0.00),
(5, 3, 1, '[\"34\"]', 247.50, 12.50, 15.00, '2025-08-03 09:24:14', 'cancelled', 0.00),
(9, 3, 1, '[\"14\"]', 252.50, 12.50, 10.00, '2025-08-03 11:24:50', 'cancelled', 30.30);

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `id` int(11) NOT NULL,
  `bus_no` varchar(20) NOT NULL,
  `bus_type` enum('AC','Non-AC','Sleeper','Express') NOT NULL,
  `source` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `seats_total` int(11) NOT NULL,
  `available_seats` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`id`, `bus_no`, `bus_type`, `source`, `destination`, `departure_time`, `arrival_time`, `fare`, `seats_total`, `available_seats`, `date`) VALUES
(1, 'GJ18A5678', 'Non-AC', 'Ahmedabad', 'Surat', '2025-08-07 00:00:00', '2025-08-08 16:09:00', 250.00, 40, 34, '2025-08-06');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_trans`
--

CREATE TABLE `loyalty_trans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `type` enum('earned','redeemed') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_trans`
--

INSERT INTO `loyalty_trans` (`id`, `user_id`, `points`, `type`, `description`, `date`) VALUES
(1, 3, 0, 'earned', 'Refund for cancelled booking BK00005', '2025-08-03 09:44:52');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `source` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `distance_km` decimal(6,1) NOT NULL,
  `estimated_time` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `loyalty_points`, `created_at`) VALUES
(1, 'PRAJAPATI DEEP', 'dhprajapati3005@gmail.com', '12345678', 0, '2025-07-27 13:00:37'),
(2, 'akki', 'a@gmail.com', 'akhil123', 2, '2025-07-27 13:45:13'),
(3, 'PRAJAPATI DEEP', 'hiten123@gmail.com', '$2y$10$xinr.QWnV3ggZDBeKuGIcO8HQZ4zlzlb3qfBno8MHCHGBSLhhtTw2', 71, '2025-08-03 08:41:04');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`user_id`, `balance`) VALUES
(3, 1161.60);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `type`, `amount`, `description`, `transaction_date`) VALUES
(1, 3, 'credit', 1000.00, 'Added via Bank Transfer (Ac: 789456123852, IFSC: SDEF7HYJJ8)', '2025-08-03 10:24:33'),
(2, 3, 'credit', 213.40, 'Refund for cancelled booking BK00004', '2025-08-03 10:30:46'),
(3, 3, 'debit', 252.50, 'Payment for booking BK00008', '2025-08-03 10:57:00'),
(4, 3, 'credit', 231.00, 'Refund for cancelled booking BK00003', '2025-08-03 10:59:18'),
(5, 3, 'debit', 252.50, 'Payment for booking BK00009', '2025-08-03 11:24:50'),
(6, 3, 'credit', 222.20, 'Refund for cancelled booking BK00009', '2025-08-03 11:33:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_trans`
--
ALTER TABLE `loyalty_trans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loyalty_trans`
--
ALTER TABLE `loyalty_trans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`);

--
-- Constraints for table `loyalty_trans`
--
ALTER TABLE `loyalty_trans`
  ADD CONSTRAINT `loyalty_trans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
