-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 24 jan. 2025 à 15:28
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `stock_system`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(15, 'Laptops', 'Various types of laptops including gaming, business, and personal laptops'),
(16, 'Printers', 'Printers for home and office use, including laser and inkjet printers'),
(17, 'Keyboards', 'Mechanical and membrane keyboards for desktop and laptop use'),
(18, 'Mice', 'Wired and wireless computer mice for different usage'),
(19, 'Monitors', 'Computer monitors including LED, LCD, and 4K models'),
(20, 'Accessories', 'Various computer accessories including laptop bags, cables, etc.'),
(21, 'Networking', 'Networking devices including routers, switches, and modems');

-- --------------------------------------------------------

--
-- Structure de la table `products`
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
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `quantity`, `buy_price`, `sale_price`, `categorie_id`, `date`, `file_name`, `supplier_id`) VALUES
(127, 'Gaming Laptop GL-500', '55', 1200.00, 1500.00, 15, '2025-01-22 11:00:00', 'gaming_laptop_gl500.jpg', 11),
(128, 'Business Laptop BL-300', '40', 900.00, 1200.00, 15, '2025-01-22 11:00:00', 'business_laptop_bl300.jpg', 12),
(129, 'Personal Laptop PL-200', '60', 500.00, 700.00, 15, '2025-01-22 11:00:00', 'personal_laptop_pl200.jpg', 13),
(130, 'Ultra-thin Laptop UT-700', '30', 1000.00, 1300.00, 15, '2025-01-22 11:00:00', 'ultra_thin_laptop_ut700.jpg', 14),
(131, 'Laser Printer LP-10', '70', 150.00, 250.00, 16, '2025-01-22 11:00:00', 'laser_printer_lp10.jpg', 15),
(132, 'Inkjet Printer IP-20', '80', 100.00, 180.00, 16, '2025-01-22 11:00:00', 'inkjet_printer_ip20.jpg', 16),
(133, 'All-in-One Printer AIO-30', '40', 200.00, 350.00, 16, '2025-01-22 11:00:00', 'all_in_one_printer_aio30.jpg', 17),
(134, 'Mechanical Keyboard MK-100', '100', 50.00, 80.00, 17, '2025-01-22 11:00:00', 'mechanical_keyboard_mk100.jpg', 18),
(135, 'Membrane Keyboard MB-200', '120', 20.00, 40.00, 17, '2025-01-22 11:00:00', 'membrane_keyboard_mb200.jpg', 19),
(136, 'RGB Keyboard RGB-300', '90', 70.00, 120.00, 17, '2025-01-22 11:00:00', 'rgb_keyboard_rgb300.jpg', 20),
(137, 'Wireless Mouse WM-10', '150', 15.00, 25.00, 18, '2025-01-22 11:00:00', 'wireless_mouse_wm10.jpg', 11),
(138, 'Wired Mouse M-20', '200', 10.00, 20.00, 18, '2025-01-22 11:00:00', 'wired_mouse_m20.jpg', 12),
(139, 'Gaming Mouse GM-30', '100', 30.00, 50.00, 18, '2025-01-22 11:00:00', 'gaming_mouse_gm30.jpg', 13),
(140, '4K Monitor 4K-500', '30', 400.00, 600.00, 19, '2025-01-22 11:00:00', '4k_monitor_4k500.jpg', 14),
(141, 'LED Monitor LED-200', '50', 200.00, 300.00, 19, '2025-01-22 11:00:00', 'led_monitor_led200.jpg', 15),
(142, 'Curved Monitor CUR-100', '40', 350.00, 500.00, 19, '2025-01-22 11:00:00', 'curved_monitor_cur100.jpg', 16),
(143, 'Laptop Bag LB-10', '100', 15.00, 30.00, 20, '2025-01-22 11:00:00', 'laptop_bag_lb10.jpg', 17),
(144, 'USB-C Cable UC-20', '200', 5.00, 15.00, 20, '2025-01-22 11:00:00', 'usb_c_cable_uc20.jpg', 18),
(145, 'Wireless Charger WC-30', '70', 20.00, 40.00, 20, '2025-01-22 11:00:00', 'wireless_charger_wc30.jpg', 19),
(146, 'Router RT-10', '60', 50.00, 100.00, 21, '2025-01-22 11:00:00', 'router_rt10.jpg', 20),
(147, 'WiFi Extender WE-20', '80', 25.00, 50.00, 21, '2025-01-22 11:00:00', 'wifi_extender_we20.jpg', 11),
(148, 'Network Switch SW-30', '40', 75.00, 120.00, 21, '2025-01-22 11:00:00', 'network_switch_sw30.jpg', 12),
(149, 'Modem MD-40', '1', 80.00, 150.00, 21, '2025-01-22 11:00:00', 'modem_md40.jpg', 13);

-- --------------------------------------------------------

--
-- Structure de la table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(25,2) NOT NULL,
  `purchase_price` decimal(25,2) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `purchases`
--

INSERT INTO `purchases` (`id`, `product_id`, `qty`, `price`, `purchase_price`, `date`) VALUES
(132, 127, 10, 4500.00, 0.00, '2023-01-20'),
(133, 128, 5, 6000.00, 0.00, '2023-02-15'),
(134, 129, 7, 4900.00, 0.00, '2023-03-10'),
(135, 130, 2, 2600.00, 0.00, '2023-04-05'),
(136, 131, 6, 1500.00, 0.00, '2023-05-12'),
(137, 132, 4, 720.00, 0.00, '2023-06-20'),
(138, 133, 3, 1050.00, 0.00, '2023-07-18'),
(139, 134, 10, 800.00, 0.00, '2023-08-25'),
(140, 135, 12, 480.00, 0.00, '2023-09-12'),
(141, 136, 8, 960.00, 0.00, '2023-10-07'),
(142, 137, 15, 375.00, 0.00, '2023-11-01'),
(143, 138, 18, 360.00, 0.00, '2023-11-20'),
(144, 139, 9, 450.00, 0.00, '2023-12-10'),
(145, 140, 5, 3000.00, 0.00, '2024-01-15'),
(146, 141, 6, 1800.00, 0.00, '2024-02-28'),
(147, 142, 4, 2000.00, 0.00, '2024-03-22'),
(148, 143, 20, 600.00, 0.00, '2024-04-10'),
(149, 144, 25, 375.00, 0.00, '2024-05-15'),
(150, 145, 8, 320.00, 0.00, '2024-06-05'),
(151, 146, 6, 600.00, 0.00, '2024-07-14'),
(152, 147, 10, 500.00, 0.00, '2024-08-20'),
(153, 148, 5, 600.00, 0.00, '2024-09-18'),
(154, 149, 30, 450.00, 0.00, '2024-10-10'),
(155, 149, 1, 0.00, 0.00, '0000-00-00'),
(156, 127, 1, 0.00, 0.00, '0000-00-00'),
(157, 127, 49, 0.00, 0.00, '0000-00-00'),
(158, 127, 1, 0.00, 0.00, '2025-01-24'),
(159, 127, 1, 0.00, 0.00, '2025-01-24'),
(160, 127, 1, 0.00, 0.00, '2025-01-24'),
(161, 127, 10, 0.00, 0.00, '2025-01-24'),
(162, 127, 1, 0.00, 0.00, '2025-01-24'),
(163, 127, 1, 0.00, 0.00, '2025-01-24'),
(164, 127, 10, 0.00, 0.00, '2025-01-24'),
(165, 127, 10, 0.00, 0.00, '2025-01-24'),
(166, 127, 10, 0.00, 0.00, '2025-01-24'),
(167, 127, 10, 0.00, 0.00, '2025-01-24'),
(168, 127, 1, 0.00, 0.00, '2025-01-24'),
(169, 127, 1, 0.00, 0.00, '2025-01-24'),
(170, 127, 10, 0.00, 0.00, '2025-01-24');

-- --------------------------------------------------------

--
-- Structure de la table `suppliers`
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
-- Déchargement des données de la table `suppliers`
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
-- Structure de la table `users`
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
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `user_level`, `image`, `status`, `last_login`) VALUES
(1, 'John Doe', 'admin', '$2y$10$JeZi6WnsQeGqtAHUo.ebQ.KZC9zHW3mrGa6VI2RbnUFGxr5Mfjkhy', 1, 'profile_1.jpg', 1, '2025-01-24 11:57:48'),
(2, 'Jane Smith', 'manager', '$2y$10$NpdpkTGVqjvTtZzQm4B60ODwX6atKpOJoBwcUePCgJ.760ci6DShW', 2, 'no_image.jpg', 1, '2025-01-24 10:11:48'),
(3, 'Youssouf', 'user', '$2y$10$87IpdleFa7NWTv2r.NcZDO35gxPKQnruz3P6MhjRe18j1yA64Ra5i', 3, 'no_image.jpg', 1, '2025-01-24 10:13:11');

-- --------------------------------------------------------

--
-- Structure de la table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_level` int(11) UNSIGNED NOT NULL,
  `group_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_groups`
--

INSERT INTO `user_groups` (`id`, `group_name`, `group_level`, `group_status`) VALUES
(1, 'Admin', 1, 1),
(2, 'Manager', 2, 1),
(3, 'User', 3, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Index pour la table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_level` (`user_level`);

--
-- Index pour la table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_level` (`group_level`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT pour la table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT pour la table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `FK_products` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `FK_sales_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `SK` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_user` FOREIGN KEY (`user_level`) REFERENCES `user_groups` (`group_level`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
