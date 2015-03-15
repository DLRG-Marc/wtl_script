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
 * @date - time  03.05.2013 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_sets.php');
    $setID = mysql_real_escape_string($_GET['setID']);
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
    $fieldClass = array('caption'=>'Field','character'=>'Field','digit'=>'Field','charLengthMin'=>'Field','charLengthMax'=>'Field','fieldLength'=>'Field',
        'fieldRows'=>'Field','regEx'=>'Field');

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','admin',$setID);

    // Auswahlarray serialisieren bevor durch Cross-Side Script schutz zerstört
    $_POST['character'] = $_POST['charReg'][0];
    $_POST['digit'] = $_POST['charReg'][1];
    $_POST['charLengthMin'] = $_POST['charLength'][0];
    $_POST['charLengthMax'] = $_POST['charLength'][1];
    $_POST['fieldLength'] = $_POST['fieldSize'][0];
    $_POST['fieldRows'] = $_POST['fieldSize'][1];
    $_POST['charReg'] = serialize($_POST['charReg']);
    $_POST['charLength'] = serialize($_POST['charLength']);
    $_POST['fieldSize'] = serialize($_POST['fieldSize']);

    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }

    echo "<div id='wtl_inputfields'>
          <div class='waitinglist'>";
    if( $authority === TRUE )
    {
        // aufruf der Set-Funktion
        if( !$setID )
        {
            // Seitenüberschrift
            echo "<h1>Einstellungen Eingabefelder</h1>";
            makeSets($dbId,'wtl_fields','input',FALSE,'');
        }
        // Eingabefelder auf Gültigkeit prüfen
        if( isset($_POST['sendInputFields']) )
        {
            $input_OK = TRUE;
            if( (strlen($_POST['caption']) < 2) || (preg_match('/[^\w\-äÄöÖüÜß.,:\s]/',$_POST['caption'])) )
            {
                $input_OK = FALSE;
                $fieldClass['caption'] = 'errorField';
                $errorTitle['caption'] = 'Nur die Zeichen 0-9, A-Z, a-z incl. Umlaute sowie - , . : und Leerzeichen sind zulässig!';
            }
            if( preg_match('/[\D]/',$_POST['charLengthMin']) )
            {
                $input_OK = FALSE;
                $fieldClass['charLengthMin'] = 'errorField';
                $errorTitle['charLengthMin'] = 'Es sind nur Zahlen zulässig!';
            }
            if( preg_match('/[\D]/',$_POST['charLengthMax']) )
            {
                $input_OK = FALSE;
                $fieldClass['charLengthMax'] = 'errorField';
                $errorTitle['charLengthMax'] = 'Es sind nur Zahlen zulässig!';
            }
            if( ($_POST['charLengthMax'] < $_POST['charLengthMin']) && !empty($_POST['charLengthMin']) && !empty($_POST['charLengthMax']) )
            {
                $input_OK = FALSE;
                $fieldClass['charLengthMax'] = 'errorField';
                $errorTitle['charLengthMax'] = 'Die max. Länge darf nicht kleiner als die min. Länge sein!';
            }
            if( preg_match('/[\D]/',$_POST['fieldLength']) )
            {
                $input_OK = FALSE;
                $fieldClass['fieldLength'] = 'errorField';
                $errorTitle['fieldLength'] = 'Es sind nur Zahlen zulässig!';
            }
            if( preg_match('/[\D]/',$_POST['fieldRows']) )
            {
                $input_OK = FALSE;
                $fieldClass['fieldRows'] = 'errorField';
                $errorTitle['fieldRows'] = 'Es sind nur Zahlen zulässig!';
            }
            if( preg_match('/[\\\]/',$_POST['regEx']) )
            {
                $input_OK = FALSE;
                $fieldClass['regEx'] = 'errorField';
                $errorTitle['regEx'] = 'Der \ ist hier nicht zulässig!';
            }
        }

        // wenn Daten geändert wurden
        if( isset($_POST['sendInputFields']) )
        {
            // wenn Eingaben OK
            if( $input_OK )
            {
                $SQL_Befehl_Write = "UPDATE wtl_fields SET caption = '".$MYSQL['caption']."', charReg = '".$MYSQL['charReg']."',
                    regEx = '".$MYSQL['regEx']."', charLength = '".$MYSQL['charLength']."', fieldSize = '".$MYSQL['fieldSize']."',
                    notRequ = '".$MYSQL['notRequiered']."', lastEditor = '".$username."' WHERE id = '".$setID."'";
                $result = mysql_query($SQL_Befehl_Write,$dbId);
                if( (mysql_affected_rows($dbId) == 1) )
                {
                    $message = "<p><b>Du hast erfolgreich die Einstellungen des Eingabefeldes '".$_POST['setName']."' geändert !</b></p>
                        <p><a href='".$script_url."'>zurück zu den Einstellungen.</a></p>";
                }
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $errorPage = TRUE;
                $errorMessage .= errorNote();
            }
        }

        // wenn kein Fehler
        if( !$errorPage )
        {
            // Daten neu einlesen
            $result = mysql_query("SELECT * FROM wtl_fields WHERE id = '".$setID."'",$dbId);
            while( $data = mysql_fetch_object($result) )
            {
                $_POST['setName'] = $data->setName;
                $_POST['caption'] = $data->caption;
                $charLengthArray = unserialize($data->charLength);
                $fieldSizeArray = unserialize($data->fieldSize);
                $charRegArray = unserialize($data->charReg);
                $_POST['regEx'] = $data->regEx;
                $_POST['notRequiered'] = $data->notRequ;
            }
            if( in_array('1',$charRegArray) )
            {
                $_POST['character'] = '1';
            }
            if( in_array('2',$charRegArray) )
            {
                $_POST['digit'] = '2';
            }
            $_POST['charLengthMin'] = $charLengthArray[0];
            $_POST['charLengthMax'] = $charLengthArray[1];
            $_POST['fieldLength'] = $fieldSizeArray[0];
            $_POST['fieldRows'] = $fieldSizeArray[1];
        }

        // wenn Felder anzeigen bzw. ändern oder hinzufügen
        if( $setID )
        {
            // HTML Seite bauen
            // Überschrift
            echo "<h1>Einstellungen des Eingabefeldes '".$_POST['setName']."'</h1>";
            // Meldung bei erfolgreicher Änderung
            if( $message != '' )
            {
                echo $message;
            }
            else
            {
                echo "<p><a class='summary_img' title='zurück zur Setübersicht' href='".$script_url."'>
                  <img width='16' height='16' alt='summary' src='".$img_path."summary.png'></a></p>
                ";
                // Formularfelder bauen und ausfüllen
                echo "
                <form name='inputFields_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                <div class='border'>
                <table>
                    <tr>
                        <td colspan='3'>".$errorMessage."</td>
                    </tr>
                    <tr>
                        <td>Beschriftung :</td>
                        <td><textarea class='".$fieldClass['caption']."' name='caption' cols='34' rows='2'
                            title='".$errorTitle['caption']."'>".$_POST['caption']."</textarea></td>
                    </tr>
                    <tr>
                        <td>Buchstaben erlaubt :</td>
                        <td><input class='".$fieldClass['character']."' type='checkbox' name='charReg[]'"; if($_POST['character']=='1'){echo " checked='checked'";}
                            echo" value='1'/></td>
                    </tr>
                    <tr>
                        <td>Zahlen erlaubt :</td>
                        <td><input class='".$fieldClass['digit']."' type='checkbox' name='charReg[]'"; if($_POST['digit']=='2'){echo " checked='checked'";}
                            echo" value='2'/></td>
                    </tr>
                    <tr>
                        <td>zusätzlich erlaubte Zeichen :</td>
                        <td><input class='".$fieldClass['regEx']."' type='text' name='regEx' size='37'
                            title='".$errorTitle['regEx']."' value='".$_POST['regEx']."'/></td>
                    </tr>
                    <tr>
                        <td>min. Zeichenlänge :</td>
                        <td colspan='2'><input class='".$fieldClass['charLengthMin']."' type='text' name='charLength[]' size='5'
                            title='".$errorTitle['charLengthMin']."' value='".$_POST['charLengthMin']."'/></td>
                    </tr>
                    <tr>
                        <td>max. Zeichenlänge :</td>
                        <td colspan='2'><input class='".$fieldClass['charLengthMax']."' type='text' name='charLength[]' size='5'
                            title='".$errorTitle['charLengthMax']."' value='".$_POST['charLengthMax']."'/></td>
                    </tr>
                    <tr>
                        <td>Grösse des Feldes :</td>
                        <td colspan='2'><input class='".$fieldClass['fieldLength']."' type='text' name='fieldSize[]' size='5'
                            title='".$errorTitle['fieldLength']."' value='".$_POST['fieldLength']."'/></td>
                    </tr>
                    <tr>
                        <td>Anzahl der Zeilen :</td>
                        <td colspan='2'><input class='".$fieldClass['fieldRows']."' type='text' name='fieldSize[]' size='5'
                            title='".$errorTitle['fieldRows']."' value='".$_POST['fieldRows']."'/></td>
                    </tr>
                    <tr>
                        <td>kein Pflichtfeld :</td>
                        <td colspan='2'><input type='checkbox' name='notRequiered'";
                            if($_POST['notRequiered']=='1'){echo " checked='checked'";} echo" value='1'/></td>
                    </tr>
                    <tr>
                        <td><input name='setName' type='hidden' value='".$_POST['setName']."'/></td>
                        <td colspan='2'>
                          <button class='submit_img' type='submit' name='sendInputFields'><img src='".$img_path."accept.png' alt='accept'/> speichern</button>
                          <button class='cancel_img' type='reset' name='cancel' 
                            onclick=\"location.href='".$script_url."'\"><img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button></td>
                    </tr>
                </table>
                </div>
                </form>
                ";
            }
        }
    }
    else
    {
        echo "<h1>Einstellungen Eingabefelder</h1>";
        echo "<p><b>Du hast keine Berechtigung zum ändern der Eingabefelder für diese Warteliste!</b></p>";
    }
    echo "</div></div>";
?>