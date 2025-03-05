-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 05, 2025 at 04:14 AM
-- Server version: 8.3.0
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
  `DateofArrival` timestamp NOT NULL,
  `WithWho` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `canMakeDecision` tinyint(1) NOT NULL DEFAULT '1',
  `is_dealt` tinyint(1) DEFAULT '0',
  `priority` int GENERATED ALWAYS AS ((case when ((`FromWhere` = _utf8mb4'WhatsApp') and (`WithWho` = _utf8mb4'Ibu / Bapa')) then 1 when ((`FromWhere` = _utf8mb4'WhatsApp') and (`WithWho` = _utf8mb4'Rakan / Saudara')) then 2 when ((`FromWhere` = _utf8mb4'WhatsApp') and (`WithWho` = _utf8mb4'Sendiri') and (`canMakeDecision` = 1)) then 3 when ((`FromWhere` = _utf8mb4'WhatsApp') and (`WithWho` = _utf8mb4'Sendiri') and (`canMakeDecision` = 0)) then 4 when ((`FromWhere` = _utf8mb4'Walk-In') and (`WithWho` = _utf8mb4'Ibu / Bapa')) then 5 when ((`FromWhere` = _utf8mb4'Walk-In') and (`WithWho` = _utf8mb4'Rakan / Saudara')) then 6 when ((`FromWhere` = _utf8mb4'Walk-In') and (`WithWho` = _utf8mb4'Sendiri') and (`canMakeDecision` = 1)) then 7 else 8 end)) STORED,
  PRIMARY KEY (`NoIC`),
  KEY `idx_noic_display` (`NoIC_Display`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendedstudent`
--

INSERT INTO `attendedstudent` (`FromWhere`, `Giliran`, `fullName`, `NoIC`, `NoTel`, `DateofArrival`, `WithWho`, `canMakeDecision`, `is_dealt`) VALUES
('WhatsApp', 1, 'MUHAMMAD FARHAN BIN SAMIL', '031029040017', '019-6340894', '2025-03-03 03:24:12', 'IbuBapa', 0, 1),
('Walk-In', 2, 'tiga', '123131232183', '821-32381283', '2025-02-19 08:34:08', 'Sendiri', 0, 0),
('WhatsApp', 1, 'empat', '123213213123', '123-12321312', '2025-02-19 08:35:07', 'Sendiri', 0, 1),
('WhatsApp', 1, 'satu', '123812838213', '128-38128312', '2025-02-19 08:33:42', 'Sendiri', 0, 1),
('Walk-In', 2, 'dua', '123821838213', '812-93891283', '2025-02-19 08:33:54', 'Sendiri', 0, 1);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
