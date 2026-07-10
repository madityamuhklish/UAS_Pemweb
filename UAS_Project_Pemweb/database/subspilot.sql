-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2026 at 11:42 AM
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
-- Database: `subspilot`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `created_at`) VALUES
(1, 1, 'Menambahkan subscription Netflix', '2026-07-09 09:16:14'),
(2, 1, 'Menambahkan subscription Spotify', '2026-07-09 09:16:14'),
(3, 1, 'Mengubah data Canva Pro', '2026-07-09 09:16:14'),
(4, 1, 'Login ke sistem', '2026-07-09 09:16:14');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Streaming', 'Layanan hiburan film dan video', '2026-07-09 09:13:52'),
(2, 'Music', 'Layanan musik digital', '2026-07-09 09:13:52'),
(3, 'AI Tools', 'Layanan artificial intelligence', '2026-07-09 09:13:52'),
(4, 'Cloud Storage', 'Penyimpanan cloud', '2026-07-09 09:13:52'),
(5, 'Design', 'Tools desain digital', '2026-07-09 09:13:52');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Paid','Pending','Failed') DEFAULT 'Paid',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_name`, `provider`, `created_at`) VALUES
(1, 'Bank Transfer', 'BCA', '2026-07-09 09:14:21'),
(2, 'E-Wallet', 'DANA', '2026-07-09 09:14:21'),
(3, 'Credit Card', 'Visa', '2026-07-09 09:14:21'),
(4, 'E-Wallet', 'Gopay', '2026-07-09 09:14:21');

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `reminder_date` date DEFAULT NULL,
  `reminder_type` enum('H-7','H-3','H-1','Today') DEFAULT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`id`, `subscription_id`, `reminder_date`, `reminder_type`, `is_sent`, `created_at`) VALUES
(1, 1, '2026-07-08', 'H-7', 0, '2026-07-09 09:15:38'),
(2, 2, '2026-07-17', 'H-3', 0, '2026-07-09 09:15:38'),
(3, 3, '2026-07-24', 'H-1', 0, '2026-07-09 09:15:38'),
(4, 1, '2026-07-08', 'H-7', 0, '2026-07-09 09:15:52'),
(5, 2, '2026-07-17', 'H-3', 0, '2026-07-09 09:15:52'),
(6, 3, '2026-07-24', 'H-1', 0, '2026-07-09 09:15:52');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT 'default.png',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `billing_cycle` enum('Weekly','Monthly','Quarterly','Yearly') DEFAULT 'Monthly',
  `start_date` date DEFAULT NULL,
  `next_payment` date DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `status` enum('Active','Cancelled','Paused') DEFAULT 'Active',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `category_id`, `payment_method_id`, `service_name`, `logo`, `amount`, `currency`, `billing_cycle`, `start_date`, `next_payment`, `auto_renew`, `status`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Netflix', 'default.png', 186000.00, 'IDR', 'Monthly', '2026-06-15', '2026-07-15', 1, 'Active', 'Premium streaming', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(2, 1, 2, 2, 'Spotify', 'default.png', 54000.00, 'IDR', 'Monthly', '2026-06-20', '2026-07-20', 1, 'Active', 'Music subscription', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(3, 1, 3, 1, 'ChatGPT Plus', 'default.png', 300000.00, 'IDR', 'Monthly', '2026-06-25', '2026-07-25', 1, 'Active', 'AI assistant', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(4, 1, 4, 2, 'Google Drive', 'default.png', 26000.00, 'IDR', 'Monthly', '2026-06-10', '2026-07-10', 1, 'Active', 'Cloud storage', '2026-07-09 09:15:05', '2026-07-09 09:15:05'),
(5, 1, 5, 3, 'Canva Pro', 'default.png', 95000.00, 'IDR', 'Monthly', '2026-06-30', '2026-07-30', 1, 'Paused', 'Design tool', '2026-07-09 09:15:05', '2026-07-09 09:15:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT 'default.png',
  `role` enum('admin','user') DEFAULT 'user',
  `dark_mode` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `photo`, `role`, `dark_mode`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Kahlil Gibran', 'admin@gmail.com', '$2y$10$FVnbuc09nCm9EnsTf1BDfO4x1hQ4mGz3KxmIfoJMZF669gzacmPLW', 'default.png', 'user', 0, 'active', '2026-07-09 07:22:52', '2026-07-09 07:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) DEFAULT NULL,
  `estimated_price` decimal(10,2) DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `service_name`, `estimated_price`, `priority`, `note`, `created_at`) VALUES
(1, 1, 'Adobe Creative Cloud', 800000.00, 'High', 'Untuk editing profesional', '2026-07-09 09:15:17'),
(2, 1, 'YouTube Premium', 59000.00, 'Medium', 'Bebas iklan', '2026-07-09 09:15:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_subscription_user` (`user_id`),
  ADD KEY `fk_subscription_category` (`category_id`),
  ADD KEY `fk_subscription_payment` (`payment_method_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `fk_subscription_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_subscription_payment` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_subscription_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
