/**/
/* Automatic Waitinglist WTL */
/* Copyright (C) 2012-2020 Marc Busse */
/**/
/* This script is free software: you can redistribute it and/or */
/* modify it under the terms of the GNU General Public License */
/* as published by the Free Software Foundation, either */
/* version 3 of the License, or (at your option) any later version. */
/**/
/* This script is distributed in the hope that it will be useful, */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU */
/* General Public License for more details */
/* at <http://www.gnu.org/licenses/>. */
/**/
/* @WTL version  1.5.0 */
/* @date - time  01.10.2013 - 19:00 */
/* @copyright    Marc Busse 2012-2020 */
/* @author       Marc Busse <http://www.eutin.dlrg.de> */
/* @license      GPL */
/**/


SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Daten für Tabelle `wtl_fields`
--

INSERT INTO `wtl_fields` (`id`, `isSet`, `setNo`, `setName`, `xChecked`, `fieldType`, `caption`, `data`, `dataLabel`, `charReg`, `regEx`, `charLength`, `fieldSize`, `orientation`, `notRequ`, `tstampEdit`, `lastEditor`) VALUES
(1, '1', 1, '1_Strasse', '', 'input', 'Strasse', '', '', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', '.', 'a:2:{i:0;s:1:"5";i:1;s:2:"37";}', 'a:2:{i:0;s:2:"37";i:1;s:1:"1";}', '', '', '2013-02-08 12:57:09', 'Marc Busse'),
(2, '1', 2, '2_Postleitzahl', '', 'input', 'Postleitzahl', '', '', 'a:1:{i:0;s:1:"2";}', '', 'a:2:{i:0;s:1:"5";i:1;s:1:"5";}', 'a:2:{i:0;s:1:"5";i:1;s:1:"1";}', '', '', '2013-02-08 12:57:55', 'Marc Busse'),
(3, '1', 3, '3_Ort', '', 'input', 'Ort', '', '', 'a:1:{i:0;s:1:"1";}', '', 'a:2:{i:0;s:1:"3";i:1;s:2:"37";}', 'a:2:{i:0;s:2:"37";i:1;s:1:"1";}', '', '', '2013-02-08 12:58:29', 'Marc Busse'),
(4, '1', 4, 'Schwimmabzeichen', '', 'dropdown', 'bestehendes\r\nSchwimmabzeichen', '', '', '', '', '', '', '', '', '2013-02-11 14:00:09', 'Marc Busse'),
(5, '1', 5, '4_Telefon', '', 'input', 'Telefon Bsp.\r\n0451-12345', '', '', 'a:1:{i:0;s:1:"2";}', '-', 'a:2:{i:0;s:1:"6";i:1;s:2:"20";}', 'a:2:{i:0;s:2:"37";i:1;s:1:"1";}', '', '', '2013-02-08 13:06:03', 'Marc Busse'),
(6, '1', 6, '5_Bemerkung', '', 'input', 'Bemerkungen', '', '', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', '.,;-', 'a:2:{i:0;s:1:"3";i:1;s:3:"255";}', 'a:2:{i:0;s:2:"37";i:1;s:1:"5";}', '', '1', '2013-02-08 13:05:52', 'Marc Busse'),
(7, '1', 7, 'Trainingszeit', '', 'dropdown', 'gewünschte Trainingszeit', '', '', '', '', '', '', '', '', '2013-02-08 13:23:52', 'Marc Busse'),
(9, '1', 9, 'Abzeichen', '1', 'dropdown', 'Abzeichen', '', '', '', '', '', '', '', '', '2013-02-08 13:37:50', 'Marc Busse'),
(10, '0', 4, '', '', '', '', '100', 'keins', '', '', '', '', '', '', '2013-02-08 13:23:13', 'Marc Busse'),
(11, '0', 4, '', '', '', '', '111', 'Frühschwimmer', '', '', '', '', '', '', '2013-02-08 13:22:13', 'Marc Busse'),
(12, '0', 4, '', '', '', '', '121', 'Bronze', '', '', '', '', '', '', '2013-02-08 13:22:24', 'Marc Busse'),
(13, '0', 4, '', '', '', '', '122', 'Silber', '', '', '', '', '', '', '2013-02-08 13:22:43', 'Marc Busse'),
(14, '0', 4, '', '', '', '', '123', 'Gold', '', '', '', '', '', '', '2013-02-08 13:22:53', 'Marc Busse'),
(15, '0', 7, '', '', '', '', '16:00', 'Mo. 16:00', '', '', '', '', '', '', '2013-02-08 13:24:21', 'Marc Busse'),
(16, '0', 7, '', '', '', '', '13:00', 'Sa. 13:00', '', '', '', '', '', '', '2013-02-10 23:41:46', 'Marc Busse'),
(17, '0', 7, '', '', '', '', '14:00', 'Sa. 14:00', '', '', '', '', '', '', '2013-02-08 13:24:57', 'Marc Busse'),
(18, '0', 7, '', '', '', '', 'EGAL', 'egal', '', '', '', '', '', '', '2013-02-08 13:25:14', 'Marc Busse'),
(35, '0', 11, '', '', '', '', '&gt;5', '&gt;5 Jahre', '', '', '', '', '', '', '2013-02-13 20:39:43', 'Marc Busse'),
(20, '0', 9, '', '', '', '', '100', 'keins', '', '', '', '', '', '', '2013-02-08 13:38:02', 'Marc Busse'),
(21, '0', 9, '', '', '', '', '111', 'Frühschwimmer', '', '', '', '', '', '', '2013-02-08 13:38:12', 'Marc Busse'),
(22, '0', 9, '', '', '', '', '121', 'Bronze', '', '', '', '', '', '', '2013-02-08 13:38:22', 'Marc Busse'),
(23, '0', 9, '', '', '', '', '122', 'Silber', '', '', '', '', '', '', '2013-02-08 13:38:32', 'Marc Busse'),
(24, '0', 9, '', '', '', '', '123', 'Gold', '', '', '', '', '', '', '2013-02-08 13:38:40', 'Marc Busse'),
(25, '0', 9, '', '', '', '', '100 ODER 111', 'Anfänger', '', '', '', '', '', '', '2013-02-08 13:39:00', 'Marc Busse'),
(34, '0', 11, '', '', '', '', '&gt;=9', '&gt;=9 Jahre', '', '', '', '', '', '', '2013-02-13 20:39:50', 'Marc Busse'),
(33, '1', 11, 'Alter', '1', 'dropdown', 'Alter', '', '', '', '', '', '', '', '', '2013-02-13 20:45:02', 'Marc Busse'),
(28, '1', 10, 'Zeit', '1', 'dropdown', 'Trainingszeit', '', '', '', '', '', '', '', '', '2013-02-08 15:11:59', 'Marc Busse'),
(29, '0', 10, '', '', '', '', '16:00', 'Mo. 16:00', '', '', '', '', '', '', '2013-02-08 15:12:15', 'Marc Busse'),
(30, '0', 10, '', '', '', '', '13:00', 'Sa. 13:00', '', '', '', '', '', '', '2013-02-10 23:42:17', 'Marc Busse'),
(31, '0', 10, '', '', '', '', '14:00', 'Sa. 14:00', '', '', '', '', '', '', '2013-02-08 15:12:39', 'Marc Busse'),
(32, '0', 10, '', '', '', '', 'EGAL', 'egal', '', '', '', '', '', '', '2013-02-13 01:51:41', 'Marc Busse'),
(36, '0', 11, '', '', '', '', 'EGAL', 'egal', '', '', '', '', '', '', '2013-02-13 20:42:13', 'Marc Busse'),
(37, '0', 11, '', '', '', '', '&lt;4', '&lt;4 Jahre', '', '', '', '', '', '', '2013-02-13 20:44:31', 'Marc Busse');

--
-- Daten für Tabelle `wtl_lists`
--

INSERT INTO `wtl_lists` (`id`, `isSet`, `setNo`, `setName`, `published`, `dlrgName`, `mailadress`, `headerText`, `footerText`, `inputfields`, `selectfields`, `registerMail`, `ageLimit`, `headerTextDataEdit`, `girder`, `autoclose`, `closeDate`, `registerLimit`, `closeText`, `entryMail`, `entryLimit`, `connectFields`, `viewRegister`, `viewEntry`, `viewStatistic`, `viewStatDetails`, `viewDownloads`, `tstampEdit`, `lastEditor`) VALUES
(1, '1', 1, 'Schwimmausbildung', '1', 'DLRG Musterstadt', 'tl@musterstadt.dlrg.de', 'Sobald ein Platz frei wird, teilen wir dir den Termin per Mail mit.\r\n\r\nMontags 16:00 bis 17:00 (Anfänger bis DJSA Gold)\r\nSamstags 13:00 bis 14:00 (DJSA Bronze bis DJSA Gold)\r\nSamstags 14:00 bis 15:00 (Anfänger bis DJSA Bronze)', '', 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"5";i:4;s:1:"6";}', 'a:2:{i:0;s:1:"4";i:1;s:1:"7";}', 'Hallo #VORNAME# #NACHNAME#,\r\n\r\ndu hast dich soeben erfolgreich in die Warteliste #LISTENNAME# der #DLRGNAME# eingetragen.\r\n\r\nDas angegebene Abzeichen ist #Schwimmabzeichen#.\r\n\r\nMit der folgenden Anmeldenummer kannst Du unter &lt;http://www.musterstadt.dlrg.de/php/wtl_script/index.php?doc=wtl_reg_1&amp;data=edit&gt; jederzeit Deine Daten einsehen bzw. ändern.\r\nAnmeldenummer: #MELDENR#\r\nBitte halte Deine Daten, insbesondere die Mailadresse aktuell, ansonsten kann eine erfolgreiche Aufnahme nicht garantiert werden.\r\n\r\nMit sportlichen Grüßen\r\n#DLRGNAME#\r\n\r\n\r\nDiese E-Mail wurde automatisch aufgrund eines Eintrages am #MELDEDATUM# in unsere Warteliste versandt.', 'a:2:{i:0;s:0:"";i:1;s:0:"";}', '', '', '1', 0, '100', '', 'Hallo #VORNAME# #NACHNAME# ,\r\n\r\nwir freuen uns, Dich zum Probeschwimmen am #STARTDATUM# einladen zu können.\r\nOb eine endgültige Aufnahme erfolgen kann, entscheiden wir nach dem Probeschwimmen.\r\n\r\nDu solltest Dich am #STARTDATUM# 1/4 Stunde vor der Schwimmzeit im Vorraum der Schwimmhalle einfinden.\r\n\r\nUnser Schwimmunterricht findet einmal die Woche jeweils 45-60 min je Einheit statt. Wir haben derzeit kein Kurssystem. D.h. du schwimmst bei uns so lange, bis du es sicher kannst und Lust hast.\r\n\r\nDie Mitgliedschaft ist immer für ein Jahr und verlängert sich stillschweigend, wenn Sie nicht bis zum 30. November eines Jahres gekündigt wird. Die derzeit gültigen Beiträge können unter &lt;http://www.musterstadt.dlrg.de/ueber-uns/mitglied-werden.html&gt; oder dem Beitrittsformular entnommen werden.\r\n\r\nBitte bestätige mit dem folgenden Link den Erhalt dieser Mail (auch bei Nichtinteresse) bis zum #ANTWORTDATUM#:\r\n#BESTAETIGUNGSLINK#\r\nSollte bis zu diesem Datum keine Rückmeldung erfolgen, so werden wir den Platz anderweitig vergeben.\r\n\r\nSolltest Du noch Fragen haben, so wende Dich bitte an #AUFNEHMER#, #AUFNEHMERMAIL# oder Tel: #AUFNEHMERTEL#.\r\n\r\nMit sportlichen Grüssen\r\n#DLRGNAME#\r\n\r\n\r\nDiese Mail erhälst Du aufgrund eines Eintrages in unsere Warteliste #LISTENNAME# am #MELDEDATUM#', 20, 'a:2:{i:0;a:1:{s:3:"Age";s:2:"33";}i:1;a:2:{i:4;s:1:"9";i:7;s:2:"28";}}', 'a:4:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"4";i:3;s:1:"7";}', 'a:3:{i:0;s:1:"6";i:1;s:1:"4";i:2;s:1:"7";}', 'a:1:{i:0;s:1:"7";}', 'a:2:{i:0;s:1:"4";i:1;s:1:"7";}', 'a:6:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"5";i:4;s:1:"4";i:5;s:1:"7";}', '2013-04-23 12:05:01', 'Marc Busse');

--
-- Daten für Tabelle `wtl_members`
--

INSERT INTO `wtl_members` (`id`, `tstamp`, `listId`, `registerId`, `firstname`, `lastname`, `dateOfBirth`, `mail`, `inputs`, `selected`, `options`, `checked`, `entryId`, `entryTstamp`, `startTstamp`, `answerTstamp`, `entryConfMail`, `entryUsername`, `entryUserId`, `tstampEdit`, `lastEditor`, `deleted`, `confirm`, `confirmTstamp`) VALUES
(1, 1360361546, 1, '0ae8fe', 'Max', 'Muster', 946681200, 'max@muster.de', '#1;Musterstr. 123##2;12345##3;Musterhausen##5;0123-456789##6;Dies ist Testperson 1.#', '#4;100##7;16:00#', '', '', '3ce725', 1360547241, 1363302000, 1362092400, '1', 'Marc Busse', 1, '2013-02-11 02:07:39', '', '', '1', 1360548541),
(2, 1360363421, 1, 'a43c72', 'Marie', 'Muster', 107737200, 'marie@muster.de', '#1;Musterstr. 98##2;98765##3;Musterhausen##5;0123-456789##6;Dies ist die 2. Testperson#', '#4;111##7;13:00#', '', '', '3ce725', 1360547241, 1363302000, 1362092400, '1', 'Marc Busse', 1, '2013-02-11 02:06:35', '', '', '2', 1360548477),
(3, 1359759600, 1, 'ca01f2', 'Markus', 'Mustermann', 1071442800, 'markus@mustermann.de', '#1;Musterstr. 56##2;23456##3;Musterhausen##5;0123-56789##6;Dies ist der 3. Testmensch#', '#4;111##7;13:00#', '', '', '7e05b5', 1360813646, 1363302000, 1362092400, '1', 'Marc Busse', 1, '2013-02-14 18:12:16', '', '', '2', 1360816026),
(4, 1360860038, 1, '7c9157', 'Markus', 'Muster', 1009148400, 'markus@muster.de', '#1;Meierstr. 45g##2;98765##3;DORF##5;0123-45678##6;die 5. Testperson#', '#4;100##7;13:00#', '', '', '', 0, 0, 0, '', '', 0, '2013-02-14 18:09:48', '', '', '0', 0),
(5, 1360859594, 1, '40b811', 'Testi', 'Testmann', 1014332400, 'testi@testmann.de', '#1;Hierstr. 123d##2;45678##3;Dortmund##5;0123-45678##6;Der 4. Testmensch#', '#4;121##7;14:00#', '', '', '', 0, 0, 0, '', '', 0, '2013-02-14 16:42:05', '', '', '0', 0),
(6, 1360450800, 1, '7b0778', 'Markus-Testi', 'Testermann', 942274800, 'm.testi@testermann.de', '#1;dorfstr. 123##2;12345##3;hierort##5;0123-45678##6;Die 6. Testperson#', '#4;123##7;14:00#', '', '', '', 0, 0, 0, '', '', 0, '2013-02-14 18:11:49', '', '', '0', 0);

--
-- Daten für Tabelle `wtl_user`
--

INSERT INTO `wtl_user` (`id`, `isSet`, `setNo`, `setName`, `username`, `userpw`, `realname`, `mail`, `phone`, `sAdmin`, `admin`, `disable`, `viewAuth`, `registerAuth`, `entryAuth`, `deleteAuth`, `uploadAuth`, `updated`, `lastAction`, `tstampEdit`, `lastEditor`) VALUES
(1, '1', 1, 'Superadmin', 'sa.muster', 'adee18782b9b0bdeaa2b3611a93d6262', 'Superadmin Muster', 'admin@deine-gliederung.dlrg.de', '0123-45678', '1', '1', '', 'a:1:{i:0;s:0:"";}', 'a:1:{i:0;s:0:"";}', 'a:1:{i:0;s:0:"";}', 'a:1:{i:0;s:0:"";}', 'a:1:{i:0;s:0:"";}', '', 1360863167, '2013-02-14 17:31:41', ''),
(2, '1', 2, 'ViewUser', 'm.muster', 'bfc24e832597bc1b48407d201997a4ef', 'Max Muster', 'max.muster@deine-gliederung.dlrg.de', '0123-45678', '', '', '', 'a:1:{i:0;s:1:"1";}', 'a:1:{i:0;s:0:"";}', 'a:1:{i:0;s:1:"1";}', 'a:1:{i:0;s:0:"";}', 'a:1:{i:0;s:0:"";}', '', 1360865235, '2013-02-14 18:14:21', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
