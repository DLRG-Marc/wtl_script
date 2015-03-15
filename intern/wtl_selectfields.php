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
 * @date - time  03.05.2014 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_sets.php');
    //$setID = mysql_real_escape_string($_GET['setID']);
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

    $selectdata = $_POST['data'];
    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }
    foreach( $_GET as $index => $val )
    {
        $_GET[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_GET[$index]);
    }
    $setID = $MYSQL['setID'];

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','admin',$setID);

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
        if( isset($_POST['assumeField']) && ($_GET['action'] != 'delete') )
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
        if( $_GET['action'] == 'delete' )
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
                    if( $_GET['action'] == 'edit' )
                    {
                        $SQL_Befehl_Write = "UPDATE wtl_fields SET data = '".$MYSQL['data']."', dataLabel = '".$MYSQL['dataLabel']."',
                            lastEditor = '".$username."' WHERE id = '".$MYSQL['id']."'";
                    }
                    // Feld löschen 
                    if( $_GET['action'] == 'delete' )
                    {
                        $SQL_Befehl_Write = "DELETE FROM wtl_fields WHERE id = '".$MYSQL['id']."'";
                    }
                    // Feld anfügen
                    if( $_GET['action'] == 'add' )
                    {
                        $SQL_Befehl_Write = "INSERT INTO wtl_fields SET isSet = '0', setNo = '".$setNo."', data = '".$MYSQL['data']."',
                            dataLabel = '".$MYSQL['dataLabel']."', lastEditor = '".$username."'";
                    }
                }
                $result = mysql_query($SQL_Befehl_Write,$dbId);
                if( (mysql_affected_rows($dbId) >= 1) )
                {
                    $headText .= '<p><b>Der Datensatz wurde erfolgreich geändert.</b></p>';
                }
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $headText .= errorNote();
            }
            $_GET['action'] = '';
        }

        // wenn Felder anzeigen bzw. ändern oder hinzufügen
        if( $setID )
        {
            // Seitenüberschrift
            echo "<h1>Auswahlfelder des Set '".$setName."'</h1>
              <p><a class='summary_img' title='zurück zur Setübersicht' href='".$script_url."'>
                <img width='16' height='16' alt='summary' src='".$img_path."summary.png'></a></p>
            ";
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
                      <td><button class='submit_img' type='submit' name='assumeCaption'><img src='".$img_path."accept.png' alt='accept'/> speichern</button></td>
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
            if( ($quantity_data_details != 0) && ($result_data_details !== FALSE) || ($_GET['action'] == 'add') )
            {
                $headText .= "<p><b>Für dieses Set gibt es ".$quantity_data_details." Auswahlfelder</b></p>";
                $rows = array();
                $rows[0] = array('Auswahldaten','Auswahltext','','','');
                $i = 1;
                while( $data = mysql_fetch_object($result_data_details) )
                {
                    if( !isset($_POST['assumeField']) )
                    {
                        $_POST['data'] = $data->data;
                        $_POST['dataLabel'] = $data->dataLabel;
                    }
                    // wenn Feld ändern
                    if( ($_GET['action'] == 'edit') && ($data->id == $_GET['id']) )
                    {
                        $rows[$i] = array
                        (
                            "<input class='".$fieldClass['data']."' type='text' name='data' size='15' title='".$errorTitle['data']."' value='".$_POST['data']."'/>",
                            "<input class='".$fieldClass['dataLabel']."' type='text' name='dataLabel' size='35' title='".$errorTitle['dataLabel']."' value='".$_POST['dataLabel']."'/>",
                            "<button class='submit_img' type='submit' name='assumeField'><img src='".$img_path."accept.png' alt='accept'/> speichern</button>",
                            "<button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."&amp;setID=".$setID."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button>",
                            ""
                        );
                    }
                    // wenn Felder anzeigen
                    else
                    {
                        $rows[$i] = array($data->data,htmlspecialchars_decode($data->dataLabel));
                        // wenn Feld löschen
                        if( ($_GET['action'] == 'delete') && ($data->id == $_GET['id']) )
                        {
                            array_push
                            (
                                $rows[$i],
                                "Diese Auswahl wird endgültig gelöscht !",
                                "<button class='submit_img' type='submit' name='assumeField'><img src='".$img_path."accept.png' alt='accept'/> löschen</button>",
                                "<button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."&amp;setID=".$setID."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button>"
                            );
                        }
                        else
                        {
                            array_push
                            (
                                $rows[$i],
                                "<a class='edit_img' title='Feld-Name ändern' href='".$script_url."&amp;action=edit&amp;setID=".$setID."&amp;id=".$data->id."'><img width='16' height='16' alt='edit', src='".$img_path."edit.png'></a>",
                                "<a class='delete_img' title='Feld löschen' href='".$script_url."&amp;action=delete&amp;setID=".$setID."&amp;id=".$data->id."'><img width='16' height='16' alt='delete', src='".$img_path."delete.png'></a>",
                                ""
                            );
                        }
                    }
                    $i++;
                }
                // wenn Feld hinzufügen
                if( $_GET['action'] == 'add' )
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
                        "<button class='submit_img' type='submit' name='assumeField'><img src='".$img_path."accept.png' alt='accept'/> speichern</button>",
                        "<button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."&amp;setID=".$setID."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button>",
                        ""
                    );
                    $i++;
                }
            }
            else
            {
                $bottomText .= "<p><b>Es sind keine Auswahlfelder für dieses Set vorhanden !</b></p>";
            }
            $headText .= "<p><a class='add_img' title='Feld hinzufügen' href='".$script_url."&amp;action=add&amp;setID=".$setID."'>
              <img width='16' height='16' alt='add' src='".$img_path."add.png'></a></p>";
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