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
 * @WTL version  1.7.0
 * @date - time  23.07.2017 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    header('content-type: text/html; charset=utf-8');

    // Includes
    if( file_exists('wtl_globals_local.php') )
    {
        require_once('wtl_globals_local.php');
    }
    else
    {
        require_once('wtl_globals.php');
    }
    require_once('f_global.php');
    require_once('f_login.php');

    // Verbindung zur Datenbank herstellen
    $dbId = connectDatebase();

    // Settings
    $listID = mysqli_real_escape_string($dbId,$_GET['listID']);
    $location_prefix ="Location: http://".$_SERVER['SERVER_NAME'].rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')."/index.php";

    if( isset($listID) && ($listID != '') )
    {
        session_name('WTLSSID');
        session_start();
        if( $listID == 0 )
        {
            login($dbId,'wtl_user',0,'');
            if( (isset($_SESSION['intern']['loggedIn'])) && ($_SESSION['intern']['loggedIn'] === TRUE) )
            {
                if( $GLOBALS['INSTALL']['TABLESCREATED'] === FALSE )
                {
                    $location = $location_prefix."?doc=wtl_install";
                }
                else
                {
                    mysqli_query($dbId,"UPDATE wtl_user SET lastAction ='".time()."' WHERE id = '".$_SESSION['intern']['userId']."'");
                    $location = $location_prefix."?doc=wtl_view";
                }
            }
            else
            {
                $location = $location_prefix."?doc=wtl_intern&login=false";
            }
        }
    }
    else
    {
        $location = $location_prefix."?doc=wtl_edit&data=edit";
    }
    header($location);
    exit;
?>