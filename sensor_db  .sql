-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2024 at 04:48 PM
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
-- Database: `sensor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `alcohol`
--

CREATE TABLE `alcohol` (
  `id` int(11) NOT NULL,
  `rfid` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `alcoholvalue` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alcohol`
--

INSERT INTO `alcohol` (`id`, `rfid`, `created_at`, `updated_at`, `alcoholvalue`) VALUES
(1, '83B5FF0F', '2024-08-03 12:00:00', '2024-08-03 12:00:00', 2.34),
(2, '83B5FF0F', '2024-08-03 12:05:00', '2024-08-03 12:05:00', 1.87),
(3, '83B5FF0F', '2024-08-03 12:10:00', '2024-08-03 12:10:00', 2.91),
(4, '83B5FF0F', '2024-08-03 12:15:00', '2024-08-03 12:15:00', 1.45),
(5, '83B5FF0F', '2024-08-03 12:20:00', '2024-08-03 12:20:00', 2.78),
(6, '83B5FF0F', '2024-08-03 12:25:00', '2024-08-03 12:25:00', 1.23),
(7, '83B5FF0F', '2024-08-03 12:30:00', '2024-08-03 12:30:00', 2.56),
(8, '83B5FF0F', '2024-08-03 12:35:00', '2024-08-03 12:35:00', 1.98),
(9, '83B5FF0F', '2024-08-03 12:40:00', '2024-08-03 12:40:00', 2.67),
(10, '83B5FF0F', '2024-08-03 12:45:00', '2024-08-03 12:45:00', 1.76),
(11, '83B5FF0F', '2024-08-03 12:50:00', '2024-08-03 12:50:00', 2.12),
(12, '83B5FF0F', '2024-08-03 12:55:00', '2024-08-03 12:55:00', 1.34),
(13, '83B5FF0F', '2024-08-03 13:00:00', '2024-08-03 13:00:00', 2.89),
(14, '83B5FF0F', '2024-08-03 13:05:00', '2024-08-03 13:05:00', 1.56),
(15, '83B5FF0F', '2024-08-03 13:10:00', '2024-08-03 13:10:00', 2.43),
(16, '83B5FF0F', '2024-08-03 13:15:00', '2024-08-03 13:15:00', 1.87),
(17, '83B5FF0F', '2024-08-03 13:20:00', '2024-08-03 13:20:00', 2.76),
(18, '83B5FF0F', '2024-08-03 13:25:00', '2024-08-03 13:25:00', 1.23),
(19, '83B5FF0F', '2024-08-03 13:30:00', '2024-08-03 13:30:00', 2.98),
(20, '83B5FF0F', '2024-08-03 13:35:00', '2024-08-03 13:35:00', 1.65),
(21, '83B5FF0F', '2024-08-03 13:40:00', '2024-08-03 13:40:00', 2.34),
(22, '83B5FF0F', '2024-08-03 13:45:00', '2024-08-03 13:45:00', 1.78),
(23, '83B5FF0F', '2024-08-03 13:50:00', '2024-08-03 13:50:00', 2.56),
(24, '83B5FF0F', '2024-08-03 13:55:00', '2024-08-03 13:55:00', 1.98),
(25, '83B5FF0F', '2024-08-03 14:00:00', '2024-08-03 14:00:00', 2.87),
(26, '83B5FF0F', '2024-08-03 14:05:00', '2024-08-03 14:05:00', 1.45),
(27, '83B5FF0F', '2024-08-03 14:10:00', '2024-08-03 14:10:00', 2.67),
(28, '83B5FF0F', '2024-08-03 14:15:00', '2024-08-03 14:15:00', 1.89),
(29, '83B5FF0F', '2024-08-03 14:20:00', '2024-08-03 14:20:00', 2.34),
(30, '83B5FF0F', '2024-08-03 14:25:00', '2024-08-03 14:25:00', 1.76),
(31, '83B5FF0F', '2024-08-03 14:30:00', '2024-08-03 14:30:00', 2.98),
(32, '83B5FF0F', '2024-08-03 14:35:00', '2024-08-03 14:35:00', 1.23),
(33, '83B5FF0F', '2024-08-03 14:40:00', '2024-08-03 14:40:00', 2.56),
(34, '83B5FF0F', '2024-08-03 14:45:00', '2024-08-03 14:45:00', 1.87),
(35, '83B5FF0F', '2024-08-03 14:50:00', '2024-08-03 14:50:00', 2.45),
(36, '83B5FF0F', '2024-08-03 14:55:00', '2024-08-03 14:55:00', 1.67),
(37, '83B5FF0F', '2024-08-03 15:00:00', '2024-08-03 15:00:00', 2.89),
(38, '83B5FF0F', '2024-08-03 15:05:00', '2024-08-03 15:05:00', 1.34),
(39, '83B5FF0F', '2024-08-03 15:10:00', '2024-08-03 15:10:00', 2.76),
(40, '83B5FF0F', '2024-08-03 15:15:00', '2024-08-03 15:15:00', 1.98),
(41, '83B5FF0F', '2024-08-03 15:20:00', '2024-08-03 15:20:00', 2.45),
(42, '83B5FF0F', '2024-08-03 15:25:00', '2024-08-03 15:25:00', 1.76),
(43, '83B5FF0F', '2024-08-03 15:30:00', '2024-08-03 15:30:00', 2.87),
(44, '83B5FF0F', '2024-08-03 15:35:00', '2024-08-03 15:35:00', 1.23),
(45, '83B5FF0F', '2024-08-03 15:40:00', '2024-08-03 15:40:00', 2.98),
(46, '83B5FF0F', '2024-08-03 15:45:00', '2024-08-03 15:45:00', 1.56),
(47, '83B5FF0F', '2024-08-03 15:50:00', '2024-08-03 15:50:00', 2.34),
(48, '83B5FF0F', '2024-08-03 15:55:00', '2024-08-03 15:55:00', 1.87),
(49, '83B5FF0F', '2024-08-03 16:00:00', '2024-08-03 16:00:00', 2.65),
(50, '83B5FF0F', '2024-08-03 16:05:00', '2024-08-03 16:05:00', 1.98);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `rfid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `identification` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `admin` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`rfid`, `name`, `identification`, `username`, `password`, `department`, `email`, `admin`, `created_at`, `updated_at`) VALUES
('53814B30', 'SIRINDA WISAKA', '6632040019', 'user_4', '$2y$10$I4yoxGBj7NSTsfszz8SgMeD88Wm9ZWzOJSNM/ierEcnHzVQj/YteG', 'computer', 'test2@gmail.com', 0, '2024-06-30 23:38:17', '2024-06-30 23:38:17'),
('83B5FF0F', 'TANAWAT CHITRATTA', '6632040011', 'admin1', '$2y$10$jEp68CQwcqcsh5602zGwGOhOwpsp.WM0TBHLDZHoCR5wUgb8VxmxW', 'computer', 'tanawatchitratta@gmail.com', 1, '2024-06-03 00:00:05', '2024-06-17 22:19:23'),
('83E9E40C', 'POOCHIS WICHAI', '6632040034', 'user_2', '$2y$10$O0nrTUCPpVHZb9PINOrq0OPQl8Q5T8Mlr/s/cMgzg6NJmrWMYIGjC', 'engineering', 'email3@example.com', 0, '2024-06-03 00:01:20', '2024-06-18 12:22:31'),
('A35F050E', 'DECHAWAT CHANPITAK', '6632040036', 'user_1', '$2y$10$B7cc11Gwmlq0bITdyZQOd.hZrJ1CVfVf5mqhIDbn9HfPlMFNUrJCy', 'electrician', 'email2@example.com', 0, '2024-06-03 00:00:49', '2024-06-18 12:21:37'),
('D74F53C8', ' KIRIN INPROM', '6632040020', 'user_3', '$2y$10$2gI6aZItt170cGNjFm6z4ek8RnKrRJ90LbALL0TT3Er.Jz3e.5P8i', 'computer', 'test1@gmail.com', 0, '2024-07-01 00:15:03', '2024-07-01 00:15:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alcohol`
--
ALTER TABLE `alcohol`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rfid` (`rfid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`rfid`),
  ADD UNIQUE KEY `temp_username` (`username`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alcohol`
--
ALTER TABLE `alcohol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alcohol`
--
ALTER TABLE `alcohol`
  ADD CONSTRAINT `fk_rfid` FOREIGN KEY (`rfid`) REFERENCES `users` (`rfid`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
