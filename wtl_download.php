<?php

/**
 * Automaticly Waitinglist WTL
 * Copyright (C) 2012-2016 Marc Busse
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
 * @WTL version  1.2
 * @date - time  01.02.2013 - 19:00
 * @copyright    Marc Busse 2012-2016
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    header('content-type: text/html; charset=utf-8');

    // Settings
    if( file_exists('wtl_globals_local.php') )
    {
        require_once('wtl_globals_local.php');
    }
    else
    {
        require_once('wtl_globals.php');
    }
    require_once('f_global.php');
    require_once('f_sets.php');
    require_once('f_files.php');
    $location_prefix ="Location: http://".$_SERVER['SERVER_NAME'].rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')."/index.php";
    $dbId = connectDatebase();

    if( isset($_POST['download_export_csv']) )
    {
        if( !empty($_POST['confirmed']) )
        {
            $confirmed = " AND confirm = '".mysql_real_escape_string($_POST['confirmed'])."'";
        }
        if( !empty($_POST['entryId']) )
        {
            $entry = " AND entryId = '".mysql_real_escape_string($_POST['entryId'])."'";
        }
        $fileCSV_export = $GLOBALS['SYSTEM_SETTINGS']['FILE_PATH']."wtl_export_".$_POST['listId'].".csv";
        downloadTableDataToCSVfile($dbId,'wtl','wtl_members',mysql_real_escape_string($_POST['listId']),$fileCSV_export,"deleted != '1'".$confirmed.$entry." ORDER BY dateOfBirth DESC");
        // Passenden Datentyp erzeugen
        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename=\"".basename($fileCSV_export)."\"");
        // Datei ausgeben
        readfile($fileCSV_export);
    }
    else
    {
        header($location_prefix."?doc=wtl_edit&data=edit");
    }
?>