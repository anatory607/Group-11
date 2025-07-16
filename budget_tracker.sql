-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 10:59 AM
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
-- Database: `budget_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `budget_month` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `category_id`, `user_id`, `amount`, `budget_month`, `created_at`, `group_id`) VALUES
(1, 3, 1, 5000.00, '0000-00-00', '2025-06-29 13:37:54', NULL),
(2, 25, 2, 250000.00, '0000-00-00', '2025-06-30 10:48:42', NULL),
(3, 41, 4, 300000.00, '0000-00-00', '2025-07-11 21:15:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `user_id`) VALUES
(1, 'Food', 'Matumizi ya chakula na vinywaji', 1),
(2, 'Rent', 'Kodi ya nyumba', 1),
(3, 'Transport', 'Nauli na mafuta', 1),
(4, 'Utilities', 'Umeme, maji, na bili', 1),
(5, 'Education', 'Ada na vifaa vya shule', 1),
(6, 'Healthcare', 'Huduma za afya', 1),
(7, 'Entertainment', 'Burudani kama sinema na outing', 1),
(8, 'Shopping', 'Mavazi na bidhaa', 1),
(9, 'Salary', 'Mshahara wa kazi', 1),
(10, 'Business', 'Mapato ya biashara', 1),
(11, 'Investment', 'Mapato ya uwekezaji', 1),
(12, 'Gifts', 'Zawadi au misaada', 1),
(13, 'Bonus', 'Malipo ya ziada kazini', 1),
(14, 'educatio', 'ada na vifaa', 1),
(15, 'Food', 'Matumizi ya chakula na vinywaji', 2),
(16, 'Rent', 'Kodi ya nyumba', 2),
(17, 'Transport', 'Nauli na mafuta', 2),
(18, 'Utilities', 'Umeme, maji, na bili', 2),
(19, 'Education', 'Ada na vifaa vya shule', 2),
(20, 'Healthcare', 'Huduma za afya', 2),
(21, 'Entertainment', 'Burudani kama sinema na outing', 2),
(22, 'Shopping', 'Mavazi na bidhaa', 2),
(23, 'Salary', 'Mshahara wa kazi', 2),
(24, 'Business', 'Mapato ya biashara', 2),
(25, 'Investment', 'Mapato ya uwekezaji', 2),
(26, 'Gifts', 'Zawadi au misaada', 2),
(27, 'Bonus', 'Malipo ya ziada kazini', 2),
(28, 'Food', 'Matumizi ya chakula na vinywaji', 3),
(29, 'Rent', 'Kodi ya nyumba', 3),
(30, 'Transport', 'Nauli na mafuta', 3),
(31, 'Utilities', 'Umeme, maji, na bili', 3),
(32, 'Education', 'Ada na vifaa vya shule', 3),
(33, 'Healthcare', 'Huduma za afya', 3),
(34, 'Entertainment', 'Burudani kama sinema na outing', 3),
(35, 'Shopping', 'Mavazi na bidhaa', 3),
(36, 'Salary', 'Mshahara wa kazi', 3),
(37, 'Business', 'Mapato ya biashara', 3),
(38, 'Investment', 'Mapato ya uwekezaji', 3),
(39, 'Gifts', 'Zawadi au misaada', 3),
(40, 'Bonus', 'Malipo ya ziada kazini', 3),
(41, 'Food', 'Matumizi ya chakula na vinywaji', 4),
(42, 'Rent', 'Kodi ya nyumba', 4),
(43, 'Transport', 'Nauli na mafuta', 4),
(44, 'Utilities', 'Umeme, maji, na bili', 4),
(45, 'Education', 'Ada na vifaa vya shule', 4),
(46, 'Healthcare', 'Huduma za afya', 4),
(47, 'Entertainment', 'Burudani kama sinema na outing', 4),
(48, 'Shopping', 'Mavazi na bidhaa', 4),
(49, 'Salary', 'Mshahara wa kazi', 4),
(50, 'Business', 'Mapato ya biashara', 4),
(51, 'Investment', 'Mapato ya uwekezaji', 4),
(52, 'Gifts', 'Zawadi au misaada', 4),
(53, 'Bonus', 'Malipo ya ziada kazini', 4);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `owner_id`, `created_at`) VALUES
(1, 'work1', 1, '2025-06-29 14:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`group_id`, `user_id`, `role`) VALUES
(1, 1, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(50) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`) VALUES
('app_name', 'csc'),
('contact_email', 'csc@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `category_id`, `type`, `amount`, `category`, `transaction_date`, `description`, `created_at`, `group_id`) VALUES
(6, 3, 35, 'income', 45000.00, '', '2025-06-30', '', '2025-06-30 10:46:45', NULL),
(7, 1, 1, 'expense', 3000.00, 'Food', '2025-06-30', '', '2025-06-30 11:36:22', NULL),
(8, 1, 9, 'income', 45555.00, '', '2025-06-30', 'wel', '2025-06-30 11:37:07', NULL),
(9, 4, 43, 'income', 2333.00, '', '2025-07-11', '', '2025-07-11 21:03:43', NULL),
(11, 4, 51, 'expense', 3500.00, 'Investment', '2025-07-10', '', '2025-07-11 21:05:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'kishosha john', 'k@gmail.com', '$2y$10$YBbvgR2Ectmrn0Vxq5LaUeOstIfyQGcGQYDyFDbbGwToDpwvsnD3K', 'member', '2025-06-29 12:41:10'),
(2, 'top man', 'top@gmail.com', '$2y$10$2vhZLNbeW1x5BCRlOwqKE.ravKfB6KmrfLVvHOO5s5h1bF.7di.Z.', 'admin', '2025-06-30 10:08:40'),
(3, 'anatory', 'a@gmail.com', '$2y$10$iLl4gekJ4wIouB9mbTmGDOnXlOvFNFv/RwiijUh30MaaMxFrwNVLO', 'member', '2025-06-30 10:45:37'),
(4, 'said', 'sa@gmail.com', '$2y$10$fVje7xXrL1/c5QUkTQZZl.62NEBsjqNfgcv.s6muZr2Ey6fmlFTBm', 'member', '2025-07-11 21:02:42'),
(5, 'sd', 'aatort@gmail.com', '$2y$10$mmTlu2272xba7Lq66YKJ8O/AJLAKoXUVHH9FwRpIZrRwb8axQX5kO', 'member', '2025-07-14 08:46:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`group_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budgets_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budgets_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
