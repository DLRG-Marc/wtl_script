<?php

/**
 * Automatic Waitinglist WTL
 * Copyright (C) 2012-2020 Marc Busse
 *
 * This script is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details
 * at <http://www.gnu.org/licenses/>. 
 *
 * @WTL version  1.6.0
 * @date - time  15.03.2015 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


// Version
define('VERSION', '1.7.0');

// Daten der Mysql-Datenbank
$GLOBALS['DB_SETTINGS']['HOST'] = 'mysql.dlrg.de';  // muss normalerweise nicht geändert werden
$GLOBALS['DB_SETTINGS']['PORT'] = '3306';           // muss normalerweise nicht geändert werden
$GLOBALS['DB_SETTINGS']['USER'] = '';       // normalerweise Gliederungsnummer
$GLOBALS['DB_SETTINGS']['DATABASE'] = '';   // normalerweise Gliederungsnummer
$GLOBALS['DB_SETTINGS']['PASSWORD'] = '';   // Passwort der Datenbank

// globale Scriptdaten
$GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH'] = dirname(__FILE__);    // nicht ändern!!
$GLOBALS['SYSTEM_SETTINGS']['GRAPHIC_PATH'] = 'graphic/';          // muss normalerweise nicht geändert werden
$GLOBALS['SYSTEM_SETTINGS']['FILE_PATH'] = 'files/';               // muss normalerweise nicht geändert werden
$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'] = 'content/';          // muss normalerweise nicht geändert werden
$GLOBALS['SYSTEM_SETTINGS']['TEMP_PATH'] = 'temp/';                // muss normalerweise nicht geändert werden
$GLOBALS['SYSTEM_SETTINGS']['GLOBAL_FILENAME'] = 'wtl_globals.php';// muss normalerweise nicht geändert werden
$GLOBALS['SYSTEM_SETTINGS']['WTL_REGISTER_URL'] = 'wtl_script/index.php?doc=wtl_reg_';     // muss normalerweise nicht geändert werden
$GLOBALS['SYSTEM_SETTINGS']['WTL_CONFIRMED_URL'] = 'wtl_script/index.php?doc=wtl_conf';    // muss normalerweise nicht geändert werden
$GLOBALS['SYSTEM_SETTINGS']['AUTOLOGOUTTIME'] = '15';              // Zeit bis zum Autologout in Minuten

// Daten der Gliederung
$GLOBALS['HOME']['NAME'] = 'DLRG Musterhausen e.V.';               // auf eigenen DLRG Namen ändern
$GLOBALS['HOME']['MAIL'] = 'webmaster@musterhausen.dlrg.de';       // auf mailadresse des eigenen webmasters ändern

// Daten zum Installieren
$GLOBALS['INSTALL']['TABLESUPDATED'] = FALSE;                      // muss normalerweise nicht geändert werden
$GLOBALS['INSTALL']['FIRSTINSTALL'] = TRUE;                        // auf TRUE ändern für erneuten Erstzugang
$GLOBALS['INSTALL']['TABLESCREATED'] = FALSE;                      // muss normalerweise nicht geändert werden
$GLOBALS['INSTALL']['USERNAME'] = 'wtl-script';                    // muss normalerweise nicht geändert werden
$GLOBALS['INSTALL']['USERPW'] = 'cf3b413faf543dfc65fc9c1b82987a6e';// muss normalerweise nicht geändert werden (wtl-script)

?>