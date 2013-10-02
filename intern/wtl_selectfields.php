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
 * @WTL version  1.4.3
 * @date - time  01.10.2013 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_sets.php');
    $setID = mysql_real_escape_string($_GET['setID']);
    $editField = mysql_real_escape_string($_GET['editField']);
    $addField = mysql_real_escape_string($_GET['addField']);
    $delField = mysql_real_escape_string($_GET['delField']);
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $script_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $script_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }
    $authority = FALSE;
    $username = $_SESSION['intern']['realname'];
    $fieldClass = array('caption'=>'Field','selectData'=>'Field','selectDataLabel'=>'Field');

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','admin',$setID);

    $selectdata = $_POST['data'];
    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }

    echo "<div id='wtl_selectfields'>
          <div class='waitinglist'>";
    if( $authority === TRUE )
    {
        // Set-Funktion aufrufen oder passendes Set heraussuchen
        if( !$setID )
        {
            // Seitenüberschrift
            echo "<h1>Einstellungen Auswahlfelder</h1>";
            makeSets($dbId,'wtl_fields','dropdown',TRUE,'Antwortset');
        }
        else
        {
            $result = mysql_query("SELECT setNo, setName, xChecked FROM wtl_fields WHERE id = '".$setID."'",$dbId);
            $setArray = mysql_fetch_row($result);
            $setNo = $setArray[0];
            $setName = $setArray[1];
            $xChecked = $setArray[2];
        }
        // Eingabefelder auf Gültigkeit prüfen
        if( isset($_POST['assumeField']) && !$delField )
        {
            $input_OK = TRUE;
            if( (($xChecked == '1') && (stripos($setName,'alter')!==FALSE)) && ((strlen($_POST['data']) < 1) || ((preg_match('/[^\d<>=]/',$selectdata))) && ($_POST['data'] != 'EGAL')) )
            {
                $input_OK = FALSE;
                $fieldClass['data'] = 'errorField';
                $errorTitle['data'] = 'Nur die Zeichen < > = und Zahlen sowie EGAL sind zulässig!';
            }
            if( (($xChecked != '1') || (stripos($setName,'alter')===FALSE)) && ((strlen($_POST['data']) < 1) || (preg_match('/[#;]/',$selectdata))) )
            {
                $input_OK = FALSE;
                $fieldClass['data'] = 'errorField';
                $errorTitle['data'] = 'Die Zeichen # und ; sind nicht zulässig!';
            }
            if( (strlen($_POST['dataLabel']) < 1) )
            {
                $input_OK = FALSE;
                $fieldClass['dataLabel'] = 'errorField';
                $errorTitle['dataLabel'] = 'Das Feld muß ausgefüllt werden!';
            }
        }
        // Beschriftungsfeld auf Gültigkeit prüfen
        if( isset($_POST['assumeCaption']) )
        {
            $input_OK = TRUE;
            if( (strlen($_POST['caption']) < 2) || (preg_match('/[^\w\-äÄöÖüÜß.,\s]/',$_POST['caption'])) )
            {
                $input_OK = FALSE;
                $fieldClass['caption'] = 'errorField';
                $errorTitle['caption'] = 'Nur die Zeichen 0-9, A-Z, a-z incl. Umlaute sowie - , . und Leerzeichen sind zulässig!';
            }
        }
        // auf OK setzen bei Daten löschen
        if( $delField )
        {
            $input_OK = TRUE;
        }

        // wenn Daten geändert wurden
        if( isset($_POST['assumeField']) || isset($_POST['assumeCaption']) )
        {
            // wenn Eingaben OK
            if( $input_OK )
            {
                // wenn Beschriftung eingegeben wurde
                if( isset($_POST['assumeCaption']) )
                {
                    $SQL_Befehl_Write = "UPDATE wtl_fields SET caption = '".$MYSQL['caption']."' WHERE id = '".$setID."'";
                }
                if( isset($_POST['assumeField']) )
                {
                    // Feld ändern
                    if($editField )
                    {
                        $SQL_Befehl_Write = "UPDATE wtl_fields SET data = '".$MYSQL['data']."', dataLabel = '".$MYSQL['dataLabel']."',
                            lastEditor = '".$username."' WHERE id = '".$editField."'";
                    }
                    // Feld löschen 
                    if( $delField )
                    {
                        $SQL_Befehl_Write = "DELETE FROM wtl_fields WHERE id = '".$delField."'";
                    }
                    // Feld anfügen
                    if( $addField )
                    {
                        $SQL_Befehl_Write = "INSERT INTO wtl_fields SET isSet = '0', setNo = '".$setNo."', data = '".$MYSQL['data']."',
                            dataLabel = '".$MYSQL['dataLabel']."', lastEditor = '".$username."'";
                    }
                }
                $result = mysql_query($SQL_Befehl_Write,$dbId);
                if( (mysql_affected_rows($dbId) >= 1) )
                {
                    $editField = NULL;
                    $addField = NULL;
                    $headText .= '<p><b>Der Datensatz wurde erfolgreich geändert.</b></p>';
                }
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $headText .= errorNote();
            }
        }

        // wenn Felder anzeigen bzw. ändern oder hinzufügen
        if( $setID )
        {
            // Seitenüberschrift
            echo "<h1>Auswahlfelder des Set '".$setName."'</h1>";
            // Beschriftung des Auswahlfeldes
            $result = mysql_query("SELECT caption FROM wtl_fields WHERE id = '".$setID."'",$dbId);
            while( $data = mysql_fetch_object($result) )
            {
                $_POST['caption'] = $data->caption;
            }
            echo "<form name='caption_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                <div class='border'>
                <table>
                    <tr>
                        <th>Beschriftung</th>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <td><textarea class='".$fieldClass['caption']."' name='caption' cols='34' rows='2'
                            title='".$errorTitle['caption']."'>".$_POST['caption']."</textarea></td>
                        <td><input class='button' type='submit' name='assumeCaption' value='übernehmen'/></td>
                    </tr>
                </table>
                </div>
                </form>
            ";
            // Abfrage der Daten aus der DB
            $result_data_details = mysql_query("SELECT * FROM wtl_fields WHERE isSet != '1' AND setNo = '".$setNo."' ORDER BY data",$dbId);
            $quantity_data_details = mysql_num_rows($result_data_details);
            // Tabelle für die Auswahlfeld-Anzeige bauen
            echo "<div class='selectfields'>";
            echo "<form name='selectFields_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>";
            if( ($quantity_data_details != 0) && ($result_data_details !== FALSE) || $addField )
            {
                $headText .= "<p><b>".$quantity_data_details." Auswahlfelder für das Set Nr. ".$setNo."</b></p>";
                $rows = array();
                $rows[0] = array('Auswahldaten','Auswahltext','','');
                $i = 1;
                // wenn Feld hinzufügen
                if( $addField )
                {
                    if( !isset($_POST['assumeField']) )
                    {
                        $_POST['data'] = '';
                        $_POST['dataLabel'] = '';
                    }
                    $rows[$i] = array
                    (
                        "<input class='".$fieldClass['data']."' type='text' name='data' size='15' title='".$errorTitle['data']."' value='".$_POST['data']."'/>",
                        "<input class='".$fieldClass['dataLabel']."' type='text' name='dataLabel' size='35' title='".$errorTitle['dataLabel']."' value='".$_POST['dataLabel']."'/>",
                        "<input class='button' type='submit' name='assumeField' value='übernehmen'/>",
                        "<input class='button' type='reset' name='cancel' value='abbrechen' onclick=\"location.href='".$script_url."&amp;setID=".$setID."'\"/>"
                    );
                    $i++;
                }
                while( $data = mysql_fetch_object($result_data_details) )
                {
                    if( !isset($_POST['assumeField']) )
                    {
                        $_POST['data'] = $data->data;
                        $_POST['dataLabel'] = $data->dataLabel;
                    }
                    // wenn Feld ändern
                    if( $data->id == $editField )
                    {
                        $rows[$i] = array
                        (
                            "<input class='".$fieldClass['data']."' type='text' name='data' size='15' title='".$errorTitle['data']."' value='".$_POST['data']."'/>",
                            "<input class='".$fieldClass['dataLabel']."' type='text' name='dataLabel' size='35' title='".$errorTitle['dataLabel']."' value='".$_POST['dataLabel']."'/>",
                            "<input class='button' type='submit' name='assumeField' value='übernehmen'/>",
                            "<input class='button' type='reset' name='cancel' value='abbrechen' onclick=\"location.href='".$script_url."&amp;setID=".$setID."'\"/>"
                        );
                    }
                    // wenn Felder anzeigen
                    else
                    {
                        $rows[$i] = array($data->data,htmlspecialchars_decode($data->dataLabel));
                        if( $data->id == $delField )
                        {
                            array_push
                            (
                                $rows[$i],
                                "Diese Auswahl wird endgültig gelöscht !",
                                "<input class='button' type='submit' name='assumeField' value='übernehmen'/>"
                            );
                        }
                        else
                        {
                            array_push
                            (
                                $rows[$i],
                                "<input class='button' type='button' name='editField' value='Feld ändern' onclick=\"location.href='".$script_url."&amp;setID=".$setID."&amp;editField=".$data->id."'\"/>",
                                "<input class='button' type='button' name='deleteField' value='Feld löschen' onclick=\"location.href='".$script_url."&amp;setID=".$setID."&amp;delField=".$data->id."'\"/>"
                            );
                        }
                    }
                    $i++;
                }
            }
            else
            {
                $bottomText .= "<p><b>Es sind keine Auswahlfelder für dieses Set vorhanden !</b></p>";
            }
            $headText .= "<p><input class='button' type='button' name='addField' value='Feld hinzufügen'
                onclick=\"location.href='".$script_url."&amp;setID=".$setID."&amp;addField=1'\"/></p>";
            $headText .= "<p><input class='button' type='button' name='viewFieldSets' value='Setübersicht'
                onclick=\"location.href='".$script_url."'\"/></p>";
            makeTable($rows,$headText,$bottomText);
            echo "</form></div>";
        }
    }
    else
    {
        echo "<h1>Einstellungen Auswahlfelder</h1>";
        echo "<p><b>Du hast keine Berechtigung zum ändern der Auswahlfelder für diese Warteliste!</b></p>";
    }
    echo "</div></div>";
?>