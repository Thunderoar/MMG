-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 19, 2025 at 07:54 AM
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
  `priority` int GENERATED ALWAYS AS (
    CASE 
      WHEN FromWhere = 'WhatsApp' AND WithWho = 'Ibu / Bapa' THEN 1
      WHEN FromWhere = 'WhatsApp' AND WithWho = 'Rakan / Saudara' THEN 2
      WHEN FromWhere = 'WhatsApp' AND WithWho = 'Sendiri' AND canMakeDecision = 1 THEN 3
      WHEN FromWhere = 'WhatsApp' AND WithWho = 'Sendiri' AND canMakeDecision = 0 THEN 4
      WHEN FromWhere = 'Walk-In' AND WithWho = 'Ibu / Bapa' THEN 5
      WHEN FromWhere = 'Walk-In' AND WithWho = 'Rakan / Saudara' THEN 6
      WHEN FromWhere = 'Walk-In' AND WithWho = 'Sendiri' AND canMakeDecision = 1 THEN 7
      ELSE 8
    END
  ) STORED,
  PRIMARY KEY (`NoIC`),
  KEY `idx_noic_display` (`NoIC_Display`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendedstudent`
--

INSERT INTO `attendedstudent` (`FromWhere`, `Giliran`, `fullName`, `NoIC`, `NoTel`, `DateofArrival`, `WithWho`, `canMakeDecision`, `is_dealt`) VALUES
('Walk-In', 1, 'one', '121212312421', '214-12412421', '2025-02-19 07:49:58', 'Sendiri', 0, 0),
('WhatsApp', 3, 'tiga', '123981293891', '128-88421842', '2025-02-19 07:50:37', 'Sendiri', 0, 0),
('WhatsApp', 2, 'dua', '124141241128', '081-28231241', '2025-02-19 07:50:18', 'Sendiri', 0, 0);

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
