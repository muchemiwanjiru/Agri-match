-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2025 at 06:58 PM
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
-- Database: `agrimatch`
--

-- --------------------------------------------------------

--
-- Table structure for table `booked_operator`
--

CREATE TABLE `booked_operator` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` int(11) NOT NULL,
  `purchasedby` int(11) NOT NULL,
  `booked_datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booked_operator`
--

INSERT INTO `booked_operator` (`id`, `user_id`, `operator_id`, `start_date`, `end_date`, `amount`, `purchasedby`, `booked_datetime`) VALUES
(1, 2, 1, '2025-03-07', '2025-03-08', 1000, 2, '2025-03-06 22:43:16');

-- --------------------------------------------------------

--
-- Table structure for table `booked_tractor`
--

CREATE TABLE `booked_tractor` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tractor_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `purchasedby` int(11) NOT NULL,
  `booked_datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booked_tractor`
--

INSERT INTO `booked_tractor` (`id`, `user_id`, `tractor_id`, `start_date`, `end_date`, `amount`, `image`, `purchasedby`, `booked_datetime`) VALUES
(1, 2, 12, '2025-03-07', '2025-03-08', 100000, 'uploads/sam-110.jpeg', 2, '2025-03-06 21:47:07'),
(2, 2, 12, '2025-03-07', '2025-03-08', 100000, 'uploads/sam-110.jpeg', 2, '2025-03-06 21:54:21');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tractor_id` int(11) DEFAULT NULL,
  `delivery_status` enum('pending','completed') DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL DEFAULT '',
  `purchasedby` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `tractor_id`, `delivery_status`, `start_date`, `end_date`, `amount`, `image`, `purchasedby`) VALUES
(19, 2, 12, 'completed', '2025-03-07', '2025-03-08', 100000, 'uploads/sam-110.jpeg', 2);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `message`, `created_at`) VALUES
(1, 'xcv', 'emma@gmail.com', '09339393993', 'dsfghjhk', '2025-03-05 06:35:24');

-- --------------------------------------------------------

--
-- Table structure for table `operators`
--

CREATE TABLE `operators` (
  `id` int(11) NOT NULL,
  `age` int(11) NOT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `strength` varchar(100) NOT NULL,
  `skills` text NOT NULL,
  `amount` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('available','booked') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operators`
--

INSERT INTO `operators` (`id`, `age`, `sex`, `strength`, `skills`, `amount`, `created_at`, `status`) VALUES
(1, 20, 'Male', 'very', 'dfghjhg', 1000, '2025-03-06 19:10:21', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `operator_bookings`
--

CREATE TABLE `operator_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` int(11) NOT NULL,
  `purchasedby` int(11) NOT NULL,
  `delivery_status` enum('pending','completed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `operator_listings`
--

CREATE TABLE `operator_listings` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `certification` varchar(100) DEFAULT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `available_from` date DEFAULT NULL,
  `available_to` date DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `operator_wishlist`
--

CREATE TABLE `operator_wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` int(11) NOT NULL,
  `purchasedby` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `package_count` int(11) NOT NULL,
  `transport_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `package_count`, `transport_date`, `description`, `weight`, `user_id`) VALUES
(1, 12, '2025-03-04', 'dfdffdfd', 12.00, 5),
(2, 12, '2025-03-12', 'sdfghj', 12.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `returned_bookings`
--

CREATE TABLE `returned_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tractor_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `purchasedby` int(11) NOT NULL,
  `returned_datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returned_bookings`
--

INSERT INTO `returned_bookings` (`id`, `user_id`, `tractor_id`, `start_date`, `end_date`, `amount`, `image`, `purchasedby`, `returned_datetime`) VALUES
(1, 2, 12, '2025-03-07', '2025-03-09', 100000, 'uploads/sam-110.jpeg', 2, '2025-03-06 19:58:14'),
(2, 2, 12, '2025-03-07', '2025-03-08', 100000, 'uploads/sam-110.jpeg', 2, '2025-03-06 21:41:11'),
(3, 2, 12, '2025-03-07', '2025-03-08', 100000, 'uploads/sam-110.jpeg', 2, '2025-03-06 21:47:37');

-- --------------------------------------------------------

--
-- Table structure for table `returned_operator_bookings`
--

CREATE TABLE `returned_operator_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` int(11) NOT NULL,
  `purchasedby` int(11) NOT NULL,
  `returned_datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returned_operator_bookings`
--

INSERT INTO `returned_operator_bookings` (`id`, `user_id`, `operator_id`, `start_date`, `end_date`, `amount`, `purchasedby`, `returned_datetime`) VALUES
(1, 2, 1, '2025-03-07', '2025-03-08', 1000, 2, '2025-03-06 22:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `tractor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `tractor_id`, `user_id`, `rating`, `comment`) VALUES
(2, 12, 2, 1, 'cvcccxxc');

-- --------------------------------------------------------

--
-- Table structure for table `tractors`
--

CREATE TABLE `tractors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('available','booked') DEFAULT 'available',
  `model` varchar(100) NOT NULL,
  `capability` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tractors`
--

INSERT INTO `tractors` (`id`, `name`, `status`, `model`, `capability`, `description`, `amount`, `image`, `user_id`) VALUES
(11, 'John Deere', 'available', '44rr', 'full', 'Healthy and strong', 100000, 'uploads/massey-250.jpeg', 1),
(12, 'Massey Ferguson', 'booked', 'w100', 'very strong', 'effiecient', 100000, 'uploads/sam-110.jpeg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('renter','rentee','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'him', '$2y$10$zaUQu0OKBD5sx1cDRftsRON3YuLCza/ppMKFmCkQnyHKhlNv.wEsS', 'admin'),
(2, 'she', '$2y$10$xbL8VwNrbtg9y3sAb.IDCOeyFEfj5AsWcyWrffQ4RZtA2ApyQpeSa', 'rentee'),
(3, 'he', '$2y$10$hvPmb6nehhZvNqEyCacPJO38IYLvC8FSH3uNNmuygo9aEYfdXOvWK', 'renter'),
(4, 'her', '$2y$10$St.dEKlwuOTfcg1FrSB2OeLVC1vWNQr/IKxeo5lfRO1LoO6WORhK.', 'renter'),
(5, 'his', '$2y$10$dYvS82XvA1oTBEIOa/Qzx.Dr46P03joF9Gz5.1R1ujn1ktdwDqKAy', 'rentee');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tractor_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL DEFAULT '',
  `purchasedby` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booked_operator`
--
ALTER TABLE `booked_operator`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `operator_id` (`operator_id`);

--
-- Indexes for table `booked_tractor`
--
ALTER TABLE `booked_tractor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tractor_id` (`tractor_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tractor_id` (`tractor_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operators`
--
ALTER TABLE `operators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operator_bookings`
--
ALTER TABLE `operator_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `operator_id` (`operator_id`);

--
-- Indexes for table `operator_listings`
--
ALTER TABLE `operator_listings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operator_wishlist`
--
ALTER TABLE `operator_wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `operator_id` (`operator_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `returned_bookings`
--
ALTER TABLE `returned_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tractor_id` (`tractor_id`);

--
-- Indexes for table `returned_operator_bookings`
--
ALTER TABLE `returned_operator_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `operator_id` (`operator_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tractor_id` (`tractor_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tractors`
--
ALTER TABLE `tractors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tractor_id` (`tractor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booked_operator`
--
ALTER TABLE `booked_operator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booked_tractor`
--
ALTER TABLE `booked_tractor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `operators`
--
ALTER TABLE `operators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `operator_bookings`
--
ALTER TABLE `operator_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `operator_listings`
--
ALTER TABLE `operator_listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `operator_wishlist`
--
ALTER TABLE `operator_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `returned_bookings`
--
ALTER TABLE `returned_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `returned_operator_bookings`
--
ALTER TABLE `returned_operator_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tractors`
--
ALTER TABLE `tractors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booked_operator`
--
ALTER TABLE `booked_operator`
  ADD CONSTRAINT `booked_operator_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `booked_operator_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`);

--
-- Constraints for table `booked_tractor`
--
ALTER TABLE `booked_tractor`
  ADD CONSTRAINT `booked_tractor_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `booked_tractor_ibfk_2` FOREIGN KEY (`tractor_id`) REFERENCES `tractors` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`tractor_id`) REFERENCES `tractors` (`id`);

--
-- Constraints for table `operator_bookings`
--
ALTER TABLE `operator_bookings`
  ADD CONSTRAINT `operator_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `operator_bookings_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`);

--
-- Constraints for table `operator_wishlist`
--
ALTER TABLE `operator_wishlist`
  ADD CONSTRAINT `operator_wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `operator_wishlist_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`);

--
-- Constraints for table `returned_bookings`
--
ALTER TABLE `returned_bookings`
  ADD CONSTRAINT `returned_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `returned_bookings_ibfk_2` FOREIGN KEY (`tractor_id`) REFERENCES `tractors` (`id`);

--
-- Constraints for table `returned_operator_bookings`
--
ALTER TABLE `returned_operator_bookings`
  ADD CONSTRAINT `returned_operator_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `returned_operator_bookings_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`tractor_id`) REFERENCES `tractors` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`tractor_id`) REFERENCES `tractors` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
