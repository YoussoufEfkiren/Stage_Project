-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2025 at 10:21 AM
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
-- Database: `stock_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(8, 'Laptops', 'Various types of laptops including gaming, business, and personal laptops'),
(9, 'Printers', 'Printers for home and office use, including laser and inkjet printers'),
(10, 'Keyboards', 'Mechanical and membrane keyboards for desktop and laptop use'),
(11, 'Mice', 'Wired and wireless computer mice for different usage'),
(12, 'Monitors', 'Computer monitors including LED, LCD, and 4K models'),
(13, 'Accessories', 'Various computer accessories including laptop bags, cables, etc.'),
(14, 'Networking', 'Networking devices including routers, switches, and modems');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` varchar(50) DEFAULT NULL,
  `buy_price` decimal(25,2) DEFAULT NULL,
  `sale_price` decimal(25,2) NOT NULL,
  `categorie_id` int(11) UNSIGNED NOT NULL,
  `date` datetime NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `supplier_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `quantity`, `buy_price`, `sale_price`, `categorie_id`, `date`, `file_name`, `supplier_id`) VALUES
(64, 'USB-C Cable U1', '200', 10.00, 20.00, 13, '2025-01-21 23:10:52', 'usb_c_cable_u1.jpg', 11),
(65, 'USB-C Cable U2', '180', 12.00, 24.00, 13, '2025-01-21 23:10:52', 'usb_c_cable_u2.jpg', 12),
(66, 'Router Model R1', '50', 30.00, 60.00, 14, '2025-01-21 23:10:52', 'router_model_r1.jpg', 13),
(67, 'Router Model R2', '70', 40.00, 80.00, 14, '2025-01-21 23:10:52', 'router_model_r2.jpg', 14),
(68, 'WiFi Extender W1', '100', 20.00, 40.00, 14, '2025-01-21 23:10:52', 'wifi_extender_w1.jpg', 15),
(69, 'WiFi Extender W2', '120', 25.00, 50.00, 14, '2025-01-21 23:10:52', 'wifi_extender_w2.jpg', 16),
(70, 'Laptop Stand S1', '150', 15.00, 30.00, 13, '2025-01-21 23:10:52', 'laptop_stand_s1.jpg', 17),
(71, 'Laptop Stand S2', '130', 20.00, 40.00, 13, '2025-01-21 23:10:52', 'laptop_stand_s2.jpg', 18),
(72, 'External Hard Drive H1', '80', 80.00, 160.00, 13, '2025-01-21 23:10:52', 'external_hard_drive_h1.jpg', 19),
(73, 'External Hard Drive H2', '60', 100.00, 200.00, 13, '2025-01-21 23:10:52', 'external_hard_drive_h2.jpg', 20),
(74, 'Wireless Headphones H1', '100', 50.00, 100.00, 13, '2025-01-21 23:10:52', 'wireless_headphones_h1.jpg', 11),
(75, 'Wireless Headphones H2', '120', 60.00, 120.00, 13, '2025-01-21 23:10:52', 'wireless_headphones_h2.jpg', 12),
(76, 'Laptop Cooler C1', '150', 25.00, 50.00, 13, '2025-01-21 23:10:52', 'laptop_cooler_c1.jpg', 13),
(77, 'Laptop Cooler C2', '180', 30.00, 60.00, 13, '2025-01-21 23:10:52', 'laptop_cooler_c2.jpg', 14),
(78, 'Smartwatch S1', '200', 50.00, 100.00, 13, '2025-01-21 23:10:52', 'smartwatch_s1.jpg', 15);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(25,2) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `qty`, `price`, `date`) VALUES
(89, 64, 5, 100.00, '2022-03-15'),
(90, 65, 8, 192.00, '2022-07-18'),
(91, 66, 3, 180.00, '2023-01-22'),
(92, 67, 4, 320.00, '2023-04-30'),
(93, 68, 7, 280.00, '2023-08-05'),
(94, 69, 10, 500.00, '2023-10-19'),
(95, 70, 6, 180.00, '2023-12-25'),
(96, 71, 9, 360.00, '2024-02-14'),
(97, 72, 4, 640.00, '2024-04-22'),
(98, 73, 2, 400.00, '2024-06-10'),
(99, 74, 5, 500.00, '2024-08-01'),
(100, 75, 8, 960.00, '2024-10-15'),
(101, 76, 10, 400.00, '2024-11-30'),
(102, 77, 12, 720.00, '2025-01-05'),
(103, 78, 15, 1500.00, '2025-02-12'),
(104, 64, 20, 400.00, '2022-05-10'),
(105, 65, 25, 600.00, '2023-03-20'),
(106, 66, 8, 480.00, '2024-07-05'),
(107, 67, 3, 240.00, '2025-01-20'),
(108, 68, 6, 360.00, '2025-01-30');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `contact`, `address`, `created_at`) VALUES
(11, 'TechSupplies A', 'techsuppliesA@example.com', '123-456-7890', '123 Tech St', '2025-01-21 21:57:12'),
(12, 'TechSupplies B', 'techsuppliesB@example.com', '098-765-4321', '456 IT Ave', '2025-01-21 21:57:12'),
(13, 'TechSupplies C', 'techsuppliesC@example.com', '567-890-1234', '789 Gadget Blvd', '2025-01-21 21:57:12'),
(14, 'TechSupplies D', 'techsuppliesD@example.com', '678-901-2345', '101 Digital Rd', '2025-01-21 21:57:12'),
(15, 'TechSupplies E', 'techsuppliesE@example.com', '789-012-3456', '202 Silicon St', '2025-01-21 21:57:12'),
(16, 'TechSupplies F', 'techsuppliesF@example.com', '890-123-4567', '303 Circuit St', '2025-01-21 21:57:12'),
(17, 'TechSupplies G', 'techsuppliesG@example.com', '901-234-5678', '404 Network Dr', '2025-01-21 21:57:12'),
(18, 'TechSupplies H', 'techsuppliesH@example.com', '234-567-8901', '505 Byte St', '2025-01-21 21:57:12'),
(19, 'TechSupplies I', 'techsuppliesI@example.com', '345-678-9012', '606 Digital Way', '2025-01-21 21:57:12'),
(20, 'TechSupplies J', 'techsuppliesJ@example.com', '456-789-0123', '707 Technology Blvd', '2025-01-21 21:57:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_level` int(11) UNSIGNED NOT NULL,
  `image` varchar(255) DEFAULT 'no_image.jpg',
  `status` int(1) NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `user_level`, `image`, `status`, `last_login`) VALUES
(1, 'John Doe', 'admin', '$2y$10$JeZi6WnsQeGqtAHUo.ebQ.KZC9zHW3mrGa6VI2RbnUFGxr5Mfjkhy', 1, 'no_image.jpg', 1, '2025-01-22 10:15:11'),
(2, 'Jane Smith', 'manager', '$2y$10$NpdpkTGVqjvTtZzQm4B60ODwX6atKpOJoBwcUePCgJ.760ci6DShW', 2, 'no_image.jpg', 1, NULL),
(3, 'Youssouf', 'user', '$2y$10$87IpdleFa7NWTv2r.NcZDO35gxPKQnruz3P6MhjRe18j1yA64Ra5i', 3, 'no_image.jpg', 1, '2025-01-21 23:17:36');

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_level` int(11) UNSIGNED NOT NULL,
  `group_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `group_name`, `group_level`, `group_status`) VALUES
(1, 'Admin', 1, 1),
(2, 'Manager', 2, 1),
(3, 'User', 3, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_level` (`user_level`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_level` (`group_level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `FK_products` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `FK_sales_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `SK` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_user` FOREIGN KEY (`user_level`) REFERENCES `user_groups` (`group_level`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
