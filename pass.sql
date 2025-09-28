-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2025 at 08:49 PM
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
-- Database: `pass`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` year(4) NOT NULL,
  `registration_number` varchar(20) NOT NULL,
  `status` enum('Available','Rented') DEFAULT 'Available',
  `price_per_day` decimal(10,2) DEFAULT 500.00,
  `image` varchar(255) DEFAULT 'default_car.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`car_id`, `make`, `model`, `year`, `registration_number`, `status`, `price_per_day`, `image`) VALUES
(2, 'BMW', 'X6', '2025', 'RT 567 L', 'Available', 2.00, '68c9b1c075162.jpg'),
(3, 'toyota', 'hilux', '2005', 'WRT 34', 'Rented', 400.00, '68d5908d38f1e.jpeg'),
(4, 'vw', 'polo', '2024', 'WRY 67 GP', 'Rented', 350.00, '68d59b3d783f0.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `created_at`) VALUES
(3, 'masego', 'phehla', 'masego@stac.ac.za', '$2y$10$OWu/6NHHFRPWa7aHVaAYKuuRRJ1K3mZ2RavXywi0V0Rah6LpBVZDC', NULL, '2025-09-16 18:28:28'),
(4, 'rendy', 'Gema', 'rendy@gmail.ac.za', '$2y$10$BT0XygHiHU0WWBENZw2uvunv8wf51Eb.K5WKGXwyTRxWXwLrpbxgu', NULL, '2025-09-16 18:35:38');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rental_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Ongoing','Returned') DEFAULT 'Ongoing',
  `total_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `car_id`, `customer_id`, `rental_date`, `return_date`, `status`, `total_amount`) VALUES
(2, 2, 3, '2025-09-18', '2025-09-20', 'Returned', 1500.00),
(3, 4, 3, '2025-09-25', '2025-09-27', NULL, 1050.00),
(4, 3, 3, '2025-09-25', '2025-09-28', 'Ongoing', 1600.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Staff') DEFAULT 'Staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`) VALUES
(1, 'raymond', 'raymond@speedywheels.za', 'Ray@123', 'Staff'),
(2, 'Ben', 'BEN', '12345', 'Staff');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
