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


//@$dbId int
//@sqlTable string
//@fieldType string
//@$withCheckbox boolean
//@$boxHeader string
// NO RETURN
function makeSets($dbId,$sqlTable,$fieldType,$withCheckbox,$boxHeader)
{
    // Settings
    $setID = mysql_real_escape_string($_GET['setID']);
    $delSet = mysql_real_escape_string($_GET['delSet']);
    $addSet = mysql_real_escape_string($_GET['addSet']);
    $editSet = mysql_real_escape_string($_GET['editSet']);
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
    // Eingaben der Sets auf Gültigkeit prüfen
    if( isset($_POST['assumeSet']) && !$delSet )
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
    if( $delSet )
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
            // Set änden
            if( $editSet )
            {
                $SQL_Befehl_Write = "UPDATE ".$sqlTable." SET setName = '".$MYSQL['setName']."', lastEditor = '".$_SESSION['intern']['realname']."'
                    ".$sqlField." WHERE id = '".$editSet."'";
            }
            // Set löschen 
            if( $delSet )
            {
                $result_setNo = mysql_query("SELECT setNo FROM ".$sqlTable." WHERE id = '".$delSet."'", $dbId);
                $result_setNoArray = mysql_fetch_row($result_setNo);
                $SQL_Befehl_Write = "DELETE FROM ".$sqlTable." WHERE setNo = '".$result_setNoArray[0]."'";
            }
            // Set anfügen
            if( $addSet )
            {
                $SQL_Befehl_Write = "INSERT INTO ".$sqlTable." SET isSet = '1', setNo = '".$addSet."', setName = '".$MYSQL['setName']."',
                    lastEditor = '".$_SESSION['intern']['realname']."'".$sqlField;
            }
            $result_admited = mysql_query($SQL_Befehl_Write, $dbId);
            $quantity_admited = mysql_affected_rows($dbId);
            if( ($quantity_admited == 1) && ($result_admited === TRUE) )
            {
                $editSet = NULL;
                $addSet = NULL;
                $headText .= '<p><b>Der Datensatz wurde erfolgreich geändert.</b></p>';
            }
        }
        // wenn Eingaben fehlerhaft
        else
        {
            $headText .= errorNote();
        }
    }
    // wenn kein Set ausgewählt
    if( !$setID )
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
        $result_read_maxSetNo = mysql_query("SELECT setNo FROM ".$sqlTable." WHERE isSet = '1' ORDER BY setNo DESC LIMIT 1", $dbId);
        while( $daten = mysql_fetch_object($result_read_maxSetNo) )
        {
            $nextSetNo = ($daten->setNo)+1;
        }
        if( !$nextSetNo )
        {
            $nextSetNo = 1;
        }
        $result_read = mysql_query("SELECT id, setNo, setName".$sqlSelect." FROM ".$sqlTable." WHERE isSet = '1'".$sqlField." GROUP BY setNo ASC", $dbId);
        $quantity_data = mysql_num_rows($result_read);
        // Tabelle für die Set-Anzeige bauen
        echo "<div class='sets'>";
        echo "<form name='sets_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>";
        if( ($quantity_data != 0) && ($result_read !== FALSE) || $addSet )
        {
            $headText .= "<p><b>Es sind ".$quantity_data." Sets vorhanden</b></p>";
            $rows = array();
            if( $withCheckbox )
            {
                $rows[0] = array('Set-Name',$boxHeader,'','');
            }
            else
            {
                $rows[0] = array('Set-Name','','');
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
                if( $daten_1->id == $editSet )
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
                            "<input class='button' type='submit' name='assumeSet' value='übernehmen'/>",
                            "<input class='button' type='reset' name='cancel' value='abbrechen' onclick=\"location.href='".$script_url."'\"/>"
                        );
                    }
                    else
                    {
                        $rows[$i] = array
                        (
                            "<input class='".$fieldClass['setName']."' type='text' name='setName' size='25' title='".$errorTitle['setName']."' value='".$_POST['setName']."'/>",
                            "<input class='button' type='submit' name='assumeSet' value='übernehmen'/>",
                            "<input class='button' type='reset' name='cancel' value='abbrechen' onclick=\"location.href='".$script_url."'\"/>"
                        );
                    }
                }
                // wenn Sets anzeigen
                else
                {
                    $rows[$i] = array
                    (
                        "<a href='".$script_url."&amp;setID=".$daten_1->id."'>".$daten_1->setName."</a>"
                    );
                    if( $withCheckbox )
                    {
                        if( $daten_1->xChecked == '1' )
                        {
                            $xChecked = "<div class='ok_img'><img src='".$GLOBALS['SYSTEM_SETTINGS']['GRAPHIC_PATH']."tick.png' alt='ok'/></div>";
                        }
                        array_push
                        (
                            $rows[$i],
                            $xChecked
                        );
                        unset($xChecked);
                    }
                    // wenn Set löschen
                    if( $daten_1->id == $delSet )
                    {
                        array_push
                        (
                            $rows[$i],
                            "Dieses Set wird endgültig gelöscht !",
                            "<input class='button' type='submit' name='assumeSet' value='löschen'/>"
                        );
                    }
                    else
                    {
                        array_push
                        (
                            $rows[$i],
                            "<input class='button' type='button' name='editSet' value='Set ändern' onclick=\"location.href='".$script_url.
                                "&amp;editSet=".$daten_1->id."'\"/>",
                            "<input class='button' type='button' name='deleteSet' value='Set löschen' onclick=\"location.href='".$script_url.
                                "&amp;delSet=".$daten_1->id."'\"/>"
                        );
                    }
                }
                $i++;
            }
            // wenn Set hinzufügen
            if( $addSet )
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
                        "<input class='button' type='submit' name='assumeSet' value='übernehmen'/>",
                        "<input class='button' type='reset' name='cancel' value='abbrechen' onclick=\"location.href='".$script_url."'\"/>"
                    );
                }
                else
                {
                    $rows[$i] = array
                    (
                        "<input class='".$fieldClass['setName']."' type='text' name='setName' size='25' title='".$errorTitle['setName']."' value='".$_POST['setName']."'/>",
                        "<input class='button' type='submit' name='assumeSet' value='übernehmen'/>",
                        "<input class='button' type='reset' name='cancel' value='abbrechen' onclick=\"location.href='".$script_url."'\"/>"
                    );
                }
            }
        }
        else
        {
            echo"<p><b>Es ist kein Set vorhanden!</b></p>";
        }
        $bottomText = "<p><input class='button' type='button' name='addSet' value='Set hinzufügen'
            onclick=\"location.href='".$script_url."&amp;addSet=".$nextSetNo."'\"/></p>";
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
                    while( $daten = mysql_fetch_object($result) )
                    {
                        echo "<option ";if($_POST['listId']==$daten->id){echo "selected='selected'";}
                        echo" value='".$daten->id."'>".$daten->setName."</option>";
                    }
                    echo "
                    </select></td>
                    <td><input class='button' type='submit' name='sendSelected' value='auswählen'/></td>
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
    while( $daten = mysql_fetch_object($result) )
    {
        echo "<option ";if(in_array($daten->id,$selected)){echo "selected='selected'";}
        echo" value='".$daten->id."'>".$daten->setName."</option>";
    }
    if( $nothing )
    {
        echo "<option ";if(in_array('',$selected)){echo "selected='selected'";}
        echo" value=''>nichts auswählen</option>";
    }
}

?>