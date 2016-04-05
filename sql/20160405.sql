-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 05. Apr 2016 um 23:20
-- Server-Version: 10.1.8-MariaDB
-- PHP-Version: 5.5.30

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

CREATE TABLE `accessory_product` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `accessory_product_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute`
--

CREATE TABLE `attribute` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` int(11) NOT NULL,
  `length` int(11) DEFAULT NULL,
  `is_uppercase` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_multi_select` tinyint(1) NOT NULL DEFAULT '0',
  `option_values` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `attribute`
--

INSERT INTO `attribute` (`id`, `type`, `length`, `is_uppercase`, `is_active`, `is_multi_select`, `option_values`) VALUES
(1, 1, 123, NULL, 1, 0, NULL),
(2, 5, 64, NULL, 0, 0, NULL),
(3, 3, 0, NULL, 1, 0, NULL),
(4, 1, 0, NULL, 1, 0, NULL),
(5, 6, 0, NULL, 1, 0, NULL),
(6, 1, 0, NULL, 1, 0, NULL),
(7, 1, 0, NULL, 1, 0, NULL),
(8, 1, 0, NULL, 1, 0, NULL),
(37, 1, NULL, NULL, 1, 0, NULL),
(38, 6, NULL, NULL, 1, 0, NULL),
(39, 1, NULL, NULL, 1, 0, NULL),
(41, 1, NULL, NULL, 1, 0, NULL),
(42, 2, NULL, NULL, 1, 0, NULL),
(43, 2, NULL, NULL, 1, 0, NULL),
(44, 2, NULL, NULL, 1, 0, NULL),
(45, 8, NULL, NULL, 1, 0, NULL),
(46, 8, NULL, NULL, 1, 0, NULL),
(47, 9, NULL, NULL, 1, 0, NULL),
(48, 8, NULL, NULL, 1, 0, NULL),
(49, 2, NULL, NULL, 1, 0, NULL),
(50, 1, NULL, NULL, 1, 0, NULL),
(51, 1, NULL, NULL, 1, 0, NULL),
(52, 1, NULL, NULL, 1, 0, NULL),
(53, 1, NULL, NULL, 1, 0, NULL),
(54, 1, NULL, NULL, 1, 0, NULL),
(55, 1, NULL, NULL, 1, 0, NULL),
(56, 1, NULL, NULL, 1, 0, NULL),
(57, 5, NULL, NULL, 1, 0, NULL),
(58, 5, NULL, NULL, 1, 0, NULL),
(59, 1, NULL, NULL, 1, 0, NULL),
(60, 1, NULL, NULL, 1, 0, NULL),
(61, 1, NULL, NULL, 1, 0, NULL),
(62, 1, NULL, NULL, 1, 0, NULL),
(63, 1, NULL, NULL, 1, 0, NULL),
(64, 1, NULL, NULL, 1, 0, NULL),
(65, 1, NULL, NULL, 1, 0, NULL),
(66, 1, NULL, NULL, 1, 0, NULL),
(67, 6, NULL, NULL, 1, 0, NULL),
(68, 1, NULL, NULL, 1, 0, NULL),
(69, 1, NULL, NULL, 1, 0, NULL),
(70, 1, NULL, NULL, 1, 0, NULL),
(71, 1, NULL, NULL, 1, 0, NULL),
(72, 1, NULL, NULL, 1, 0, NULL),
(73, 1, NULL, NULL, 1, 0, NULL),
(74, 1, NULL, NULL, 1, 0, NULL),
(75, 1, NULL, NULL, 1, 0, NULL),
(76, 1, NULL, NULL, 1, 0, NULL),
(77, 1, NULL, NULL, 1, 0, NULL),
(79, 1, NULL, NULL, 1, 0, NULL),
(80, 1, NULL, NULL, 1, 0, NULL),
(81, 1, NULL, NULL, 1, 0, NULL),
(82, 1, NULL, NULL, 1, 0, NULL),
(83, 1, NULL, NULL, 1, 0, NULL),
(84, 6, NULL, NULL, 1, 0, NULL),
(85, 7, NULL, NULL, 1, 0, NULL),
(86, 8, NULL, NULL, 1, 0, NULL),
(87, 9, NULL, NULL, 1, 0, NULL),
(88, 1, NULL, NULL, 1, 0, NULL),
(89, 1, NULL, NULL, 1, 0, NULL),
(90, 1, NULL, NULL, 1, 0, NULL),
(91, 1, NULL, NULL, 1, 0, NULL),
(92, 8, NULL, NULL, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_group`
--

CREATE TABLE `attribute_group` (
  `id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `attribute_group`
--

INSERT INTO `attribute_group` (`id`) VALUES
(1),
(2),
(40),
(62),
(63),
(64);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_group_attribute`
--

CREATE TABLE `attribute_group_attribute` (
  `id` int(10) UNSIGNED NOT NULL,
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `attribute_group_id` int(10) UNSIGNED DEFAULT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `attribute_group_attribute`
--

INSERT INTO `attribute_group_attribute` (`id`, `attribute_id`, `attribute_group_id`, `sort`) VALUES
(58, 2, 1, 0),
(82, 3, 1, 0),
(83, 1, 1, 0),
(84, 4, 1, 0),
(85, 5, 1, 0),
(86, 6, 1, 0),
(87, 7, 1, 0),
(88, 8, 1, 0),
(89, 37, 1, 0),
(90, 38, 1, 0),
(91, 39, 1, 0),
(93, 42, 2, 0),
(94, 43, 2, 0),
(95, 44, 2, 0),
(104, 1, 40, 0),
(105, 62, 40, 0),
(106, 61, 40, 0),
(107, 63, 40, 0),
(108, 64, 40, 0),
(109, 39, 40, 0),
(110, 65, 40, 0),
(119, 1, 62, 0),
(133, 66, 62, 0),
(134, 67, 62, 0),
(135, 68, 62, 0),
(136, 69, 62, 0),
(137, 70, 62, 0),
(138, 71, 62, 0),
(139, 72, 62, 0),
(140, 39, 62, 0),
(141, 79, 63, 0),
(143, 77, 63, 0),
(144, 76, 63, 0),
(145, 75, 63, 0),
(146, 74, 63, 0),
(147, 73, 63, 0),
(148, 37, 63, 0),
(149, 80, 63, 0),
(150, 81, 63, 0),
(151, 1, 63, 0),
(152, 82, 63, 0),
(153, 83, 63, 0),
(154, 84, 1, 0),
(155, 85, 1, 0),
(156, 86, 1, 0),
(157, 87, 1, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_group_lang`
--

CREATE TABLE `attribute_group_lang` (
  `id` int(10) UNSIGNED NOT NULL,
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `attribute_group_lang`
--

INSERT INTO `attribute_group_lang` (`id`, `attribute_group_id`, `lang_id`, `name`, `title`, `alias`) VALUES
(1, 1, 1, 'Tabelle: Module SAFEMASTER ', '', ''),
(2, 2, 1, 'Tabs Produktseite', '', ''),
(170, 40, 1, 'Tabelle: Einrichtung zur Isolationsfehlersuche ', '', ''),
(171, 40, 2, '', '', ''),
(172, 40, 3, '', '', ''),
(173, 40, 4, '', '', ''),
(174, 40, 5, '', '', ''),
(175, 40, 6, '', '', ''),
(314, 1, 2, '', '', ''),
(315, 1, 3, '', '', ''),
(316, 1, 4, '', '', ''),
(317, 1, 5, '', '', ''),
(318, 1, 6, '', '', ''),
(319, 2, 2, '', '', ''),
(320, 2, 3, '', '', ''),
(321, 2, 4, '', '', ''),
(322, 2, 5, '', '', ''),
(323, 2, 6, '', '', ''),
(324, 62, 1, 'Tabelle: Halbleiterschütze u. Halbleiterrelais ', '', ''),
(325, 62, 2, 'Kopie von ', '', ''),
(326, 62, 3, 'Kopie von ', '', ''),
(327, 62, 4, 'Kopie von ', '', ''),
(328, 62, 5, 'Kopie von ', '', ''),
(329, 62, 6, 'Kopie von ', '', ''),
(330, 63, 1, 'Tabelle: Kartenrelais', '', ''),
(331, 63, 2, 'Kopie von Kopie von ', '', ''),
(332, 63, 3, 'Kopie von Kopie von ', '', ''),
(333, 63, 4, 'Kopie von Kopie von ', '', ''),
(334, 63, 5, 'Kopie von Kopie von ', '', ''),
(335, 63, 6, 'Kopie von Kopie von ', '', ''),
(336, 64, 1, 'Kategorie Stammdaten', '', ''),
(337, 64, 2, '', '', ''),
(338, 64, 3, '', '', ''),
(339, 64, 4, '', '', ''),
(340, 64, 5, '', '', ''),
(341, 64, 6, '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_lang`
--

CREATE TABLE `attribute_lang` (
  `id` int(10) UNSIGNED NOT NULL,
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `attribute_lang`
--

INSERT INTO `attribute_lang` (`id`, `attribute_id`, `lang_id`, `name`, `title`, `alias`, `unit`) VALUES
(1, 1, 1, 'Funktion', '', '', ''),
(2, 3, 1, 'Kat. / PL nach EN 13849-1 1)', '', '', ''),
(3, 3, 2, 'Attr -  3', '', '', ''),
(4, 3, 3, 'Attr -  4', '', '', ''),
(5, 3, 4, 'Attr -  5', '', '', ''),
(6, 3, 5, 'Attr -  6', '', '', ''),
(7, 3, 6, 'Attr -  7', '', '', ''),
(8, 4, 1, 'SIL CL nach EN 62061 1)', '', 'ssss', ''),
(9, 4, 2, 'Attr -  9', '', '', ''),
(10, 4, 3, 'Attr -  10', '', '', ''),
(11, 4, 4, 'Attr -  11', '', '', ''),
(12, 4, 5, 'Attr -  12', '', '', ''),
(13, 4, 6, 'Attr -  13', '', '', ''),
(14, 5, 1, '1- / 2- kanalig', '', '', ''),
(15, 5, 2, 'Attr -  15', '', '', ''),
(16, 5, 3, 'Attr -  16', '', '', ''),
(17, 5, 4, 'Attr -  17', '', '', ''),
(18, 5, 5, 'Attr -  18', '', '', ''),
(19, 5, 6, 'Attr -  19', '', '', ''),
(20, 6, 1, 'Ausgangskontakte, max.', '', '', ''),
(21, 6, 2, 'Attr -  21', '', '', ''),
(22, 6, 3, 'Attr -  22', '', '', ''),
(23, 6, 4, 'Attr -  23', '', '', ''),
(24, 6, 5, 'Attr -  24', '', '', ''),
(25, 6, 6, 'Attr -  25', '', '', ''),
(26, 7, 1, 'Termischer Strom Ith [A] max. ', '', 'asasas', ''),
(27, 7, 2, 'Attr -  27', '', '', ''),
(28, 7, 3, 'Attr -  28', '', '', ''),
(29, 7, 4, 'Attr -  29', '', '', ''),
(30, 7, 5, 'Attr -  30', '', '', ''),
(31, 7, 6, 'Attr -  31', '', '', ''),
(32, 8, 1, 'Querschlußerkennung', '', 'with group 1', ''),
(33, 8, 2, 'Attr -  33', '', '', ''),
(34, 8, 3, 'Attr -  34', '', '', ''),
(35, 8, 4, 'Attr -  35', '', '', ''),
(36, 8, 5, 'Attr -  36', '', '', ''),
(37, 8, 6, 'Attr -  37', '', '', ''),
(170, 2, 1, 'auch als Schutztürwächter geeignet', '', '', ''),
(171, 2, 2, '', '', '', ''),
(172, 2, 3, '', '', '', ''),
(173, 2, 4, '', '', '', ''),
(174, 2, 5, '', '', '', ''),
(175, 2, 6, '', '', '', ''),
(182, 1, 2, '', '', '', ''),
(183, 1, 3, '', '', '', ''),
(184, 1, 4, '', '', '', ''),
(185, 1, 5, '', '', '', ''),
(186, 1, 6, '', '', '', ''),
(235, 37, 1, 'Nennspannung', '', '', 'DC'),
(236, 37, 2, '', '', '', ''),
(237, 37, 3, '', '', '', ''),
(238, 37, 4, '', '', '', ''),
(239, 37, 5, '', '', '', ''),
(240, 37, 6, '', '', '', ''),
(241, 38, 1, 'Anschlusstechnik', '', '', ''),
(242, 38, 2, '', '', '', ''),
(243, 38, 3, '', '', '', ''),
(244, 38, 4, '', '', '', ''),
(245, 38, 5, '', '', '', ''),
(246, 38, 6, '', '', '', ''),
(247, 39, 1, 'Baubreite mm', '', '', ''),
(248, 39, 2, '', '', '', ''),
(249, 39, 3, '', '', '', ''),
(250, 39, 4, '', '', '', ''),
(251, 39, 5, '', '', '', ''),
(252, 39, 6, '', '', '', ''),
(259, 41, 1, 'abnehmbare Klemmen', '', '', ''),
(260, 41, 2, '', '', '', ''),
(261, 41, 3, '', '', '', ''),
(262, 41, 4, '', '', '', ''),
(263, 41, 5, '', '', '', ''),
(264, 41, 6, '', '', '', ''),
(265, 42, 1, 'Anwendungen', '', '', ''),
(266, 42, 2, '', '', '', ''),
(267, 42, 3, '', '', '', ''),
(268, 42, 4, '', '', '', ''),
(269, 42, 5, '', '', '', ''),
(270, 42, 6, '', '', '', ''),
(271, 43, 1, 'Technik', '', '', ''),
(272, 43, 2, '', '', '', ''),
(273, 43, 3, '', '', '', ''),
(274, 43, 4, '', '', '', ''),
(275, 43, 5, '', '', '', ''),
(276, 43, 6, '', '', '', ''),
(277, 44, 1, 'Vorteile', '', '', ''),
(278, 44, 2, '', '', '', ''),
(279, 44, 3, '', '', '', ''),
(280, 44, 4, '', '', '', ''),
(281, 44, 5, '', '', '', ''),
(282, 44, 6, '', '', '', ''),
(283, 45, 1, 'Produktbild 1', '', '', ''),
(284, 45, 2, '', '', '', ''),
(285, 45, 3, '', '', '', ''),
(286, 45, 4, '', '', '', ''),
(287, 45, 5, '', '', '', ''),
(288, 45, 6, '', '', '', ''),
(289, 46, 1, 'Produktbild 2', '', '', ''),
(290, 46, 2, '', '', '', ''),
(291, 46, 3, '', '', '', ''),
(292, 46, 4, '', '', '', ''),
(293, 46, 5, '', '', '', ''),
(294, 46, 6, '', '', '', ''),
(295, 47, 1, 'Datenblatt', '', '', ''),
(296, 47, 2, '', '', '', ''),
(297, 47, 3, '', '', '', ''),
(298, 47, 4, '', '', '', ''),
(299, 47, 5, '', '', '', ''),
(300, 47, 6, '', '', '', ''),
(301, 48, 1, 'Technische Zeichnung', '', '', ''),
(302, 48, 2, '', '', '', ''),
(303, 48, 3, '', '', '', ''),
(304, 48, 4, '', '', '', ''),
(305, 48, 5, '', '', '', ''),
(306, 48, 6, '', '', '', ''),
(307, 49, 1, 'Verwendbare DOLD-Auswertegeräte', '', '', ''),
(308, 49, 2, '', '', '', ''),
(309, 49, 3, '', '', '', ''),
(310, 49, 4, '', '', '', ''),
(311, 49, 5, '', '', '', ''),
(312, 49, 6, '', '', '', ''),
(313, 50, 1, 'DC 24 V', '', '', ''),
(314, 50, 2, '', '', '', ''),
(315, 50, 3, '', '', '', ''),
(316, 50, 4, '', '', '', ''),
(317, 50, 5, '', '', '', ''),
(318, 50, 6, '', '', '', ''),
(319, 51, 1, '2-kanalig', '', '', ''),
(320, 51, 2, '', '', '', ''),
(321, 51, 3, '', '', '', ''),
(322, 51, 4, '', '', '', ''),
(323, 51, 5, '', '', '', ''),
(324, 51, 6, '', '', '', ''),
(325, 52, 1, 'Kat. nach EN 13849-1 ', '', '', ''),
(326, 52, 2, '', '', '', ''),
(327, 52, 3, '', '', '', ''),
(328, 52, 4, '', '', '', ''),
(329, 52, 5, '', '', '', ''),
(330, 52, 6, '', '', '', ''),
(331, 53, 1, 'Kat. / PL nach EN 13849-1*', '', '', ''),
(332, 53, 2, '', '', '', ''),
(333, 53, 3, '', '', '', ''),
(334, 53, 4, '', '', '', ''),
(335, 53, 5, '', '', '', ''),
(336, 53, 6, '', '', '', ''),
(337, 54, 1, 'Netzart', '', '', ''),
(338, 54, 2, '', '', '', ''),
(339, 54, 3, '', '', '', ''),
(340, 54, 4, '', '', '', ''),
(341, 54, 5, '', '', '', ''),
(342, 54, 6, '', '', '', ''),
(343, 55, 1, 'Nennspannung bis [V]', '', '', ''),
(344, 55, 2, '', '', '', ''),
(345, 55, 3, '', '', '', ''),
(346, 55, 4, '', '', '', ''),
(347, 55, 5, '', '', '', ''),
(348, 55, 6, '', '', '', ''),
(349, 56, 1, 'Ansprechwert', '', '', ''),
(350, 56, 2, '', '', '', ''),
(351, 56, 3, '', '', '', ''),
(352, 56, 4, '', '', '', ''),
(353, 56, 5, '', '', '', ''),
(354, 56, 6, '', '', '', ''),
(355, 57, 1, 'mit Hilfsspannung', '', '', ''),
(356, 57, 2, '', '', '', ''),
(357, 57, 3, '', '', '', ''),
(358, 57, 4, '', '', '', ''),
(359, 57, 5, '', '', '', ''),
(360, 57, 6, '', '', '', ''),
(361, 58, 1, 'Anzeige für Erdschluss', '', '', ''),
(362, 58, 2, '', '', '', ''),
(363, 58, 3, '', '', '', ''),
(364, 58, 4, '', '', '', ''),
(365, 58, 5, '', '', '', ''),
(366, 58, 6, '', '', '', ''),
(367, 59, 1, 'Anschluss für Anzeigeinstrument', '', '', ''),
(368, 59, 2, '', '', '', ''),
(369, 59, 3, '', '', '', ''),
(370, 59, 4, '', '', '', ''),
(371, 59, 5, '', '', '', ''),
(372, 59, 6, '', '', '', ''),
(373, 60, 1, 'Gehäuse Bauform: Baubreite [mm]', '', '', ''),
(374, 60, 2, '', '', '', ''),
(375, 60, 3, '', '', '', ''),
(376, 60, 4, '', '', '', ''),
(377, 60, 5, '', '', '', ''),
(378, 60, 6, '', '', '', ''),
(379, 61, 1, 'Bus-Schnittstelle', '', '', ''),
(380, 61, 2, '', '', '', ''),
(381, 61, 3, '', '', '', ''),
(382, 61, 4, '', '', '', ''),
(383, 61, 5, '', '', '', ''),
(384, 61, 6, '', '', '', ''),
(385, 62, 1, 'Fehlerspeicherung', '', '', ''),
(386, 62, 2, '', '', '', ''),
(387, 62, 3, '', '', '', ''),
(388, 62, 4, '', '', '', ''),
(389, 62, 5, '', '', '', ''),
(390, 62, 6, '', '', '', ''),
(391, 63, 1, 'Betriebsart', '', '', ''),
(392, 63, 2, '', '', '', ''),
(393, 63, 3, '', '', '', ''),
(394, 63, 4, '', '', '', ''),
(395, 63, 5, '', '', '', ''),
(396, 63, 6, '', '', '', ''),
(397, 64, 1, 'Betriebsspannung', '', '', ''),
(398, 64, 2, '', '', '', ''),
(399, 64, 3, '', '', '', ''),
(400, 64, 4, '', '', '', ''),
(401, 64, 5, '', '', '', ''),
(402, 64, 6, '', '', '', ''),
(403, 65, 1, 'Nennspannung IT-System', '', '', ''),
(404, 65, 2, '', '', '', ''),
(405, 65, 3, '', '', '', ''),
(406, 65, 4, '', '', '', ''),
(407, 65, 5, '', '', '', ''),
(408, 65, 6, '', '', '', ''),
(409, 66, 1, 'Laststrom [A]', '', '', ''),
(410, 66, 2, '', '', '', ''),
(411, 66, 3, '', '', '', ''),
(412, 66, 4, '', '', '', ''),
(413, 66, 5, '', '', '', ''),
(414, 66, 6, '', '', '', ''),
(415, 67, 1, 'Lastspannung AC bis [V]', '', '', ''),
(416, 67, 2, '', '', '', ''),
(417, 67, 3, '', '', '', ''),
(418, 67, 4, '', '', '', ''),
(419, 67, 5, '', '', '', ''),
(420, 67, 6, '', '', '', ''),
(421, 68, 1, 'Hilfsspannung DC [V]', '', '', ''),
(422, 68, 2, '', '', '', ''),
(423, 68, 3, '', '', '', ''),
(424, 68, 4, '', '', '', ''),
(425, 68, 5, '', '', '', ''),
(426, 68, 6, '', '', '', ''),
(427, 69, 1, 'Ansteuerung digital [V]', '', '', ''),
(428, 69, 2, '', '', '', ''),
(429, 69, 3, '', '', '', ''),
(430, 69, 4, '', '', '', ''),
(431, 69, 5, '', '', '', ''),
(432, 69, 6, '', '', '', ''),
(433, 70, 1, 'Ansteuerung analog', '', '', ''),
(434, 70, 2, '', '', '', ''),
(435, 70, 3, '', '', '', ''),
(436, 70, 4, '', '', '', ''),
(437, 70, 5, '', '', '', ''),
(438, 70, 6, '', '', '', ''),
(439, 71, 1, 'Meldeausgang', '', '', ''),
(440, 71, 2, '', '', '', ''),
(441, 71, 3, '', '', '', ''),
(442, 71, 4, '', '', '', ''),
(443, 71, 5, '', '', '', ''),
(444, 71, 6, '', '', '', ''),
(445, 72, 1, 'Temperaturüberwachung', '', '', ''),
(446, 72, 2, '', '', '', ''),
(447, 72, 3, '', '', '', ''),
(448, 72, 4, '', '', '', ''),
(449, 72, 5, '', '', '', ''),
(450, 72, 6, '', '', '', ''),
(451, 73, 1, 'Grenzdauerstrom', '', '', ''),
(452, 73, 2, '', '', '', ''),
(453, 73, 3, '', '', '', ''),
(454, 73, 4, '', '', '', ''),
(455, 73, 5, '', '', '', ''),
(456, 73, 6, '', '', '', ''),
(457, 74, 1, 'waschdicht', '', '', ''),
(458, 74, 2, '', '', '', ''),
(459, 74, 3, '', '', '', ''),
(460, 74, 4, '', '', '', ''),
(461, 74, 5, '', '', '', ''),
(462, 74, 6, '', '', '', ''),
(463, 75, 1, 'AgSnO2', '', '', ''),
(464, 75, 2, '', '', '', ''),
(465, 75, 3, '', '', '', ''),
(466, 75, 4, '', '', '', ''),
(467, 75, 5, '', '', '', ''),
(468, 75, 6, '', '', '', ''),
(469, 76, 1, 'Temperatur', '', '', ''),
(470, 76, 2, '', '', '', ''),
(471, 76, 3, '', '', '', ''),
(472, 76, 4, '', '', '', ''),
(473, 76, 5, '', '', '', ''),
(474, 76, 6, '', '', '', ''),
(475, 77, 1, 'Bauhöhe [mm]', '', '', ''),
(476, 77, 2, '', '', '', ''),
(477, 77, 3, '', '', '', ''),
(478, 77, 4, '', '', '', ''),
(479, 77, 5, '', '', '', ''),
(480, 77, 6, '', '', '', ''),
(487, 79, 1, 'Kontaktanzahl', '', '', ''),
(488, 79, 2, '', '', '', ''),
(489, 79, 3, '', '', '', ''),
(490, 79, 4, '', '', '', ''),
(491, 79, 5, '', '', '', ''),
(492, 79, 6, '', '', '', ''),
(493, 80, 1, 'AgNi 0,15+ 5um Au', '', '', ''),
(494, 80, 2, '', '', '', ''),
(495, 80, 3, '', '', '', ''),
(496, 80, 4, '', '', '', ''),
(497, 80, 5, '', '', '', ''),
(498, 80, 6, '', '', '', ''),
(499, 81, 1, 'AgNi 10', '', '', ''),
(500, 81, 2, '', '', '', ''),
(501, 81, 3, '', '', '', ''),
(502, 81, 4, '', '', '', ''),
(503, 81, 5, '', '', '', ''),
(504, 81, 6, '', '', '', ''),
(505, 82, 1, 'Nennverbrauch', '', '', ''),
(506, 82, 2, '', '', '', ''),
(507, 82, 3, '', '', '', ''),
(508, 82, 4, '', '', '', ''),
(509, 82, 5, '', '', '', ''),
(510, 82, 6, '', '', '', ''),
(511, 83, 1, 'AgNi 10+ 3um Au', '', '', ''),
(512, 83, 2, '', '', '', ''),
(513, 83, 3, '', '', '', ''),
(514, 83, 4, '', '', '', ''),
(515, 83, 5, '', '', '', ''),
(516, 83, 6, '', '', '', ''),
(517, 84, 1, 'Test - Range INT', '', '', ''),
(518, 84, 2, '', '', '', ''),
(519, 84, 3, '', '', '', ''),
(520, 84, 4, '', '', '', ''),
(521, 84, 5, '', '', '', ''),
(522, 84, 6, '', '', '', ''),
(523, 85, 1, 'Test - Range Float', '', '', ''),
(524, 85, 2, '', '', '', ''),
(525, 85, 3, '', '', '', ''),
(526, 85, 4, '', '', '', ''),
(527, 85, 5, '', '', '', ''),
(528, 85, 6, '', '', '', ''),
(529, 86, 1, 'Bilder', '', '', ''),
(530, 86, 2, 'Pictures', '', '', ''),
(531, 86, 3, '', '', '', ''),
(532, 86, 4, '', '', '', ''),
(533, 86, 5, '', '', '', ''),
(534, 86, 6, '', '', '', ''),
(535, 87, 1, 'Dateien', '', '', ''),
(536, 87, 2, '', '', '', ''),
(537, 87, 3, '', '', '', ''),
(538, 87, 4, '', '', '', ''),
(539, 87, 5, '', '', '', ''),
(540, 87, 6, '', '', '', ''),
(541, 88, 1, 'Marketing Title', '', '', ''),
(542, 88, 2, 'Marketing Title', '', '', ''),
(543, 88, 3, 'Titre de Marketing', '', '', ''),
(544, 88, 4, 'Titulo de Marketing', '', '', ''),
(545, 88, 5, '', '', '', ''),
(546, 88, 6, '', '', '', ''),
(547, 89, 1, 'Marketing Beschreibung', '', '', ''),
(548, 89, 2, '', '', '', ''),
(549, 89, 3, '', '', '', ''),
(550, 89, 4, '', '', '', ''),
(551, 89, 5, '', '', '', ''),
(552, 89, 6, '', '', '', ''),
(553, 90, 1, 'SEO Titel', '', '', ''),
(554, 90, 2, '', '', '', ''),
(555, 90, 3, '', '', '', ''),
(556, 90, 4, '', '', '', ''),
(557, 90, 5, '', '', '', ''),
(558, 90, 6, '', '', '', ''),
(559, 91, 1, 'SEO Beschreibung', '', '', ''),
(560, 91, 2, '', '', '', ''),
(561, 91, 3, '', '', '', ''),
(562, 91, 4, '', '', '', ''),
(563, 91, 5, '', '', '', ''),
(564, 91, 6, '', '', '', ''),
(565, 92, 1, 'Kategoriebild', '', '', ''),
(566, 92, 2, '', '', '', ''),
(567, 92, 3, '', '', '', ''),
(568, 92, 4, '', '', '', ''),
(569, 92, 5, '', '', '', ''),
(570, 92, 6, '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attribute_value`
--

CREATE TABLE `attribute_value` (
  `id` int(10) UNSIGNED NOT NULL,
  `value` text,
  `value_min` varchar(255) DEFAULT NULL,
  `value_max` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_inherited` tinyint(1) NOT NULL DEFAULT '0',
  `product_lang_id` int(10) UNSIGNED NOT NULL,
  `attribute_lang_id` int(10) UNSIGNED NOT NULL,
  `attribute_group_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Trigger `attribute_value`
--
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

CREATE TABLE `attribute_value_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `item` varchar(128) NOT NULL,
  `old_value` text,
  `new_value` text,
  `date` datetime NOT NULL,
  `attribute_value_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `category`
--

CREATE TABLE `category` (
  `id` int(11) UNSIGNED NOT NULL,
  `template_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Root-Product for Default-Attribute-Values',
  `sort` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `category`
--

INSERT INTO `category` (`id`, `template_id`, `category_id`, `product_id`, `sort`) VALUES
(39, NULL, NULL, 91, 0),
(40, NULL, NULL, 92, 0),
(41, NULL, NULL, 93, 0),
(42, NULL, NULL, 94, 0),
(43, NULL, NULL, 95, 0),
(44, NULL, NULL, 96, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `category_lang`
--

CREATE TABLE `category_lang` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `category_lang`
--

INSERT INTO `category_lang` (`id`, `category_id`, `lang_id`, `name`, `alias`, `description`) VALUES
(234, 39, 1, 'Pet S 190ml', NULL, NULL),
(235, 39, 2, '', NULL, NULL),
(236, 39, 3, '', NULL, NULL),
(237, 39, 4, '', NULL, NULL),
(238, 39, 5, '', NULL, NULL),
(239, 39, 6, '', NULL, NULL),
(240, 40, 1, 'Pet L 350ml', NULL, NULL),
(241, 40, 2, '', NULL, NULL),
(242, 40, 3, '', NULL, NULL),
(243, 40, 4, '', NULL, NULL),
(244, 40, 5, '', NULL, NULL),
(245, 40, 6, '', NULL, NULL),
(246, 41, 1, 'Caylar', NULL, NULL),
(247, 41, 2, '', NULL, NULL),
(248, 41, 3, '', NULL, NULL),
(249, 41, 4, '', NULL, NULL),
(250, 41, 5, '', NULL, NULL),
(251, 41, 6, '', NULL, NULL),
(252, 42, 1, 'Büyük Pet', NULL, NULL),
(253, 42, 2, '', NULL, NULL),
(254, 42, 3, '', NULL, NULL),
(255, 42, 4, '', NULL, NULL),
(256, 42, 5, '', NULL, NULL),
(257, 42, 6, '', NULL, NULL),
(258, 43, 1, 'Kurular', NULL, NULL),
(259, 43, 2, '', NULL, NULL),
(260, 43, 3, '', NULL, NULL),
(261, 43, 4, '', NULL, NULL),
(262, 43, 5, '', NULL, NULL),
(263, 43, 6, '', NULL, NULL),
(264, 44, 1, 'Kova', NULL, NULL),
(265, 44, 2, '', NULL, NULL),
(266, 44, 3, '', NULL, NULL),
(267, 44, 4, '', NULL, NULL),
(268, 44, 5, '', NULL, NULL),
(269, 44, 6, '', NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lang`
--

CREATE TABLE `lang` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `iso` varchar(2) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE `linked_product` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `linked_product_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `multiple_usage`
--

CREATE TABLE `multiple_usage` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product`
--

CREATE TABLE `product` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `online` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `image_url` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `product`
--

INSERT INTO `product` (`id`, `product_id`, `number`, `price`, `online`, `sort`, `is_system`, `image_url`) VALUES
(91, NULL, NULL, NULL, 0, 0, 0, NULL),
(92, NULL, NULL, NULL, 0, 0, 0, NULL),
(93, NULL, NULL, NULL, 0, 0, 0, NULL),
(94, NULL, NULL, NULL, 0, 0, 0, NULL),
(95, NULL, NULL, NULL, 0, 0, 0, NULL),
(96, NULL, NULL, NULL, 0, 0, 0, NULL),
(97, NULL, NULL, NULL, 0, 0, 0, NULL),
(98, NULL, NULL, 0, 0, 0, 0, NULL),
(99, NULL, NULL, 0, 0, 0, 0, NULL),
(100, NULL, NULL, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_attribute_group`
--

CREATE TABLE `product_attribute_group` (
  `id` int(10) UNSIGNED NOT NULL,
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_category`
--

CREATE TABLE `product_category` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `product_category`
--

INSERT INTO `product_category` (`id`, `product_id`, `category_id`, `sort`) VALUES
(80, 91, 39, NULL),
(81, 92, 40, NULL),
(82, 93, 41, NULL),
(83, 94, 42, NULL),
(84, 95, 43, NULL),
(85, 96, 44, NULL),
(86, 97, 39, NULL),
(87, 98, 39, NULL),
(88, 99, 39, NULL),
(89, 100, 39, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_group`
--

CREATE TABLE `product_group` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_group_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE `product_group_lang` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_group_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE `product_group_product` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_group_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `product_lang`
--

CREATE TABLE `product_lang` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `lang_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `product_lang`
--

INSERT INTO `product_lang` (`id`, `product_id`, `lang_id`, `name`, `alias`, `description`) VALUES
(541, 91, 1, NULL, NULL, NULL),
(542, 91, 2, NULL, NULL, NULL),
(543, 91, 3, NULL, NULL, NULL),
(544, 91, 4, NULL, NULL, NULL),
(545, 91, 5, NULL, NULL, NULL),
(546, 91, 6, NULL, NULL, NULL),
(547, 92, 1, NULL, NULL, NULL),
(548, 92, 2, NULL, NULL, NULL),
(549, 92, 3, NULL, NULL, NULL),
(550, 92, 4, NULL, NULL, NULL),
(551, 92, 5, NULL, NULL, NULL),
(552, 92, 6, NULL, NULL, NULL),
(553, 93, 1, '', '', ''),
(554, 93, 2, '', '', ''),
(555, 93, 3, '', '', ''),
(556, 93, 4, '', '', ''),
(557, 93, 5, '', '', ''),
(558, 93, 6, '', '', ''),
(559, 94, 1, NULL, NULL, NULL),
(560, 94, 2, NULL, NULL, NULL),
(561, 94, 3, NULL, NULL, NULL),
(562, 94, 4, NULL, NULL, NULL),
(563, 94, 5, NULL, NULL, NULL),
(564, 94, 6, NULL, NULL, NULL),
(565, 95, 1, NULL, NULL, NULL),
(566, 95, 2, NULL, NULL, NULL),
(567, 95, 3, NULL, NULL, NULL),
(568, 95, 4, NULL, NULL, NULL),
(569, 95, 5, NULL, NULL, NULL),
(570, 95, 6, NULL, NULL, NULL),
(571, 96, 1, NULL, NULL, NULL),
(572, 96, 2, NULL, NULL, NULL),
(573, 96, 3, NULL, NULL, NULL),
(574, 96, 4, NULL, NULL, NULL),
(575, 96, 5, NULL, NULL, NULL),
(576, 96, 6, NULL, NULL, NULL),
(577, 97, 1, 'Kekik', '', ''),
(578, 97, 2, '', '', ''),
(579, 97, 3, '', '', ''),
(580, 97, 4, '', '', ''),
(581, 97, 5, '', '', ''),
(582, 97, 6, '', '', ''),
(583, 98, 1, 'Kirmizi Pulbiber', NULL, ''),
(584, 98, 2, '', NULL, ''),
(585, 99, 1, 'Kimyon', NULL, ''),
(586, 99, 2, '', NULL, ''),
(587, 100, 1, 'Defne', NULL, ''),
(588, 100, 2, '', NULL, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `template`
--

CREATE TABLE `template` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `template`
--

INSERT INTO `template` (`id`, `name`) VALUES
(1, 'Schaltgeräte'),
(2, 'Not Aus Module'),
(3, 'Mess und Überwachungstechnik'),
(21, 'Leistungselektronik'),
(22, 'Kartenrelais');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `template_attribute_group`
--

CREATE TABLE `template_attribute_group` (
  `id` int(10) UNSIGNED NOT NULL,
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `template_id` int(10) UNSIGNED NOT NULL,
  `sort` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `template_attribute_group`
--

INSERT INTO `template_attribute_group` (`id`, `attribute_group_id`, `template_id`, `sort`) VALUES
(5, 2, 1, 0),
(12, 1, 1, 0),
(13, 1, 2, 0),
(14, 2, 2, 0),
(15, 40, 3, 0),
(16, 2, 3, 0),
(17, 62, 21, 0),
(18, 63, 22, 0),
(20, 40, 1, 0),
(23, 63, 1, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `upload`
--

CREATE TABLE `upload` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_type` varchar(256) NOT NULL,
  `reference_id` int(10) UNSIGNED NOT NULL,
  `rank` int(10) UNSIGNED NOT NULL,
  `destination` varchar(128) DEFAULT NULL,
  `mimetype` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `tmpname` varchar(256) NOT NULL,
  `size` int(10) UNSIGNED NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `allow_edit` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `name`, `password`, `email`, `last_login`, `failes_login_count`, `is_locked`, `allow_products`, `allow_attributes`, `allow_templates`, `allow_admin`, `allow_delete`, `allow_edit`) VALUES
(1, 'sysadmin', 'sysadmin', 'sysadmin@4fb.de', '2016-03-24 18:51:10', 0, 0, 1, 1, 1, 1, 1, 1),
(2, 'Erdal Mersinlioglu', 'sysadmin', 'erdal.mersinlioglu@4fb.de', '2016-01-04 09:40:25', 0, 0, 1, 1, 0, 0, 1, 1),
(3, 'Tester1', 'sysadmin', 'tester1@4fb.de', NULL, 0, 0, 1, 1, 1, 1, 1, 1),
(4, 'Produkt', 'produkt', 'produkt@4fb.de', '2016-01-04 10:48:20', 1, 0, 1, 1, 0, 0, 1, 1);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `accessory_product`
--
ALTER TABLE `accessory_product`
  ADD PRIMARY KEY (`product_id`,`accessory_product_id`),
  ADD KEY `fk_product_product_product2_idx` (`accessory_product_id`),
  ADD KEY `fk_product_product_product1_idx` (`product_id`);

--
-- Indizes für die Tabelle `attribute`
--
ALTER TABLE `attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `attribute_group`
--
ALTER TABLE `attribute_group`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `attribute_group_attribute`
--
ALTER TABLE `attribute_group_attribute`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attribute_grp_attribute_col1_idx` (`attribute_group_id`),
  ADD KEY `fk_attribute_grpl_attribute1_idx` (`attribute_id`);

--
-- Indizes für die Tabelle `attribute_group_lang`
--
ALTER TABLE `attribute_group_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attibute_col_lang_attribute_col1_idx` (`attribute_group_id`),
  ADD KEY `fk_attibute_col_lang_lang1_idx` (`lang_id`);

--
-- Indizes für die Tabelle `attribute_lang`
--
ALTER TABLE `attribute_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attribute_lang_attribute1_idx` (`attribute_id`),
  ADD KEY `fk_attribute_lang_lang1_idx` (`lang_id`);

--
-- Indizes für die Tabelle `attribute_value`
--
ALTER TABLE `attribute_value`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_lang_attribute_lang_attribute_idx` (`attribute_lang_id`),
  ADD KEY `fk_product_lang_attribute_lang_product_idx` (`product_lang_id`),
  ADD KEY `fk_attribute_value_attribute_group1_idx` (`attribute_group_id`);

--
-- Indizes für die Tabelle `attribute_value_log`
--
ALTER TABLE `attribute_value_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attribute_value_attribute_value_log1_idx` (`attribute_value_id`),
  ADD KEY `fk_user_attribute_value1_idx` (`user_id`);

--
-- Indizes für die Tabelle `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category_category1_idx` (`category_id`),
  ADD KEY `fk_category_template1_idx` (`template_id`),
  ADD KEY `fk_category_product1_idx` (`product_id`);

--
-- Indizes für die Tabelle `category_lang`
--
ALTER TABLE `category_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_lang_category` (`category_id`),
  ADD KEY `idx_category_lang_lang1` (`lang_id`);

--
-- Indizes für die Tabelle `lang`
--
ALTER TABLE `lang`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `linked_product`
--
ALTER TABLE `linked_product`
  ADD PRIMARY KEY (`product_id`,`linked_product_id`),
  ADD KEY `fk_product_product_product4_idx` (`linked_product_id`),
  ADD KEY `fk_product_product_product3_idx` (`product_id`);

--
-- Indizes für die Tabelle `multiple_usage`
--
ALTER TABLE `multiple_usage`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `fk_product_category1_category1_idx` (`category_id`),
  ADD KEY `fk_product_category1_product1_idx` (`product_id`);

--
-- Indizes für die Tabelle `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_idx` (`product_id`);

--
-- Indizes für die Tabelle `product_attribute_group`
--
ALTER TABLE `product_attribute_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attr_grp_product_product1_idx` (`product_id`),
  ADD KEY `fk_attr_grp_product_attribute_grp1_idx` (`attribute_group_id`);

--
-- Indizes für die Tabelle `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_category_product1_idx` (`product_id`),
  ADD KEY `fk_product_category_category1_idx` (`category_id`);

--
-- Indizes für die Tabelle `product_group`
--
ALTER TABLE `product_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_group_product_group1_idx` (`product_group_id`);

--
-- Indizes für die Tabelle `product_group_lang`
--
ALTER TABLE `product_group_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_group_lang_product_group1_idx` (`product_group_id`),
  ADD KEY `fk_product_group_lang_lang1_idx` (`lang_id`);

--
-- Indizes für die Tabelle `product_group_product`
--
ALTER TABLE `product_group_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_group_product_product1_idx` (`product_id`),
  ADD KEY `fk_product_group_product_product_group1_idx` (`product_group_id`);

--
-- Indizes für die Tabelle `product_lang`
--
ALTER TABLE `product_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_lang_product1_idx` (`product_id`),
  ADD KEY `fk_product_lang_lang1_idx` (`lang_id`);

--
-- Indizes für die Tabelle `template`
--
ALTER TABLE `template`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `template_attribute_group`
--
ALTER TABLE `template_attribute_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attribute_grp_template_template1_idx` (`template_id`),
  ADD KEY `idx_attribute_grp_template_attribute_grp1_idx` (`attribute_group_id`);

--
-- Indizes für die Tabelle `upload`
--
ALTER TABLE `upload`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `attribute`
--
ALTER TABLE `attribute`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;
--
-- AUTO_INCREMENT für Tabelle `attribute_group`
--
ALTER TABLE `attribute_group`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
--
-- AUTO_INCREMENT für Tabelle `attribute_group_attribute`
--
ALTER TABLE `attribute_group_attribute`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;
--
-- AUTO_INCREMENT für Tabelle `attribute_group_lang`
--
ALTER TABLE `attribute_group_lang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=342;
--
-- AUTO_INCREMENT für Tabelle `attribute_lang`
--
ALTER TABLE `attribute_lang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=571;
--
-- AUTO_INCREMENT für Tabelle `attribute_value`
--
ALTER TABLE `attribute_value`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `attribute_value_log`
--
ALTER TABLE `attribute_value_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT für Tabelle `category_lang`
--
ALTER TABLE `category_lang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=270;
--
-- AUTO_INCREMENT für Tabelle `lang`
--
ALTER TABLE `lang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `product`
--
ALTER TABLE `product`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;
--
-- AUTO_INCREMENT für Tabelle `product_attribute_group`
--
ALTER TABLE `product_attribute_group`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `product_category`
--
ALTER TABLE `product_category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;
--
-- AUTO_INCREMENT für Tabelle `product_group`
--
ALTER TABLE `product_group`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
--
-- AUTO_INCREMENT für Tabelle `product_group_lang`
--
ALTER TABLE `product_group_lang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;
--
-- AUTO_INCREMENT für Tabelle `product_group_product`
--
ALTER TABLE `product_group_product`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `product_lang`
--
ALTER TABLE `product_lang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=589;
--
-- AUTO_INCREMENT für Tabelle `template`
--
ALTER TABLE `template`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT für Tabelle `template_attribute_group`
--
ALTER TABLE `template_attribute_group`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT für Tabelle `upload`
--
ALTER TABLE `upload`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT für Tabelle `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
