-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2025 at 09:53 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_phone` varchar(15) NOT NULL,
  `client_gender` enum('Male','Female','Other') NOT NULL,
  `room_id` int(11) NOT NULL,
  `building` varchar(20) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `check_in_date` date NOT NULL,
  `number_of_days` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `expected_check_out` date NOT NULL,
  `status` enum('booked','checked-in','checked-out','cancelled') NOT NULL DEFAULT 'booked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `client_name`, `client_phone`, `client_gender`, `room_id`, `building`, `room_name`, `check_in_date`, `number_of_days`, `price`, `expected_check_out`, `status`) VALUES
(13, 'kibet', '9865', 'Male', 65, 'Runda', 'LTE 2', '2025-04-18', 3, 500.00, '0000-00-00', 'checked-out'),
(14, 'kibet', '9865', 'Male', 66, 'Runda', 'LTE 3', '2025-04-10', 4, 500.00, '0000-00-00', 'checked-out'),
(20, 'kibet', '9865', 'Male', 65, 'Runda', 'LTE 3', '2025-04-05', 3, 15.00, '0000-00-00', 'checked-in'),
(21, 'kibet', '9865', 'Female', 66, 'Runda', 'LTE 2', '2025-04-12', 2, 20.00, '0000-00-00', 'checked-out'),
(23, 'Geofrey', '09876543', 'Female', 86, 'Runda2', '0', '2025-04-11', 3, 1200.00, '2025-04-14', 'checked-out');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_phone` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `seller_id`, `client_name`, `client_phone`, `payment_method`, `amount_paid`, `balance`, `total`, `created_at`) VALUES
(1, 3, 'John Doe', '+254701234567', 'Credit Card', 150.00, 50.00, 200.00, '2025-04-06 20:50:54'),
(2, 3, 'Jane Smith', '+254709876543', 'Cash', 200.00, 0.00, 200.00, '2025-04-06 20:50:54'),
(12, 1, 'kibet', '9860', 'mobile', 65.00, 55.00, 10.00, '2025-04-10 13:39:52'),
(22, 1, 'kibet', '9865', 'mobile', 67.00, 17.00, 50.00, '2025-04-10 14:09:39'),
(23, 1, 'kibet', '9865', 'mobile', 67.00, 17.00, 50.00, '2025-04-10 14:10:47'),
(24, 1, 'kibet', '9865', 'bank', 10.00, 0.00, 10.00, '2025-04-10 14:11:13'),
(25, 1, 'kibet', '9865', 'cash', 76.00, 61.00, 15.00, '2025-04-10 14:24:11'),
(26, 1, 'kibet', '9865', 'mobile', 50.00, 35.00, 15.00, '2025-04-10 14:36:26'),
(27, 1, 'kibet', '9865', 'mobile', 50.00, 35.00, 15.00, '2025-04-10 14:42:37'),
(28, 1, 'kibet', '9865', 'mobile', 50.00, 35.00, 15.00, '2025-04-10 14:43:02'),
(29, 1, 'kibet', '9865', 'mobile', 50.00, 35.00, 15.00, '2025-04-10 14:44:22'),
(30, 1, 'kibet', '9865', 'cash', 50.00, 0.00, 50.00, '2025-04-10 14:45:16'),
(31, 1, 'kibet', '9865', 'cash', 50.00, 0.00, 50.00, '2025-04-10 14:53:49'),
(32, 3, 'kibet', '9865', 'bank', 400.00, 0.00, 400.00, '2025-04-10 14:58:28'),
(33, 1, 'kibet', '9865', 'bank', 65.00, 5.00, 60.00, '2025-04-10 15:34:35'),
(34, 1, 'kibet', '9865', 'bank', 65.00, 55.00, 10.00, '2025-04-10 15:37:36'),
(35, 3, 'kibet', '9865', 'bank', 67.00, 52.00, 15.00, '2025-04-10 15:41:02'),
(36, 1, 'kibet', '9865', 'bank', 67.00, 42.00, 25.00, '2025-04-11 09:37:44'),
(37, 1, 'kibet', '9865', 'bank', 56.00, 46.00, 10.00, '2025-04-11 09:56:41'),
(38, 1, 'kibet', '9865', 'bank', 89.00, 79.00, 10.00, '2025-04-11 09:58:37'),
(39, 1, 'kibet', '9865', 'bank', 653.00, 593.00, 60.00, '2025-04-11 10:00:40'),
(40, 1, 'kibet', '9865', 'bank', 567.00, 232.00, 335.00, '2025-04-11 10:02:42'),
(41, 1, 'kibet', '9865', 'cash', 90.00, 15.00, 75.00, '2025-04-11 10:09:32'),
(42, 1, 'kibet', '9865', 'bank', 76.00, 51.00, 25.00, '2025-04-11 10:11:04'),
(43, 1, 'kibet', '9865', 'bank', 654.00, 629.00, 25.00, '2025-04-11 10:12:44'),
(44, 1, 'kibet', '9865', 'cash', 76.00, 51.00, 25.00, '2025-04-11 10:13:45'),
(45, 3, 'kibet', '9865', 'bank', 67.00, 52.00, 15.00, '2025-04-11 10:14:54'),
(46, 1, 'kibet', '9865', 'cash', 67.00, 57.00, 10.00, '2025-04-11 10:16:04'),
(47, 3, 'kibet', '9865', 'cash', 566.00, 491.00, 75.00, '2025-04-11 10:24:30'),
(48, 3, 'kibet', '9865', 'cash', 67.00, 57.00, 10.00, '2025-04-11 10:27:19'),
(49, 3, 'kibet', '9865', 'cash', 67.00, 7.00, 60.00, '2025-04-11 10:27:31'),
(50, 3, 'kibet', '9865', 'cash', 670.00, 500.00, 170.00, '2025-04-11 10:27:43'),
(51, 3, 'kibet', '9865', 'mobile', 125.00, 0.00, 125.00, '2025-04-11 10:31:35'),
(52, 3, 'kibet', '9865', 'cash', 67.00, 42.00, 25.00, '2025-04-11 10:36:46'),
(53, 1, 'kibet', '9865', 'mobile', 560.00, 500.00, 60.00, '2025-04-11 10:38:10'),
(54, 3, 'kibet', '9865', 'cash', 67.00, 42.00, 25.00, '2025-04-11 10:52:06'),
(55, 1, 'kibet', '9865', 'cash', 250.00, 225.00, 25.00, '2025-04-11 10:59:56'),
(56, 1, 'kibet', '9865', 'mobile', 76.00, 51.00, 25.00, '2025-04-11 11:04:58'),
(57, 3, 'Eduu', '09876543', 'cash', 425.00, 0.00, 425.00, '2025-04-11 19:33:35'),
(58, 1, 'jkl', '9865', 'mobile', 240.00, 0.00, 240.00, '2025-04-11 19:36:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES
(12, 12, 10, '', 1, 10.00),
(30, 22, 11, '', 1, 50.00),
(31, 23, 11, '', 1, 50.00),
(32, 24, 10, '', 1, 10.00),
(33, 25, 9, '', 1, 15.00),
(34, 26, 9, '', 1, 15.00),
(35, 27, 9, '', 1, 15.00),
(36, 28, 9, '', 1, 15.00),
(37, 29, 9, '', 1, 15.00),
(38, 30, 11, '', 1, 50.00),
(39, 31, 11, '', 1, 50.00),
(40, 32, 11, '', 8, 50.00),
(41, 33, 11, '', 1, 50.00),
(42, 33, 10, '', 1, 10.00),
(43, 34, 10, '', 1, 10.00),
(44, 35, 9, '', 1, 15.00),
(45, 36, 9, '', 1, 15.00),
(46, 36, 10, '', 1, 10.00),
(47, 37, 10, '', 1, 10.00),
(48, 38, 10, '', 1, 10.00),
(49, 39, 10, '', 1, 10.00),
(50, 39, 11, '', 1, 50.00),
(51, 40, 10, '', 1, 10.00),
(52, 40, 9, '', 5, 15.00),
(53, 40, 11, '', 5, 50.00),
(54, 41, 11, '', 1, 50.00),
(55, 41, 10, '', 1, 10.00),
(56, 41, 9, '', 1, 15.00),
(57, 42, 9, '', 1, 15.00),
(58, 42, 10, '', 1, 10.00),
(59, 43, 10, '', 1, 10.00),
(60, 43, 9, '', 1, 15.00),
(61, 44, 10, '', 1, 10.00),
(62, 44, 9, '', 1, 15.00),
(63, 45, 9, '', 1, 15.00),
(64, 46, 10, '', 1, 10.00),
(65, 47, 10, '', 1, 10.00),
(66, 47, 9, '', 1, 15.00),
(67, 47, 11, '', 1, 50.00),
(68, 48, 10, '', 1, 10.00),
(69, 49, 10, '', 1, 10.00),
(70, 49, 11, '', 1, 50.00),
(71, 50, 10, '', 1, 10.00),
(72, 50, 11, '', 2, 50.00),
(73, 50, 9, '', 4, 15.00),
(74, 51, 10, '', 6, 10.00),
(75, 51, 9, '', 1, 15.00),
(76, 51, 11, '', 1, 50.00),
(77, 52, 10, '', 1, 10.00),
(78, 52, 9, '', 1, 15.00),
(79, 53, 11, '', 1, 50.00),
(80, 53, 10, '', 1, 10.00),
(81, 54, 10, '', 1, 10.00),
(82, 54, 9, '', 1, 15.00),
(83, 55, 9, '', 1, 15.00),
(84, 55, 10, '', 1, 10.00),
(85, 56, 9, '', 1, 15.00),
(86, 56, 10, '', 1, 10.00),
(87, 57, 9, '', 3, 15.00),
(88, 57, 10, '', 3, 10.00),
(89, 57, 11, '', 7, 50.00),
(90, 58, 14, '', 4, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('food','drinks','services','amenities') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `price`, `image_url`, `quantity`, `created_at`, `updated_at`) VALUES
(9, 'Toiletry Kit', '0', 'drinks', 15.00, 'img/products/product_67f2aac8879f9.jpeg', 268, '2025-04-03 11:47:18', '2025-04-11 17:33:35'),
(10, 'Hotel Slippers', '0', 'drinks', 10.00, 'img/products/prod_67f2b5de99334.jpeg', 450, '2025-04-03 11:47:18', '2025-04-11 17:33:35'),
(11, 'Samosa', '0', 'drinks', 50.00, 'img/products/prod_67f2b563b50b9.jpeg', 67, '2025-04-06 15:56:37', '2025-04-11 17:50:16'),
(14, 'Sprit', '0', 'drinks', 60.00, 'img/products/prod_67f9530827ad4.jpeg', 96, '2025-04-11 17:36:08', '2025-04-11 17:36:42');

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `id` int(11) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `building` varchar(100) NOT NULL,
  `room_capacity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `room_category_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`id`, `room_name`, `building`, `room_capacity`, `price`, `room_category_id`, `image`) VALUES
(65, 'LTE 1', 'Runda', 2, 5.00, 8, 'single.jpeg'),
(66, 'LTE 2', 'Runda', 4, 10.00, 8, 'single.jpeg'),
(67, 'LTE 3', 'Runda', 2, 8.00, 8, 'single.jpeg'),
(86, 'Block A 1', 'Runda2', 3, 400.00, 10, 'bedsitter.jpeg'),
(87, 'Block A 2', 'Runda2', 3, 400.00, 10, 'bedsitter.jpeg'),
(88, 'Block A 3', 'Runda2', 3, 400.00, 10, 'bedsitter.jpeg'),
(89, 'Block A 4', 'Runda2', 3, 400.00, 10, 'bedsitter.jpeg'),
(90, 'Block A 5', 'Runda2', 3, 400.00, 10, 'bedsitter.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `room_category`
--

CREATE TABLE `room_category` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_category`
--

INSERT INTO `room_category` (`id`, `category`) VALUES
(7, 'Singles'),
(8, 'Bedsitters'),
(9, '2 Bedroom'),
(10, '3 Bedroom');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(20) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `position`, `email`, `username`, `phone`, `password`) VALUES
(1, 'John Doe', 'Managers', 'johndoe@example.com', 'admin', '123456789', '$2y$10$MgEImpx0PunL/zlZ./r3ZO29QPgOjqkAK6TjKpbym2.9bPNT/812m'),
(3, 'Edward Njambi Kariuki', '456', 'edward.kariuki@piu.ac.ke', 'user', '0721887579', '$2y$10$6icblsVGrV27OxQIq7Ve/uARusqQ2QnzL4OmvOJOzwSRQ7/Dsjr9m');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_seller` (`seller_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_room_building` (`room_name`,`building`),
  ADD KEY `room_category_id` (`room_category_id`);

--
-- Indexes for table `room_category`
--
ALTER TABLE `room_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `room_category`
--
ALTER TABLE `room_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_seller` FOREIGN KEY (`seller_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room`
--
ALTER TABLE `room`
  ADD CONSTRAINT `room_ibfk_1` FOREIGN KEY (`room_category_id`) REFERENCES `room_category` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
