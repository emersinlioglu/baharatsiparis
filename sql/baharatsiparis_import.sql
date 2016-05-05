-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 24. Apr 2016 um 20:25
-- Server-Version: 10.1.8-MariaDB
-- PHP-Version: 5.5.30

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `baharatsiparis`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `accessory_product`
--

DROP TABLE IF EXISTS `accessory_product`;
CREATE TABLE IF NOT EXISTS `accessory_product` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `accessory_product_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`,`accessory_product_id`),
  KEY `fk_product_product_product2_idx` (`accessory_product_id`),
  KEY `fk_product_product_product1_idx` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute`
--

DROP TABLE IF EXISTS `attribute`;
CREATE TABLE IF NOT EXISTS `attribute` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `length` int(11) DEFAULT NULL,
  `is_uppercase` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_multi_select` tinyint(1) NOT NULL DEFAULT '0',
  `option_values` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_group`
--

DROP TABLE IF EXISTS `attribute_group`;
CREATE TABLE IF NOT EXISTS `attribute_group` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_group_attribute`
--

DROP TABLE IF EXISTS `attribute_group_attribute`;
CREATE TABLE IF NOT EXISTS `attribute_group_attribute` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `attribute_group_id` int(10) UNSIGNED DEFAULT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_attribute_grp_attribute_col1_idx` (`attribute_group_id`),
  KEY `fk_attribute_grpl_attribute1_idx` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_group_lang`
--

DROP TABLE IF EXISTS `attribute_group_lang`;
CREATE TABLE IF NOT EXISTS `attribute_group_lang` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_attibute_col_lang_attribute_col1_idx` (`attribute_group_id`),
  KEY `fk_attibute_col_lang_lang1_idx` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_lang`
--

DROP TABLE IF EXISTS `attribute_lang`;
CREATE TABLE IF NOT EXISTS `attribute_lang` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_attribute_lang_attribute1_idx` (`attribute_id`),
  KEY `fk_attribute_lang_lang1_idx` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_value`
--

DROP TABLE IF EXISTS `attribute_value`;
CREATE TABLE IF NOT EXISTS `attribute_value` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `value` text,
  `value_min` varchar(255) DEFAULT NULL,
  `value_max` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_inherited` tinyint(1) NOT NULL DEFAULT '0',
  `product_lang_id` int(10) UNSIGNED NOT NULL,
  `attribute_lang_id` int(10) UNSIGNED NOT NULL,
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_lang_attribute_lang_attribute_idx` (`attribute_lang_id`),
  KEY `fk_product_lang_attribute_lang_product_idx` (`product_lang_id`),
  KEY `fk_attribute_value_attribute_group1_idx` (`attribute_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Trigger `attribute_value`
--
DROP TRIGGER IF EXISTS `attribute_value_AFTER_INSERT`;
DELIMITER $$
CREATE TRIGGER `attribute_value_AFTER_INSERT` AFTER INSERT ON `attribute_value` FOR EACH ROW BEGIN

        -- definitions
        DECLARE now DATETIME;
        set now := NOW();

		IF NOT ISNULL(NEW.`value`) THEN
			-- insert log entry
			INSERT INTO
				attribute_value_log
			SET
				`attribute_value_id` = NEW.id,
                `user_id` = @userid,
				`item` = 'insert_value',
                `old_value` = '',
				`new_value` = NEW.`value`,
                `date` = now
			;
		END IF;

		IF NOT ISNULL(NEW.`value_min`) OR NOT ISNULL(NEW.`value_max`) THEN
			-- insert log entry
			INSERT INTO
				attribute_value_log
			SET
				`attribute_value_id` = NEW.id,
                `user_id` = @userid,
				`item` = 'insert_value',
                `old_value` = '',
				`new_value` = CONCAT(NEW.`value_min`, '-', NEW.`value_max`),
                `date` = now
			;
		END IF;

-- 		IF NOT ISNULL(NEW.`value_max`) THEN
-- 			-- insert log entry
-- 			INSERT INTO
-- 				attribute_value_log
-- 			SET
-- 				`attribute_value_id` = NEW.id,
--                 `user_id` = @userid,
-- 				`item` = 'insert',
--                 `old_value` = '',
-- 				`new_value` = NEW.`value_max`,
--                 `date` = now
-- 			;
-- 		END IF;

--         IF NOT ISNULL(NEW.`value_max`) THEN
-- 			-- insert log entry
-- 			INSERT INTO
-- 				attribute_value_log
-- 			SET
-- 				`attribute_value_id` = NEW.id,
--                 `user_id` = @userid,
-- 				`item` = 'insert_is_inherited',
--                 `old_value` = '',
-- 				`new_value` = NEW.`value_max`,
--                 `date` = now
-- 			;
-- 		END IF;

    END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `attribute_value_AFTER_UPDATE`;
DELIMITER $$
CREATE TRIGGER `attribute_value_AFTER_UPDATE` AFTER UPDATE ON `attribute_value` FOR EACH ROW BEGIN

	-- definitions
	DECLARE now 	DATETIME;
	SET now 		:= NOW();

    IF NEW.`value` <> OLD.`value` THEN
		INSERT INTO
			attribute_value_log
		SET
			`attribute_value_id` = NEW.id,
			`user_id` = @userid,
			`item` = 'value',
			`old_value` = OLD.`value`,
			`new_value` = NEW.`value`,
			`date` = now
		;
	END IF;

    IF NEW.`value_min` <> OLD.`value_min` OR NEW.`value_max` <> OLD.`value_max` THEN
		INSERT INTO
			attribute_value_log
		SET
			`attribute_value_id` = NEW.id,
			`user_id` = @userid,
			`item` = 'value_min_max',
			`old_value` = CONCAT(OLD.`value_min`, '-', OLD.`value_max`),
			`new_value` = CONCAT(NEW.`value_min`, '-', NEW.`value_max`),
			`date` = now
		;
	END IF;

--    IF NEW.`value_max` <> OLD.`value_max` THEN
--  		INSERT INTO
--  			attribute_value_log
--  		SET
--  			`attribute_value_id` = NEW.id,
--  			`user_id` = @userid,
--  			`item` = 'value_max',
--  			`old_value` = OLD.`value_max`,
--  			`new_value` = NEW.`value_max`,
--  			`date` = now
--  		;
--  	END IF;

--     IF NEW.`is_inherited` <> OLD.`is_inherited` THEN
-- 		-- insert log entry
-- 		INSERT INTO
-- 			attribute_value_log
-- 		SET
-- 			`attribute_value_id` = NEW.id,
-- 			`user_id` = @userid,
-- 			`item` = 'is_inherited',
-- 			`old_value` = OLD.`is_inherited`,
-- 			`new_value` = NEW.`is_inherited`,
-- 			`date` = now
-- 		;
-- 	END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_value_log`
--

DROP TABLE IF EXISTS `attribute_value_log`;
CREATE TABLE IF NOT EXISTS `attribute_value_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `item` varchar(128) NOT NULL,
  `old_value` text,
  `new_value` text,
  `date` datetime NOT NULL,
  `attribute_value_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_attribute_value_attribute_value_log1_idx` (`attribute_value_id`),
  KEY `fk_user_attribute_value1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Root-Product for Default-Attribute-Values',
  `sort` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_category_category1_idx` (`category_id`),
  KEY `fk_category_template1_idx` (`template_id`),
  KEY `fk_category_product1_idx` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `category`
--

INSERT INTO `category` (`id`, `template_id`, `category_id`, `product_id`, `sort`) VALUES
(1, NULL, NULL, NULL, NULL),
(2, NULL, NULL, NULL, NULL),
(3, NULL, NULL, NULL, NULL),
(4, NULL, NULL, NULL, NULL),
(5, NULL, NULL, NULL, NULL),
(6, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `category_lang`
--

DROP TABLE IF EXISTS `category_lang`;
CREATE TABLE IF NOT EXISTS `category_lang` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_category_lang_category` (`category_id`),
  KEY `idx_category_lang_lang1` (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `category_lang`
--

INSERT INTO `category_lang` (`id`, `category_id`, `lang_id`, `name`, `alias`, `description`, `sort`) VALUES
(1, 1, 1, 'Pet S 190ml', NULL, NULL, NULL),
(2, 1, 2, 'Pet S 190ml', NULL, NULL, NULL),
(3, 2, 1, 'Pet mittel 350ml', NULL, NULL, NULL),
(4, 2, 2, 'Pet L 350ml', NULL, NULL, NULL),
(5, 3, 1, 'Groß Pet', NULL, NULL, NULL),
(6, 3, 2, 'Büyük Pet', NULL, NULL, NULL),
(7, 4, 1, 'Tee', NULL, NULL, NULL),
(8, 4, 2, 'Caylar', NULL, NULL, NULL),
(9, 5, 1, 'Getrocknet', NULL, NULL, NULL),
(10, 5, 2, 'Kurular', NULL, NULL, NULL),
(11, 6, 1, 'Eimer', NULL, NULL, NULL),
(12, 6, 2, 'Kova', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lang`
--

DROP TABLE IF EXISTS `lang`;
CREATE TABLE IF NOT EXISTS `lang` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `iso` varchar(2) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `lang`
--

INSERT INTO `lang` (`id`, `name`, `iso`, `sort`, `is_active`) VALUES
(1, 'de', 'de', 1, 1),
(2, 'tr', 'tr', 2, 1),
(3, 'en', 'en', 3, 0),
(4, 'ru', 'ru', 4, 0),
(5, 'po', 'po', 5, 0),
(6, 'sp', 'sp', 6, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `linked_product`
--

DROP TABLE IF EXISTS `linked_product`;
CREATE TABLE IF NOT EXISTS `linked_product` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `linked_product_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`,`linked_product_id`),
  KEY `fk_product_product_product4_idx` (`linked_product_id`),
  KEY `fk_product_product_product3_idx` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `multiple_usage`
--

DROP TABLE IF EXISTS `multiple_usage`;
CREATE TABLE IF NOT EXISTS `multiple_usage` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `fk_product_category1_category1_idx` (`category_id`),
  KEY `fk_product_category1_product1_idx` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `amount` varchar(16) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `online` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `image_url` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_idx` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `product`
--

INSERT INTO `product` (`id`, `product_id`, `number`, `amount`, `price`, `online`, `sort`, `is_system`, `image_url`) VALUES
(1, NULL, NULL, '80g', 0.69, 0, 0, 0, NULL),
(2, NULL, NULL, '55g', 0.65, 0, 0, 0, NULL),
(3, NULL, NULL, '25g', 0.39, 0, 0, 0, NULL),
(4, NULL, NULL, '90g', 0.65, 0, 0, 0, NULL),
(5, NULL, NULL, '90g', 0.5, 0, 0, 0, NULL),
(6, NULL, NULL, '35g', 0.5, 0, 0, 0, NULL),
(7, NULL, NULL, '', 0, 0, 0, 0, NULL),
(8, NULL, NULL, '65g', 0.59, 0, 0, 0, NULL),
(9, NULL, NULL, '25g', 0.55, 0, 0, 0, NULL),
(10, NULL, NULL, '60g', 0.5, 0, 0, 0, NULL),
(11, NULL, NULL, '30g', 0.5, 0, 0, 0, NULL),
(12, NULL, NULL, '75g', 0.55, 0, 0, 0, NULL),
(13, NULL, NULL, '20g', 1.15, 0, 0, 0, NULL),
(14, NULL, NULL, '', 0.69, 0, 0, 0, NULL),
(15, NULL, NULL, '45g', 0.5, 0, 0, 0, NULL),
(16, NULL, NULL, '65g', 0.65, 0, 0, 0, NULL),
(17, NULL, NULL, '50g', 0.5, 0, 0, 0, NULL),
(18, NULL, NULL, '', 0.69, 0, 0, 0, NULL),
(19, NULL, NULL, '65g', 0.69, 0, 0, 0, NULL),
(20, NULL, NULL, '140g', 0.5, 0, 0, 0, NULL),
(21, NULL, NULL, '90g', 0.89, 0, 0, 0, NULL),
(22, NULL, NULL, '70g', 0.55, 0, 0, 0, NULL),
(23, NULL, NULL, '70g', 0.5, 0, 0, 0, NULL),
(24, NULL, NULL, '80g', 0.5, 0, 0, 0, NULL),
(25, NULL, NULL, '75g', 0.5, 0, 0, 0, NULL),
(26, NULL, NULL, '140g', 0.5, 0, 0, 0, NULL),
(27, NULL, NULL, '70g', 0.55, 0, 0, 0, NULL),
(28, NULL, NULL, '175g', 0.55, 0, 0, 0, NULL),
(29, NULL, NULL, '175g', 0.5, 0, 0, 0, NULL),
(30, NULL, NULL, '60g', 0.69, 0, 0, 0, NULL),
(31, NULL, NULL, '50g', 1.55, 0, 0, 0, NULL),
(32, NULL, NULL, '65g', 1.55, 0, 0, 0, NULL),
(33, NULL, NULL, '85g', 0.59, 0, 0, 0, NULL),
(34, NULL, NULL, '100g', 0.65, 0, 0, 0, NULL),
(35, NULL, NULL, '50g', 0.5, 0, 0, 0, NULL),
(36, NULL, NULL, '50g', 0.5, 0, 0, 0, NULL),
(37, NULL, NULL, '60g', 0.5, 0, 0, 0, NULL),
(38, NULL, NULL, '85g', 0.69, 0, 0, 0, NULL),
(39, NULL, NULL, '40g', 0.55, 0, 0, 0, NULL),
(40, NULL, NULL, '65g', 0.65, 0, 0, 0, NULL),
(41, NULL, NULL, '70g', 0.65, 0, 0, 0, NULL),
(42, NULL, NULL, '80g', 0.5, 0, 0, 0, NULL),
(43, NULL, NULL, '50g', 0, 0, 0, 0, NULL),
(44, NULL, NULL, '80g', 0.59, 0, 0, 0, NULL),
(45, NULL, NULL, '50g', 0.5, 0, 0, 0, NULL),
(46, NULL, NULL, '100g', 0.5, 0, 0, 0, NULL),
(47, NULL, NULL, '8g', 0.5, 0, 0, 0, NULL),
(48, NULL, NULL, '13g', 0.39, 0, 0, 0, NULL),
(49, NULL, NULL, '175g', 0.5, 0, 0, 0, NULL),
(50, NULL, NULL, '100g', 0.75, 0, 0, 0, NULL),
(51, NULL, NULL, '25g', 0.39, 0, 0, 0, NULL),
(52, NULL, NULL, '12st', 2.35, 0, 0, 0, NULL),
(53, NULL, NULL, '75g', 2.35, 0, 0, 0, NULL),
(54, NULL, NULL, '165g', 0.5, 0, 0, 0, NULL),
(55, NULL, NULL, '50g', 1.29, 0, 0, 0, NULL),
(56, NULL, NULL, '70g', 1.29, 0, 0, 0, NULL),
(57, NULL, NULL, '40g', 0.5, 0, 0, 0, NULL),
(58, NULL, NULL, '20g', 0.39, 0, 0, 0, NULL),
(59, NULL, NULL, '65g', 0.55, 0, 0, 0, NULL),
(60, NULL, NULL, '65g', 0.55, 0, 0, 0, NULL),
(61, NULL, NULL, '65g', 0.65, 0, 0, 0, NULL),
(62, NULL, NULL, '70g', 0.55, 0, 0, 0, NULL),
(63, NULL, NULL, '50g', 0.59, 0, 0, 0, NULL),
(64, NULL, NULL, '70g', 0.55, 0, 0, 0, NULL),
(65, NULL, NULL, '70g', 0.69, 0, 0, 0, NULL),
(66, NULL, NULL, '15g', 0.55, 0, 0, 0, NULL),
(67, NULL, NULL, '15g', 0.39, 0, 0, 0, NULL),
(68, NULL, NULL, '75g', 1.55, 0, 0, 0, NULL),
(69, NULL, NULL, '50g', 1.55, 0, 0, 0, NULL),
(70, NULL, NULL, '40g', 1.55, 0, 0, 0, NULL),
(71, NULL, NULL, '80g', 1.25, 0, 0, 0, NULL),
(72, NULL, NULL, '75g', 1.25, 0, 0, 0, NULL),
(73, NULL, NULL, '75g', 1.45, 0, 0, 0, NULL),
(74, NULL, NULL, '90g', 1.45, 0, 0, 0, NULL),
(75, NULL, NULL, '', 0.89, 0, 0, 0, NULL),
(76, NULL, NULL, '70g', 0.89, 0, 0, 0, NULL),
(77, NULL, NULL, '85g', 2.85, 0, 0, 0, NULL),
(78, NULL, NULL, '80g', 2.85, 0, 0, 0, NULL),
(79, NULL, NULL, '30g', 0.5, 0, 0, 0, NULL),
(80, NULL, NULL, '145g', 0.5, 0, 0, 0, NULL),
(81, NULL, NULL, '45g', 0.5, 0, 0, 0, NULL),
(82, NULL, NULL, '15g', 1.75, 0, 0, 0, NULL),
(83, NULL, NULL, '50g', 0.55, 0, 0, 0, NULL),
(84, NULL, NULL, '15g', 0.5, 0, 0, 0, NULL),
(85, NULL, NULL, '10g', 0.55, 0, 0, 0, NULL),
(86, NULL, NULL, '80g', 0.69, 0, 0, 0, NULL),
(87, NULL, NULL, '80g', 0.65, 0, 0, 0, NULL),
(88, NULL, NULL, '110g', 0.5, 0, 0, 0, NULL),
(89, NULL, NULL, '75g', 0.5, 0, 0, 0, NULL),
(90, NULL, NULL, '85g', 0.55, 0, 0, 0, NULL),
(91, NULL, NULL, '100g', 0.69, 0, 0, 0, NULL),
(92, NULL, NULL, '70g', 0.55, 0, 0, 0, NULL),
(93, NULL, NULL, '80g', 0.69, 0, 0, 0, NULL),
(94, NULL, NULL, '70g', 1.85, 0, 0, 0, NULL),
(95, NULL, NULL, '85g', 0.65, 0, 0, 0, NULL),
(96, NULL, NULL, '80g', 0.5, 0, 0, 0, NULL),
(97, NULL, NULL, '25g', 0.5, 0, 0, 0, NULL),
(98, NULL, NULL, '80g', 0.69, 0, 0, 0, NULL),
(99, NULL, NULL, '45g', 0.5, 0, 0, 0, NULL),
(100, NULL, NULL, '3st', 1.15, 0, 0, 0, NULL),
(101, NULL, NULL, '55g', 0.69, 0, 0, 0, NULL),
(102, NULL, NULL, '', 0.65, 0, 0, 0, NULL),
(103, NULL, NULL, '75g', 0.5, 0, 0, 0, NULL),
(104, NULL, NULL, '7st', 0.69, 0, 0, 0, NULL),
(105, NULL, NULL, '130g', 0.65, 0, 0, 0, NULL),
(106, NULL, NULL, '115g', 0.65, 0, 0, 0, NULL),
(107, NULL, NULL, '80g', 0.55, 0, 0, 0, NULL),
(108, NULL, NULL, '170g', 1.25, 0, 0, 0, NULL),
(109, NULL, NULL, '55g', 0.79, 0, 0, 0, NULL),
(110, NULL, NULL, '180g', 1.25, 0, 0, 0, NULL),
(111, NULL, NULL, '200g', 1.05, 0, 0, 0, NULL),
(112, NULL, NULL, '150g', 1.05, 0, 0, 0, NULL),
(113, NULL, NULL, '55g', 1.05, 0, 0, 0, NULL),
(114, NULL, NULL, '150g', 1.05, 0, 0, 0, NULL),
(115, NULL, NULL, '165g', 1.05, 0, 0, 0, NULL),
(116, NULL, NULL, '', 1.65, 0, 0, 0, NULL),
(117, NULL, NULL, '150g', 1.25, 0, 0, 0, NULL),
(118, NULL, NULL, '115g', 1.05, 0, 0, 0, NULL),
(119, NULL, NULL, '170g', 1.65, 0, 0, 0, NULL),
(120, NULL, NULL, '140g', 1.65, 0, 0, 0, NULL),
(121, NULL, NULL, '180g', 1.75, 0, 0, 0, NULL),
(122, NULL, NULL, '150g', 1.05, 0, 0, 0, NULL),
(123, NULL, NULL, '150g', 1.05, 0, 0, 0, NULL),
(124, NULL, NULL, '170g', 1.05, 0, 0, 0, NULL),
(125, NULL, NULL, '150g', 1.05, 0, 0, 0, NULL),
(126, NULL, NULL, '170g', 1.05, 0, 0, 0, NULL),
(127, NULL, NULL, '300g', 1.05, 0, 0, 0, NULL),
(128, NULL, NULL, '150g', 1.25, 0, 0, 0, NULL),
(129, NULL, NULL, '350g', 1.05, 0, 0, 0, NULL),
(130, NULL, NULL, '350g', 1.15, 0, 0, 0, NULL),
(131, NULL, NULL, '130g', 1.25, 0, 0, 0, NULL),
(132, NULL, NULL, '170g', 1.05, 0, 0, 0, NULL),
(133, NULL, NULL, '190g', 1.25, 0, 0, 0, NULL),
(134, NULL, NULL, '105g', 1.05, 0, 0, 0, NULL),
(135, NULL, NULL, '100g', 1.05, 0, 0, 0, NULL),
(136, NULL, NULL, '140g', 1.05, 0, 0, 0, NULL),
(137, NULL, NULL, '140g', 1.25, 0, 0, 0, NULL),
(138, NULL, NULL, '150g', 1.25, 0, 0, 0, NULL),
(139, NULL, NULL, '165g', 1.05, 0, 0, 0, NULL),
(140, NULL, NULL, '160g', 1.05, 0, 0, 0, NULL),
(141, NULL, NULL, '115g', 1.05, 0, 0, 0, NULL),
(142, NULL, NULL, '12g', 0.89, 0, 0, 0, NULL),
(143, NULL, NULL, '170g', 1.25, 0, 0, 0, NULL),
(144, NULL, NULL, '25g', 0.79, 0, 0, 0, NULL),
(145, NULL, NULL, '350g', 1.05, 0, 0, 0, NULL),
(146, NULL, NULL, '55g', 0.79, 0, 0, 0, NULL),
(147, NULL, NULL, '350g', 1.05, 0, 0, 0, NULL),
(148, NULL, NULL, '110g', 2.35, 0, 0, 0, NULL),
(149, NULL, NULL, '40g', 0.79, 0, 0, 0, NULL),
(150, NULL, NULL, '145g', 1.05, 0, 0, 0, NULL),
(151, NULL, NULL, '135g', 1.05, 0, 0, 0, NULL),
(152, NULL, NULL, '110g', 1.25, 0, 0, 0, NULL),
(153, NULL, NULL, '145g', 1.05, 0, 0, 0, NULL),
(154, NULL, NULL, '145g', 1.05, 0, 0, 0, NULL),
(155, NULL, NULL, '145g', 1.45, 0, 0, 0, NULL),
(156, NULL, NULL, '145g', 1.25, 0, 0, 0, NULL),
(157, NULL, NULL, '30g', 1.05, 0, 0, 0, NULL),
(158, NULL, NULL, '30g', 0.89, 0, 0, 0, NULL),
(159, NULL, NULL, '150g', 2.45, 0, 0, 0, NULL),
(160, NULL, NULL, '150g', 2.45, 0, 0, 0, NULL),
(161, NULL, NULL, '175g', 2.85, 0, 0, 0, NULL),
(162, NULL, NULL, '150g', 2.85, 0, 0, 0, NULL),
(163, NULL, NULL, '170g', 5.49, 0, 0, 0, NULL),
(164, NULL, NULL, '65g', 0.89, 0, 0, 0, NULL),
(165, NULL, NULL, '300g', 1.05, 0, 0, 0, NULL),
(166, NULL, NULL, '85g', 1.05, 0, 0, 0, NULL),
(167, NULL, NULL, '155g', 1.35, 0, 0, 0, NULL),
(168, NULL, NULL, '165g', 1.25, 0, 0, 0, NULL),
(169, NULL, NULL, '185g', 1.05, 0, 0, 0, NULL),
(170, NULL, NULL, '150g', 1.05, 0, 0, 0, NULL),
(171, NULL, NULL, '170g', 1.65, 0, 0, 0, NULL),
(172, NULL, NULL, '55g', 0.89, 0, 0, 0, NULL),
(173, NULL, NULL, '170g', 1.25, 0, 0, 0, NULL),
(174, NULL, NULL, '', 1.25, 0, 0, 0, NULL),
(175, NULL, NULL, '14st', 1.25, 0, 0, 0, NULL),
(176, NULL, NULL, '150g', 1.05, 0, 0, 0, NULL),
(177, NULL, NULL, '280g', 1.25, 0, 0, 0, NULL),
(178, NULL, NULL, '250g', 1.25, 0, 0, 0, NULL),
(179, NULL, NULL, '160g', 1.75, 0, 0, 0, NULL),
(180, NULL, NULL, '500g', 2.65, 0, 0, 0, NULL),
(181, NULL, NULL, '180g', 2.65, 0, 0, 0, NULL),
(182, NULL, NULL, '500g', 2.65, 0, 0, 0, NULL),
(183, NULL, NULL, '500g', 2.65, 0, 0, 0, NULL),
(184, NULL, NULL, '350g', 2.65, 0, 0, 0, NULL),
(185, NULL, NULL, '550g', 3.95, 0, 0, 0, NULL),
(186, NULL, NULL, '500g', 2.65, 0, 0, 0, NULL),
(187, NULL, NULL, '500g', 2.65, 0, 0, 0, NULL),
(188, NULL, NULL, '900g', 2.65, 0, 0, 0, NULL),
(189, NULL, NULL, '500g', 2.75, 0, 0, 0, NULL),
(190, NULL, NULL, '600g', 2.95, 0, 0, 0, NULL),
(191, NULL, NULL, '500g', 2.65, 0, 0, 0, NULL),
(192, NULL, NULL, '180g', 1.75, 0, 0, 0, NULL),
(193, NULL, NULL, '130g', 1.75, 0, 0, 0, NULL),
(194, NULL, NULL, '450g', 2.65, 0, 0, 0, NULL),
(195, NULL, NULL, '450g', 2.65, 0, 0, 0, NULL),
(196, NULL, NULL, '475g', 3.25, 0, 0, 0, NULL),
(197, NULL, NULL, '500g', 2.75, 0, 0, 0, NULL),
(198, NULL, NULL, '400g', 2.95, 0, 0, 0, NULL),
(199, NULL, NULL, '450g', 2.65, 0, 0, 0, NULL),
(200, NULL, NULL, '400g', 2.65, 0, 0, 0, NULL),
(201, NULL, NULL, '100g', 1.75, 0, 0, 0, NULL),
(202, NULL, NULL, '500g', 6.45, 0, 0, 0, NULL),
(203, NULL, NULL, '500g', 7.95, 0, 0, 0, NULL),
(204, NULL, NULL, '200g', 2.65, 0, 0, 0, NULL),
(205, NULL, NULL, '900g', 2.65, 0, 0, 0, NULL),
(206, NULL, NULL, '500g', 2.95, 0, 0, 0, NULL),
(207, NULL, NULL, '550g', 2.65, 0, 0, 0, NULL),
(208, NULL, NULL, '500g', 2.65, 0, 0, 0, NULL),
(209, NULL, NULL, '180g', 1.75, 0, 0, 0, NULL),
(210, NULL, NULL, '850g', 2.65, 0, 0, 0, NULL),
(211, NULL, NULL, '10x30g', 0.69, 0, 0, 0, NULL),
(212, NULL, NULL, '20x40g', 0.69, 0, 0, 0, NULL),
(213, NULL, NULL, '25x50g', 0, 0, 0, 0, NULL),
(214, NULL, NULL, '25x30g', 0.69, 0, 0, 0, NULL),
(215, NULL, NULL, '20x30g', 0.69, 0, 0, 0, NULL),
(216, NULL, NULL, '25x30g', 0.69, 0, 0, 0, NULL),
(217, NULL, NULL, '15x30g', 0.69, 0, 0, 0, NULL),
(218, NULL, NULL, '15x30g', 0.69, 0, 0, 0, NULL),
(219, NULL, NULL, '10x30g', 0.69, 0, 0, 0, NULL),
(220, NULL, NULL, '20x30g', 0.69, 0, 0, 0, NULL),
(221, NULL, NULL, '12x30g', 0.69, 0, 0, 0, NULL),
(222, NULL, NULL, '25x30g', 0.69, 0, 0, 0, NULL),
(223, NULL, NULL, '15x30g', 0.69, 0, 0, 0, NULL),
(224, NULL, NULL, '10x30g', 0.69, 0, 0, 0, NULL),
(225, NULL, NULL, '10x30g', 0.69, 0, 0, 0, NULL),
(226, NULL, NULL, '10x30g', 0.69, 0, 0, 0, NULL),
(227, NULL, NULL, '10x30g', 0.69, 0, 0, 0, NULL),
(228, NULL, NULL, '25st', 1.15, 0, 0, 0, NULL),
(229, NULL, NULL, '100g', 1.15, 0, 0, 0, NULL),
(230, NULL, NULL, '200g', 1.39, 0, 0, 0, NULL),
(231, NULL, NULL, '25st', 1.15, 0, 0, 0, NULL),
(232, NULL, NULL, '200g', 0.95, 0, 0, 0, NULL),
(233, NULL, NULL, '50g', 1.49, 0, 0, 0, NULL),
(234, NULL, NULL, '25st', 1.99, 0, 0, 0, NULL),
(235, NULL, NULL, '10kg', 18.95, 0, 0, 0, NULL),
(236, NULL, NULL, '5kg', 9.95, 0, 0, 0, NULL),
(237, NULL, NULL, '5kg', 17.95, 0, 0, 0, NULL),
(238, NULL, NULL, '5kg', 17.95, 0, 0, 0, NULL),
(239, NULL, NULL, '4,5kg', 18.95, 0, 0, 0, NULL),
(240, NULL, NULL, '5kg', 17.95, 0, 0, 0, NULL),
(241, NULL, NULL, '5kg', 17.95, 0, 0, 0, NULL),
(242, NULL, NULL, '2,5kg', 8.95, 0, 0, 0, NULL),
(243, NULL, NULL, '2,5kg', 8.95, 0, 0, 0, NULL),
(244, NULL, NULL, '2,250g', 9.45, 0, 0, 0, NULL),
(245, NULL, NULL, '2,5kg', 8.95, 0, 0, 0, NULL),
(246, NULL, NULL, '2,5kg', 8.95, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_attribute_group`
--

DROP TABLE IF EXISTS `product_attribute_group`;
CREATE TABLE IF NOT EXISTS `product_attribute_group` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_attr_grp_product_product1_idx` (`product_id`),
  KEY `fk_attr_grp_product_attribute_grp1_idx` (`attribute_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_category`
--

DROP TABLE IF EXISTS `product_category`;
CREATE TABLE IF NOT EXISTS `product_category` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_product_category_product1_idx` (`product_id`),
  KEY `fk_product_category_category1_idx` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1477 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `product_category`
--

INSERT INTO `product_category` (`id`, `product_id`, `category_id`, `sort`) VALUES
(1231, 1, 1, NULL),
(1232, 2, 1, NULL),
(1233, 3, 1, NULL),
(1234, 4, 1, NULL),
(1235, 5, 1, NULL),
(1236, 6, 1, NULL),
(1237, 7, 1, NULL),
(1238, 8, 1, NULL),
(1239, 9, 1, NULL),
(1240, 10, 1, NULL),
(1241, 11, 1, NULL),
(1242, 12, 1, NULL),
(1243, 13, 1, NULL),
(1244, 14, 1, NULL),
(1245, 15, 1, NULL),
(1246, 16, 1, NULL),
(1247, 17, 1, NULL),
(1248, 18, 1, NULL),
(1249, 19, 1, NULL),
(1250, 20, 1, NULL),
(1251, 21, 1, NULL),
(1252, 22, 1, NULL),
(1253, 23, 1, NULL),
(1254, 24, 1, NULL),
(1255, 25, 1, NULL),
(1256, 26, 1, NULL),
(1257, 27, 1, NULL),
(1258, 28, 1, NULL),
(1259, 29, 1, NULL),
(1260, 30, 1, NULL),
(1261, 31, 1, NULL),
(1262, 32, 1, NULL),
(1263, 33, 1, NULL),
(1264, 34, 1, NULL),
(1265, 35, 1, NULL),
(1266, 36, 1, NULL),
(1267, 37, 1, NULL),
(1268, 38, 1, NULL),
(1269, 39, 1, NULL),
(1270, 40, 1, NULL),
(1271, 41, 1, NULL),
(1272, 42, 1, NULL),
(1273, 43, 1, NULL),
(1274, 44, 1, NULL),
(1275, 45, 1, NULL),
(1276, 46, 1, NULL),
(1277, 47, 1, NULL),
(1278, 48, 1, NULL),
(1279, 49, 1, NULL),
(1280, 50, 1, NULL),
(1281, 51, 1, NULL),
(1282, 52, 1, NULL),
(1283, 53, 1, NULL),
(1284, 54, 1, NULL),
(1285, 55, 1, NULL),
(1286, 56, 1, NULL),
(1287, 57, 1, NULL),
(1288, 58, 1, NULL),
(1289, 59, 1, NULL),
(1290, 60, 1, NULL),
(1291, 61, 1, NULL),
(1292, 62, 1, NULL),
(1293, 63, 1, NULL),
(1294, 64, 1, NULL),
(1295, 65, 1, NULL),
(1296, 66, 1, NULL),
(1297, 67, 1, NULL),
(1298, 68, 1, NULL),
(1299, 69, 1, NULL),
(1300, 70, 1, NULL),
(1301, 71, 1, NULL),
(1302, 72, 1, NULL),
(1303, 73, 1, NULL),
(1304, 74, 1, NULL),
(1305, 75, 1, NULL),
(1306, 76, 1, NULL),
(1307, 77, 1, NULL),
(1308, 78, 1, NULL),
(1309, 79, 1, NULL),
(1310, 80, 1, NULL),
(1311, 81, 1, NULL),
(1312, 82, 1, NULL),
(1313, 83, 1, NULL),
(1314, 84, 1, NULL),
(1315, 85, 1, NULL),
(1316, 86, 1, NULL),
(1317, 87, 1, NULL),
(1318, 88, 1, NULL),
(1319, 89, 1, NULL),
(1320, 90, 1, NULL),
(1321, 91, 1, NULL),
(1322, 92, 1, NULL),
(1323, 93, 1, NULL),
(1324, 94, 1, NULL),
(1325, 95, 1, NULL),
(1326, 96, 1, NULL),
(1327, 97, 1, NULL),
(1328, 98, 1, NULL),
(1329, 99, 1, NULL),
(1330, 100, 1, NULL),
(1331, 101, 1, NULL),
(1332, 102, 1, NULL),
(1333, 103, 1, NULL),
(1334, 104, 1, NULL),
(1335, 105, 1, NULL),
(1336, 106, 1, NULL),
(1337, 107, 1, NULL),
(1338, 108, 2, NULL),
(1339, 109, 2, NULL),
(1340, 110, 2, NULL),
(1341, 111, 2, NULL),
(1342, 112, 2, NULL),
(1343, 113, 2, NULL),
(1344, 114, 2, NULL),
(1345, 115, 2, NULL),
(1346, 116, 2, NULL),
(1347, 117, 2, NULL),
(1348, 118, 2, NULL),
(1349, 119, 2, NULL),
(1350, 120, 2, NULL),
(1351, 121, 2, NULL),
(1352, 122, 2, NULL),
(1353, 123, 2, NULL),
(1354, 124, 2, NULL),
(1355, 125, 2, NULL),
(1356, 126, 2, NULL),
(1357, 127, 2, NULL),
(1358, 128, 2, NULL),
(1359, 129, 2, NULL),
(1360, 130, 2, NULL),
(1361, 131, 2, NULL),
(1362, 132, 2, NULL),
(1363, 133, 2, NULL),
(1364, 134, 2, NULL),
(1365, 135, 2, NULL),
(1366, 136, 2, NULL),
(1367, 137, 2, NULL),
(1368, 138, 2, NULL),
(1369, 139, 2, NULL),
(1370, 140, 2, NULL),
(1371, 141, 2, NULL),
(1372, 142, 2, NULL),
(1373, 143, 2, NULL),
(1374, 144, 2, NULL),
(1375, 145, 2, NULL),
(1376, 146, 2, NULL),
(1377, 147, 2, NULL),
(1378, 148, 2, NULL),
(1379, 149, 2, NULL),
(1380, 150, 2, NULL),
(1381, 151, 2, NULL),
(1382, 152, 2, NULL),
(1383, 153, 2, NULL),
(1384, 154, 2, NULL),
(1385, 155, 2, NULL),
(1386, 156, 2, NULL),
(1387, 157, 2, NULL),
(1388, 158, 2, NULL),
(1389, 159, 2, NULL),
(1390, 160, 2, NULL),
(1391, 161, 2, NULL),
(1392, 162, 2, NULL),
(1393, 163, 2, NULL),
(1394, 164, 2, NULL),
(1395, 165, 2, NULL),
(1396, 166, 2, NULL),
(1397, 167, 2, NULL),
(1398, 168, 2, NULL),
(1399, 169, 2, NULL),
(1400, 170, 2, NULL),
(1401, 171, 2, NULL),
(1402, 172, 2, NULL),
(1403, 173, 2, NULL),
(1404, 174, 2, NULL),
(1405, 175, 2, NULL),
(1406, 176, 2, NULL),
(1407, 177, 2, NULL),
(1408, 178, 2, NULL),
(1409, 179, 3, NULL),
(1410, 180, 3, NULL),
(1411, 181, 3, NULL),
(1412, 182, 3, NULL),
(1413, 183, 3, NULL),
(1414, 184, 3, NULL),
(1415, 185, 3, NULL),
(1416, 186, 3, NULL),
(1417, 187, 3, NULL),
(1418, 188, 3, NULL),
(1419, 189, 3, NULL),
(1420, 190, 3, NULL),
(1421, 191, 3, NULL),
(1422, 192, 3, NULL),
(1423, 193, 3, NULL),
(1424, 194, 3, NULL),
(1425, 195, 3, NULL),
(1426, 196, 3, NULL),
(1427, 197, 3, NULL),
(1428, 198, 3, NULL),
(1429, 199, 3, NULL),
(1430, 200, 3, NULL),
(1431, 201, 3, NULL),
(1432, 202, 3, NULL),
(1433, 203, 3, NULL),
(1434, 204, 3, NULL),
(1435, 205, 3, NULL),
(1436, 206, 3, NULL),
(1437, 207, 3, NULL),
(1438, 208, 3, NULL),
(1439, 209, 3, NULL),
(1440, 210, 3, NULL),
(1441, 211, 4, NULL),
(1442, 212, 4, NULL),
(1443, 213, 4, NULL),
(1444, 214, 4, NULL),
(1445, 215, 4, NULL),
(1446, 216, 4, NULL),
(1447, 217, 4, NULL),
(1448, 218, 4, NULL),
(1449, 219, 4, NULL),
(1450, 220, 4, NULL),
(1451, 221, 4, NULL),
(1452, 222, 4, NULL),
(1453, 223, 4, NULL),
(1454, 224, 4, NULL),
(1455, 225, 4, NULL),
(1456, 226, 4, NULL),
(1457, 227, 4, NULL),
(1458, 228, 5, NULL),
(1459, 229, 5, NULL),
(1460, 230, 5, NULL),
(1461, 231, 5, NULL),
(1462, 232, 5, NULL),
(1463, 233, 5, NULL),
(1464, 234, 5, NULL),
(1465, 235, 6, NULL),
(1466, 236, 6, NULL),
(1467, 237, 6, NULL),
(1468, 238, 6, NULL),
(1469, 239, 6, NULL),
(1470, 240, 6, NULL),
(1471, 241, 6, NULL),
(1472, 242, 6, NULL),
(1473, 243, 6, NULL),
(1474, 244, 6, NULL),
(1475, 245, 6, NULL),
(1476, 246, 6, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_group`
--

DROP TABLE IF EXISTS `product_group`;
CREATE TABLE IF NOT EXISTS `product_group` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_group_id` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_group_product_group1_idx` (`product_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `product_group`
--

INSERT INTO `product_group` (`id`, `product_group_id`) VALUES
(1, NULL),
(2, NULL),
(3, NULL),
(49, NULL),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 4),
(9, 4),
(48, 4),
(50, 4),
(10, 5),
(46, 5),
(47, 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_group_lang`
--

DROP TABLE IF EXISTS `product_group_lang`;
CREATE TABLE IF NOT EXISTS `product_group_lang` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_group_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_group_lang_product_group1_idx` (`product_group_id`),
  KEY `fk_product_group_lang_lang1_idx` (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `product_group_lang`
--

INSERT INTO `product_group_lang` (`id`, `product_group_id`, `lang_id`, `name`) VALUES
(1, 1, 1, 'PG 1'),
(2, 2, 1, 'PG 2'),
(3, 3, 1, 'PG 3'),
(4, 4, 1, 'PG 4'),
(5, 5, 1, 'PG 5'),
(6, 6, 1, 'PG 6'),
(7, 7, 1, 'PG 7'),
(8, 8, 1, 'PG 8'),
(9, 9, 1, 'PG 9'),
(10, 10, 1, 'PG 10'),
(221, 46, 1, '5-3'),
(222, 46, 2, ''),
(223, 46, 3, ''),
(224, 46, 4, ''),
(225, 46, 5, ''),
(226, 46, 6, ''),
(227, 47, 1, '5-1'),
(228, 47, 2, ''),
(229, 47, 3, ''),
(230, 47, 4, ''),
(231, 47, 5, ''),
(232, 47, 6, ''),
(233, 48, 1, 'PG 4-1'),
(234, 48, 2, ''),
(235, 48, 3, ''),
(236, 48, 4, ''),
(237, 48, 5, ''),
(238, 48, 6, ''),
(239, 49, 1, 'Root Group 1'),
(240, 49, 2, ''),
(241, 49, 3, ''),
(242, 49, 4, ''),
(243, 49, 5, ''),
(244, 49, 6, ''),
(245, 50, 1, 'Subgroup 1'),
(246, 50, 2, ''),
(247, 50, 3, ''),
(248, 50, 4, ''),
(249, 50, 5, ''),
(250, 50, 6, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_group_product`
--

DROP TABLE IF EXISTS `product_group_product`;
CREATE TABLE IF NOT EXISTS `product_group_product` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_group_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_product_group_product_product1_idx` (`product_id`),
  KEY `fk_product_group_product_product_group1_idx` (`product_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_lang`
--

DROP TABLE IF EXISTS `product_lang`;
CREATE TABLE IF NOT EXISTS `product_lang` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_lang_product1_idx` (`product_id`),
  KEY `fk_product_lang_lang1_idx` (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=493 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `product_lang`
--

INSERT INTO `product_lang` (`id`, `product_id`, `lang_id`, `name`, `alias`, `description`) VALUES
(1, 1, 1, 'Adana Gewürzzubereitung', NULL, NULL),
(2, 1, 2, 'Adana Baharati', NULL, NULL),
(3, 2, 1, 'Anis ', NULL, NULL),
(4, 2, 2, 'Anason Bütün', NULL, NULL),
(5, 3, 1, 'Basilikum', NULL, NULL),
(6, 3, 2, 'Reyhan', NULL, NULL),
(7, 4, 1, 'Blaumohn', NULL, NULL),
(8, 4, 2, 'Hashas', NULL, NULL),
(9, 5, 1, 'Bockshornklee', NULL, NULL),
(10, 5, 2, 'Cemen', NULL, NULL),
(11, 6, 1, 'Bohnenkraut', NULL, NULL),
(12, 6, 2, 'Cibrisa', NULL, NULL),
(13, 7, 1, 'Couscous Gewürzzubereitung', NULL, NULL),
(14, 7, 2, 'Kuskus Baharati', NULL, NULL),
(15, 8, 1, 'Curry Gewürzzubereitung', NULL, NULL),
(16, 8, 2, 'Curry', NULL, NULL),
(17, 9, 1, 'Dilspitzen', NULL, NULL),
(18, 9, 2, 'Dereotu', NULL, NULL),
(19, 10, 1, 'Dönergewürzzubereitung', NULL, NULL),
(20, 10, 2, 'Döner Baharati', NULL, NULL),
(21, 11, 1, 'Einmach Gewürzzub.', NULL, NULL),
(22, 11, 2, 'Tursuluk Bah.', NULL, NULL),
(23, 12, 1, 'Essigbaumfrucht Gewürzzub.', NULL, NULL),
(24, 12, 2, 'Sumak', NULL, NULL),
(25, 13, 1, 'Estragonkraut', NULL, NULL),
(26, 13, 2, 'Tarhun', NULL, NULL),
(27, 14, 1, 'Falafelgewürzzubereitung', NULL, NULL),
(28, 14, 2, 'Falafel Baharati', NULL, NULL),
(29, 15, 1, 'Fenchel', NULL, NULL),
(30, 15, 2, 'Rezene', NULL, NULL),
(31, 16, 1, 'Fisch Gewürzzubereitung', NULL, NULL),
(32, 16, 2, 'Balik Harci', NULL, NULL),
(33, 17, 1, 'Fleischgewürzzubereitung', NULL, NULL),
(34, 17, 2, 'Et Baharati', NULL, NULL),
(35, 18, 1, 'Garam Masala Gewürzzub.', NULL, NULL),
(36, 18, 2, 'Garam Masala', NULL, NULL),
(37, 19, 1, 'Gefüllte Paprika Gewürzzub.', NULL, NULL),
(38, 19, 2, 'Dolma Baharati', NULL, NULL),
(39, 20, 1, 'Gewürzsalz', NULL, NULL),
(40, 20, 2, 'Lezzet Tuz', NULL, NULL),
(41, 21, 1, 'Grillgewürz spezial', NULL, NULL),
(42, 21, 2, 'Izgara Bah. Özel', NULL, NULL),
(43, 22, 1, 'Hackfleischgewürzzub.', NULL, NULL),
(44, 22, 2, 'Cig Köfte Baharati', NULL, NULL),
(45, 23, 1, 'Hackfleischgewürzzub.', NULL, NULL),
(46, 23, 2, 'Köfte Baharati', NULL, NULL),
(47, 24, 1, 'Hackfleischzub. Inegöl', NULL, NULL),
(48, 24, 2, 'Inegöl Köfte', NULL, NULL),
(49, 25, 1, 'Hackfleischzub. Panade', NULL, NULL),
(50, 25, 2, 'Köfte Harci', NULL, NULL),
(51, 26, 1, 'Hänchengewürzsalz', NULL, NULL),
(52, 26, 2, 'Tavuk Baharati', NULL, NULL),
(53, 27, 1, 'Hänchengewürzzub.', NULL, NULL),
(54, 27, 2, 'Tavuk Izgara', NULL, NULL),
(55, 28, 1, 'Himalaya Salz pink, grob', NULL, NULL),
(56, 28, 2, 'Himalaya Tuzu pink', NULL, NULL),
(57, 29, 1, 'Himalaya Salz, grob', NULL, NULL),
(58, 29, 2, 'Himalaya Tuzu', NULL, NULL),
(59, 30, 1, 'Ingwer, gemahlen', NULL, NULL),
(60, 30, 2, 'Zencefil Tozu', NULL, NULL),
(61, 31, 1, 'Kardamon, ganz', NULL, NULL),
(62, 31, 2, 'Kakule Bütün', NULL, NULL),
(63, 32, 1, 'Kardamon, gemahlen', NULL, NULL),
(64, 32, 2, 'Kakule Toz', NULL, NULL),
(65, 33, 1, 'Knoblauch, gemahlen', NULL, NULL),
(66, 33, 2, 'Sarimsak Tozu', NULL, NULL),
(67, 34, 1, 'Knoblauch, Granulat', NULL, NULL),
(68, 34, 2, 'Sarimsak Tozu Iri', NULL, NULL),
(69, 35, 1, 'Kokosraspel', NULL, NULL),
(70, 35, 2, 'Hind Cvz Rende', NULL, NULL),
(71, 36, 1, 'Koriander, ganz', NULL, NULL),
(72, 36, 2, 'Kisnis Bütün', NULL, NULL),
(73, 37, 1, 'Koriander, gemahlen', NULL, NULL),
(74, 37, 2, 'Kisnis Tozu', NULL, NULL),
(75, 38, 1, 'Korinthen', NULL, NULL),
(76, 38, 2, 'Kus Üzümü', NULL, NULL),
(77, 39, 1, 'Kräuter der Provence', NULL, NULL),
(78, 39, 2, 'Fransiz Baharati', NULL, NULL),
(79, 40, 1, 'Kreuzkümmel, ganz', NULL, NULL),
(80, 40, 2, 'Kimyon Bütün', NULL, NULL),
(81, 41, 1, 'Kreuzkümmel, gemahlen', NULL, NULL),
(82, 41, 2, 'Kimyon Toz', NULL, NULL),
(83, 42, 1, 'Kümmel ganz', NULL, NULL),
(84, 42, 2, 'Küncü Bütün', NULL, NULL),
(85, 43, 1, 'Kümmel gemahlen', NULL, NULL),
(86, 43, 2, 'Küncü Toz', NULL, NULL),
(87, 44, 1, 'Kurkuma, gemahlen', NULL, NULL),
(88, 44, 2, 'Sari Kök, Zerdecal', NULL, NULL),
(89, 45, 1, 'Lammfleisch Gewürzzub.', NULL, NULL),
(90, 45, 2, 'Kuzu Et Baharati', NULL, NULL),
(91, 46, 1, 'Leinsamen', NULL, NULL),
(92, 46, 2, 'Keten Tohumu', NULL, NULL),
(93, 47, 1, 'Lorbeerblätter', NULL, NULL),
(94, 47, 2, 'Defne Yapragi', NULL, NULL),
(95, 48, 1, 'majoran', NULL, NULL),
(96, 48, 2, 'Mercankök', NULL, NULL),
(97, 49, 1, 'Meersalz', NULL, NULL),
(98, 49, 2, 'Deniz Tuzu iri', NULL, NULL),
(99, 50, 1, 'Mexikanische Gewürzmischung', NULL, NULL),
(100, 50, 2, 'Mexiko Bah.', NULL, NULL),
(101, 51, 1, 'Minze', NULL, NULL),
(102, 51, 2, 'Nane', NULL, NULL),
(103, 52, 1, 'Muskatnuss ganz', NULL, NULL),
(104, 52, 2, 'Hindistan CvzBtn', NULL, NULL),
(105, 53, 1, 'Muskatnuss gemahlen', NULL, NULL),
(106, 53, 2, 'Hindistan CvzToz', NULL, NULL),
(107, 54, 1, 'Natron', NULL, NULL),
(108, 54, 2, 'Karbonat', NULL, NULL),
(109, 55, 1, 'Nelke, ganz', NULL, NULL),
(110, 55, 2, 'Karanfil Bütün', NULL, NULL),
(111, 56, 1, 'Nelke, gemahlen', NULL, NULL),
(112, 56, 2, 'Karanfil Tozu', NULL, NULL),
(113, 57, 1, 'Oliven Gewürzzub.', NULL, NULL),
(114, 57, 2, 'Zeytin Baharati', NULL, NULL),
(115, 58, 1, 'Oregano', NULL, NULL),
(116, 58, 2, 'Kekik', NULL, NULL),
(117, 59, 1, 'Paprika eselsüss hochrot', NULL, NULL),
(118, 59, 2, 'Tatli Toz Biber', NULL, NULL),
(119, 60, 1, 'Paprika scharf gem.', NULL, NULL),
(120, 60, 2, 'Aci Toz Biber', NULL, NULL),
(121, 61, 1, 'Paprikaflocken edelsüss', NULL, NULL),
(122, 61, 2, 'Tatli Pul Biber', NULL, NULL),
(123, 62, 1, 'Paprikaflocken. Mit Saat', NULL, NULL),
(124, 62, 2, 'Pul Biber', NULL, NULL),
(125, 63, 1, 'Paprikaflocken.extra scharf', NULL, NULL),
(126, 63, 2, 'Pul Bib. Ekstra Aci', NULL, NULL),
(127, 64, 1, 'Paprikaflocken.Ohne Saat', NULL, NULL),
(128, 64, 2, 'Pul B.Cekirdeksiz', NULL, NULL),
(129, 65, 1, 'Paprikagewürzzub.extra scharf', NULL, NULL),
(130, 65, 2, 'isot', NULL, NULL),
(131, 66, 1, 'Paprikaschoten rosa', NULL, NULL),
(132, 66, 2, 'Bütün Süs Biberi', NULL, NULL),
(133, 67, 1, 'Petersille', NULL, NULL),
(134, 67, 2, 'Maydanoz', NULL, NULL),
(135, 68, 1, 'Pfeffer bunter mix, ganz', NULL, NULL),
(136, 68, 2, 'Karisik '''' Biber', NULL, NULL),
(137, 69, 1, 'Pfeffer grün, ganz', NULL, NULL),
(138, 69, 2, 'Yesil Büt Biber', NULL, NULL),
(139, 70, 1, 'Pfeffer rosa, ganz', NULL, NULL),
(140, 70, 2, 'Kirmizi biber bütün', NULL, NULL),
(141, 71, 1, 'Pfeffer, ganz', NULL, NULL),
(142, 71, 2, 'Kara Biber Bütün', NULL, NULL),
(143, 72, 1, 'Pfeffer, gemahlen', NULL, NULL),
(144, 72, 2, 'Kara Biber Toz', NULL, NULL),
(145, 73, 1, 'Pfeffer, weiss gem.', NULL, NULL),
(146, 73, 2, 'Beyaz Biber Toz', NULL, NULL),
(147, 74, 1, 'Pfeffer, weiss, ganz', NULL, NULL),
(148, 74, 2, 'Beyaz Biber Bütün', NULL, NULL),
(149, 75, 1, 'Piment, ganz', NULL, NULL),
(150, 75, 2, 'Yeni Bahar Btn', NULL, NULL),
(151, 76, 1, 'Piment, gemahlen', NULL, NULL),
(152, 76, 2, 'Yeni Bahar Toz', NULL, NULL),
(153, 77, 1, 'Pinienkerne', NULL, NULL),
(154, 77, 2, 'Dolmalik Fstk Cin', NULL, NULL),
(155, 78, 1, 'Pistazienkerne grün', NULL, NULL),
(156, 78, 2, 'Fistik ici', NULL, NULL),
(157, 79, 1, 'Pizza Gewürzzub.', NULL, NULL),
(158, 79, 2, 'Pizza Baharati', NULL, NULL),
(159, 80, 1, 'Pommes Frites Salz', NULL, NULL),
(160, 80, 2, 'Pommes Baharati', NULL, NULL),
(161, 81, 1, 'Rosmarin', NULL, NULL),
(162, 81, 2, 'Biberiye', NULL, NULL),
(163, 82, 1, 'Saflorblüten', NULL, NULL),
(164, 82, 2, 'Safran, Hasfir', NULL, NULL),
(165, 83, 1, 'Salat Gewürzzubereitung', NULL, NULL),
(166, 83, 2, 'Salata Baharati', NULL, NULL),
(167, 84, 1, 'Salbei', NULL, NULL),
(168, 84, 2, 'Adacayi', NULL, NULL),
(169, 85, 1, 'Schnittlauch', NULL, NULL),
(170, 85, 2, 'Frenk Sogani', NULL, NULL),
(171, 86, 1, 'Schwarzkümmel', NULL, NULL),
(172, 86, 2, 'Cörek Otu', NULL, NULL),
(173, 87, 1, 'Schwarzkümmel+Sesam mix', NULL, NULL),
(174, 87, 2, 'Cörek+Susam Mix', NULL, NULL),
(175, 88, 1, 'Senfkörner', NULL, NULL),
(176, 88, 2, 'Hardal Bütün', NULL, NULL),
(177, 89, 1, 'Senfmehl', NULL, NULL),
(178, 89, 2, 'Hardal Toz', NULL, NULL),
(179, 90, 1, 'Sesam', NULL, NULL),
(180, 90, 2, 'Susam', NULL, NULL),
(181, 91, 1, 'Sesam geröstet', NULL, NULL),
(182, 91, 2, 'Susam Kavrulmus', NULL, NULL),
(183, 92, 1, 'Sieben Mix Gewürzzub.', NULL, NULL),
(184, 92, 2, 'Yedi Türlü Bah', NULL, NULL),
(185, 93, 1, 'Spaghetti Gewürzzub.', NULL, NULL),
(186, 93, 2, 'Makarna Baharati', NULL, NULL),
(187, 94, 1, 'Steinweichsel, gemahlen', NULL, NULL),
(188, 94, 2, 'Mahlep Tozu', NULL, NULL),
(189, 95, 1, 'Tandoori Masala Gewürzzub.', NULL, NULL),
(190, 95, 2, 'Tandori Masala', NULL, NULL),
(191, 96, 1, 'Tekirdag Köfte Gewürzzub.', NULL, NULL),
(192, 96, 2, 'Tekirdag Köfte', NULL, NULL),
(193, 97, 1, 'Thymian', NULL, NULL),
(194, 97, 2, 'Dag Kekik', NULL, NULL),
(195, 98, 1, 'Türk. Pizza Gewürzmischung', NULL, NULL),
(196, 98, 2, 'Lahmacun Bah.', NULL, NULL),
(197, 99, 1, 'Tzaziki Gewürzmischung', NULL, NULL),
(198, 99, 2, 'Cacik Baharati', NULL, NULL),
(199, 100, 1, 'Vanilliestangen', NULL, NULL),
(200, 100, 2, 'Vanilya cubuk', NULL, NULL),
(201, 101, 1, 'Wacholderbeeren', NULL, NULL),
(202, 101, 2, 'Ardic', NULL, NULL),
(203, 102, 1, 'Zatar Gewürzzub.', NULL, NULL),
(204, 102, 2, 'Zatar Baharati', NULL, NULL),
(205, 103, 1, 'Zimt', NULL, NULL),
(206, 103, 2, 'Tarcin', NULL, NULL),
(207, 104, 1, 'Zimtstangen', NULL, NULL),
(208, 104, 2, 'Tarcin Bütün', NULL, NULL),
(209, 105, 1, 'Zitronensäure', NULL, NULL),
(210, 105, 2, 'Limon Tuzu', NULL, NULL),
(211, 106, 1, 'Zitronensäure, grob', NULL, NULL),
(212, 106, 2, 'Limon Tuzu, Kaya', NULL, NULL),
(213, 107, 1, 'Zwiebelpulver', NULL, NULL),
(214, 107, 2, 'Sogan Tozu', NULL, NULL),
(215, 108, 1, 'Asana Gewürzzub.', NULL, NULL),
(216, 108, 2, 'Adana Baharati', NULL, NULL),
(217, 109, 1, 'Basilikum', NULL, NULL),
(218, 109, 2, 'Reyhan', NULL, NULL),
(219, 110, 1, 'Blaumohn', NULL, NULL),
(220, 110, 2, 'Hashas', NULL, NULL),
(221, 111, 1, 'Bockshornklee', NULL, NULL),
(222, 111, 2, 'Cemen', NULL, NULL),
(223, 112, 1, 'Curry', NULL, NULL),
(224, 112, 2, 'Curry', NULL, NULL),
(225, 113, 1, 'Dilspitzen', NULL, NULL),
(226, 113, 2, 'Dereotu', NULL, NULL),
(227, 114, 1, 'Döner Gewürzzub.', NULL, NULL),
(228, 114, 2, 'Döner Bah', NULL, NULL),
(229, 115, 1, 'Essigbaumfrucht', NULL, NULL),
(230, 115, 2, 'Sumak', NULL, NULL),
(231, 116, 1, 'Falafel Gewürzzub.', NULL, NULL),
(232, 116, 2, 'Falafel Baharati', NULL, NULL),
(233, 117, 1, 'Fisch Gewürzzub.', NULL, NULL),
(234, 117, 2, 'Balik Harci', NULL, NULL),
(235, 118, 1, 'Fleischgewürzzub.', NULL, NULL),
(236, 118, 2, 'Et Baharati', NULL, NULL),
(237, 119, 1, 'Garam Masala Gewürzzub.', NULL, NULL),
(238, 119, 2, 'Garam Masala', NULL, NULL),
(239, 120, 1, 'Gefüllte Paprika Gewürzzub.', NULL, NULL),
(240, 120, 2, 'Dolma Baharati', NULL, NULL),
(241, 121, 1, 'Grillgewürzzub.spezial', NULL, NULL),
(242, 121, 2, 'Izgara Bah. Özel', NULL, NULL),
(243, 122, 1, 'Hachfleisch Gewürzzub.', NULL, NULL),
(244, 122, 2, 'Cig Köfte Bah', NULL, NULL),
(245, 123, 1, 'Hachfleisch Gewürzzub.', NULL, NULL),
(246, 123, 2, 'Köfte Bah', NULL, NULL),
(247, 124, 1, 'Hachfleischgewürzzub.Inegöl', NULL, NULL),
(248, 124, 2, 'Inegöl Köfte', NULL, NULL),
(249, 125, 1, 'Hachfleischgewürzzub.Panade', NULL, NULL),
(250, 125, 2, 'Köfte Harci', NULL, NULL),
(251, 126, 1, 'Hachfleischgewürzzub.Tekirdag', NULL, NULL),
(252, 126, 2, 'Tekirdag Köfte', NULL, NULL),
(253, 127, 1, 'Hähnchen Gewürzzsalz', NULL, NULL),
(254, 127, 2, 'Tavuk Bah', NULL, NULL),
(255, 128, 1, 'Hähnchen Gewürzzub.', NULL, NULL),
(256, 128, 2, 'Tavuk Izgara', NULL, NULL),
(257, 129, 1, 'Himalaya Salz', NULL, NULL),
(258, 129, 2, 'Himayala Tuzu', NULL, NULL),
(259, 130, 1, 'Himalaya Salz pink', NULL, NULL),
(260, 130, 2, 'Himayala Tz Pink', NULL, NULL),
(261, 131, 1, 'Ingwer gemahlen', NULL, NULL),
(262, 131, 2, 'Zencefil Tozu', NULL, NULL),
(263, 132, 1, 'Knoblauch gemahlen', NULL, NULL),
(264, 132, 2, 'Sarmsk Toz', NULL, NULL),
(265, 133, 1, 'Knoblauch Granulat', NULL, NULL),
(266, 133, 2, 'Srmsk Grn', NULL, NULL),
(267, 134, 1, 'Kokosraspel', NULL, NULL),
(268, 134, 2, 'Hind Cvz Rende', NULL, NULL),
(269, 135, 1, 'Koriander ganz', NULL, NULL),
(270, 135, 2, 'Kisnis Büt', NULL, NULL),
(271, 136, 1, 'Koriander gemahlen', NULL, NULL),
(272, 136, 2, 'Kisnis Toz', NULL, NULL),
(273, 137, 1, 'Kreuzkümmel ganz', NULL, NULL),
(274, 137, 2, 'Kimyon Büt', NULL, NULL),
(275, 138, 1, 'Kreuzkümmel gemahlen', NULL, NULL),
(276, 138, 2, 'Kimyon Toz', NULL, NULL),
(277, 139, 1, 'Kümmel ganz', NULL, NULL),
(278, 139, 2, 'Küncü Büt', NULL, NULL),
(279, 140, 1, 'Kurkuma gemahlen', NULL, NULL),
(280, 140, 2, 'Sari Kök,Zerdecal', NULL, NULL),
(281, 141, 1, 'Lammfleisch Gewürzzub.', NULL, NULL),
(282, 141, 2, 'Kuzu Et', NULL, NULL),
(283, 142, 1, 'Lorbeerblätter', NULL, NULL),
(284, 142, 2, 'Defne Yapragi', NULL, NULL),
(285, 143, 1, 'Lprinthen', NULL, NULL),
(286, 143, 2, 'Kus Üzümü', NULL, NULL),
(287, 144, 1, 'Majoran', NULL, NULL),
(288, 144, 2, 'Mercankök', NULL, NULL),
(289, 145, 1, 'Meersalz', NULL, NULL),
(290, 145, 2, 'Deniz Tuzu iri', NULL, NULL),
(291, 146, 1, 'Minze', NULL, NULL),
(292, 146, 2, 'Nane', NULL, NULL),
(293, 147, 1, 'Natron', NULL, NULL),
(294, 147, 2, 'Karbonat', NULL, NULL),
(295, 148, 1, 'Nelke ganz', NULL, NULL),
(296, 148, 2, 'Karanfil Bütün', NULL, NULL),
(297, 149, 1, 'Oregano', NULL, NULL),
(298, 149, 2, 'Kekik', NULL, NULL),
(299, 150, 1, 'Paprika scharf gemahlen', NULL, NULL),
(300, 150, 2, 'Aci Toz Bib', NULL, NULL),
(301, 151, 1, 'Paprika süss gemahlen', NULL, NULL),
(302, 151, 2, 'Tatli Toz Bib', NULL, NULL),
(303, 152, 1, 'Paprikaflocken extra scharf', NULL, NULL),
(304, 152, 2, 'Pul B. Ekstra Aci', NULL, NULL),
(305, 153, 1, 'Paprikaflocken mit Saat', NULL, NULL),
(306, 153, 2, 'Pul Biber', NULL, NULL),
(307, 154, 1, 'Paprikaflocken ohne Saat', NULL, NULL),
(308, 154, 2, 'Pul Bib.Cekirdsiz', NULL, NULL),
(309, 155, 1, 'Paprikaflocken süss', NULL, NULL),
(310, 155, 2, 'Tatli Pul Biber', NULL, NULL),
(311, 156, 1, 'Paprikagewürzzub. Scharf ext.', NULL, NULL),
(312, 156, 2, 'isot', NULL, NULL),
(313, 157, 1, 'Paprikaschoten rose', NULL, NULL),
(314, 157, 2, 'Bütün Süs Biberi', NULL, NULL),
(315, 158, 1, 'Petersilie', NULL, NULL),
(316, 158, 2, 'Maydanoz', NULL, NULL),
(317, 159, 1, 'Pfeffer scharz gemahlen', NULL, NULL),
(318, 159, 2, 'Kara BbrTz', NULL, NULL),
(319, 160, 1, 'Pfeffer schwarz ganz', NULL, NULL),
(320, 160, 2, 'Kara BbrBtn', NULL, NULL),
(321, 161, 1, 'Pfeffer weiss ganz', NULL, NULL),
(322, 161, 2, 'Byz Biber Bütün', NULL, NULL),
(323, 162, 1, 'Pfeffer weiss gemahlen', NULL, NULL),
(324, 162, 2, 'Byz Biber Toz', NULL, NULL),
(325, 163, 1, 'Pinienkerne', NULL, NULL),
(326, 163, 2, 'Dolmalik Fistik', NULL, NULL),
(327, 164, 1, 'Pizza Gewürzzub.', NULL, NULL),
(328, 164, 2, 'Pizza Bah', NULL, NULL),
(329, 165, 1, 'Pommes Fritz Salz', NULL, NULL),
(330, 165, 2, 'Pomes Bah', NULL, NULL),
(331, 166, 1, 'Rosmarin', NULL, NULL),
(332, 166, 2, 'Biberiye', NULL, NULL),
(333, 167, 1, 'Schwarzkümmel', NULL, NULL),
(334, 167, 2, 'Cörek Otu', NULL, NULL),
(335, 168, 1, 'Schwarzkümmel+Sesam', NULL, NULL),
(336, 168, 2, 'Cörek+Susam Mix', NULL, NULL),
(337, 169, 1, 'Sesam', NULL, NULL),
(338, 169, 2, 'Susam', NULL, NULL),
(339, 170, 1, 'Sieben Mix Gewürzzub.', NULL, NULL),
(340, 170, 2, 'Yedi Türlü', NULL, NULL),
(341, 171, 1, 'Tandoria Masala', NULL, NULL),
(342, 171, 2, 'Tandori Masala', NULL, NULL),
(343, 172, 1, 'Thymian', NULL, NULL),
(344, 172, 2, 'Dag Kekik', NULL, NULL),
(345, 173, 1, 'Türk. Pizza Gewürzmischung', NULL, NULL),
(346, 173, 2, 'Lahmacun Bah.', NULL, NULL),
(347, 174, 1, 'Zatar Gewürzzub.', NULL, NULL),
(348, 174, 2, 'Zatar Baharati', NULL, NULL),
(349, 175, 1, 'Zimt ganz', NULL, NULL),
(350, 175, 2, 'Tarcin Bütün', NULL, NULL),
(351, 176, 1, 'Zimt gemahlen', NULL, NULL),
(352, 176, 2, 'Tarcin', NULL, NULL),
(353, 177, 1, 'Zintronensäure', NULL, NULL),
(354, 177, 2, 'Limon Tuzu', NULL, NULL),
(355, 178, 1, 'Zitronensäure grob', NULL, NULL),
(356, 178, 2, 'Limon Tuzu iri', NULL, NULL),
(357, 179, 1, 'Basilikum', NULL, NULL),
(358, 179, 2, 'Reyhan', NULL, NULL),
(359, 180, 1, 'Curry', NULL, NULL),
(360, 180, 2, 'Curry', NULL, NULL),
(361, 181, 1, 'Dilspitzen', NULL, NULL),
(362, 181, 2, 'Dere Otu', NULL, NULL),
(363, 182, 1, 'Döner Gewürzzubereitung', NULL, NULL),
(364, 182, 2, 'Döner Baharati', NULL, NULL),
(365, 183, 1, 'Essigbaumfrucht Gewürzzub.', NULL, NULL),
(366, 183, 2, 'Sumak', NULL, NULL),
(367, 184, 1, 'Fleisch Gewürzzubereitung', NULL, NULL),
(368, 184, 2, 'Et Baharati', NULL, NULL),
(369, 185, 1, 'Grill spezial Gewürzzubereitung', NULL, NULL),
(370, 185, 2, 'Izgara Bah. Özel', NULL, NULL),
(371, 186, 1, 'Hachfleischgewürzzubereitung', NULL, NULL),
(372, 186, 2, 'Köfte Baharati', NULL, NULL),
(373, 187, 1, 'Hackfleischzub. Panade', NULL, NULL),
(374, 187, 2, 'Köfte Harci', NULL, NULL),
(375, 188, 1, 'Hähnchen Gewürzzub.', NULL, NULL),
(376, 188, 2, 'Tavuk Baharati', NULL, NULL),
(377, 189, 1, 'Knoblauch gemahlen', NULL, NULL),
(378, 189, 2, 'Sarimsak Tozu', NULL, NULL),
(379, 190, 1, 'Knoblauch Granulat', NULL, NULL),
(380, 190, 2, 'Sarimsak irmigi', NULL, NULL),
(381, 191, 1, 'Kreuzkümmel gemahlen', NULL, NULL),
(382, 191, 2, 'Kimyon', NULL, NULL),
(383, 192, 1, 'Minze', NULL, NULL),
(384, 192, 2, 'Nane', NULL, NULL),
(385, 193, 1, 'Oregano', NULL, NULL),
(386, 193, 2, 'Kekik', NULL, NULL),
(387, 194, 1, 'Paprika scharf gemahlen', NULL, NULL),
(388, 194, 2, 'Aci Toz Biber', NULL, NULL),
(389, 195, 1, 'Paprika süss gemahlen', NULL, NULL),
(390, 195, 2, 'Tatli Toz Biber', NULL, NULL),
(391, 196, 1, 'Paprikaflocken süß', NULL, NULL),
(392, 196, 2, 'Tatli Pul Biber', NULL, NULL),
(393, 197, 1, 'Paprikagewürzzub. Extra scharf', NULL, NULL),
(394, 197, 2, 'isot', NULL, NULL),
(395, 198, 1, 'Paprikagewürzzub. Extra scharf', NULL, NULL),
(396, 198, 2, 'Pul Bib. Ekstra Aci', NULL, NULL),
(397, 199, 1, 'Paprikagewürzzub. Mit Saat', NULL, NULL),
(398, 199, 2, 'Pul Biber', NULL, NULL),
(399, 200, 1, 'Paprikagewürzzub. Ohne Saat', NULL, NULL),
(400, 200, 2, 'Pul Bib. Cekirdsiz', NULL, NULL),
(401, 201, 1, 'Petersille', NULL, NULL),
(402, 201, 2, 'Maydanoz', NULL, NULL),
(403, 202, 1, 'Pfeffer schwarz, gemahlen', NULL, NULL),
(404, 202, 2, 'Kara Biber', NULL, NULL),
(405, 203, 1, 'Pfeffer weiss, gemahlen', NULL, NULL),
(406, 203, 2, 'Beyaz Toz Biber', NULL, NULL),
(407, 204, 1, 'Pizza Gewürzzub.', NULL, NULL),
(408, 204, 2, 'Pizza Baharati', NULL, NULL),
(409, 205, 1, 'Pommes Fritz Salz', NULL, NULL),
(410, 205, 2, 'Patates Baharat', NULL, NULL),
(411, 206, 1, 'Schwarzkümmel', NULL, NULL),
(412, 206, 2, 'Cörek Otu', NULL, NULL),
(413, 207, 1, 'Sesam', NULL, NULL),
(414, 207, 2, 'Susam', NULL, NULL),
(415, 208, 1, 'Sieben Mix Gewürzzubereitung', NULL, NULL),
(416, 208, 2, 'Yedi Türlü', NULL, NULL),
(417, 209, 1, 'Thymian', NULL, NULL),
(418, 209, 2, 'Dag Kekik', NULL, NULL),
(419, 210, 1, 'Zitronensäure', NULL, NULL),
(420, 210, 2, 'Limon Tuzu', NULL, NULL),
(421, 211, 1, 'Brennesseltee', NULL, NULL),
(422, 211, 2, 'Isırgan', NULL, NULL),
(423, 212, 1, 'grüner Tee', NULL, NULL),
(424, 212, 2, 'Yeşilçay', NULL, NULL),
(425, 213, 1, 'Hagebuttentee', NULL, NULL),
(426, 213, 2, 'Kuşburnu', NULL, NULL),
(427, 214, 1, 'Heidekraut-Tee', NULL, NULL),
(428, 214, 2, 'Funda', NULL, NULL),
(429, 215, 1, 'Hibiskus-Tee', NULL, NULL),
(430, 215, 2, 'Hibisküs', NULL, NULL),
(431, 216, 1, 'Johanniskraut Tee', NULL, NULL),
(432, 216, 2, 'Kantaron', NULL, NULL),
(433, 217, 1, 'Kamillentee', NULL, NULL),
(434, 217, 2, 'Papatya', NULL, NULL),
(435, 218, 1, 'Lakritztee (Süßholz)', NULL, NULL),
(436, 218, 2, 'Meyan Kökü', NULL, NULL),
(437, 219, 1, 'Lavendel Tee', NULL, NULL),
(438, 219, 2, 'Karabaşotu', NULL, NULL),
(439, 220, 1, 'Lavendel Tee', NULL, NULL),
(440, 220, 2, 'Lavanta', NULL, NULL),
(441, 221, 1, 'Lindenblütentee', NULL, NULL),
(442, 221, 2, 'Ihlamur', NULL, NULL),
(443, 222, 1, 'Majoran Tee', NULL, NULL),
(444, 222, 2, 'Mercanköşkü', NULL, NULL),
(445, 223, 1, 'Malvenblüten-Tee', NULL, NULL),
(446, 223, 2, 'Hatmi Çiçeği', NULL, NULL),
(447, 224, 1, 'Melissentte', NULL, NULL),
(448, 224, 2, 'Melisa', NULL, NULL),
(449, 225, 1, 'Salbei', NULL, NULL),
(450, 225, 2, 'Adaçayı', NULL, NULL),
(451, 226, 1, 'Thymiantee', NULL, NULL),
(452, 226, 2, 'Kekik', NULL, NULL),
(453, 227, 1, 'Zinnkraut Tee', NULL, NULL),
(454, 227, 2, 'Kırkkilit', NULL, NULL),
(455, 228, 1, 'Auberginen getrocknet ', NULL, NULL),
(456, 228, 2, 'Patlican kur.dolmalik', NULL, NULL),
(457, 229, 1, 'Auberginen Moussaka getrocknet', NULL, NULL),
(458, 229, 2, 'Patlican musakkalik', NULL, NULL),
(459, 230, 1, 'Kürbiskern geschält', NULL, NULL),
(460, 230, 2, 'Kabak Cekirdek ici', NULL, NULL),
(461, 231, 1, 'Paprikaschote getrocknet ', NULL, NULL),
(462, 231, 2, 'Biber kur. Dolmalik', NULL, NULL),
(463, 232, 1, 'Sonnenkern geschält', NULL, NULL),
(464, 232, 2, 'Ay Cekirdek ici', NULL, NULL),
(465, 233, 1, 'Spitzpaprika getrocknet', NULL, NULL),
(466, 233, 2, 'Biber kurusu (sivri)', NULL, NULL),
(467, 234, 1, 'Zucchini getrocknet', NULL, NULL),
(468, 234, 2, 'Kabak kurusu', NULL, NULL),
(469, 235, 1, 'Hähnchen Gewürzzub.', NULL, NULL),
(470, 235, 2, 'Tavuk Baharati', NULL, NULL),
(471, 236, 1, 'Hähnchen Gewürzzub.', NULL, NULL),
(472, 236, 2, 'Tavuk Baharati', NULL, NULL),
(473, 237, 1, 'Paprika scharf gemahlen', NULL, NULL),
(474, 237, 2, 'Aci Toz Hochrot', NULL, NULL),
(475, 238, 1, 'Paprika süss gem. hochrot', NULL, NULL),
(476, 238, 2, 'Tatli Tz Bi.Hochrot', NULL, NULL),
(477, 239, 1, 'Paprikaflocken extra scharf', NULL, NULL),
(478, 239, 2, 'Pul Bib. Ekstra Aci', NULL, NULL),
(479, 240, 1, 'Paprikaflocken mit Saat', NULL, NULL),
(480, 240, 2, 'Pulbiber', NULL, NULL),
(481, 241, 1, 'Paprikaflocken ohne Saat', NULL, NULL),
(482, 241, 2, 'Pul B.Cekirdsiz', NULL, NULL),
(483, 242, 1, 'Paprika scharf gemahlen', NULL, NULL),
(484, 242, 2, 'Aci Toz Hochrot', NULL, NULL),
(485, 243, 1, 'Paprikaflocken ohne Saat', NULL, NULL),
(486, 243, 2, 'Pul B.Cekirdeksiz', NULL, NULL),
(487, 244, 1, 'Paprikaflocken extra scharf', NULL, NULL),
(488, 244, 2, 'Pul Bib. Ekstra Aci', NULL, NULL),
(489, 245, 1, 'Paprikaflocken mit Saat', NULL, NULL),
(490, 245, 2, 'Pulbiber', NULL, NULL),
(491, 246, 1, 'Paprika süss gem. hochrot', NULL, NULL),
(492, 246, 2, 'Tatli Tz Bib. Hochrot', NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `template`
--

DROP TABLE IF EXISTS `template`;
CREATE TABLE IF NOT EXISTS `template` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `template_attribute_group`
--

DROP TABLE IF EXISTS `template_attribute_group`;
CREATE TABLE IF NOT EXISTS `template_attribute_group` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `template_id` int(10) UNSIGNED NOT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_attribute_grp_template_template1_idx` (`template_id`),
  KEY `idx_attribute_grp_template_attribute_grp1_idx` (`attribute_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `upload`
--

DROP TABLE IF EXISTS `upload`;
CREATE TABLE IF NOT EXISTS `upload` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference_type` varchar(256) NOT NULL,
  `reference_id` int(10) UNSIGNED NOT NULL,
  `rank` int(10) UNSIGNED NOT NULL,
  `destination` varchar(128) DEFAULT NULL,
  `mimetype` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `tmpname` varchar(256) NOT NULL,
  `size` int(10) UNSIGNED NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `upload`
--

INSERT INTO `upload` (`id`, `reference_type`, `reference_id`, `rank`, `destination`, `mimetype`, `name`, `tmpname`, `size`, `description`) VALUES
(1, 'attributevalue', 1118, 1, 'image', 'image/png', '20151110_dold_pim_dbstruktur_1.png', './public/uploads/attributevalue/1118/20151110_dold_pim_dbstruktur_1_568bb1248f561.png', 823371, NULL),
(2, 'attributevalue', 1153, 1, 'file', 'image/jpeg', 'dummy.jpg', './public/uploads/attributevalue/1153/dummy_568bb3e901f34.jpg', 15242, NULL),
(3, 'attributevalue', 1154, 0, 'image', 'image/jpeg', 'dummy.jpg', './public/uploads/attributevalue/1154/dummy_568bb3f33a7e9.jpg', 15242, NULL),
(4, 'attributevalue', 2048, 1, 'image', 'image/jpeg', 'Tulips.jpg', './public/uploads/attributevalue/2048/tulips_568f8a9c6c65a.jpg', 620888, NULL),
(5, 'attributevalue', 2048, 2, 'image', 'image/jpeg', 'hawaii.jpg', './public/uploads/attributevalue/2048/hawaii_568f8ab460662.jpg', 12984, NULL),
(6, 'attributevalue', 2047, 1, 'file', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Teilnehmer.xlsx', './public/uploads/attributevalue/2047/teilnehmer_568f8ad88cfc9.xlsx', 7674, NULL),
(7, 'attributevalue', 2047, 2, 'file', 'text/plain', 'robocopy.txt', './public/uploads/attributevalue/2047/robocopy_568f8b061763b.txt', 1220, NULL),
(8, 'attributevalue', 1142, 1, 'image', 'image/jpeg', 'Sicherheitstechnik_Not-Aus-Module.jpg', './public/uploads/attributevalue/1142/sicherheitstechnik_not_aus_module_569f882340b3b.jpg', 11870, NULL),
(11, 'attributevalue', 3628, 1, 'file', 'application/pdf', '20151110_dold_pim_dbstruktur.pdf', './public/uploads/attributevalue/3628/20151110_dold_pim_dbstruktur_569fae7009013.pdf', 477930, NULL),
(12, 'attributevalue', 3628, 2, 'file', 'application/pdf', '20151120_DOLPIM-21_dold_pim_dbstruktur_rh.pdf', './public/uploads/attributevalue/3628/20151120_dolpim_21_dold_pim_dbstruktur_rh_569fae771f41c.pdf', 539776, NULL),
(13, 'attributevalue', 3629, 1, 'image', 'image/jpeg', 'Tulips.jpg', './public/uploads/attributevalue/3629/tulips_569fae95e6aba.jpg', 620888, NULL),
(14, 'attributevalue', 3629, 2, 'image', 'image/jpeg', 'Chrysanthemum.jpg', './public/uploads/attributevalue/3629/chrysanthemum_569fae9711f42.jpg', 879394, NULL),
(15, 'attributevalue', 3733, 1, 'file', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Teilnehmer.xlsx', './public/uploads/attributevalue/3733/teilnehmer_569fb0286f9c6.xlsx', 7674, NULL),
(16, 'attributevalue', 3734, 1, 'image', 'image/jpeg', 'dummy-995x250-Orange.jpg', './public/uploads/attributevalue/3734/dummy_995x250_orange_569fb05791a4a.jpg', 64463, NULL),
(17, 'attributevalue', 3734, 2, 'image', 'image/jpeg', 'dummy-995x250-Wine.jpg', './public/uploads/attributevalue/3734/dummy_995x250_wine_569fb059f078f.jpg', 48272, NULL),
(18, 'attributevalue', 1154, 1, 'image', 'image/png', 'LG5925.png', './public/uploads/attributevalue/1154/lg5925_56a619a3e5c49.png', 72674, NULL),
(19, 'attributevalue', 1484, 1, 'image', 'image/jpeg', 'Koala.jpg', './public/uploads/attributevalue/1484/koala_56a8a81e580c2.jpg', 780831, NULL),
(20, 'attributevalue', 1484, 0, 'image', 'image/jpeg', 'Jellyfish.jpg', './public/uploads/attributevalue/1484/jellyfish_56a8a82824be3.jpg', 775702, NULL),
(21, 'attributevalue', 3734, 3, 'image', 'image/jpeg', 'Koala.jpg', './public/uploads/attributevalue/3734/koala_56a9efe9581d8.jpg', 780831, NULL),
(22, 'attributevalue', 3734, 4, 'image', 'image/jpeg', 'Jellyfish.jpg', './public/uploads/attributevalue/3734/jellyfish_56a9efee21df2.jpg', 775702, NULL),
(23, 'attributevalue', 3734, 5, 'image', 'image/jpeg', 'Hydrangeas.jpg', './public/uploads/attributevalue/3734/hydrangeas_56a9efef029aa.jpg', 595284, NULL),
(24, 'attributevalue', 3734, 6, 'image', 'image/jpeg', 'Desert.jpg', './public/uploads/attributevalue/3734/desert_56a9eff011f2e.jpg', 845941, NULL),
(25, 'attributevalue', 3734, 7, 'image', 'image/jpeg', 'Chrysanthemum.jpg', './public/uploads/attributevalue/3734/chrysanthemum_56a9eff12e106.jpg', 879394, NULL),
(26, 'attributevalue', 3734, 8, 'image', 'image/jpeg', 'Tulips.jpg', './public/uploads/attributevalue/3734/tulips_56a9eff20511b.jpg', 620888, NULL),
(27, 'attributevalue', 3734, 9, 'image', 'image/jpeg', 'Penguins.jpg', './public/uploads/attributevalue/3734/penguins_56a9eff306871.jpg', 777835, NULL),
(28, 'attributevalue', 3734, 10, 'image', 'image/jpeg', 'Lighthouse.jpg', './public/uploads/attributevalue/3734/lighthouse_56a9eff3c6a19.jpg', 561276, NULL),
(29, 'attributevalue', 6913, 1, 'image', 'image/png', 'DOLD PIM.png', './public/uploads/attributevalue/6913/dold_pim_56b85233c2143.png', 113070, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `failes_login_count` int(11) DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `allow_products` tinyint(1) NOT NULL DEFAULT '0',
  `allow_attributes` tinyint(1) NOT NULL DEFAULT '0',
  `allow_templates` tinyint(1) NOT NULL DEFAULT '0',
  `allow_admin` tinyint(1) NOT NULL DEFAULT '0',
  `allow_delete` tinyint(1) NOT NULL DEFAULT '0',
  `allow_edit` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `name`, `password`, `email`, `last_login`, `failes_login_count`, `is_locked`, `allow_products`, `allow_attributes`, `allow_templates`, `allow_admin`, `allow_delete`, `allow_edit`) VALUES
(1, 'sysadmin', 'sysadmin', 'sysadmin@4fb.de', '2016-04-22 22:33:06', 0, 0, 1, 1, 1, 1, 1, 1),
(2, 'Erdal Mersinlioglu', 'sysadmin', 'erdal.mersinlioglu@4fb.de', '2016-01-04 09:40:25', 0, 0, 1, 1, 0, 0, 1, 1),
(3, 'Tester1', 'sysadmin', 'tester1@4fb.de', NULL, 0, 0, 1, 1, 1, 1, 1, 1),
(4, 'Produkt', 'produkt', 'produkt@4fb.de', '2016-01-04 10:48:20', 1, 0, 1, 1, 0, 0, 1, 1),
(5, 'sysadmina', 'sysadmin', 'sysadmina@4fb.de', NULL, NULL, 0, 1, 1, 1, 1, 1, 1);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `accessory_product`
--
ALTER TABLE `accessory_product`
  ADD CONSTRAINT `fk_product_product_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_product_product2` FOREIGN KEY (`accessory_product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `attribute_group_attribute`
--
ALTER TABLE `attribute_group_attribute`
  ADD CONSTRAINT `fk_attribute_attribute_grp_attribute1` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_attribute_attribute_grp_attribute_grp1` FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_group` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `attribute_group_lang`
--
ALTER TABLE `attribute_group_lang`
  ADD CONSTRAINT `fk_attibute_grp_lang_lang1` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_attribute_grp_lang_attribute_grp1` FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_group` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `attribute_lang`
--
ALTER TABLE `attribute_lang`
  ADD CONSTRAINT `fk_attribute_lang_attribute1` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_attribute_lang_lang1` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `attribute_value`
--
ALTER TABLE `attribute_value`
  ADD CONSTRAINT `fk_attribute_value_attribute_group1` FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_group` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_lang_attribute_lang_product_lang1` FOREIGN KEY (`product_lang_id`) REFERENCES `product_lang` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_lang_has_attribute_lang_attribute1` FOREIGN KEY (`attribute_lang_id`) REFERENCES `attribute_lang` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `attribute_value_log`
--
ALTER TABLE `attribute_value_log`
  ADD CONSTRAINT `fk_attribute_value_attribute_value_log1` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_value` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_user_attribute_value1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `fk_category_category1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_category_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_category_template1` FOREIGN KEY (`template_id`) REFERENCES `template` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `category_lang`
--
ALTER TABLE `category_lang`
  ADD CONSTRAINT `fk_category_lang_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_category_lang_lang1` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `linked_product`
--
ALTER TABLE `linked_product`
  ADD CONSTRAINT `fk_product_product_product3` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_product_product4` FOREIGN KEY (`linked_product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `multiple_usage`
--
ALTER TABLE `multiple_usage`
  ADD CONSTRAINT `fk_product_category1_category1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_category1_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `product_attribute_group`
--
ALTER TABLE `product_attribute_group`
  ADD CONSTRAINT `fk_attr_grp_product_attribute_grp1` FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_attri_grp_product_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `product_category`
--
ALTER TABLE `product_category`
  ADD CONSTRAINT `fk_category_product_category1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_category_product_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `product_group`
--
ALTER TABLE `product_group`
  ADD CONSTRAINT `fk_product_group_product_group1` FOREIGN KEY (`product_group_id`) REFERENCES `product_group` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `product_group_lang`
--
ALTER TABLE `product_group_lang`
  ADD CONSTRAINT `fk_product_group_lang_lang1` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_group_lang_product_group1` FOREIGN KEY (`product_group_id`) REFERENCES `product_group` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `product_group_product`
--
ALTER TABLE `product_group_product`
  ADD CONSTRAINT `fk_product_group__product_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_group_has_product_product_group1` FOREIGN KEY (`product_group_id`) REFERENCES `product_group` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `product_lang`
--
ALTER TABLE `product_lang`
  ADD CONSTRAINT `fk_product_lang_lang1` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_lang_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `template_attribute_group`
--
ALTER TABLE `template_attribute_group`
  ADD CONSTRAINT `fk_attr_grp_has_template_template1` FOREIGN KEY (`template_id`) REFERENCES `template` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_attr_grp_template_attribute_col1` FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_group` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
