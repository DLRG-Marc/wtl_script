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


    // Settings
    require_once('f_sets.php');
    $setID = mysqli_real_escape_string($dbId,$_GET['setID']);
    $sendPW = $_GET['sendPW'];
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
    $fieldClass = array('username'=>'Field','realname'=>'Field','usermail'=>'Field','phone'=>'Field','sAdmin'=>'Field','admin'=>'Field',
        'view'=>'Selectfield','register'=>'Selectfield','entry'=>'Selectfield','delete'=>'Selectfield','upload'=>'Selectfield');

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','sAdmin','');

    // Auswahlarray serialisieren bevor durch Cross-Side Script schutz zerstört
    $_POST['view'] = serialize($_POST['view']);
    $_POST['register'] = serialize($_POST['register']);
    $_POST['entry'] = serialize($_POST['entry']);
    $_POST['delete'] = serialize($_POST['delete']);
    $_POST['upload'] = serialize($_POST['upload']);

    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysqli_real_escape_string($dbId,$_POST[$index]);
    }

    echo "<div id='wtl_user'>
          <div class='waitinglist'>";
    if( ($authority === TRUE) || ($GLOBALS['INSTALL']['FIRSTINSTALL'] === TRUE) )
    {
        // aufruf der Set-Funktion
        if( !$setID )
        {
            // Seitenüberschrift
            echo "<h1>Einstellungen Administratoren</h1>";
            makeSets($dbId,'wtl_user','',FALSE,'');
        }
        // Eingabefelder auf Gültigkeit prüfen
        if( isset($_POST['assumeUser']) )
        {
            $input_OK = TRUE;
            if( (strlen($_POST['username']) < 3) || (preg_match('/[^a-zA-Z\-äÄöÖüÜß.\s]/',$_POST['username'])) )
            {
                $input_OK = FALSE;
                $fieldClass['username'] = 'errorField';
                $errorTitle['username'] = 'Nur die Zeichen A-Z, a-z incl. Umlaute sowie - . und Leerzeichen sind zulässig!';
            }
            if( (strlen($_POST['realname']) < 3) || (preg_match('/[^a-zA-Z\-äÄöÖüÜß\s]/',$_POST['realname'])) )
            {
                $input_OK = FALSE;
                $fieldClass['realname'] = 'errorField';
                $errorTitle['realname'] = 'Nur die Zeichen A-Z, a-z incl. Umlaute sowie - und Leerzeichen sind zulässig!';
            }
            if( !check_email($_POST['usermail']) )
            { 
                $input_OK = FALSE;
                $fieldClass['usermail'] = 'errorField';
                $errorTitle['usermail'] = 'Ungültige e-mail Adresse!';
            }
            if( (strlen($_POST['phone']) < 2) || (preg_match('/[^\d\-]/',$_POST['phone'])) )
            {
                $input_OK = FALSE;
                $fieldClass['phone'] = 'errorField';
                $errorTitle['phone'] = 'Es sind nur Zahlen und - zulässig!';
            }
            if( $_POST['sAdmin'] != '1' )
            {
                $result = mysqli_query($dbId,"SELECT count(id) FROM swm_user WHERE sAdmin = '1'");
                $adminArray = mysqli_fetch_row($result);
                if( $adminArray[0] < '1' )
                {
                    $input_OK = FALSE;
                    $fieldClass['sAdmin'] = 'errorField';
                    $errorTitle['sAdmin'] = 'Es muß min. ein Administrator "Super-Admin" sein!';
                }
            }
            if( count(unserialize($_POST['view'])) == 0 )
            {
                $inputUser_OK = FALSE;
                $fieldClass['view'] = 'errorSelectfield';
                $errorTitle['view'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['view'])) > 1 && in_array('',unserialize($_POST['view'])) )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['view'] = 'errorSelectfield';
                $errorTitle['view'] ='Es kann nicht "nichts auswählen" und ein weiteres Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['register'])) == 0 )
            {
                $input_OK = FALSE;
                $fieldClass['register'] = 'errorSelectfield';
                $errorTitle['register'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['register'])) > 1 && in_array('',unserialize($_POST['register'])) )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['register'] = 'errorSelectfield';
                $errorTitle['register'] ='Es kann nicht "nichts auswählen" und ein weiteres Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['entry'])) == 0 )
            {
                $input_OK = FALSE;
                $fieldClass['entry'] = 'errorSelectfield';
                $errorTitle['entry'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['entry'])) > 1 && in_array('',unserialize($_POST['entry'])) )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['entry'] = 'errorSelectfield';
                $errorTitle['entry'] ='Es kann nicht "nichts auswählen" und ein weiteres Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['delete'])) == 0 )
            {
                $input_OK = FALSE;
                $fieldClass['delete'] = 'errorSelectfield';
                $errorTitle['delete'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['delete'])) > 1 && in_array('',unserialize($_POST['delete'])) )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['delete'] = 'errorSelectfield';
                $errorTitle['delete'] ='Es kann nicht "nichts auswählen" und ein weiteres Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['upload'])) == 0 )
            {
                $input_OK = FALSE;
                $fieldClass['upload'] = 'errorSelectfield';
                $errorTitle['upload'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['upload'])) > 1 && in_array('',unserialize($_POST['upload'])) )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['upload'] = 'errorSelectfield';
                $errorTitle['upload'] ='Es kann nicht "nichts auswählen" und ein weiteres Feld ausgewählt werden!';
            }
        }

        // wenn Formular abgeschickt wurde
        if( isset($_POST['assumeUser']) )
        {
            // wenn Eingaben OK
            if( $input_OK )
            {
                $realname = words_to_words_first_capital_letter($MYSQL['realname']);
                $SQL_Befehl_Write = "UPDATE wtl_user SET username = '".$MYSQL['username']."', realname = '".$realname."',
                    mail = '".$MYSQL['usermail']."', phone = '".$MYSQL['phone']."', sAdmin = '".$MYSQL['sAdmin']."', admin = '".$MYSQL['admin']."',
                    disable = '".$MYSQL['disable']."', viewAuth = '".$MYSQL['view']."', registerAuth = '".$MYSQL['register']."',
                    entryAuth = '".$MYSQL['entry']."', deleteAuth = '".$MYSQL['delete']."', uploadAuth = '".$MYSQL['upload']."',
                    lastEditor = '".$username."' WHERE id = '".$setID."'";
                $result = mysqli_query($dbId,$SQL_Befehl_Write);
                if( (mysqli_affected_rows($dbId) == 1) && ($result === TRUE) )
                {
                    $message = "<p><b>Die Einstellungen des Administrators '".$_POST['setName']."' wurden geändert !</b></p>
                        <p><a href='".$script_url."'>zurück zu den Einstellungen.</a></p>";
                    // ggf. Firstinstall zurücksetzen
                    $result = mysqli_query($dbId,"SELECT count(id) FROM wtl_user WHERE userpw = '' AND id = '".$setID."'");
                    $adminArray = mysqli_fetch_row($result);
                    if( $adminArray[0] == '1' )
                    {
                        $sendPW = 'new';
                    }
                    changeGlobals("['INSTALL']['FIRSTINSTALL']","TRUE","FALSE");
                }
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $errorPage = TRUE;
                $errorMessage .= errorNote();
            }
        }

        if( $sendPW == 'new' )
        {
            $startPW = buildPassword(8);
            $result = mysqli_query($dbId,"SELECT username, userpw, realname, mail FROM wtl_user WHERE id = '".$setID."'");
            $daten = mysqli_fetch_object($result);
            $result_write = mysqli_query($dbId,"UPDATE wtl_user SET userpw = '".md5($startPW)."' WHERE id = '".$setID."'");
            if( ($result_write != 0) && (mysqli_affected_rows($dbId) == 1) )
            {
                // email vorbereiten und versenden
                $mailtext  = "Hallo ".$daten->realname.",\n\n";
                $mailtext .= "du erhältst Zugang zur Wartelistenverwaltung.\n";
                $mailtext .= "Dein Benutzername lautet: ".$daten->username."\n";
                $mailtext .= "Dein Startpasswort lautet: ".$startPW."\n\n";
                $mailtext .= "Bitte ändere das Passwort sofort nach dem ersten Login!\n\n\n";
                $mailtext .= "Diese Mail wurde vom Administrator des Wartelistentools der ".$GLOBALS['HOME']['NAME']." versendet.";
                send_mail($GLOBALS['HOME']['MAIL'],$daten->mail,"Zugang zur Wartelistenverwaltung",$mailtext);
            }
        }

        // wenn kein Fehler
        if( !$errorPage )
        {
            // Daten neu einlesen
            $result = mysqli_query($dbId,"SELECT * FROM wtl_user WHERE id = '".$setID."'");
            while( $daten = mysqli_fetch_object($result) )
            {
                $_POST['setName'] = $daten->setName;
                $_POST['username'] = $daten->username;
                $_POST['realname'] = $daten->realname;
                $_POST['usermail'] = $daten->mail;
                $_POST['phone'] = $daten->phone;
                $_POST['disable'] = $daten->disable;
                $_POST['sAdmin'] = $daten->sAdmin;
                $_POST['admin'] = $daten->admin;
                $_POST['view'] = $daten->viewAuth;
                $_POST['register'] = $daten->registerAuth;
                $_POST['entry'] = $daten->entryAuth;
                $_POST['delete'] = $daten->deleteAuth;
                $_POST['upload'] = $daten->uploadAuth;
                if( $daten->userpw != '' )
                {
                    $pwisset = TRUE;
                }
            }
        }

        if( $setID )
        {
            // HTML Seite bauen
            // Überschrift
            echo "<h1>Einstellungen Administrator '".$_POST['setName']."'</h1>";
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
                <form name='userFields_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                <div class='border'>
                <table>
                    <tr>
                        <td colspan='3'>".$errorMessage."</td>
                    </tr>
                    <tr>
                        <td>Benutzername :</td>
                        <td colspan='2'><input class='".$fieldClass['username']."' type='text' name='username' size='37'
                            title='".$errorTitle['username']."' value='".$_POST['username']."'/></td>
                    </tr>
                    <tr>
                        <td>Vor- und Nachname :</td>
                        <td colspan='2'><input class='".$fieldClass['realname']."' type='text' name='realname' size='37'
                            title='".$errorTitle['realname']."' value='".$_POST['realname']."'/></td>
                    </tr>
                    <tr>
                        <td>E-Mail :</td>
                        <td colspan='2'><input class='".$fieldClass['usermail']."' type='text' name='usermail' size='37'
                            title='".$errorTitle['usermail']."' value='".$_POST['usermail']."'/></td>
                    </tr>
                    <tr>
                        <td>Telefon :</td>
                        <td colspan='2'><input class='".$fieldClass['phone']."' type='text' name='phone' size='20'
                            title='".$errorTitle['phone']."' value='".$_POST['phone']."'/></td>
                    </tr>
                    <tr>
                        <td>Benutzer deaktiviert :</td>
                        <td colspan='2'><input type='checkbox' name='disable'";
                            if($_POST['disable']=='1'){echo " checked='checked'";} echo " value='1'/></td>
                    </tr>
                    <tr>
                        <td>ist Super-Admin :</td>
                        <td colspan='2'><input type='checkbox' name='sAdmin'";if($_POST['sAdmin']=='1'){echo " checked='checked'";} echo " value='1'/>";
                            if($fieldClass['sAdmin']=='errorField'){echo " &nbsp;&nbsp;".$errorTitle['sAdmin'];} echo"</td>
                    </tr>
                    <tr>
                        <td>ist Admin :</td>
                        <td colspan='2'><input type='checkbox' name='admin'";if($_POST['admin']=='1'){echo " checked='checked'";} echo " value='1'/>";
                            if($fieldClass['admin']=='errorField'){echo " &nbsp;&nbsp;".$errorTitle['admin'];} echo"</td>
                    </tr>
                    <tr>
                        <td>Benutzer darf<br/><b>Wartelisten ansehen</b> :</td>
                        <td colspan='2'><select name='view[]' class='".$fieldClass['view']."' size='3'
                            title='".$errorTitle['view']."' multiple='multiple'>";
                            make_dropdown_list($dbId,'wtl_lists','',unserialize($_POST['view']),TRUE);
                        echo "
                        </select></td>
                    </tr>
                    <tr>
                        <td>Benutzer darf<br/><b>Datum in Wartelisten ändern</b> :</td>
                        <td colspan='2'><select name='register[]' class='".$fieldClass['register']."' size='3'
                            title='".$errorTitle['register']."' multiple='multiple'>";
                            make_dropdown_list($dbId,'wtl_lists','',unserialize($_POST['register']),TRUE);
                        echo "
                        </select></td>
                    </tr>
                    <tr>
                        <td>Benutzer darf<br/><b>aus Wartelisten aufnehmen</b> :</td>
                        <td colspan='2'><select name='entry[]' class='".$fieldClass['entry']."' size='3'
                            title='".$errorTitle['entry']."' multiple='multiple'>";
                            make_dropdown_list($dbId,'wtl_lists','',unserialize($_POST['entry']),TRUE);
                        echo "
                        </select></td>
                    </tr>
                    <tr>
                        <td>Benutzer darf<br/><b>Wartende löschen</b> :</td>
                        <td colspan='2'><select name='delete[]' class='".$fieldClass['delete']."' size='3'
                            title='".$errorTitle['delete']."' multiple='multiple'>";
                            make_dropdown_list($dbId,'wtl_lists','',unserialize($_POST['delete']),TRUE);
                        echo "
                        </select></td>
                    </tr>
                    <tr>
                        <td>Benutzer darf<br/><b>Dateien hoch/runterladen</b> :</td>
                        <td colspan='2'><select name='upload[]' class='".$fieldClass['upload']."' size='3'
                            title='".$errorTitle['upload']."' multiple='multiple'>";
                            make_dropdown_list($dbId,'wtl_lists','',unserialize($_POST['upload']),TRUE);
                        echo "
                        </select></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td><button class='submit_img' type='submit' name='assumeUser'>
                        <img src='".$img_path."accept.png' alt='accept'/> speichern</button></td>
                      <td><button class='cancel_img' type='reset' name='cancel' onclick=\"location.href='".$script_url."'\">
                        <img src='".$img_path."cancel.png' alt='cancel'/> abbrechen</button></td>
                    </tr>
                    <tr>
                        <td><input name='setName' type='hidden' value='".$_POST['setName']."'/></td>
                        <td colspan='2'>";
                        if( $pwisset )
                        {
                          echo "<a class='sendpw_img' title='neues Passwort versenden' href='".$script_url."&amp;setID=".$setID."&amp;sendPW=new'>
                            <img width='16' height='16' alt='sendpw', src='".$img_path."sendpw.png'></a></p>
                          ";
                        }
                        else
                        {
                            echo "<p></p>Ein Startpasswort wird dem User automatisch zugesendet.<p></p>";
                        }
                        echo "</td>
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
        echo "<h1>Einstellungen Administratoren</h1>";
        echo "<p><b>Du hast keine Berechtigung zum ändern der Administratoren für diese Warteliste!</b></p>";
    }
    echo "</div></div>";
?>