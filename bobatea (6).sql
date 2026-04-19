-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 19, 2026 at 09:01 PM
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
-- Database: `bobatea`
--
CREATE DATABASE IF NOT EXISTS `bobatea` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bobatea`;

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `d_id` int(6) NOT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `number` varchar(100) NOT NULL,
  `street` varchar(100) NOT NULL,
  `state` varchar(14) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postcode` int(5) NOT NULL,
  `u_id` int(6) DEFAULT NULL,
  `a_id` int(4) DEFAULT NULL,
  `b_id` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`d_id`, `nickname`, `number`, `street`, `state`, `city`, `postcode`, `u_id`, `a_id`, `b_id`) VALUES
(100001, 'Queensbay', 'LG-122, Queensbay Mall, 100', 'Persiaran Bayan Indah', 'Pulau Pinang', 'Bayan Lepas', 11900, NULL, NULL, 1001),
(100002, 'George Town', '102-C-04, New World Park', 'Jalan Burma', 'Pulau Pinang', 'George Town', 10050, NULL, NULL, 1002),
(100003, 'Bayan Lepas', '5-G-7, The Promenade, Persiaran Mahsuri', 'Bandar Bayan Baru', 'Pulau Pinang', 'Bayan Lepas', 11950, NULL, NULL, 1003),
(100005, 'Home', '98-16-2, Taman Sinar Bukit Dumbar', 'Jalan Faraday', 'Pulau Pinang', 'Gelugor', 11700, 100001, NULL, NULL),
(100006, 'Tarumt', '77', 'Lorong Lembah Permai Tiga', 'Pulau Pinang', 'Tanjung Bungah', 11200, 100001, NULL, NULL),
(100007, 'aiai', 'LG-122, Queensbay Mall, 100', 'Persiaran Bayan Indah', 'Pulau Pinang', 'Bayan Lepas', 11910, 100006, NULL, NULL),
(100008, 'f1', 'LG-122, Queensbay Mall, 100', 'Persiaran Bayan Indah', 'Pulau Pinang', 'Bayan Lepas', 11910, 100007, NULL, NULL),
(100009, 'user1', '12A', 'Jalan buah', 'Selangor', 'Ampang', 68000, 100008, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `a_id` int(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(13) NOT NULL,
  `photo` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `b_id` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`a_id`, `name`, `email`, `contact`, `photo`, `password`, `role`, `b_id`) VALUES
(1001, 'Tan Hor Ching', 'horching.tan18@gmail.com', '0125102206', '69d7c9561d09a.jpg', '8c569ad2f081c06b1f70391eeda871c8fc015ce0', 'superadmin', 1001),
(1005, 'Shan Yi Chun', 'tanhorching@gmail.com', '0125644786', '', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'superadmin', 1002),
(1006, 'admin 2', '2@gmail.com', '0122222222', '69d8eb849bbd9.jpg', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'admin', 1003),
(1009, 'super', 'super@super.com', '01111111111', '', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'superadmin', 1001),
(1010, 'Stephen', '123@gmail.com', '0123456789', '', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'admin', 1001);

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `b_id` int(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `photo` varchar(100) NOT NULL,
  `rest_day` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `branch_open_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`b_id`, `name`, `contact`, `photo`, `rest_day`, `start_time`, `end_time`, `branch_open_date`) VALUES
(1001, 'Queensbay FourLeaves Boba', '+604-1223319', '69e3b4f227ddb.jpg', 'Tuesday', '00:00:00', '23:59:00', '2026-02-10'),
(1002, 'Georgetown FourLeaves Boba', '+604-1223318', 'georgetown.jpg', 'Wednesday', '11:00:00', '21:00:00', '2024-01-01'),
(1003, 'Bayan Lepas FourLeaves Boba', '+604-1223320', 'bayanlepas.jpg', 'Thursday', '11:00:00', '21:00:00', '2025-02-18');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `c_id` int(4) NOT NULL,
  `name` varchar(25) NOT NULL,
  `description` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`c_id`, `name`, `description`) VALUES
(1001, 'Coffee', 'Bold and aromatic brews made from high-quality beans, available in classic and specialty styles.'),
(1002, 'Milk Tea', 'A creamy blend of premium tea and milk, offering a rich and balanced classic flavor'),
(1003, 'Fruit Tea', 'A refreshing infusion of tea and real fruit juices, served chilled for a vibrant, tangy kick.'),
(1005, 'Pure Tea', 'Experience the real taste of tea.'),
(1006, 'Chocolate Series', 'blends premium cocoa with smooth, creamy textures for the ultimate treat.'),
(1007, 'Cold Drinks', 'Filtered Tap Water');

-- --------------------------------------------------------

--
-- Table structure for table `favourite`
--

CREATE TABLE `favourite` (
  `fav_id` int(6) NOT NULL,
  `u_id` int(6) NOT NULL,
  `p_id` int(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourite`
--

INSERT INTO `favourite` (`fav_id`, `u_id`, `p_id`) VALUES
(100060, 100001, 100007),
(100062, 100001, 100003),
(100067, 100007, 100001),
(100068, 100007, 100003);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `f_id` int(6) NOT NULL,
  `ft_id` int(4) NOT NULL,
  `u_id` int(6) NOT NULL,
  `date_create` date NOT NULL,
  `message` varchar(100) NOT NULL,
  `image1` varchar(100) DEFAULT NULL,
  `image2` varchar(100) DEFAULT NULL,
  `image3` varchar(100) DEFAULT NULL,
  `image4` varchar(100) DEFAULT NULL,
  `image5` varchar(100) DEFAULT NULL,
  `a_id` int(4) DEFAULT NULL,
  `reply` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`f_id`, `ft_id`, `u_id`, `date_create`, `message`, `image1`, `image2`, `image3`, `image4`, `image5`, `a_id`, `reply`) VALUES
(1, 1001, 100006, '2026-04-19', 'good', '69e3de1fdf870.jpg', NULL, NULL, NULL, NULL, NULL, 'thanks'),
(2, 1001, 100007, '2026-04-19', 'good', '69e3e6b272515.jpg', NULL, NULL, NULL, NULL, NULL, 'thanks'),
(3, 1003, 100007, '2026-04-19', 'good', '69e3eb979f409.jpg', NULL, NULL, NULL, NULL, 1005, 'thanks'),
(4, 1001, 100007, '2026-04-19', 'good', '69e468149f19d.jpg', NULL, NULL, NULL, NULL, 1005, 'thanks');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_type`
--

CREATE TABLE `feedback_type` (
  `ft_id` int(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_type`
--

INSERT INTO `feedback_type` (`ft_id`, `name`, `description`) VALUES
(1001, 'Quality & Taste', 'Specifically for the drinks'),
(1002, 'Service Experience', 'Regarding the staff\'s friendliness, speed of service, or accuracy of the order.'),
(1003, 'Store Environment', 'cleanliness, seating, music volume, or the overall vibe of the physical location.'),
(1004, 'System improvement', 'reporting bugs, UI issues, or trouble with the digital payment process.'),
(1005, 'Menu Suggestion', 'to request new flavors, toppings, or dairy alternatives.'),
(1006, 'General Inquiry', 'For miscellaneous questions or partnership requests.'),
(1007, 'Order Issue', 'wrong or missing items or toppings'),
(1008, 'Other', 'A catch-all for unique feedback.');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `o_id` int(6) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `u_id` int(6) NOT NULL,
  `total` decimal(6,2) NOT NULL,
  `status` varchar(100) NOT NULL,
  `rate` int(1) DEFAULT NULL,
  `payment_datetime` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`o_id`, `datetime`, `u_id`, `total`, `status`, `rate`, `payment_datetime`) VALUES
(27, '2026-04-11 01:09:09', 100001, 15.40, 'paid', NULL, '2026-04-11'),
(28, '2026-04-11 01:11:35', 100001, 25.80, 'paid', NULL, '2026-04-11'),
(29, '2026-04-11 01:12:27', 100001, 12.90, 'paid', NULL, '2026-04-11'),
(30, '2026-04-11 09:13:59', 100001, 12.90, 'paid', NULL, '2026-04-11'),
(31, '2026-04-11 09:34:05', 100001, 12.90, 'paid', NULL, '2026-04-11'),
(32, '2026-04-11 09:44:27', 100001, 10.90, 'paid', NULL, '2026-04-11'),
(33, '2026-04-11 10:48:01', 100001, 12.90, 'paid', NULL, '2026-04-11'),
(34, '2026-04-11 11:45:39', 100001, 12.90, 'paid', NULL, '2026-04-11'),
(35, '2026-04-11 11:55:27', 100001, 61.60, 'paid', NULL, '2026-04-11'),
(36, '2026-04-11 12:00:39', 100001, 14.40, 'paid', 5, '2026-04-11'),
(37, '2026-04-11 12:48:32', 100001, 12.90, 'paid', 5, '2026-04-11'),
(40, '2026-04-19 03:41:42', 100006, 15.05, 'paid', NULL, '2026-04-19'),
(41, '2026-04-19 04:15:13', 100007, 16.90, 'paid', 5, '2026-04-19'),
(42, '2026-04-19 04:34:24', 100007, 9.90, 'paid', 3, '2026-04-19'),
(43, '2026-04-19 13:15:54', 100007, 12.90, 'paid', 5, '2026-04-19'),
(44, '2026-04-19 13:17:09', 100007, 12.90, 'paid', 5, '2026-04-19');

-- --------------------------------------------------------

--
-- Table structure for table `price_log`
--

CREATE TABLE `price_log` (
  `log_id` int(11) NOT NULL,
  `a_id` int(4) DEFAULT NULL,
  `p_id` int(6) DEFAULT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) DEFAULT NULL,
  `old_discount` int(11) DEFAULT NULL,
  `new_discount` int(11) DEFAULT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price_log`
--

INSERT INTO `price_log` (`log_id`, `a_id`, `p_id`, `old_price`, `new_price`, `old_discount`, `new_discount`, `changed_at`) VALUES
(1, 1009, 100010, 11.90, NULL, NULL, NULL, '2026-04-19 22:00:01'),
(2, 1009, 100010, 6.90, NULL, 10, NULL, '2026-04-19 22:27:32'),
(3, 1009, 100010, 3.85, NULL, 5, NULL, '2026-04-19 22:28:17'),
(4, 1009, 100010, 3.20, 3.20, 3, 3, '2026-04-19 22:34:40'),
(5, 1009, 100001, 7.50, 7.50, 5, 5, '2026-04-19 23:31:09'),
(6, 1009, 100004, 40.90, NULL, 11, NULL, '2026-04-19 23:41:54'),
(7, 1009, 100005, 10.90, 5.50, 5, 2, '2026-04-19 23:57:31'),
(8, 1009, 100010, 11.60, 11.45, 6, 6, '2026-04-20 00:01:24'),
(9, 1009, 100013, 12.90, 12.55, 5, 3, '2026-04-20 00:45:38'),
(10, 1009, 100011, 8.50, 8.00, 3, 6, '2026-04-20 00:48:20'),
(11, 1009, 100004, 40.90, 30.00, 11, 27, '2026-04-20 00:54:16'),
(12, 1009, 100003, 12.90, 12.00, 4, 7, '2026-04-20 00:56:17'),
(13, 1009, 100011, 8.00, 9.00, 6, 0, '2026-04-20 01:18:07'),
(14, 1010, 100011, 9.00, 7.00, 0, 22, '2026-04-20 01:57:26'),
(15, 1010, 100010, 11.45, 7.50, 6, 35, '2026-04-20 02:02:39'),
(16, 1010, 100003, 12.00, 8.50, 7, 29, '2026-04-20 02:02:52'),
(17, 1006, 100004, 30.00, 10.50, 27, 65, '2026-04-20 02:04:48'),
(18, 1006, 100013, 12.55, 25.60, 3, 0, '2026-04-20 02:05:03');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `p_id` int(6) NOT NULL,
  `name` varchar(30) NOT NULL,
  `price` decimal(5,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `cold` int(1) NOT NULL,
  `hot` int(1) NOT NULL,
  `c_id` int(4) NOT NULL,
  `image` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`p_id`, `name`, `price`, `discount`, `status`, `cold`, `hot`, `c_id`, `image`) VALUES
(100001, 'Latte', 6.50, 4.00, 'available', 1, 1, 1001, 'icedlatte.jpg'),
(100002, 'Brown Sugar Pearl Milk Tea', 9.90, 3.00, 'available', 1, 0, 1002, 'brownsugarpearlmilktea.jpg'),
(100003, 'Hazelnut Coffee', 8.50, 29.17, 'available', 1, 1, 1001, '69d378fd89c65.jpg'),
(100004, 'Matcha Latte', 10.50, 65.00, 'available', 1, 1, 1001, 'matchalatte.jpg'),
(100005, 'Mocha', 5.50, 1.95, 'available', 1, 1, 1001, 'mocha.jpg'),
(100006, 'Signature Boba Milk Tea', 8.90, 5.00, 'available', 1, 0, 1002, 'signaturebobamilktea.jpg'),
(100007, 'Signature Mixed Fruit Tea', 12.90, 5.00, 'available', 1, 0, 1003, 'signaturemixedfruittea.png'),
(100008, 'Oolong Tea', 7.90, 5.00, 'available', 1, 1, 1005, 'oolongtea.png'),
(100009, 'Signature Chocolate', 12.90, 5.00, 'available', 1, 1, 1006, 'signaturechocolate.jpg'),
(100010, 'Cappuccino', 7.50, 34.50, 'available', 1, 1, 1001, '69d394b15f100.jpg'),
(100011, 'Americano', 7.00, 22.22, 'available', 1, 1, 1001, '69d394db1249a.jpg'),
(100012, 'Espresso', 10.90, 4.00, 'available', 1, 0, 1001, '69d395061ea2d.jpg'),
(100013, 'Macchiato', 25.60, 0.00, 'available', 1, 1, 1001, '69d395187cda9.jpg'),
(100014, 'Mango Milk Tea', 13.90, 5.00, 'available', 1, 0, 1002, '69d39546543c4.jpg'),
(100015, 'Matcha Milk Tea', 11.90, 4.00, 'available', 1, 0, 1002, '69d3956c94c15.jpg'),
(100016, 'Taro Milk Tea', 12.90, 8.00, 'available', 1, 0, 1002, '69d39581e297d.jpg'),
(100017, 'Dark Chocolate Milk Tea', 13.90, 5.00, 'available', 1, 0, 1006, '69d395a330b25.jpg'),
(100018, 'Strawberry Milk Tea', 13.90, 4.00, 'available', 1, 0, 1002, '69d395e69b252.jpg'),
(100019, 'kopi', 2.50, 4.00, 'available', 1, 1, 1001, '69d5c2bfdffce.jpg'),
(100020, 'Fruit Tea \'A\'', 9.85, 4.00, 'available', 1, 0, 1003, '69e3bab322eb5.jpg'),
(100021, 'Filterd Tap Water A', 0.50, 4.00, 'available', 1, 1, 1007, '69e3c371a9baf.jpg'),
(100022, 'Filtered Tap Water 2', 1.00, 4.00, 'available', 1, 1, 1007, '69e3c39537aad.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `product_order`
--

CREATE TABLE `product_order` (
  `po_id` int(6) NOT NULL,
  `o_id` int(6) NOT NULL,
  `p_id` int(6) NOT NULL,
  `quantity` int(3) NOT NULL,
  `ice` int(3) DEFAULT NULL,
  `sugar` int(3) NOT NULL,
  `remark` varchar(100) DEFAULT NULL,
  `subtotal` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_order`
--

INSERT INTO `product_order` (`po_id`, `o_id`, `p_id`, `quantity`, `ice`, `sugar`, `remark`, `subtotal`) VALUES
(29, 27, 100007, 1, 100, 100, '', 12.90),
(30, 27, 100019, 1, 100, 100, '', 2.50),
(31, 28, 100004, 1, 100, 100, '', 12.90),
(32, 28, 100007, 1, 100, 100, '', 12.90),
(33, 29, 100003, 1, 100, 100, '', 12.90),
(34, 30, 100003, 1, 100, 100, '', 12.90),
(35, 31, 100003, 1, 100, 100, '', 12.90),
(36, 32, 100005, 1, 100, 100, '', 10.90),
(37, 33, 100003, 1, 100, 100, '', 12.90),
(38, 34, 100007, 1, 100, 100, '', 12.90),
(39, 35, 100003, 1, 100, 100, '', 12.90),
(40, 35, 100004, 1, NULL, 100, '', 12.90),
(41, 35, 100007, 1, 100, 100, '', 15.90),
(42, 35, 100016, 1, 75, 50, '', 15.90),
(43, 35, 100019, 1, 100, 100, '', 4.00),
(44, 36, 100007, 1, 100, 100, '', 14.40),
(45, 37, 100003, 1, 100, 100, '', 12.90),
(48, 40, 100020, 1, 100, 100, '', 15.05),
(49, 41, 100008, 1, 100, 100, '', 16.90),
(50, 42, 100011, 1, 100, 100, '', 9.90),
(51, 43, 100007, 1, 100, 100, '', 12.90),
(52, 44, 100009, 1, 100, 100, '', 12.90);

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE `token` (
  `token_id` varchar(100) NOT NULL,
  `u_id` int(6) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `token`
--

INSERT INTO `token` (`token_id`, `u_id`, `expire`) VALUES
('af2a389a7df8f4a6a21e9f350b37153f52f53fcf', 100006, '2026-04-19 04:11:20');

-- --------------------------------------------------------

--
-- Table structure for table `topping`
--

CREATE TABLE `topping` (
  `t_id` int(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price_per_unit` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topping`
--

INSERT INTO `topping` (`t_id`, `name`, `price_per_unit`) VALUES
(1001, 'Brown Sugar Pearl', 1.50),
(1002, 'Cheese Foam', 1.50),
(1004, 'Coconut Jelly', 1.50),
(1005, 'Oreo Crumbs', 1.50),
(1006, 'Pudding', 1.50),
(1007, 'Grass Jelly', 1.50),
(1009, 'Waffle Crumbs', 1.50),
(1010, 'Brown Sugar Jelly', 1.50),
(1011, 'Cookies', 1.50),
(1012, 'Crystal Jelly', 1.50),
(1014, 'Lychee', 5.20);

-- --------------------------------------------------------

--
-- Table structure for table `topping_item`
--

CREATE TABLE `topping_item` (
  `po_id` int(6) NOT NULL,
  `t_id` int(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topping_item`
--

INSERT INTO `topping_item` (`po_id`, `t_id`) VALUES
(41, 1012),
(41, 1007),
(42, 1010),
(42, 1004),
(43, 1009),
(44, 1012),
(48, 1014),
(49, 1010),
(49, 1001),
(49, 1004),
(49, 1012),
(49, 1007),
(49, 1005);

-- --------------------------------------------------------

--
-- Table structure for table `topping_list`
--

CREATE TABLE `topping_list` (
  `t_id` int(4) NOT NULL,
  `p_id` int(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topping_list`
--

INSERT INTO `topping_list` (`t_id`, `p_id`) VALUES
(1001, 100002),
(1001, 100006),
(1001, 100008),
(1001, 100009),
(1001, 100014),
(1001, 100015),
(1001, 100016),
(1001, 100017),
(1001, 100018),
(1002, 100002),
(1002, 100006),
(1002, 100009),
(1002, 100010),
(1002, 100015),
(1002, 100017),
(1004, 100002),
(1004, 100006),
(1004, 100007),
(1004, 100008),
(1004, 100009),
(1004, 100014),
(1004, 100015),
(1004, 100016),
(1004, 100017),
(1004, 100018),
(1005, 100002),
(1005, 100006),
(1005, 100008),
(1005, 100009),
(1005, 100015),
(1005, 100017),
(1005, 100019),
(1006, 100002),
(1006, 100006),
(1006, 100007),
(1006, 100009),
(1006, 100014),
(1006, 100015),
(1006, 100016),
(1006, 100017),
(1006, 100018),
(1007, 100002),
(1007, 100006),
(1007, 100007),
(1007, 100008),
(1007, 100009),
(1007, 100015),
(1007, 100017),
(1009, 100002),
(1009, 100006),
(1009, 100009),
(1009, 100015),
(1009, 100017),
(1009, 100019),
(1010, 100002),
(1010, 100006),
(1010, 100008),
(1010, 100009),
(1010, 100014),
(1010, 100015),
(1010, 100016),
(1010, 100017),
(1010, 100018),
(1011, 100002),
(1011, 100006),
(1011, 100009),
(1011, 100015),
(1011, 100017),
(1011, 100019),
(1012, 100002),
(1012, 100006),
(1012, 100007),
(1012, 100008),
(1012, 100009),
(1012, 100014),
(1012, 100015),
(1012, 100016),
(1012, 100017),
(1012, 100018),
(1014, 100020);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `u_id` int(6) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(13) DEFAULT NULL,
  `register_date` datetime NOT NULL DEFAULT current_timestamp(),
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `blacklist` int(1) DEFAULT NULL,
  `photo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`u_id`, `name`, `email`, `contact`, `register_date`, `password`, `role`, `blacklist`, `photo`) VALUES
(100001, 'tanhc0318', 'tanhc0318@gmail.com', '01113143302', '2026-03-29 00:00:00', 'ac250e4a00ff3144ae7689f0d23e8b26d06aa929', 'member', NULL, '69d7c84502626.jpg'),
(100005, 'member 1', '1@gmail.com', '01111111111', '2026-04-10 15:19:07', '3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d', 'member', NULL, NULL),
(100006, 'Ai ai', 'ai@ai.com', '01199999999', '2026-04-19 02:51:16', '1f5523a8f535289b3401b29958d01b2966ed61d2', 'member', NULL, NULL),
(100007, 'f1966kk', 'F1966KK@GMAIL.COM', '01144444444', '2026-04-19 04:13:40', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'member', 1, '69e3f2b2d7f02.jpg'),
(100008, 'user1', 'user1@user.com', '01155555555', '2026-04-19 15:01:18', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'member', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`d_id`),
  ADD KEY `fk_address_user` (`u_id`),
  ADD KEY `fk_address_admin` (`a_id`),
  ADD KEY `fk_address_branch` (`b_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`a_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `contact` (`contact`),
  ADD KEY `b_id` (`b_id`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`b_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`c_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `favourite`
--
ALTER TABLE `favourite`
  ADD PRIMARY KEY (`fav_id`),
  ADD KEY `p_id` (`p_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`f_id`),
  ADD KEY `ft_id` (`ft_id`),
  ADD KEY `u_id` (`u_id`),
  ADD KEY `a_id` (`a_id`);

--
-- Indexes for table `feedback_type`
--
ALTER TABLE `feedback_type`
  ADD PRIMARY KEY (`ft_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`o_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `price_log`
--
ALTER TABLE `price_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `a_id` (`a_id`),
  ADD KEY `p_id` (`p_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`p_id`),
  ADD KEY `c_id` (`c_id`);

--
-- Indexes for table `product_order`
--
ALTER TABLE `product_order`
  ADD PRIMARY KEY (`po_id`),
  ADD KEY `o_id` (`o_id`),
  ADD KEY `p_id` (`p_id`);

--
-- Indexes for table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `topping`
--
ALTER TABLE `topping`
  ADD PRIMARY KEY (`t_id`);

--
-- Indexes for table `topping_item`
--
ALTER TABLE `topping_item`
  ADD KEY `po_id` (`po_id`),
  ADD KEY `t_id` (`t_id`);

--
-- Indexes for table `topping_list`
--
ALTER TABLE `topping_list`
  ADD PRIMARY KEY (`t_id`,`p_id`),
  ADD KEY `p_id` (`p_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`u_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `contact` (`contact`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `d_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100010;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `a_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1011;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `b_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `c_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1008;

--
-- AUTO_INCREMENT for table `favourite`
--
ALTER TABLE `favourite`
  MODIFY `fav_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100069;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `f_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback_type`
--
ALTER TABLE `feedback_type`
  MODIFY `ft_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1009;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `o_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `price_log`
--
ALTER TABLE `price_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `p_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100023;

--
-- AUTO_INCREMENT for table `product_order`
--
ALTER TABLE `product_order`
  MODIFY `po_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `topping`
--
ALTER TABLE `topping`
  MODIFY `t_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1015;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `u_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100009;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `fk_address_admin` FOREIGN KEY (`a_id`) REFERENCES `admin` (`a_id`),
  ADD CONSTRAINT `fk_address_branch` FOREIGN KEY (`b_id`) REFERENCES `branch` (`b_id`),
  ADD CONSTRAINT `fk_address_user` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`);

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`b_id`) REFERENCES `branch` (`b_id`);

--
-- Constraints for table `favourite`
--
ALTER TABLE `favourite`
  ADD CONSTRAINT `favourite_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `product` (`p_id`),
  ADD CONSTRAINT `favourite_ibfk_2` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`ft_id`) REFERENCES `feedback_type` (`ft_id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`),
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`a_id`) REFERENCES `admin` (`a_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`);

--
-- Constraints for table `price_log`
--
ALTER TABLE `price_log`
  ADD CONSTRAINT `price_log_ibfk_1` FOREIGN KEY (`a_id`) REFERENCES `admin` (`a_id`),
  ADD CONSTRAINT `price_log_ibfk_2` FOREIGN KEY (`p_id`) REFERENCES `product` (`p_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`c_id`) REFERENCES `category` (`c_id`);

--
-- Constraints for table `product_order`
--
ALTER TABLE `product_order`
  ADD CONSTRAINT `product_order_ibfk_1` FOREIGN KEY (`o_id`) REFERENCES `orders` (`o_id`),
  ADD CONSTRAINT `product_order_ibfk_2` FOREIGN KEY (`p_id`) REFERENCES `product` (`p_id`);

--
-- Constraints for table `token`
--
ALTER TABLE `token`
  ADD CONSTRAINT `token_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`);

--
-- Constraints for table `topping_item`
--
ALTER TABLE `topping_item`
  ADD CONSTRAINT `topping_item_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `product_order` (`po_id`),
  ADD CONSTRAINT `topping_item_ibfk_2` FOREIGN KEY (`t_id`) REFERENCES `topping` (`t_id`);

--
-- Constraints for table `topping_list`
--
ALTER TABLE `topping_list`
  ADD CONSTRAINT `topping_list_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `product` (`p_id`),
  ADD CONSTRAINT `topping_list_ibfk_2` FOREIGN KEY (`t_id`) REFERENCES `topping` (`t_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
