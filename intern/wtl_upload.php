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
 * @WTL version  1.5.0
 * @date - time  01.10.2013 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_sets.php');
    require_once('f_files.php');
    $listID = mysql_real_escape_string($_GET['listID']);
    $_POST['entryId'] = mysql_real_escape_string($_GET['entryID']);
    $authority = FALSE;
    $fileOK = FALSE;
    $upload_OK = FALSE;

    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }
    if( empty($listID) )
    {
        $listID = $MYSQL['listId'];
    }

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','uploadAuth',$listID);

    // Daten der wtl_lists lesen
    $result = mysql_query("SELECT setName FROM wtl_lists WHERE id = '".$listID."'",$dbId);
    $setNameArray = mysql_fetch_row($result);

    echo "<div id='wtl_upload'>
          <div class='waitinglist'>";
    if( (isset($_POST['sendSelected']) || (!empty($listID))) && ($authority === TRUE) )
    {
        // Tabellen und Dateien
        $fileUploadCSVmembers = $GLOBALS['SYSTEM_SETTINGS']['FILE_PATH']."wtl_members_".$listID.".csv";
        $fixFieldNameArray = array('Nachname'=>'lastname','Vorname'=>'firstname','Geb.Datum'=>'dateOfBirth','email'=>'mail','Anmeldedatum'=>'tstamp');
        $testCharsUploadCSVmembers = '"Nachname","Vorname","Geb.Datum"';
        $hiddenFields = "<input name='listId' type='hidden' value='".$listID."'/><input name='entryId' type='hidden' value='".$_POST['entryId']."'/>";
        // CSV Download vorbereiten
        echo "<h1>Import / Export für ".$setNameArray[0]."</h1>";
        echo "<form name='wtl_download_export' method='post' action='".str_replace('index','wtl_download',$_SERVER['SCRIPT_NAME'])."'>
            <div class='border'>
                <table>
                    <tr>
                        <td class='firstline' colspan='2'>Daten der Wartenden die sich wie folgt zurückgemldet haben,<br/>werden exportiert:</td>
                    </tr>
                    <tr>
                        <td colspan='2'><select name='confirmed' class='Selectfield' size='3'>
                            <option value=''>egal</option>
                            <option value='2' selected='selected'>mit JA geantwortet</option>
                            <option value='1'>mit NEIN geantwortet</option>
                            <option value='0'>NICHT geantwortet</option>
                        </select></td>
                    </tr>
                    <tr>
                        <td colspan='2'><input class='button_long' type='submit' name='download_export_csv' value='csv Datei herunterladen'/>".$hiddenFields."</td>
                    </tr>
                </table>
            </div>
            </form>
        ";

        // CSV Upload vorbereiten
        $textUploadCSVmembers = "Um Wartende aus einem externem Programm zu übernehmen,<br/>
            müssen diese hier per <b>csv-Datei</b> hochgeladen werden.";
        $fileOK = uploadFile('<p></p>',$textUploadCSVmembers,$testCharsUploadCSVmembers,'500000',$fileUploadCSVmembers,$hiddenField);
        if( $fileOK === TRUE )
        {
            convertFileToUTF8($fileUploadCSVmembers);
            $upload_OK = insertCSVdataIntoTable($dbId,'wtl','wtl_members',$fixFieldNameArray,$listID,$fileUploadCSVmembers,"registerId = '".buildPassword(6)."'");
            if( $upload_OK === TRUE )
            {
                echo "<p><b>Dateiupload erfolgreich!</b></p>";
            }
            else
            {
                echo "<p><b>Dateiupload fehlgeschlagen!</b></p>";
            }
        }
    }
    else
    {
        if( isset($_POST['sendSelected']) && ($authority !== TRUE) )
        {
            echo "<h1>Ansicht der Warteliste ".$setNameArray[0]."</h1>
                <p><b>Du hast keine Berechtigung zum ansehen dieser Warteliste!</b></p>";
        }
        else
        {
            echo "<h1>Übersicht der Wartelisten</h1>";
            // Wartelistenauswahlformular
            select_list_formular($dbId,'wtl_lists','Eine Warteliste auswählen','Warteliste');
        }
    }
    echo "</div></div>";
?>