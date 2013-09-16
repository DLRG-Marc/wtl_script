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
 * @WTL version  1.2.3
 * @date - time  16.09.2013 - 19:00
 * @copyright    Marc Busse 2012-2016
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // HTML Seite bauen
    // Überschrift
    echo "<div id='wtl_register_site'>
          <div class='waitinglist'>
        <h1>";
        if( (($data == 'input') || ($registerId_OK === TRUE)) && ($data != 'view') )
        {
            echo "Warteliste ".$listName." ";
        }
        if( $data == 'edit' )
        {
            echo "Daten ändern";
        }
        if( $data == 'confirm')
        {
            echo "Aufnahme bestätigen";
        }
        if( $data == 'view' )
        {
            echo "Personendaten ansehen";
        }
        echo "</h1>";
    // Text der Seite
    echo $message;
    // Formular, wenn nicht nur message anzeigen
    if( $displayMessage === FALSE )
    {
        // Formularfelder bauen und ausfüllen
        echo "
        <form name='wtl_register_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
        <div class='border'>
        <table>
            <colgroup span='3'></colgroup>
            <tr>
                <td colspan='3'>".$errorMessage."</td>
            </tr>
        ";
        // bei Aufnahme Rückmeldung
        if( $confirmOK === TRUE )
        {
            echo "
                <tr>
                    <td colspan='3'><input type='radio' name='confirm' value='2'/>&nbsp;&nbsp;Ja, ".$_POST['firstname']." bestätigt seine Teilnsahme</td>
                </tr>
                <tr>
                    <td colspan='3'><input type='radio' name='confirm' value='1'/>&nbsp;&nbsp;Nein danke, ".$_POST['firstname']." hat kein Interesse mehr</td>
                </tr>
                <tr>
                    <td></td>
                    <td><input class='button' type='submit' name='sendConfirm' value='OK'/></td>
                    <td><input class='button' type='reset' name='cancel' value='Abbrechen'/></td>
                </tr>
            ";
        }
        // bei Login zum Daten ändern
        if( empty($listID) && ($data != 'confirm') )
        {
            echo "
                <tr>
                    <td>Anmeldenummer :</td>
                    <td colspan='2'><input class='".$fieldClass['registerId']."' type='text' name='registerId' size='37'
                        title='".$errorTitle['registerId']."' value='".$_POST['registerId']."'/></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input class='button' type='submit' name='sendRegisterId' value='OK'/></td>
                    <td><input class='button' type='reset' name='cancel' value='Abbrechen'/></td>
                </tr>
            ";
        }
        // bei Eintragung oder Daten ändern
        if( !empty($listID) && ($data != 'confirm') )
        {
            // bei User Login
            if( ($authority === TRUE) && (($data == 'input') || ($data == 'view')) )
            {
                $readonly = '';
                echo "
                    <tr>
                        <td>Eintragender :</td>
                        <td colspan='2'><input type='text' name='user' size='37' value='".$username."' readonly='readonly'/></td>
                    </tr>
                    <tr>
                        <td>Anmeldedatum :<br/>Bsp: 01.02.2000</td>
                        <td colspan='2'><input class='".$fieldClass['registerDate']."' type='text' name='registerDate' size='10'
                            title='".$errorTitle['registerDate']."' value='".$_POST['registerDate']."'/></td>
                    </tr>
                ";
            }
            // bei richtiger Anmeldenummer
            if( ($registerId_OK === TRUE) && ($authority !== TRUE) )
            {
                echo "
                    <tr>
                        <td>Eingetragen am :</td>
                        <td colspan='2'>".$_POST['registerDate']."</td>
                    </tr>
                ";
            }
            if( ($registerId_OK === TRUE) && ($girder == '1') )
            {
                echo "
                    <tr>
                        <td>Wartezeit :</td>
                        <td colspan='2'>".progressGirder($girderType,220,$waitingNo,$waitingPos,0,'1px solid #7F9DB9',$girderColors)."
                            <div><b>Dies besagt nichts über die tatsächliche Watezeit!</b></div></td>
                    </tr>
                ";
            }
            echo "
                <tr>
                    <td>Vorname :</td>
                    <td colspan='2'><input class='".$fieldClass['firstname']."' type='text' name='firstname' size='37' 
                        title='".$errorTitle['firstname']."' value='".$_POST['firstname']."'".$readonly."/></td>
                </tr>
                <tr>
                    <td>Nachname :</td>
                    <td colspan='2'><input class='".$fieldClass['lastname']."' type='text' name='lastname' size='37'
                        title='".$errorTitle['lastname']."' value='".$_POST['lastname']."'".$readonly."/></td>
                </tr>
                <tr>
                    <td>Geburtsdatum :<br/>Bsp: 01.02.2000</td>
                    <td colspan='2'><input class='".$fieldClass['dateOfBirth']."' type='text' name='dateOfBirth' size='10'
                        title='".$errorTitle['dateOfBirth']."' value='".$_POST['dateOfBirth']."'".$readonly."/></td>
                </tr>
                <tr>
                    <td>e-mail :</td>
                    <td colspan='2'><input class='".$fieldClass['mail']."' type='text' name='mail' size='37'
                        title='".$errorTitle['mail']."' value='".$_POST['mail']."'".$readonly."/></td>
                </tr>
            ";
            foreach( $inputfields as $id )
            {
                if( $id != '' )
                {
                    echo"<tr>";
                        $SQL_Befehl_Read = "SELECT caption, fieldSize FROM wtl_fields WHERE id = '".$id."'";
                        $result = mysql_query($SQL_Befehl_Read,$dbId);
                        while( $daten = mysql_fetch_object($result) )
                        {
                            echo "<td>".nl2br($daten->caption)." :</td>";
                            $fieldSizeArray = unserialize($daten->fieldSize);
                        }
                        echo "<td colspan='2'>";
                        if( $fieldSizeArray[1] > '1' )
                        {
                            echo "<textarea class='".$fieldClass['input_'.$id]."' name='input_".$id."' cols='".$fieldSizeArray[0]."'
                                rows='".$fieldSizeArray[1]."' title='".$errorTitle['input_'.$id]."'".$readonly.">".$_POST['input_'.$id]."
                                </textarea></td>";
                        }
                        else
                        {
                            echo "<input class='".$fieldClass['input_'.$id]."' type='text' name='input_".$id."' size='".$fieldSizeArray[0]."'
                                title='".$errorTitle['input_'.$id]."' value='".$_POST['input_'.$id]."'".$readonly."/></td>";
                        }
                    echo "</tr>";
                }
            }
            foreach( $selectfields as $id )
            {
                if( $id != '' )
                {
                    echo"<tr>";
                        $SQL_Befehl_Read = "SELECT setNo, caption FROM wtl_fields WHERE id = '".$id."'";
                        $result = mysql_query($SQL_Befehl_Read,$dbId);
                        while( $daten = mysql_fetch_object($result) )
                        {
                            $setNo = $daten->setNo;
                            echo "<td>".nl2br($daten->caption)." :</td>";
                        }
                        echo "
                            <td colspan='2'><select name='dropdown_".$id."' class='".$fieldClass['dropdown_'.$id]."' size='3'
                                title='".$errorTitle['dropdown_'.$id]."'".$readonly.">";
                            $SQL_Befehl_Read = "SELECT data, dataLabel FROM wtl_fields WHERE isSet != '1' AND setNo = '".$setNo."'
                                ORDER BY id ASC";
                            $result = mysql_query($SQL_Befehl_Read,$dbId);
                            while( $daten = mysql_fetch_object($result) )
                            {
                                echo "<option ";if($_POST['dropdown_'.$id]==$daten->data){echo "selected='selected'";}
                                echo" value='".$daten->data."'>".$daten->dataLabel."</option>";
                            }
                            echo "
                            </select></td>
                        </tr>";
                }
            }
            if( ($NotView === TRUE) || ($authority === TRUE) )
            {
                echo "
                    <tr>
                        <td><input name='listId' type='hidden' value='".$listID."'/>
                ";
                if( ($data == 'edit') || ($data == 'view') )
                {
                    echo "
                        <input name='memberId' type='hidden' value='".$_POST['memberId']."'/></td>
                        <td><input class='button' type='submit' name='sendEdit' value='Ändern'/></td>
                        <td><input class='button' type='reset' name='cancel' value='Abbrechen' 
                            onclick=\"location.href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'\"/></td>
                    </tr>
                    ";
                    if( $NotView === TRUE )
                    {
                        echo "
                            <tr>
                                <td></td>
                                <td colspan='2'><input class='button' type='submit' name='sendPredelete' value='Daten löschen'/></td>
                        </tr>
                        ";
                    }
                }
                else
                {
                    echo "
                        </td>
                        <td><input class='button' type='submit' name='sendInput' value='Eintragen'/></td>
                        <td><input class='button' type='reset' name='cancel' value='Abbrechen' 
                            onclick=\"location.href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'\"/></td>
                    </tr>
                    ";
                }
            }
        }
        echo "
        </table>
        </div>
        </form>
        ";
    }
    echo "
        </div>
        </div>
    ";
?>