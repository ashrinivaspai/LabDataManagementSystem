-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2020 at 08:54 PM
-- Server version: 5.7.9-log
-- PHP Version: 5.6.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `info`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `lock_category` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serial`),
  UNIQUE KEY `cat` (`serial`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`serial`, `name`, `lock_category`) VALUES
(1, 'Power Supplies', 0),
(2, 'DSO', 0),
(3, 'AFO', 0),
(4, 'Software', 0),
(5, 'Development Kit', 0),
(6, 'Desktop', 0);

-- --------------------------------------------------------

--
-- Table structure for table `designation`
--

DROP TABLE IF EXISTS `designation`;
CREATE TABLE IF NOT EXISTS `designation` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `post` varchar(20) NOT NULL,
  PRIMARY KEY (`serial`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `designation`
--

INSERT INTO `designation` (`serial`, `post`) VALUES
(1, 'HOD'),
(2, 'Professor'),
(3, 'Assoc. Professor'),
(4, 'Asst. Professor 3'),
(5, 'Asst. Professor 2'),
(6, 'Asst. Professor 1'),
(7, 'Lab Incharge');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `lab_serial` int(11) NOT NULL COMMENT 'serial number obtained in ''lab'' table',
  `serial_item` int(11) NOT NULL COMMENT 'serial number obtained from ''item'' table',
  `item_code` varchar(64) NOT NULL COMMENT 'name written on apparatus',
  `product_code` varchar(64) NOT NULL,
  `tequip` int(1) NOT NULL,
  `cost` int(11) NOT NULL,
  `receipt_serial` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1' COMMENT 'working condition',
  `lock_item` int(1) NOT NULL DEFAULT '0' COMMENT 'this is set when item is removed, no further alterations are allowed',
  `last_operation` int(11) NOT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`serial`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `item` (`serial_item`),
  KEY `lab_serial` (`lab_serial`),
  KEY `status` (`status`),
  KEY `last_operation` (`last_operation`),
  KEY `reciept_serial` (`receipt_serial`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`serial`, `lab_serial`, `serial_item`, `item_code`, `product_code`, `tequip`, `cost`, `receipt_serial`, `status`, `lock_item`, `last_operation`, `updated`) VALUES
(44, 21, 37, 'KIT1', 'KIT1', 0, 5000, 1, 5, 1, 4, '2018-08-20 00:26:18'),
(45, 20, 37, 'KIT2', 'KIT2', 0, 5000, 3, 5, 1, 4, '2018-08-20 00:27:29'),
(46, 21, 37, 'KIT3', 'KIT3', 0, 5000, 2, 5, 1, 4, '2018-08-29 11:29:08'),
(47, 20, 38, 'AFO1', 'AFO1', 0, 15000, 14, 1, 0, 3, '2019-01-19 12:51:59'),
(48, 20, 38, 'AFO2', 'AFO2', 0, 5000, 3, 1, 0, 2, '2018-08-20 00:00:17'),
(49, 21, 39, 'DSO1', 'DSO1', 1, 15000, 1, 2, 0, 5, '2018-08-20 12:50:03'),
(50, 20, 39, 'DSO2', 'DSO2', 1, 15000, 3, 1, 0, 1, '2018-08-20 12:48:35'),
(51, 23, 38, 'Afo4', 'Afo4', 0, 1000, 2, 4, 0, 5, '2018-09-18 11:52:11'),
(52, 22, 40, 'cd1', 'cd1', 0, 1000, 11, 1, 0, 1, '2018-08-21 20:24:03'),
(53, 22, 40, 'cd2', 'cd2', 0, 1000, 1, 2, 0, 3, '2018-08-21 20:24:57'),
(54, 20, 38, 'AFO3', 'AFO3', 0, 5000, 1, 2, 0, 3, '2018-09-16 12:42:32'),
(55, 22, 38, 'AFO5', 'AFO5', 0, 5000, 3, 1, 0, 5, '2018-09-14 15:35:12'),
(56, 23, 46, 'LP1', 'LP1', 0, 85000, 2, 1, 0, 1, '2018-09-01 00:11:31'),
(57, 21, 38, 'KIT6', 'LP1', 0, 100, 3, 2, 0, 3, '2018-09-01 00:55:28'),
(68, 21, 46, 'ENV01', 'ENV01', 0, 75000, 12, 1, 0, 5, '2019-03-23 17:18:55'),
(69, 20, 46, 'ENV02', 'EVN02', 0, 75000, 11, 1, 0, 5, '2018-09-14 15:52:13'),
(71, 21, 37, 'KIT10', 'kit10', 0, 1000, 14, 1, 0, 1, '2018-09-07 18:29:46'),
(72, 23, 46, 'ENV03', 'EVN02', 0, 75000, 11, 1, 0, 1, '2018-09-07 18:30:52'),
(73, 22, 37, 'KIT20', 'KIT20', 1, 1235, 1, 1, 0, 2, '2018-09-15 20:12:13'),
(74, 21, 41, 'AVR01', 'AVR01', 0, 5000, 2, 1, 0, 1, '2018-09-19 11:35:46'),
(75, 20, 48, 'SYS01', 'SYS01', 0, 40000, 18, 1, 0, 5, '2019-03-23 18:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

DROP TABLE IF EXISTS `inventory_log`;
CREATE TABLE IF NOT EXISTS `inventory_log` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_serial` int(11) NOT NULL,
  `lab_serial` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `operation_serial` int(11) NOT NULL,
  `comments` varchar(128) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`serial`),
  KEY `labserial` (`lab_serial`),
  KEY `staff_id` (`staff_id`),
  KEY `status` (`status`),
  KEY `inventory_serial` (`inventory_serial`),
  KEY `operation_serial` (`operation_serial`)
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `inventory_log`
--

INSERT INTO `inventory_log` (`serial`, `inventory_serial`, `lab_serial`, `status`, `operation_serial`, `comments`, `staff_id`, `timestamp`) VALUES
(122, 44, 20, 1, 1, '', 1, '2018-08-19 23:01:59'),
(123, 45, 20, 1, 1, '', 1, '2018-08-19 23:01:59'),
(124, 46, 20, 1, 1, '', 1, '2018-08-19 23:01:59'),
(125, 44, 20, 1, 5, 'done| to lab:ECL01', 10, '2018-08-19 23:15:08'),
(126, 44, 21, 1, 1, 'done| from lab:ECL05', 10, '2018-08-19 23:15:08'),
(127, 44, 21, 1, 2, 'done| from lab:ECL05', 13, '2018-08-19 23:16:53'),
(128, 44, 21, 1, 2, 'done| from lab:ECL05', 13, '2018-08-19 23:20:08'),
(129, 44, 21, 1, 2, 'done| from lab:ECL05', 13, '2018-08-19 23:22:34'),
(130, 47, 20, 1, 1, '', 1, '2018-08-19 23:55:46'),
(131, 48, 20, 1, 1, '', 1, '2018-08-20 00:00:17'),
(132, 48, 20, 1, 2, '', 1, '2018-08-20 00:00:56'),
(133, 47, 20, 1, 5, 'okay| to lab:ECL01', 1, '2018-08-20 00:14:26'),
(134, 47, 21, 1, 1, 'okay| from lab:ECL05', 1, '2018-08-20 00:14:26'),
(135, 47, 21, 2, 3, 'broke', 1, '2018-08-20 00:15:59'),
(136, 45, 20, 2, 3, 'broke', 1, '2018-08-20 00:16:46'),
(137, 45, 20, 2, 5, 'okay| to lab:ECL01', 1, '2018-08-20 00:17:07'),
(138, 45, 21, 2, 1, 'okay| from lab:ECL05', 1, '2018-08-20 00:17:07'),
(139, 45, 21, 3, 3, 'submitted to repair', 1, '2018-08-20 00:17:54'),
(140, 45, 21, 3, 5, 'okay| to lab:ECL05', 1, '2018-08-20 00:18:24'),
(141, 45, 20, 3, 1, 'okay| from lab:ECL01', 1, '2018-08-20 00:18:24'),
(142, 44, 21, 5, 4, 'no longer used. item is removed/locked', 1, '2018-08-20 00:26:18'),
(143, 45, 20, 5, 4, 'no longer used', 1, '2018-08-20 00:27:29'),
(144, 49, 20, 1, 1, 'created', 1, '2018-08-20 12:48:35'),
(145, 50, 20, 1, 1, 'created', 1, '2018-08-20 12:48:36'),
(146, 49, 20, 2, 3, 'broke', 1, '2018-08-20 12:49:20'),
(147, 49, 20, 2, 5, 'okay| to lab:ECL01', 1, '2018-08-20 12:50:03'),
(148, 49, 21, 2, 1, 'okay| from lab:ECL05', 1, '2018-08-20 12:50:03'),
(149, 51, 20, 1, 1, '', 1, '2018-08-21 15:59:22'),
(150, 51, 20, 2, 3, 'broke', 1, '2018-08-21 16:09:39'),
(151, 51, 20, 2, 5, 'okau | to lab:ECL01', 1, '2018-08-21 16:10:27'),
(152, 51, 21, 2, 1, 'okau | from lab:ECL05', 1, '2018-08-21 16:10:27'),
(153, 51, 21, 3, 3, 'repaired by tech', 1, '2018-08-21 16:13:04'),
(154, 51, 21, 3, 5, 'cant | to lab:ECL05', 1, '2018-08-21 16:13:59'),
(155, 51, 20, 3, 1, 'cant | from lab:ECL01', 1, '2018-08-21 16:13:59'),
(156, 52, 22, 1, 1, '', 1, '2018-08-21 20:24:03'),
(157, 53, 22, 1, 1, '', 1, '2018-08-21 20:24:03'),
(158, 53, 22, 2, 3, '..', 1, '2018-08-21 20:24:57'),
(159, 47, 21, 2, 5, 'done Nireeksha | to lab:ECL05', 1, '2018-08-21 23:10:46'),
(160, 47, 20, 2, 1, 'done Nireeksha | from lab:ECL01', 1, '2018-08-21 23:10:46'),
(161, 47, 20, 2, 5, 'okau | to lab:ECL01', 1, '2018-08-22 01:10:17'),
(162, 47, 21, 2, 1, 'okau | from lab:ECL05', 1, '2018-08-22 01:10:17'),
(163, 54, 20, 1, 1, '', 1, '2018-08-22 01:22:09'),
(164, 55, 20, 1, 1, '', 1, '2018-08-22 01:22:09'),
(165, 46, 20, 1, 5, 'okau | to lab:ECL01', 10, '2018-08-22 12:44:34'),
(166, 46, 21, 1, 1, 'okau | from lab:ECL05', 10, '2018-08-22 12:44:34'),
(167, 47, 21, 2, 5, 'rrsty | to lab:ECL05', 9, '2018-08-23 13:01:21'),
(168, 47, 20, 2, 1, 'rrsty | from lab:ECL01', 9, '2018-08-23 13:01:21'),
(169, 46, 21, 3, 3, '.', 1, '2018-08-29 11:28:48'),
(170, 46, 21, 5, 4, '.. item is removed/locked', 1, '2018-08-29 11:29:08'),
(171, 56, 23, 1, 1, '', 1, '2018-09-01 00:11:32'),
(172, 57, 21, 1, 1, 'mk', 1, '2018-09-01 00:55:03'),
(173, 57, 21, 2, 3, 'mkl', 1, '2018-09-01 00:55:28'),
(185, 68, 23, 1, 1, '', 1, '2018-09-07 18:16:21'),
(186, 69, 23, 1, 1, '', 1, '2018-09-07 18:16:21'),
(187, 71, 21, 1, 1, '', 1, '2018-09-07 18:29:46'),
(188, 72, 23, 1, 1, '', 1, '2018-09-07 18:30:52'),
(189, 69, 23, 1, 2, '', 1, '2018-09-07 18:41:48'),
(190, 69, 23, 1, 2, '', 1, '2018-09-07 18:45:20'),
(191, 68, 23, 1, 2, '', 1, '2018-09-07 18:46:54'),
(192, 47, 20, 2, 2, '', 1, '2018-09-07 19:15:00'),
(193, 47, 20, 2, 2, '', 1, '2018-09-07 19:29:41'),
(194, 47, 20, 2, 2, '', 1, '2018-09-07 19:30:52'),
(195, 47, 20, 2, 2, '', 1, '2018-09-07 19:35:58'),
(196, 47, 20, 2, 2, '', 1, '2018-09-07 19:36:32'),
(197, 47, 20, 2, 2, '', 1, '2018-09-07 19:38:07'),
(198, 47, 20, 2, 2, '', 1, '2018-09-07 19:38:41'),
(199, 47, 20, 2, 2, '', 1, '2018-09-07 19:39:00'),
(200, 47, 20, 2, 2, '', 1, '2018-09-07 19:41:06'),
(201, 68, 23, 1, 5, 'done | to lab:ECL05', 1, '2018-09-14 15:35:01'),
(202, 68, 20, 1, 1, 'done | from lab:ecl03', 1, '2018-09-14 15:35:01'),
(203, 55, 20, 1, 5, 'done | to lab:ecl02', 1, '2018-09-14 15:35:12'),
(204, 55, 22, 1, 1, 'done | from lab:ECL05', 1, '2018-09-14 15:35:12'),
(205, 69, 23, 1, 5, 'okay | to lab:ECL05', 1, '2018-09-14 15:44:00'),
(206, 69, 20, 1, 1, 'okay | from lab:ecl03', 1, '2018-09-14 15:44:00'),
(207, 69, 23, 1, 5, 'okay | to lab:ECL05', 1, '2018-09-14 15:44:07'),
(208, 69, 20, 1, 1, 'okay | from lab:ecl03', 1, '2018-09-14 15:44:07'),
(209, 69, 23, 1, 5, 'okay | to lab:ECL05', 1, '2018-09-14 15:52:13'),
(210, 69, 20, 1, 1, 'okay | from lab:ecl03', 1, '2018-09-14 15:52:13'),
(211, 73, 22, 1, 1, 'nothing much', 1, '2018-09-15 20:12:13'),
(212, 73, 22, 1, 2, 'nothing much', 1, '2018-09-15 20:13:03'),
(213, 54, 20, 2, 3, 'mk', 1, '2018-09-16 12:42:32'),
(214, 47, 20, 2, 3, '...', 1, '2018-09-17 19:21:37'),
(215, 51, 20, 3, 3, 'cant | from lab:ECL01', 1, '2018-09-18 11:50:17'),
(216, 51, 20, 4, 3, '...', 1, '2018-09-18 11:50:57'),
(217, 51, 20, 4, 5, 'done | to lab:ecl03', 1, '2018-09-18 11:52:11'),
(218, 51, 23, 4, 1, 'done | from lab:ECL05', 1, '2018-09-18 11:52:11'),
(219, 47, 20, 3, 3, '...', 1, '2018-09-18 11:53:09'),
(220, 74, 21, 1, 1, 'developed by students', 1, '2018-09-19 11:35:46'),
(221, 75, 21, 1, 1, '', 1, '2018-09-19 16:31:42'),
(222, 47, 20, 1, 3, 'working', 1, '2019-01-19 12:52:00'),
(223, 75, 21, 1, 5, 'okay | to lab:ECL05', 1, '2019-01-23 21:03:36'),
(224, 75, 20, 1, 1, 'okay | from lab:ECL01', 1, '2019-01-23 21:03:36'),
(225, 68, 20, 1, 5, ' | to lab:ECL01', 1, '2019-03-23 17:18:55'),
(226, 68, 21, 1, 1, ' | from lab:ECL05', 1, '2019-03-23 17:18:55'),
(227, 75, 20, 1, 5, 'done | to lab:ECL01', 1, '2019-03-23 18:01:57'),
(228, 75, 21, 1, 1, 'done | from lab:ECL05', 1, '2019-03-23 18:01:57'),
(229, 75, 21, 1, 5, 'done | to lab:ECL05', 1, '2019-03-23 18:15:08'),
(230, 75, 20, 1, 1, 'done | from lab:ECL01', 1, '2019-03-23 18:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
CREATE TABLE IF NOT EXISTS `item` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturer_serial` int(11) NOT NULL,
  `category_serial` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `version` varchar(32) NOT NULL,
  `description` varchar(128) NOT NULL,
  `lock_item` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serial`),
  KEY `catsl` (`category_serial`),
  KEY `mnsl` (`manufacturer_serial`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`serial`, `manufacturer_serial`, `category_serial`, `name`, `version`, `description`, `lock_item`) VALUES
(37, 1, 5, '8051 Kit', '1.0', 'has led\'s and some switches', 0),
(38, 2, 3, 'Oscilloscope', '1.0', '', 0),
(39, 2, 2, '100MHz DSO', '1.1', '', 0),
(40, 2, 4, 'cadence virtuso', '1.0', '', 0),
(41, 2, 5, 'AVR Development Kit', '1.0', '', 0),
(42, 2, 4, 'DSP Lab', '1.0', '', 0),
(43, 2, 4, 'MATLAB', '1.0', '', 0),
(46, 3, 6, 'Envy', '2017', '', 0),
(47, 3, 6, 'llll', '2.0', 'ssss', 1),
(48, 4, 6, 'Inspiron', '1.0', '2.4GHz', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab`
--

DROP TABLE IF EXISTS `lab`;
CREATE TABLE IF NOT EXISTS `lab` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `lab_name` varchar(64) NOT NULL,
  `lab_number` varchar(16) NOT NULL,
  `staff_incharge` int(11) NOT NULL,
  `lab_incharge` int(11) NOT NULL,
  `description` varchar(128) NOT NULL,
  `lock_lab` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serial`),
  KEY `staff` (`staff_incharge`),
  KEY `lab_incharge` (`lab_incharge`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lab`
--

INSERT INTO `lab` (`serial`, `lab_name`, `lab_number`, `staff_incharge`, `lab_incharge`, `description`, `lock_lab`) VALUES
(20, 'Analog Electronics Lab', 'ECL05', 9, 10, 'Hardware Lab (used for LIC and AEC)', 0),
(21, 'SDMM Lab', 'ECL01', 13, 12, '8051 Lab', 0),
(22, 'digital electronics lab', 'ecl02', 13, 10, '', 0),
(23, 'DSP Lab', 'ecl03', 15, 12, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `manufacturer`
--

DROP TABLE IF EXISTS `manufacturer`;
CREATE TABLE IF NOT EXISTS `manufacturer` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `lock_manufacturer` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serial`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `manufacturer`
--

INSERT INTO `manufacturer` (`serial`, `name`, `lock_manufacturer`) VALUES
(1, 'Aplab', 0),
(2, 'KeySight', 0),
(3, 'HP', 0),
(4, 'Dell', 0);

-- --------------------------------------------------------

--
-- Table structure for table `operation`
--

DROP TABLE IF EXISTS `operation`;
CREATE TABLE IF NOT EXISTS `operation` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`serial`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `operation`
--

INSERT INTO `operation` (`serial`, `name`) VALUES
(1, 'Create'),
(2, 'Edit'),
(3, 'Update'),
(4, 'Remove'),
(5, 'Transfer');

-- --------------------------------------------------------

--
-- Table structure for table `receipt`
--

DROP TABLE IF EXISTS `receipt`;
CREATE TABLE IF NOT EXISTS `receipt` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `filepath` varchar(128) DEFAULT NULL,
  `vendor_serial` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `amount` int(11) NOT NULL,
  `details` varchar(128) DEFAULT NULL,
  `lock_receipt` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serial`),
  KEY `vendor_serial` (`vendor_serial`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `receipt`
--

INSERT INTO `receipt` (`serial`, `filepath`, `vendor_serial`, `purchase_date`, `amount`, `details`, `lock_receipt`) VALUES
(1, 'uploads/1.pdf', 7, '2018-09-15', 15000, 'asso', 1),
(2, 'uploads/2.pdf', 6, '2018-09-18', 154000, '', 0),
(3, 'uploads/3.pdf', 9, '2018-09-29', 75400, '', 0),
(11, 'uploads/ENV01.pdf', 8, '2018-09-07', 150000, NULL, 0),
(12, 'uploads/KIT10.pdf', 11, '2018-09-07', 10000, NULL, 0),
(14, 'uploads/KIT10.pdf', 11, '2018-09-07', 10000, NULL, 0),
(15, 'uploads/15.pdf', 6, '2018-09-03', 12345, NULL, 0),
(16, 'uploads/16.pdf', 7, '2018-09-03', 12345, 'asso', 0),
(17, 'uploads/17.pdf', 6, '2018-09-03', 123456, NULL, 1),
(18, 'uploads/18.pdf', 8, '2018-09-30', 1234, 'namast', 0),
(19, 'uploads/19.pdf', 9, '2018-01-15', 100000, 'asdf', 1),
(20, NULL, 9, '2018-01-15', 100001, 'asdf', 1);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(60) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `designation` int(11) NOT NULL,
  `permission` int(11) NOT NULL DEFAULT '0',
  `joining_year` year(4) NOT NULL,
  `lock_staff` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serial`),
  UNIQUE KEY `staff_id` (`staff_id`),
  KEY `designation` (`designation`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`serial`, `name`, `staff_id`, `email`, `password`, `designation`, `permission`, `joining_year`, `lock_staff`) VALUES
(1, 'Admin', 1, 'admin@gmail.com', '$2y$10$ZTBmOTg5YWM0NGY5M2E1NOQNXJt6Ghajg4ArgHGVvZHUbdk0lQ6F6', 3, 5, 2014, 1),
(9, 'Anil Bhat', 2, 'bhat@gmail.com', '$2y$10$ZjMyMTdkYTE0MWNhNzI3NOgjNMOz/Ef6o.zwjJbC1Icritx3aJlka', 4, 2, 2014, 0),
(10, 'Shripathi Sir', 3, 'shripathi@gmail.com', '$2y$10$MDUwNzFkZDU3MGE3MTdhZOPzMPnBxwjR4Ie2s9YjQ1LEuVhhYR8qm', 7, 1, 1999, 0),
(12, 'Nayana Madam', 4, 'nayana@gmail.com', '$2y$10$NjdiOWM3ZjczM2UwNDI2Z.Mms1sgdslt00QenMh7045veBJKLs1wW', 7, 1, 2010, 0),
(13, 'Sukesh Sir', 5, 'sukesh@gmail.com', '$2y$10$NjQyNzQ2ZGFmMmQ0ZDVhNOux5OL5PpbP0aPfWbvuTXmNFkIl0fhA6', 3, 1, 2006, 0),
(14, 'Rekha Mam', 1000, 'hod@gmail.com', '$2y$10$MGJhMmI0MDcwNTdlMGZmZOTf6mi/xcvR/AcsrsoSpm0lk50gAEgjm', 1, 1, 1986, 0),
(15, 'K S Shivaprakasha', 2291, 'shivaprakasha.ks@nitte.edu.in', '$2y$10$ZThiYmM2YzFkNzc2NTY0O.YZgODxTD5/uaDxyfbgruPwG/DQoqmH.', 3, 1, 2016, 0),
(16, 'Srinivas', 1001, 'ashrinivaspai@yahoo.com', '$2y$10$ODE0OWYyMTVhYTBkMjhhNuCqRSoadUlo.1ok7EytJrJ6ySnlRXSre', 3, 1, 2014, 0),
(17, 'Sachin', 123456, 'sachin@gmail.com', '$2y$10$MTRlNjQxY2MzNTkxZDYxOOhjQC8.ybrqf/LCHlNyzgPvxxafXAtzK', 7, 1, 2018, 0);

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `operation` varchar(20) NOT NULL,
  PRIMARY KEY (`serial`),
  UNIQUE KEY `operation` (`operation`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`serial`, `operation`) VALUES
(2, 'Broken'),
(1, 'Operational'),
(5, 'Remove'),
(4, 'Repaired'),
(3, 'Under Repair');

-- --------------------------------------------------------

--
-- Table structure for table `transfer_item`
--

DROP TABLE IF EXISTS `transfer_item`;
CREATE TABLE IF NOT EXISTS `transfer_item` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_serial` int(11) NOT NULL,
  `to_lab` int(11) NOT NULL,
  `from_lab` int(11) NOT NULL,
  `staff_requested` int(11) NOT NULL,
  `staff_responded` int(11) DEFAULT NULL,
  `hod_approved` int(2) NOT NULL DEFAULT '0',
  `staff_approved` int(2) DEFAULT '0',
  `request_message` varchar(128) DEFAULT NULL,
  `authorize_message` varchar(128) DEFAULT NULL,
  `response_message` varchar(128) DEFAULT NULL,
  `date_requested` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_authorized` datetime DEFAULT NULL,
  `date_responded` datetime DEFAULT NULL,
  `lock_transfer` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serial`),
  KEY `inventory_serial` (`inventory_serial`),
  KEY `to_lab` (`to_lab`),
  KEY `from_lab` (`from_lab`),
  KEY `staff_requested` (`staff_requested`),
  KEY `staff_responded` (`staff_responded`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `transfer_item`
--

INSERT INTO `transfer_item` (`serial`, `inventory_serial`, `to_lab`, `from_lab`, `staff_requested`, `staff_responded`, `hod_approved`, `staff_approved`, `request_message`, `authorize_message`, `response_message`, `date_requested`, `date_authorized`, `date_responded`, `lock_transfer`) VALUES
(56, 49, 20, 21, 1, NULL, 1, 0, 'please', '', NULL, '2018-08-29 22:22:37', '0000-00-00 00:00:00', NULL, 1),
(57, 68, 20, 23, 1, 1, 1, 1, 'please dude', '', 'done', '2018-09-14 15:34:36', '0000-00-00 00:00:00', '2018-09-14 15:35:02', 0),
(58, 69, 20, 23, 1, 1, 1, 1, 'please', '', 'okay', '2018-09-14 15:43:25', '0000-00-00 00:00:00', '2018-09-14 15:52:13', 0),
(59, 51, 23, 20, 1, 1, 1, 1, 'please', '', 'done', '2018-09-18 11:51:48', '0000-00-00 00:00:00', '2018-09-18 11:52:11', 0),
(60, 75, 20, 21, 1, 1, 1, 1, 'pls', '', 'okay', '2018-10-05 14:31:53', '0000-00-00 00:00:00', '2019-01-23 21:03:36', 0),
(61, 68, 21, 20, 1, 1, 1, 1, 'please', '', '', '2019-01-23 21:00:45', '0000-00-00 00:00:00', '2019-03-23 17:18:55', 0),
(62, 68, 20, 21, 1, 1, 1, -1, 'please', 'cant', 'done', '2019-03-23 17:26:52', '2019-03-23 17:37:02', '2019-03-23 17:39:48', 0),
(63, 75, 21, 20, 1, NULL, -1, 0, 'please', 'cant', NULL, '2019-03-23 17:40:24', '2019-03-23 17:40:50', NULL, 0),
(64, 75, 21, 20, 1, 1, 1, 1, 'please', 'can', 'done', '2019-03-23 17:55:54', '2019-03-23 18:01:32', '2019-03-23 18:01:57', 0),
(65, 75, 20, 21, 1, NULL, -1, 0, 'please', 'cant', NULL, '2019-03-23 18:02:43', '2019-03-23 18:03:30', NULL, 0),
(66, 75, 20, 21, 1, 1, 1, 1, 'please', 'can', 'done', '2019-03-23 18:11:36', '2019-03-23 18:15:02', '2019-03-23 18:15:08', 0),
(67, 68, 20, 21, 1, NULL, 1, 0, 'please', 'can', NULL, '2019-03-23 18:20:08', '2019-03-23 18:20:18', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

DROP TABLE IF EXISTS `vendor`;
CREATE TABLE IF NOT EXISTS `vendor` (
  `serial` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `lock_vendor` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serial`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `vendor`
--

INSERT INTO `vendor` (`serial`, `name`, `lock_vendor`) VALUES
(6, 'Charan', 0),
(7, 'TI', 0),
(8, 'Alpha Systems', 0),
(9, 'Manohar Radio House', 0),
(11, 'Anil Computers', 0);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`status`) REFERENCES `status` (`serial`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`lab_serial`) REFERENCES `lab` (`serial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_5` FOREIGN KEY (`last_operation`) REFERENCES `operation` (`serial`),
  ADD CONSTRAINT `inventory_ibfk_6` FOREIGN KEY (`receipt_serial`) REFERENCES `receipt` (`serial`),
  ADD CONSTRAINT `item` FOREIGN KEY (`serial_item`) REFERENCES `item` (`serial`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `inventory_log_ibfk_2` FOREIGN KEY (`lab_serial`) REFERENCES `lab` (`serial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_log_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`serial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_log_ibfk_4` FOREIGN KEY (`status`) REFERENCES `status` (`serial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_log_ibfk_5` FOREIGN KEY (`inventory_serial`) REFERENCES `inventory` (`serial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_log_ibfk_6` FOREIGN KEY (`operation_serial`) REFERENCES `operation` (`serial`);

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `cat` FOREIGN KEY (`category_serial`) REFERENCES `category` (`serial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `mn` FOREIGN KEY (`manufacturer_serial`) REFERENCES `manufacturer` (`serial`) ON UPDATE CASCADE;

--
-- Constraints for table `lab`
--
ALTER TABLE `lab`
  ADD CONSTRAINT `lab_ibfk_1` FOREIGN KEY (`staff_incharge`) REFERENCES `staff` (`serial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `lab_ibfk_2` FOREIGN KEY (`lab_incharge`) REFERENCES `staff` (`serial`) ON UPDATE CASCADE;

--
-- Constraints for table `receipt`
--
ALTER TABLE `receipt`
  ADD CONSTRAINT `receipt_ibfk_1` FOREIGN KEY (`vendor_serial`) REFERENCES `vendor` (`serial`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`designation`) REFERENCES `designation` (`serial`) ON UPDATE CASCADE;

--
-- Constraints for table `transfer_item`
--
ALTER TABLE `transfer_item`
  ADD CONSTRAINT `transfer_item_ibfk_1` FOREIGN KEY (`inventory_serial`) REFERENCES `inventory` (`serial`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transfer_item_ibfk_2` FOREIGN KEY (`to_lab`) REFERENCES `lab` (`serial`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transfer_item_ibfk_3` FOREIGN KEY (`from_lab`) REFERENCES `lab` (`serial`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transfer_item_ibfk_4` FOREIGN KEY (`staff_requested`) REFERENCES `staff` (`serial`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `transfer_item_ibfk_5` FOREIGN KEY (`staff_responded`) REFERENCES `staff` (`serial`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
