-- phpMyAdmin SQL Dump
-- version 3.4.4
-- http://www.phpmyadmin.net
--

-- Erstellungszeit: 13. Feb 2013 um 19:00
-- Server Version: 5.1.67
-- PHP-Version: 5.3.10-1ubuntu3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `wtl_fields`
--

DROP TABLE IF EXISTS `wtl_fields`;
CREATE TABLE IF NOT EXISTS `wtl_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isSet` char(1) NOT NULL DEFAULT '',
  `setNo` int(10) unsigned NOT NULL DEFAULT '0',
  `setName` varchar(128) NOT NULL DEFAULT '',
  `xChecked` char(1) NOT NULL DEFAULT '',
  `fieldType` varchar(64) NOT NULL DEFAULT '',
  `caption` varchar(128) NOT NULL DEFAULT '',
  `data` varchar(128) NOT NULL DEFAULT '',
  `dataLabel` text NOT NULL,
  `charReg` text NOT NULL,
  `regEx` varchar(128) NOT NULL DEFAULT '',
  `charLength` text NOT NULL,
  `fieldSize` text NOT NULL,
  `orientation` varchar(3) NOT NULL DEFAULT '',
  `notRequ` char(1) NOT NULL DEFAULT '',
  `tstampEdit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastEditor` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `wtl_lists`
--

DROP TABLE IF EXISTS `wtl_lists`;
CREATE TABLE IF NOT EXISTS `wtl_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isSet` char(1) NOT NULL DEFAULT '',
  `setNo` int(10) unsigned NOT NULL DEFAULT '0',
  `setName` varchar(128) NOT NULL DEFAULT '',
  `published` char(1) NOT NULL DEFAULT '',
  `dlrgName` varchar(128) NOT NULL DEFAULT '',
  `mailadress` varchar(255) NOT NULL DEFAULT '',
  `headerText` mediumtext NOT NULL,
  `footerText` mediumtext NOT NULL,
  `inputfields` text NOT NULL,
  `selectfields` text NOT NULL,
  `registerMail` mediumtext NOT NULL,
  `ageLimit` text NOT NULL,
  `headerTextDataEdit` mediumtext NOT NULL,
  `girder` char(1) NOT NULL DEFAULT '',
  `entryMail` mediumtext NOT NULL,
  `entryLimit` int(10) unsigned NOT NULL DEFAULT '20',
  `connectFields` text NOT NULL,
  `viewRegister` text NOT NULL,
  `viewEntry` text NOT NULL,
  `viewStatistic` text NOT NULL,
  `viewStatDetails` text NOT NULL,
  `viewDownloads` text NOT NULL,
  `tstampEdit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastEditor` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setName` (`setName`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `wtl_members`
--

DROP TABLE IF EXISTS `wtl_members`;
CREATE TABLE IF NOT EXISTS `wtl_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `listId` int(10) unsigned NOT NULL DEFAULT '0',
  `registerId` varchar(10) NOT NULL DEFAULT '',
  `firstname` varchar(128) NOT NULL DEFAULT '',
  `lastname` varchar(128) NOT NULL DEFAULT '',
  `dateOfBirth` int(11) NOT NULL DEFAULT '0',
  `mail` varchar(255) NOT NULL DEFAULT '',
  `inputs` text NOT NULL,
  `selected` text NOT NULL,
  `options` text NOT NULL,
  `checked` text NOT NULL,
  `entryId` varchar(10) NOT NULL DEFAULT '',
  `entryTstamp` int(11) NOT NULL DEFAULT '0',
  `startTstamp` int(11) NOT NULL DEFAULT '0',
  `answerTstamp` int(11) NOT NULL DEFAULT '0',
  `entryConfMail` char(1) NOT NULL DEFAULT '',
  `entryUsername` varchar(255) NOT NULL DEFAULT '',
  `entryUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `tstampEdit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastEditor` varchar(128) NOT NULL DEFAULT '',
  `deleted` char(1) NOT NULL DEFAULT '',
  `confirm` char(1) NOT NULL DEFAULT '0',
  `confirmTstamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `registerId` (`registerId`),
  UNIQUE KEY `person` (`listId`,`firstname`,`lastname`,`dateOfBirth`,`deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `wtl_user`
--

DROP TABLE IF EXISTS `wtl_user`;
CREATE TABLE IF NOT EXISTS `wtl_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isSet` char(1) NOT NULL DEFAULT '',
  `setNo` int(10) unsigned NOT NULL DEFAULT '0',
  `setName` varchar(128) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `userpw` varchar(64) NOT NULL DEFAULT '',
  `realname` varchar(255) NOT NULL DEFAULT '',
  `mail` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(64) NOT NULL DEFAULT '',
  `sAdmin` char(1) NOT NULL DEFAULT '',
  `admin` char(1) NOT NULL DEFAULT '',
  `disable` char(1) NOT NULL DEFAULT '',
  `viewAuth` text NOT NULL,
  `registerAuth` text NOT NULL,
  `entryAuth` text NOT NULL,
  `deleteAuth` text NOT NULL,
  `uploadAuth` text NOT NULL,
  `updated` char(1) NOT NULL DEFAULT '',
  `lastAction` int(11) NOT NULL DEFAULT '0',
  `tstampEdit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastEditor` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`setNo`,`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
