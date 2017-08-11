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
 * @WTL version  1.7.3
 * @date - time  11.08.2017 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_files.php');
    $authority = FALSE;

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','sAdmin','');

    echo "<div id='wtl_install'>
        <div class='waitinglist'>";
    // Überschrift
    echo "<h1>Datentabellen für Wartelisten installieren</h1>";
    if( ($authority === TRUE) || ($GLOBALS['INSTALL']['FIRSTINSTALL'] === TRUE) )
    {
        if( isset($_POST['createTables']) )
        {
            $file = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH'].'/intern/wtl_tables.sql';
            $ok_message = 'Die Tabellen wurden erfolgreich erstellt.';
            $message = 'Die Erstellung der Tabellen ist fehlgeschlagen!';
        }
        if( isset($_POST['createData']) )
        {
            $file = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH'].'/intern/wtl_table_data.sql';
            $ok_message = 'Die Beispieldaten wurden erfolgreich erstellt.';
            $message = 'Die Erstellung der Beispieldaten ist fehlgeschlagen!';
        }
        if( isset($_POST['updateTables']) )
        {
            $file = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH'].'/intern/wtl_updates.sql';
            $ok_message = 'Das Update der Tabellen auf '.VERSION.' war erfolgreich.';
            $message = 'Das Update der Tabellen ist fehlgeschlagen!';
        }
        // Berechtigung der wtl_globals prüfen
        if( substr(decoct(fileperms($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH'].'/'.$GLOBALS['SYSTEM_SETTINGS']['GLOBAL_FILENAME'])),2) != '0666' )
        {
            $message = "<p><b>ACHTUNG:<br/>Die Datei wtl_globals.php braucht Schreibrechte, bitte Rechte auf 0666 ändern!</b></p>
                <p><a href='".$script_url."'>zurück zu den Einstellungen.</a></p>";
        }
        if( isset($_POST['createTables']) || isset($_POST['createData']) )
        {
            if( makeSQLtableFormSQLdata($dbId,$file,FALSE) === TRUE )
            {
                $message = $ok_message;
                if( isset($_POST['createTables']) )
                {
                    changeGlobals("['INSTALL']['TABLESCREATED']","FALSE","TRUE");
                    changeGlobals("['INSTALL']['FIRSTINSTALL']","TRUE","FALSE");
                }
            }
            copy($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu_reg.inc",$GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu_reg_.inc");
            unlink($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu_reg.inc");
            rename($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu_reg_.inc",$GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu_reg.inc");
            chmod($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu_reg.inc",0644);
            copy($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu.inc",$GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu_.inc");
            unlink($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu.inc");
            rename($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu_.inc",$GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu.inc");
            chmod($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH']."menu.inc",0644);
        }
        if( isset($_POST['updateTables']) && ($GLOBALS['INSTALL']['TABLESCREATED'] === TRUE))
        {
            if( makeSQLtableFormSQLdata($dbId,$file,VERSION) === TRUE )
            {
                $message = $ok_message;
            }
        }

        if( $message != '' )
        {
            echo "<p><b>".$message."</b></p>";
        }
        else
        {
            // Formularfelder bauen und ausfüllen
            echo "
            <form name='wtl_install_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
            <div class='border'>
            <table>
                <tr>
                    <td>
                        <input class='button' type='submit' name='createTables' value='Tabellen &#xA; erstellen'/>
                    </td>
                    <td>
                        <input class='button' type='submit' name='createData' value='Beispieldaten &#xA; erstellen'/>
                    </td>
                    <td>
                        <input class='button' type='submit' name='updateTables' value='Tabellen &#xA; updaten'/>
                    </td>
                </tr>
            </table>
            </div>
            </form>
            ";
        }
    }
    else
    {
        echo "<p><b>Du hast keine Berechtigung zum Neuinstallieren dieses Scriptes</b></p>";
    }
    echo "</div></div>";
?>