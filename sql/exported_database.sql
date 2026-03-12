-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 12, 2026 at 05:23 AM
-- Server version: 8.0.45
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ticket_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `entity` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `entity_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity`, `entity_id`, `timestamp`) VALUES
(1, 2, 'CREATE', 'events', 'evt-001', '2026-03-10 18:28:18'),
(2, 2, 'CREATE', 'events', 'evt-002', '2026-03-10 18:28:18'),
(3, 2, 'CREATE', 'events', 'evt-003', '2026-03-10 18:28:18'),
(4, 2, 'CREATE', 'events', 'evt-004', '2026-03-10 18:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `venue` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `available_tickets` int DEFAULT NULL,
  `sold_tickets` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `venue`, `price`, `category`, `available_tickets`, `sold_tickets`) VALUES
('evt-001', 'Shadys 45 Years - Symphonic Gala', 'Celebrate 45 years of Shadys with symphonic music', '2026-02-13', '20:00:00', 'Lima', 'National Grand Theater', 150.00, 'concerts', 791, 243),
('evt-002', 'THE HOUSE - Electronic Music Event', 'Electronic music party with international DJs', '2026-01-29', '22:00:00', 'Lima', 'Peru Arena', 80.00, 'electronic', 2000, 450),
('evt-003', 'Le Paris - Elegant Dinner Experience', 'French gastronomic experience', '2026-01-31', '19:30:00', 'Lima', 'Westin Hotel', 120.00, 'gastronomy', 150, 89),
('evt-004', 'Valentines Plans 2026', 'Romantic Valentine event', '2026-02-14', '18:00:00', 'Lima', 'Exposition Park', 10.00, 'romantic', 500, 123),
('evt-005', 'Montecarlo Circus Huanuco 2026', 'Family circus show', '2026-01-28', '16:00:00', 'Huanuco', 'Heraclio Tapia Stadium', 20.00, 'family', 3001, 1455),
('evt-006', 'Peruvian Rock Festival Vol 02', 'Rock festival with 10 bands', '2026-02-15', '14:00:00', 'Trujillo', 'Mansiche Stadium', 50.00, 'concerts', 5000, 2340),
('evt-007', 'SUU RABANAL The Law Tour', 'Concert tour event', '2026-02-08', '21:00:00', 'Callao', 'Convention Center', 50.00, 'concerts', 1200, 678),
('evt-008', 'SALSA CUMBIA Yaipen Brothers', 'Salsa and cumbia concert', '2026-02-22', '20:00:00', 'Chiclayo', 'Gran Chimu Coliseum', 30.00, 'concerts', 2500, 890),
('evt-17329', 'Gran Concierto Sinfónico', 'Una noche mágica con la orquesta nacional.', '2026-11-20', '20:30:00', 'Arequipa', 'Teatro Municipal', 120.00, 'concerts', 800, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `event_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `purchase_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `event_id`, `quantity`, `total_price`, `purchase_date`) VALUES
(2, 1, 'evt-002', 1, 180.00, '2026-03-10 18:28:18'),
(3, 1, 'evt-003', 4, 480.00, '2026-03-10 18:28:18'),
(4, 1, 'evt-004', 1, 25.00, '2026-03-10 18:28:18'),
(5, 1, 'evt-001', 3, 450.00, '2026-03-11 05:51:02'),
(6, 1, 'evt-001', 1, 150.00, '2026-03-11 05:51:31'),
(7, 1, 'evt-001', 5, 750.00, '2026-03-12 02:55:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `role`, `created_at`) VALUES
(1, 'santisu05@gmail.com', '$2y$10$mf6BTOy.yvqFhKTUWqk5vOIWsdx2JFhW6Qk8a1lnVItnE3PUAbzla', 'Santiago', 'Suarez', 'user', '2026-03-10 18:28:18'),
(2, 'admin@ticketapp.pe', 'admin123', 'System', 'Admin', 'admin', '2026-03-10 18:28:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

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
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
