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


//@$dbId int
//@sqlTable string
//@fieldType string
//@$withCheckbox boolean
//@$boxHeader string
// NO RETURN
function makeSets($dbId,$sqlTable,$fieldType,$withCheckbox,$boxHeader)
{
    // Settings
    $img_path = $GLOBALS['SYSTEM_SETTINGS']['GRAPHIC_PATH'];
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $script_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $script_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }
    $fieldClass = array('setName'=>'Field');
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
    // Eingaben der Sets auf Gültigkeit prüfen
    if( isset($_POST['assumeSet']) )
    {
        $input_OK = TRUE;
        if( (strlen($_POST['setName']) < 2) || (preg_match('/[\W]/',$_POST['setName'])) )
        {
            $input_OK = FALSE;
            $fieldClass['setName'] = 'errorField';
            $errorTitle['setName'] = 'Nur die Zeichen 0-9, A-Z, a-z sowie _ sind zulässig!';
        }
    }
    // auf OK setzen bei Set löschen
    if( $_GET['action'] == 'delete' )
    {
        $input_OK = TRUE;
    }
    // wenn Daten abgeschickt wurden
    if( isset($_POST['assumeSet']) )
    {
        // wenn Eingaben ok
        if( $input_OK )
        {
            if( $withCheckbox )
            {
                $sqlField = ", xChecked = '".$MYSQL['xChecked']."'";
            }
            if( $fieldType != '' )
            {
                $sqlField .= ", fieldType = '".$fieldType."'";
            }
            // nächste Id ermitteln
            $result = mysql_query("SHOW TABLE STATUS LIKE '".$sqlTable."'", $dbId);
            $statusArray = mysql_fetch_assoc($result);
            $next_id = $statusArray['Auto_increment'];
            // Set änden
            if( $_GET['action'] == 'edit' )
            {
                $SQL_Befehl_Write = "UPDATE ".$sqlTable." SET setName = '".$MYSQL['setName']."', lastEditor = '".$_SESSION['intern']['realname']."'
                    ".$sqlField." WHERE id = '".$MYSQL['id']."'";
                $strDone = 'geändert';
            }
            // Set kopieren
            if( $_GET['action'] == 'copy' )
            {
                mysql_query("CREATE TABLE wtl_tmp SELECT * FROM ".$sqlTable." WHERE id = '".$MYSQL['id']."'", $dbId);
                mysql_query("UPDATE wtl_tmp SET id = NULL, setNo = '".$next_id."', setName = '".$MYSQL['setName']."', published = ''", $dbId);
                $SQL_Befehl_Write = "INSERT INTO ".$sqlTable." SELECT * FROM wtl_tmp";
                $strDone = 'hinzugefügt';
            }
            // Set löschen 
            if( $_GET['action'] == 'delete' )
            {
                $SQL_Befehl_Write = "DELETE FROM ".$sqlTable." WHERE id = '".$MYSQL['id']."'";
                $strDone = 'gelöscht';
            }
            // Set anfügen
            if( $_GET['action'] == 'add' )
            {
                $SQL_Befehl_Write = "INSERT INTO ".$sqlTable." SET isSet = '1', setNo = '".$next_id."', setName = '".$MYSQL['setName']."',
                    lastEditor = '".$_SESSION['intern']['realname']."'".$sqlField;
                $strDone = 'hinzugefügt';
            }
            $result = mysql_query($SQL_Befehl_Write, $dbId);
            $quantity = mysql_affected_rows($dbId);
            if( ($quantity == 1) && ($result === TRUE) )
            {
                $headText .= '<p><b>Der Datensatz '.$_POST['setName'].' wurde erfolgreich '.$strDone.'.</b></p>';
            }
            mysql_query("DROP TABLE wtl_tmp", $dbId);
        }
        // wenn Eingaben fehlerhaft
        else
        {
            $headText .= errorNote();
        }
        $_GET['action'] = '';
    }
    // wenn kein Set ausgewählt
    if( empty($_GET['setID']) )
    {
        $sqlField = "";
        // Abfrage der Daten aus der DB
        if( $withCheckbox )
        {
            $sqlSelect = ", xChecked";
        }
        if( $fieldType != '' )
        {
            $sqlField = " AND fieldType = '".$fieldType."'";
        }
        $result_read = mysql_query("SELECT id, setName".$sqlSelect." FROM ".$sqlTable." WHERE isSet = '1'".$sqlField." GROUP BY setNo ASC ORDER BY setName ASC", $dbId);
        $quantity_data = mysql_num_rows($result_read);
        // Tabelle für die Set-Anzeige bauen
        echo "<div class='sets'>";
        echo "<form name='sets_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>";
        if( ($quantity_data != 0) && ($result_read !== FALSE) )
        {
            $headText .= "<p><b>Es sind ".$quantity_data." Sets vorhanden</b></p>";
            $rows = array();
            if( $withCheckbox )
            {
                $rows[0] = array('Set-Name',$boxHeader,'','','','');
            }
            else
            {
                $rows[0] = array('Set-Name','','','','');
            }
            $i = 1;
            while( $daten_1 = mysql_fetch_object($result_read) )
            {
                if( !isset($_POST['assumeSet']) )
                {
                    $_POST['setName'] = $daten_1->setName;
                    $_POST['xChecked'] = $daten_1->xChecked;
                }
                // wenn Set ändern
                if( ($_GET['action'] == 'edit') && ($daten_1->id == $_GET['id']) )
                {
                    if( $withCheckbox )
                    {
                        if( $_POST['xChecked'] == '1' )
                        {
                            $checked = "checked='checked'";
                        }
                        $rows[$i] = array
                        (
                            "<input class='".$fieldClass['setName']."' type='text' name='setName' size='25' title='".$errorTitle['setName']."' value='".$_POST['setName']."'/>",
                            "<input type='checkbox' name='xChecked' ".$checked." value='1'/>",
                            "<button class='submit_img' type='submit' name='assumeSet'><img src='".$img_path."accept.png' alt='accept'/> speichern</button>",
                            "<button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button>",
                            "",
                            ""
                        );
                    }
                    else
                    {
                        $rows[$i] = array
                        (
                            "<input class='".$fieldClass['setName']."' type='text' name='setName' size='25' title='".$errorTitle['setName']."' value='".$_POST['setName']."'/>",
                            "<button class='submit_img' type='submit' name='assumeSet'><img src='".$img_path."accept.png' alt='accept'/> speichern</button>",
                            "<button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button>",
                            "",
                            ""
                        );
                    }
                }
                // wenn Sets anzeigen
                else
                {
                    $rows[$i] = array
                    (
                        $daten_1->setName
                    );
                    if( $withCheckbox )
                    {
                        if( $daten_1->xChecked == '1' )
                        {
                            $xChecked = "<div class='ok_img' title='Set ist ".$boxHeader."'><img src='".$img_path."tick.png' alt='ok'/></div>";
                        }
                        array_push
                        (
                            $rows[$i],
                            $xChecked
                        );
                        unset($xChecked);
                    }
                    // wenn Set löschen
                    if( ($_GET['action'] == 'delete') && ($daten_1->id == $_GET['id']) )
                    {
                        array_push
                        (
                            $rows[$i],
                            "Dieses Set wird endgültig gelöscht !",
                            "<button class='submit_img' type='submit' name='assumeSet'><img src='".$img_path."accept.png' alt='accept'/> löschen</button>",
                            "<button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button>",
                            ""
                        );
                    }
                    else
                    {
                        array_push
                        (
                            $rows[$i],
                            "<a class='adjust_img' title='Set-Einstllungen bearbeiten' href='".$script_url."&amp;setID=".$daten_1->id."'><img width='16' height='16' alt='adjust', src='".$img_path."adjust.png'></a>",
                            "<a class='edit_img' title='Set-Name ändern' href='".$script_url."&amp;action=edit&amp;id=".$daten_1->id."'><img width='16' height='16' alt='edit', src='".$img_path."edit.png'></a>",
                            "<a class='copy_img' title='Set kopieren' href='".$script_url."&amp;action=copy&amp;id=".$daten_1->id."'><img width='16' height='16' alt='copy', src='".$img_path."copy.png'></a>",
                            "<a class='delete_img' title='Set löschen' href='".$script_url."&amp;action=delete&amp;id=".$daten_1->id."'><img width='16' height='16' alt='delete', src='".$img_path."delete.png'></a>"
                        );
                    }
                }
                $i++;
            }
            // wenn Set hinzufügen oder kopieren
            if( ($_GET['action'] == 'add') || ($_GET['action'] == 'copy') )
            {
                $_POST['setName'] = '';
                if( $withCheckbox )
                {
                    if( (isset($_POST['assumeSet'])) && ($_POST['xChecked'] == '1') )
                    {
                        $checked = "checked='checked'";
                    }
                    $rows[$i] = array
                    (
                        "<input class='".$fieldClass['setName']."' type='text' name='setName' size='25' title='".$errorTitle['setName']."' value='".$_POST['setName']."'/>",
                        "<input type='checkbox' name='xChecked' ".$checked." value='1'/>",
                        "<button class='submit_img' type='submit' name='assumeSet'><img src='".$img_path."accept.png' alt='accept'/> speichern</button>",
                        "<button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button>",
                        "",
                        ""
                    );
                }
                else
                {
                    $rows[$i] = array
                    (
                        "<input class='".$fieldClass['setName']."' type='text' name='setName' size='25' title='".$errorTitle['setName']."' value='".$_POST['setName']."'/>",
                        "<button class='submit_img' type='submit' name='assumeSet'><img src='".$img_path."accept.png' alt='accept'/> speichern</button>",
                        "<button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button>",
                        "",
                        ""
                    );
                }
            }
        }
        else
        {
            echo"<p><b>Es ist kein Set vorhanden!</b></p>";
        }
        $headText .= "<p><a class='add_img' title='Set hinzufügen' href='".$script_url."&amp;action=add'><img width='16' height='16' alt='add' src='".$img_path."add.png'></a></p>";
        makeTable($rows,$headText,$bottomText);
        echo "</form></div>";
    }
}

//@$dbId int
//@$sqlTable string
//@$headline string
//@$selText string
// NO RETURN
function select_list_formular($dbId,$sqlTable,$headline,$selText)
{
    $img_path = $GLOBALS['SYSTEM_SETTINGS']['GRAPHIC_PATH'];
    // Auswahlfelder erstellen
    $fieldClass = array('listId'=>'Selectfield');
    $SQL_Befehl_Read = "SELECT id, setName FROM ".$sqlTable." ORDER BY setName ASC";
    $result = mysql_query($SQL_Befehl_Read, $dbId);
    if( mysql_num_rows($result) != 0 )
    {
        echo "<p><b>".$headline." :</b></p>";
        echo "
            <form name='select_list_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
            <div class='border'>
            <table>
                <tr>
                    <td>".$selText." :</td>
                    <td><select name='listId' class='".$fieldClass['listId']."' size='3'>";
                    while( $data = mysql_fetch_object($result) )
                    {
                        echo "<option ";if($_POST['listId']==$data->id){echo "selected='selected'";}
                        echo" value='".$data->id."'>".$data->setName."</option>";
                    }
                    echo "
                    </select></td>
                    <td><button class='submit_img' type='submit' name='sendSelected'><img src='".$img_path."accept.png' alt='accept'/> auswählen</button></td>
                </tr>
            </table>
            </div>
            </form>
        ";
        echo "<p></p>";
    }
    else
    {
        echo "<p><b>Es ist kein ".$selText." verfügbar!</b></p>";
    }
}

//@$dbId int
//@$listId int
//@$listFieldName string
//@$returnArray array
//@return array
function namesFromSelFields($dbId,$sqlTablePrefix,$listId,$listFieldName,$returnArray)
{
    $returnValues = array();
    // Feldnamen die zum anzeigen ausgewählt wurden
    $result = mysql_query("SELECT ".$listFieldName." FROM ".$sqlTablePrefix."_lists WHERE id = '".$listId."'",$dbId);
    $fieldArray = mysql_fetch_row($result);
    $returnValues[0] = $fieldArray[0];
    foreach( unserialize($fieldArray[0]) as $viewField )
    {
        if( $viewField )
        {
            $result = mysql_query("SELECT caption FROM ".$sqlTablePrefix."_fields WHERE id = '".$viewField."'",$dbId);
            $captionArray = mysql_fetch_row($result);
            array_push($returnArray,$captionArray[0]);
        }
    }
    $returnValues[1] = $returnArray;
    return $returnValues;
}

//@$dbId int
//@$selFieldArray array
//@$dataIdArray array
//@$returnArray array
//@return array
function dataFromSelFields($dbId,$sqlTablePrefix,$selFieldArray,$dataIdArray,$returnArray)
{
    // Felder die zum anzeigen ausgewählt wurden
    $viewFieldIndex = array();
    foreach( $dataIdArray as $index => $value )
    {
        $viewFieldIndex[$index] = substr(trim($value,'#'),'0',strpos(trim($value,'#'),';'));
    }
    foreach( unserialize($selFieldArray) as $fieldId )
    {
        if( $fieldId != '' )
        {
            $index = array_search($fieldId, $viewFieldIndex);
            if( $index !== FALSE )
            {
                $value = $dataIdArray[$index];
                $result = mysql_query("SELECT fieldType, setNo FROM ".$sqlTablePrefix."_fields WHERE id = '".$fieldId."'",$dbId);
                $fieldArray = mysql_fetch_row($result);
                if( $fieldArray[0] == 'dropdown' )
                {
                    $SQL_Befehl_Read = "SELECT dataLabel FROM ".$sqlTablePrefix."_fields WHERE isSet != '1' AND setNo = '".$fieldArray[1]."'
                        AND data = '".ltrim(strstr(trim($value,'#'),';'),';')."'";
                    $result = mysql_query($SQL_Befehl_Read,$dbId);
                    $dataArray = mysql_fetch_row($result);
                    array_push($returnArray,$dataArray[0]);
                }
                if( $fieldArray[0] == 'input' )
                {
                    array_push($returnArray,ltrim(strstr(trim($value,'#'),';'),';'));
                }
            }
            else
            {
                array_push($returnArray,' ');
            }
        }
    }
    return $returnArray;
}

//@$dbId int
//@sqlTable string
//@$sqlCond string
//@$selected array
//@$nothing boolean
// NO RETURN
function make_dropdown_list($dbId,$sqlTable,$sqlCond,$selected,$nothing)
{
    $result = mysql_query("SELECT id, setName FROM ".$sqlTable." ".$sqlCond." ORDER BY setName ASC", $dbId);
    while( $data = mysql_fetch_object($result) )
    {
        echo "<option ";if(in_array($data->id,$selected)){echo "selected='selected'";}
        echo" value='".$data->id."'>".$data->setName."</option>";
    }
    if( $nothing )
    {
        echo "<option ";if(in_array('',$selected)){echo "selected='selected'";}
        echo" value=''>nichts auswählen</option>";
    }
}

?>