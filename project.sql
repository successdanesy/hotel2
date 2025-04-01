-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 11:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--
CREATE DATABASE IF NOT EXISTS `project` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `project`;

-- --------------------------------------------------------

--
-- Table structure for table `bar_orders`
--

CREATE TABLE `bar_orders` (
  `id` int(11) NOT NULL,
  `room_number` varchar(255) DEFAULT NULL,
  `order_description` text NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `timestamp` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `guest_id` int(11) DEFAULT NULL,
  `guest_type` varchar(20) DEFAULT 'guest',
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `payment_status` enum('Pay Now','Pay at Checkout') NOT NULL,
  `payment_method` enum('Cash','POS') NOT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `guest_id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `total_paid` decimal(10,2) DEFAULT NULL,
  `total_room_charges` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`) VALUES
(1, 'BREAKFAST'),
(2, 'ASSORTED RICE'),
(3, 'PASTA'),
(4, 'NOODLES'),
(5, 'SALADS'),
(6, 'SAUCE'),
(7, 'SWALLOWS'),
(8, 'SOUP'),
(9, 'PEPPER SOUP'),
(10, 'LOCAL DELICACIES'),
(11, 'FRIES'),
(12, 'PROTEINS'),
(13, 'SHAWARMA'),
(14, 'TEA'),
(15, 'GRILLS');

-- --------------------------------------------------------

--
-- Table structure for table `categories_bar`
--

CREATE TABLE `categories_bar` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories_bar`
--

INSERT INTO `categories_bar` (`id`, `category_name`) VALUES
(1, 'SOFT DRINKS'),
(2, 'ENERGY DRINKS'),
(3, 'ALCOHOLIC DRINKS'),
(4, 'WHISKEY'),
(5, 'COGNAC'),
(6, 'LIQUOR'),
(7, 'VODKA'),
(8, 'GIN'),
(9, 'RUM'),
(10, 'CHAMPAGNE'),
(11, 'ALCOHOLIC WINE'),
(12, 'NON ALCOHOLIC WINE');

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `guest_id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `imprest_requests`
--

CREATE TABLE `imprest_requests` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `price` decimal(10,2) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `imprest_requests_bar`
--

CREATE TABLE `imprest_requests_bar` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `price` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_orders`
--

CREATE TABLE `kitchen_orders` (
  `id` int(11) NOT NULL,
  `room_number` varchar(255) DEFAULT NULL,
  `order_description` text NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `timestamp` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `guest_id` int(11) DEFAULT NULL,
  `guest_type` varchar(20) DEFAULT 'guest',
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `category_id`) VALUES
(4, 'English Breakfast', 1),
(5, 'French Breakfast', 1),
(6, 'American Breakfast', 1),
(7, 'Tacos', 1),
(8, 'Fettuccine Alfredo', 1),
(9, 'Egg Sandwich', 1),
(10, 'Tomato with 3 eggs', 1),
(11, 'Jollof Rice', 2),
(12, 'White Rice', 2),
(13, 'Basmatics White Rice', 2),
(14, 'Fried Rice Coleslaw', 2),
(15, 'Chinese Fried Rice', 2),
(16, 'Coconut Rice', 2),
(17, 'Palm Oil Rice/Dry Fish', 2),
(18, 'Spaghetti Jollof', 2),
(19, 'Porridge Beans/Plantain', 2),
(20, 'Porridge Yam/Dry Fish', 2),
(21, 'Jambalaya Rice', 2),
(22, 'French Rice', 2),
(23, 'Colombia Rice', 2),
(24, 'Ghana Jollof Rice', 2),
(25, 'Egg Rice', 2),
(26, 'Egg Rice Special', 2),
(27, 'Fried Rice Special', 2),
(28, 'Ofada Rice/Sauce', 2),
(29, 'Fried Rice Local', 2),
(30, 'Jollof Spaghetti', 3),
(31, 'Village Spaghetti', 3),
(32, 'Spaghetti BTC', 3),
(33, 'Chicken Spaghetti', 3),
(34, 'Mix Spaghetti with Extra Vegetables', 3),
(35, 'Spaghetti Bonolice', 3),
(36, 'Seed Food Pasta', 3),
(37, 'Sengapol Noodles', 4),
(38, 'Indomie Chicken', 4),
(39, 'Vegetable Noodles', 4),
(40, 'Plain Noodles', 4),
(41, 'Indomie with Sausage', 4),
(42, 'Chicken Salad', 5),
(43, 'Chicken Avocado', 5),
(44, 'Salad', 5),
(45, 'Russian Salad', 5),
(46, 'Mix Broccoli Salad', 5),
(47, 'Fish Salad', 5),
(48, 'Fruit Salad', 5),
(49, 'Egg Sauce', 6),
(50, 'Red Oil Sauce', 6),
(51, 'Vegetable Sauce', 6),
(52, 'Chinese Sauce', 6),
(53, 'Chicken Sauce', 6),
(54, 'Goat Meat Sauce', 6),
(55, 'Fish Sauce', 6),
(56, 'Fried Egg', 6),
(57, 'Bonolice Sauce', 6),
(58, 'Poundo', 7),
(59, 'Garri', 7),
(60, 'Semovita', 7),
(61, 'Wheat', 7),
(62, 'Amala', 7),
(63, 'Plantain Flour', 7),
(64, 'Pounded Yam', 7),
(65, 'Banga Soup with Dry Fish', 8),
(66, 'Oha Soup', 8),
(67, 'Afang Soup', 8),
(68, 'White Soup/Fresh Fish', 8),
(69, 'White Soup with Chicken', 8),
(70, 'Egusi Soup', 8),
(71, 'Ogbanno Soup', 8),
(72, 'Bitter Leaf Soup', 8),
(73, 'Vegetable Soup', 8),
(74, 'Okra Soup', 8),
(75, 'Sea Food Okra', 8),
(76, 'Ewedu Soup', 8),
(77, 'Catfish Pepper Soup', 9),
(78, 'Croacker Fish Pepper Soup', 9),
(79, 'Tilapia Fish Pepper Soup', 9),
(80, 'Assorted', 9),
(81, 'Cow Leg Pepper Soup', 9),
(82, 'Chicken Pepper Soup', 9),
(83, 'Goat Meat Pepper Soup', 9),
(84, 'Full Goat Head Pepper Soup', 9),
(85, 'Isiewu', 10),
(86, 'Nkwobi', 10),
(87, 'Abacha', 10),
(88, 'Chicken Vegetables (Full Chicken)', 10),
(89, 'Ugba & Kpomo', 10),
(90, 'Isiewu', 10),
(91, 'Nkwobi', 10),
(92, 'Abacha', 10),
(93, 'Chicken Vegetables (Full Chicken)', 10),
(94, 'Ugba & Kpomo', 10),
(95, 'Fried Yam', 11),
(96, 'Fried Chips', 11),
(97, 'Salted Potato (Small)', 11),
(98, 'Salted Potato (Large)', 11),
(99, 'Shredded Chicken', 11),
(100, 'Fried Plantains', 11),
(101, 'Pepper Beef', 12),
(102, 'Pepper Goat', 12),
(103, 'Pepper Chicken', 12),
(104, 'Pepper Snail', 12),
(105, 'Village Ram Chop', 12),
(106, 'Pepper Titus Fish', 12),
(107, 'Sallah Ram', 12),
(108, 'Village Fish', 12),
(109, 'Honey BBQ Turkey', 12),
(110, 'Honey BBQ Chicken', 12),
(111, 'Pepper Turkey', 12),
(112, 'Pepper Chicken Drown Stick', 12),
(113, 'Pepper Croacker (Medium)', 12),
(114, 'Pepper Croacker (Large)', 12),
(115, 'Pepper Tilapia', 12),
(116, 'Chicken Shawarma', 13),
(117, 'Beef Shawarma', 13),
(118, 'Fish Shawarma', 13),
(119, 'Vegetable Roll', 13),
(120, 'Arabian Tea', 14),
(121, 'Lemon Tea', 14),
(122, 'Green Tea', 14),
(123, 'Nice Coffee', 14),
(124, 'Fresh Ginger Tea', 14),
(125, 'Ginger Your Swagger', 14),
(126, 'Clear Throat', 14),
(127, 'Lipton Tea', 14),
(128, 'Catfish Barbeque (Medium)', 15),
(129, 'Catfish Barbeque (Large)', 15),
(130, 'Catfish Barbeque (Extra Large)', 15),
(131, 'Croacker Fish Barbeque (Medium)', 15),
(132, 'Croacker Fish Barbeque (Large)', 15),
(133, 'Croacker Fish Barbeque (Extra Large)', 15),
(134, 'Tilapia Fish (Medium)', 15),
(135, 'Tilapia Fish (Large)', 15),
(136, 'Tilapia Fish (Extra Large)', 15),
(137, 'Titus Fish Grill', 15),
(138, 'Chicken Barbeque', 15),
(139, 'Grill Turkey Wings', 15),
(140, 'Yogi Grill Chicken', 15),
(141, 'Chicken Pampelimpe', 15),
(142, 'Ram Suya', 15),
(143, 'Grill Chicken Drown Stick', 15),
(144, 'Dry Meat Balls', 15),
(145, 'Dry Fish Balls', 15);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items_bar`
--

CREATE TABLE `menu_items_bar` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items_bar`
--

INSERT INTO `menu_items_bar` (`id`, `name`, `price`, `category_id`) VALUES
(1, 'Bottle Water', 500.00, 1),
(2, 'Coke', 1000.00, 1),
(3, 'Fanta', 1000.00, 1),
(4, 'Sprite', 1000.00, 1),
(5, 'Malt', 2000.00, 1),
(6, 'Fayrouz', 1000.00, 1),
(7, 'Schweppes', 1000.00, 1),
(8, 'Hollandia Yoghurt', 3000.00, 1),
(9, 'Chivita Action', 3000.00, 1),
(10, 'Chi Exotic', 3000.00, 1),
(11, 'Monster', 2500.00, 2),
(12, 'Power Horse', 2000.00, 2),
(13, 'Double Black', 2500.00, 2),
(14, 'Black Bullet', 2500.00, 2),
(15, 'Smirnoff Ice', 2000.00, 2),
(16, 'Origin Bitter', 2500.00, 3),
(17, 'Odogwu Bitter', 2500.00, 3),
(18, 'Desperados', 2000.00, 3),
(19, 'Guinness', 2500.00, 3),
(20, 'Heineken', 2500.00, 3),
(21, 'Legend', 2500.00, 3),
(22, 'Hero', 1500.00, 3),
(23, 'Trophy', 1500.00, 3),
(24, 'Goldberg', 1500.00, 3),
(25, 'Life', 1500.00, 3),
(26, 'Johnnie Walker Black Label', 40000.00, 4),
(27, 'Johnnie Walker Red Label', 25000.00, 4),
(28, 'Jameson', 35000.00, 4),
(29, 'Jameson Black Barrel', 45000.00, 4),
(30, 'Jack Daniels', 78000.00, 4),
(31, 'Imperial Blue', 15000.00, 4),
(32, 'Glenfiddich 12 years', 75000.00, 4),
(33, 'Glenfiddich 15 years', 85000.00, 4),
(34, 'Glenfiddich 18 years', 185000.00, 4),
(35, 'Chivas Regal', 85000.00, 4),
(36, 'Hennessy VS', 130000.00, 5),
(37, 'Hennessy VSOP', 180000.00, 5),
(38, 'Remy Martin', 70000.00, 5),
(39, 'St Martin VSOP', 20000.00, 5),
(40, 'Baileys', 25000.00, 6),
(41, 'Amarula', 25000.00, 6),
(42, 'Campari Small', 12000.00, 6),
(43, 'Campari Big', 35000.00, 6),
(44, 'American Honey', 45000.00, 6),
(45, 'William Lawson', 25000.00, 6),
(46, 'Gray Goose', 70000.00, 7),
(47, 'Absolut', 30000.00, 7),
(48, 'Ciroc', 40000.00, 7),
(49, 'Smirnoff XI', 15000.00, 7),
(50, 'Skyy', 45000.00, 7),
(51, 'Gordon', 15000.00, 8),
(52, 'Bombay Sapphire', 45000.00, 8),
(53, 'Tangueray', 40000.00, 8),
(54, 'Captain Morgan', 20000.00, 9),
(55, 'Barcadi', 30000.00, 9),
(56, 'Malibu', 20000.00, 9),
(57, 'Andre Rossi', 28000.00, 10),
(58, 'Andre Brut', 25000.00, 10),
(59, 'Andre Moscato', 30000.00, 10),
(60, '4 Cousins Rose', 15000.00, 10),
(61, 'Baron de Vall', 15000.00, 11),
(62, 'Agor', 22000.00, 11),
(63, 'Drostdy HOF', 25000.00, 11),
(64, 'Carlo Rossi', 20000.00, 11),
(65, '4th Street', 20000.00, 11),
(66, '4 Cousins', 20000.00, 11),
(67, 'Belair Rose', 30000.00, 11),
(68, 'B & G', 18000.00, 11),
(69, 'Chateau Neuf', 45000.00, 11),
(70, 'Moet Chandon', 35000.00, 12),
(71, 'Pure Heaven', 12000.00, 12),
(72, 'Matineli', 35000.00, 12),
(73, 'Eva', 15000.00, 12),
(74, 'Don Morris', 15000.00, 12),
(75, 'Chocolate', 35000.00, 12),
(76, 'J&W', 15000.00, 12),
(77, 'Martini ASTI', 30000.00, 12),
(78, 'Robertson Chapel', 15000.00, 12),
(79, 'XXIV Karat', 40000.00, 12);

-- --------------------------------------------------------

--
-- Table structure for table `other_imprests`
--

CREATE TABLE `other_imprests` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `price` decimal(10,2) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(255) DEFAULT NULL,
  `room_type` varchar(20) NOT NULL,
  `status` enum('Available','Occupied','Under Maintenance') DEFAULT 'Available',
  `weekday_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `weekend_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `guest_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `status`, `weekday_price`, `weekend_price`, `guest_id`, `guest_name`) VALUES
(1, '206', 'Standard', 'Available', 45000.00, 37500.00, NULL, NULL),
(2, '303', 'Standard', 'Available', 45000.00, 37500.00, NULL, NULL),
(3, '101', 'Executive', 'Available', 55000.00, 47500.00, NULL, NULL),
(4, '103', 'Executive', 'Available', 55000.00, 47500.00, NULL, NULL),
(5, '201', 'Executive', 'Available', 55000.00, 47500.00, NULL, NULL),
(6, '202', 'Executive', 'Available', 55000.00, 47500.00, NULL, NULL),
(7, '205', 'Executive', 'Available', 55000.00, 47500.00, NULL, NULL),
(8, '207', 'Executive', 'Available', 55000.00, 47500.00, NULL, NULL),
(9, '304', 'Executive', 'Available', 55000.00, 47500.00, NULL, NULL),
(10, '204', 'Executive', 'Available', 55000.00, 47500.00, NULL, NULL),
(11, '102', 'Luxury', 'Available', 75000.00, 67500.00, NULL, NULL),
(12, '203', 'Luxury', 'Available', 75000.00, 67500.00, NULL, NULL),
(13, '208', 'Luxury', 'Available', 75000.00, 67500.00, NULL, NULL),
(14, '301', 'Luxury', 'Available', 75000.00, 67500.00, NULL, NULL),
(15, '302', 'Luxury', 'Available', 75000.00, 67500.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kitchen','bar','manager') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', 'admin@antilla.com', '$2b$12$1DdTh0SmJvVIer5AamgPKuVZW/4epbIJqHMvVpYmtXBp1HtOa3McW', 'admin'),
(2, 'kitchen', 'kitchen@antilla.com', '$2b$12$X94E47Wmk9YzDb4o7hwt4O8tou/QwCzr/S8ss.as5E6mpE70zhL8K', 'kitchen'),
(3, 'bar', 'bar@antilla.com', '$2b$12$GOQZB3SbrCQSKA7Y8fza.OXediXrXbu6HosGVzilqVxaO6dy6hx5S', 'bar'),
(4, 'manager', 'manager@antilla.com', '$2b$12$Tns/.5Iu5xNdI1Odgf.UIesa/zomn/k9VKWBTfFDmpBh9ggUXmcSW', 'manager');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bar_orders`
--
ALTER TABLE `bar_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `room_number` (`room_number`),
  ADD KEY `guest_id` (`guest_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories_bar`
--
ALTER TABLE `categories_bar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`guest_id`);

--
-- Indexes for table `imprest_requests`
--
ALTER TABLE `imprest_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `imprest_requests_bar`
--
ALTER TABLE `imprest_requests_bar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kitchen_orders`
--
ALTER TABLE `kitchen_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guest_id` (`guest_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items_bar`
--
ALTER TABLE `menu_items_bar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `other_imprests`
--
ALTER TABLE `other_imprests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bar_orders`
--
ALTER TABLE `bar_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=203;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categories_bar`
--
ALTER TABLE `categories_bar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `guest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `imprest_requests`
--
ALTER TABLE `imprest_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `imprest_requests_bar`
--
ALTER TABLE `imprest_requests_bar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kitchen_orders`
--
ALTER TABLE `kitchen_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `menu_items_bar`
--
ALTER TABLE `menu_items_bar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `other_imprests`
--
ALTER TABLE `other_imprests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_number`) REFERENCES `rooms` (`room_number`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`guest_id`);

--
-- Constraints for table `menu_items_bar`
--
ALTER TABLE `menu_items_bar`
  ADD CONSTRAINT `menu_items_bar_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories_bar` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
