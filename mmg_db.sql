-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 05, 2025 at 08:32 AM
-- Server version: 9.2.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mmg_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `created_at`, `last_login`) VALUES
(1, 'admin', 'admin123', 'admin@example.com', '2025-05-05 03:01:51', '2025-05-05 03:11:14');

-- --------------------------------------------------------

--
-- Table structure for table `attendedstudent`
--

DROP TABLE IF EXISTS `attendedstudent`;
CREATE TABLE IF NOT EXISTS `attendedstudent` (
  `FromWhere` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Giliran` int NOT NULL,
  `fullName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `NoIC` char(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Stored as 12 digits without separators',
  `NoIC_Display` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci GENERATED ALWAYS AS (concat(substr(`NoIC`,1,6),_utf8mb4'-',substr(`NoIC`,7,2),_utf8mb4'-',substr(`NoIC`,9,4))) STORED,
  `NoTel` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `student_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `guardian_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invited_officer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tempat_temuduga` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `DateofArrival` timestamp NOT NULL,
  `WithWho` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `canMakeDecision` tinyint(1) NOT NULL DEFAULT '1',
  `is_dealt` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `priority` int GENERATED ALWAYS AS ((case when ((`FromWhere` = _utf8mb4'WhatsApp') and (`WithWho` = _utf8mb4'Ibu / Bapa')) then 1 when ((`FromWhere` = _utf8mb4'WhatsApp') and (`WithWho` = _utf8mb4'Rakan / Saudara')) then 2 when ((`FromWhere` = _utf8mb4'WhatsApp') and (`WithWho` = _utf8mb4'Sendiri') and (`canMakeDecision` = 1)) then 3 when ((`FromWhere` = _utf8mb4'WhatsApp') and (`WithWho` = _utf8mb4'Sendiri') and (`canMakeDecision` = 0)) then 4 when ((`FromWhere` = _utf8mb4'Walk-In') and (`WithWho` = _utf8mb4'Ibu / Bapa')) then 5 when ((`FromWhere` = _utf8mb4'Walk-In') and (`WithWho` = _utf8mb4'Rakan / Saudara')) then 6 when ((`FromWhere` = _utf8mb4'Walk-In') and (`WithWho` = _utf8mb4'Sendiri') and (`canMakeDecision` = 1)) then 7 else 8 end)) STORED,
  `seminar_id` int DEFAULT NULL,
  PRIMARY KEY (`NoIC`),
  KEY `idx_noic_display` (`NoIC_Display`),
  KEY `idx_priority` (`priority`),
  KEY `fk_seminar` (`seminar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `attendedstudent`
--
DROP TRIGGER IF EXISTS `before_attendedstudent_insert`;
DELIMITER $$
CREATE TRIGGER `before_attendedstudent_insert` BEFORE INSERT ON `attendedstudent` FOR EACH ROW BEGIN
    IF NEW.NoIC REGEXP '[^0-9]' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'NoIC must contain only numbers';
    END IF;
    IF LENGTH(NEW.NoIC) != 12 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'NoIC must be exactly 12 digits';
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `before_attendedstudent_update`;
DELIMITER $$
CREATE TRIGGER `before_attendedstudent_update` BEFORE UPDATE ON `attendedstudent` FOR EACH ROW BEGIN
    IF NEW.NoIC REGEXP '[^0-9]' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'NoIC must contain only numbers';
    END IF;
    IF LENGTH(NEW.NoIC) != 12 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'NoIC must be exactly 12 digits';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `seminar_schedules`
--

DROP TABLE IF EXISTS `seminar_schedules`;
CREATE TABLE IF NOT EXISTS `seminar_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `seminar_date` date NOT NULL,
  `seminar_time` time NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_seminar_date` (`seminar_date`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_description`, `updated_at`, `updated_by`) VALUES
(1, 'state_name', 'KELANTAN', 'Name of the state for TEMUDUGA TVET', '2025-05-05 03:11:23', 1),
(2, 'system_title', 'TEMUDUGA TVET', 'Main system title', '2025-05-05 03:11:23', 1),
(3, 'system_logo', 'image/magmalogo.png', 'Path to system logo', '2025-05-05 03:01:51', NULL),
(4, 'color_theme', '#4361ee', 'Primary color theme', '2025-05-05 03:11:23', 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendedstudent`
--
ALTER TABLE `attendedstudent`
  ADD CONSTRAINT `attendedstudent_ibfk_1` FOREIGN KEY (`seminar_id`) REFERENCES `seminar_schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_seminar` FOREIGN KEY (`seminar_id`) REFERENCES `seminar_schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_seminar_id` FOREIGN KEY (`seminar_id`) REFERENCES `seminar_schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admin_users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
