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
 * @WTL version  1.2.2
 * @date - time  31.07.2013 - 19:00
 * @copyright    Marc Busse 2012-2016
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_fields.php');
    $listID = mysql_real_escape_string($_GET['listID']);
    $data = mysql_real_escape_string($_GET['data']);
    $entryToken = mysql_real_escape_string($_GET['entryToken']);
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $script_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $script_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }
    $displayMessage = FALSE;
    $registerId_OK = FALSE;
    $confirmOK = FALSE;
    $NotView = TRUE;
    $authority = FALSE;
    $readonly = '';
    $username = $_SESSION['intern']['realname'];
    //$girderColors = array('000-025'=>'#00A700', '026-075'=>'#F0F000', '076-100'=>'#F00000');
    $girderColors = array('000-100'=>'#F0F000');
    $girderType = 1;
    $fieldClass = array('registerId'=>'Field','registerDate'=>'Field','firstname'=>'Field','lastname'=>'Field','dateOfBirth'=>'Field',
        'mail'=>'Field');

    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string(stripslashes($_POST[$index]));
    }

    // bei Anmeldenummer bzw. bei Bestätigung richtige listID. heraussuchen
    if( (isset($_POST['sendRegisterId']) || isset($_POST['sendConfirm'])) && ($data != 'view') )
    {
        $SQL_Befehl_Read = "SELECT listId FROM wtl_members WHERE registerId = '".$MYSQL['registerId']."'";
        if( isset($_POST['sendConfirm']) )
        {
            $SQL_Befehl_Read = "SELECT listId FROM wtl_members WHERE registerId = '".substr(base64_decode($entryToken),0,6)."'";
        }
        $result = mysql_query($SQL_Befehl_Read,$dbId);
        $listNoArray = mysql_fetch_row($result);
        $listID = $listNoArray[0];
    }
    elseif( isset($_POST['sendEdit']) || isset($_POST['sendPredelete']) || isset($_POST['sendDelete']) )
    {
        $listID = $_POST['listId'];
    }

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','registerAuth',$listID);

    // Daten der wtl_lists lesen
    $result = mysql_query("SELECT * FROM wtl_lists WHERE id = '".$listID."'",$dbId);
    while( $daten = mysql_fetch_object($result) )
    {
        $published = $daten->published;
        $dlrgName = $daten->dlrgName;
        $listName = $daten->setName;
        $mailadress = $daten->mailadress;
        $inputfields = unserialize($daten->inputfields);
        $selectfields = unserialize($daten->selectfields);
        $ageLimitArray = unserialize($daten->ageLimit);
        $registerMail = html_entity_decode($daten->registerMail,ENT_QUOTES,'UTF-8');
        $headerText = html_entity_decode($daten->headerText,ENT_QUOTES,'UTF-8');
        $footerText = html_entity_decode($daten->footerText,ENT_QUOTES,'UTF-8');
    }
    $_POST['ageMin'] = $ageLimitArray[0];
    $_POST['ageMax'] = $ageLimitArray[1];
    // class aller Eingabefelder
    foreach( $inputfields as $id )
    {
        $fieldClass['input_'.$id] = 'Field';
    }
    // class aller Auswahlfelder
    foreach( $selectfields as $id )
    {
        $fieldClass['dropdown_'.$id] = 'Selectfield';
    }

    echo "<div id='wtl_register'>
          <div class='waitinglist'>";
    // nur ausführen wenn die Liste veröffentlicht oder bei Daten ändern oder bei Aufnahme bestätigen
    if( ($published == '1') || ($data == 'edit') || ($data == 'confirm') || (($data == 'view') && ($authority === TRUE)) )
    {
        // bei leerem Datum, Anmeldedatum setzen 
        if( empty($_POST['registerDate']) && ($data == 'input') )
        {
            $_POST['registerDate'] = date('d.m.Y',time());
        }

        // Eingabefelder auf Gültigkeit prüfen
        // bei RegisterId
        if( isset($_POST['sendRegisterId']) )
        {
            $input_OK = TRUE;
            if( strlen($_POST['registerId'])!=6 )
            {
                $input_OK = FALSE;
                $fieldClass['registerId'] = 'errorField';
                $errorTitle['registerId'] = 'Die Anmeldenummer muß 6-stellig sein!';
            }
        }
        // bei Daten eintragen oder ändern
        if( isset($_POST['sendInput']) || isset($_POST['sendEdit']) )
        {
            $input_OK = TRUE;
            $errorArray = array();
            $errorArray[0] = TRUE;
            if( ($authority === TRUE) && (!check_date($_POST['registerDate'],'.')) )
            { 
                $input_OK = FALSE;
                $fieldClass['registerDate'] = 'errorField';
                $errorTitle['registerDate'] = 'Ungültiges Datum!';
            }
            if( !check_date($_POST['dateOfBirth'],'.') )
            {
                $input_OK = FALSE;
                $fieldClass['dateOfBirth'] = 'errorField';
                $errorTitle['dateOfBirth'] = 'Ungültiges Datum oder Eingabeformat falsch!';
            }
            if( (calcAge(date_german2mysql($_POST['dateOfBirth'])) < $_POST['ageMin']) && (!empty($_POST['ageMin'])) )
            {
                $input_OK = FALSE;
                $fieldClass['dateOfBirth'] = 'errorField';
                $errorTitle['dateOfBirth'] = 'Das Alter ist kleiner als das Mindestalter!';
            }
            if( (calcAge(date_german2mysql($_POST['dateOfBirth'])) > $_POST['ageMax']) && (!empty($_POST['ageMax'])) )
            {
                $input_OK = FALSE;
                $fieldClass['dateOfBirth'] = 'errorField';
                $errorTitle['dateOfBirth'] = 'Das Alter ist größer als das Maximalalter!';
            }
            if( !check_email($_POST['mail']) )
            { 
                $input_OK = FALSE;
                $fieldClass['mail'] = 'errorField';
                $errorTitle['mail'] = 'Ungültige e-mail Adresse!';
            }
            $errorArray = checkFieldChars('firstname','/[^a-zA-Z\-äÄöÖüÜß\s]/','Nur die Zeichen A-Z, a-z incl. Umlaute sowie - und Leerzeichen sind zulässig!',$errorArray);
            $errorArray = checkFieldChars('lastname','/[^a-zA-Z\-äÄöÖüÜß\s]/','Nur die Zeichen A-Z, a-z incl. Umlaute sowie - und Leerzeichen sind zulässig!',$errorArray);
            $errorArray = checkInputfields($dbId,'wtl_fields',$inputfields,$errorArray);
            $errorArray = checkSelectfields($selectfields,$errorArray);
            foreach( $errorArray['class'] as $key => $val )
            {
                $fieldClass[$key] = $val;
            }
            foreach( $errorArray['text'] as $key => $val )
            {
                $errorTitle[$key] = $val;
            }
            $input_OK = $input_OK && $errorArray[0];
        }
        // wenn Eingabeprüfung durchlaufen wurde
        if ( isset($input_OK) )
        {
            // wenn Eingaben OK
            if ( $input_OK === TRUE )
            {
                $firstname = words_to_words_first_capital_letter($MYSQL['firstname']);
                $lastname = words_to_words_first_capital_letter($MYSQL['lastname']);
                $dateOfBirth = strtotime(date_german2mysql($MYSQL['dateOfBirth']));
                $ageYear = date('Y') - date('Y', $dateOfBirth);
                $tstamp = time();
                $inputs = inputfielddata_to_inputdata($inputfields,$MYSQL,'input_');
                $selects = inputfielddata_to_inputdata($selectfields,$MYSQL,'dropdown_');
                if( $authority === TRUE )
                {
                    $tstamp = strtotime(date_german2mysql($_POST['registerDate']));
                }
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $errorMessage .= errorNote();
            }
        }

        // nach Eintragung
        if( isset($_POST['sendInput']) && ($input_OK === TRUE) )
        {
            $registerId = buildPassword(6);
            $SQL_Befehl_Write = "INSERT INTO wtl_members SET tstamp = '".$tstamp."', listId = '".$listID."', registerId ='".$registerId."',
                firstname = '".$firstname."', lastname = '".$lastname."', dateOfBirth = '".$dateOfBirth."', mail = '".$MYSQL['mail']."',
                inputs = '".$inputs."', selected = '".$selects."', lastEditor = '".$username."'";
            // Prüfen, ob Eintrag schon vorhanden ist
            $result = mysql_query($SQL_Befehl_Write,$dbId);
            if( !$result && (mysql_errno() == 1062) )
            {
                $displayMessage = TRUE;
                $message = "<p><b>Deine Daten wurden bereits eingetragen !</b><br/>Ein 2. Eintrag ist nicht möglich !<br/><br/>
                   <a href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>Eine weitere Anmeldung vornehmen.</a></p>";
            }
            if( mysql_affected_rows($dbId) == 1)
            {
                // email vorbereiten
                $mailWildcardArray = array('#VORNAME#','#NACHNAME#','#LISTENNAME#','#MELDEDATUM#','#MELDENR#','#DLRGNAME#');
                $mailVariableArray = array($firstname,$lastname,$listName,date('d.m.Y',$tstamp),$registerId,$dlrgName);
                preg_match_all('/#\w+#/',$registerMail,$treffer,PREG_SET_ORDER);
                foreach( $treffer as $wert )
                {
                    if( !in_array($wert[0],$mailWildcardArray) )
                    {
                        $result = mysql_query("SELECT setNo, fieldType FROM wtl_fields WHERE isSet = '1' AND setName = '".trim($wert[0],'#')."'", $dbId);
                        while( $daten = mysql_fetch_object($result) )
                        {
                            $fieldType = $daten->fieldType;
                            $setNo = $daten->setNo;
                        }
                        $result_selectField = mysql_query("SELECT dataLabel FROM wtl_fields WHERE setNo = '".$setNo."'
                            AND data = '".$_POST[$fieldType.'_'.$setNo]."'", $dbId);
                        $selectDataLabelArray = mysql_fetch_row($result_selectField);
                        $registerMail = str_replace($wert[0],$selectDataLabelArray[0],$registerMail);
                    }
                }
                $mailtext = str_replace($mailWildcardArray,$mailVariableArray,$registerMail);
                send_mail($mailadress,$_POST['mail'],$dlrgName.' Wartelisteneintrag '.$listName,$mailtext);
                // Erfolgsmeldung
                $displayMessage = TRUE;
                $message = "<p><b>Du hast Dich erfolgreich in die Warteliste ".$listName." eingetragen !</b></p>
                    <p>Eine e-mail mit Deiner Anmeldenummer wurde an die angegebene Adresse versandt.<br/>
                    Solltest Du innerhalb von 24h keine e-mail erhalten haben, wende Dich bitte an das
                    <a href='mailto:".$mailadress."'>Aufnahmeteam ".$listName."</a>.</p>
                    <p><a href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>Einen weiteren Eintrag in die Warteliste vornehmen.</a></p>";
            }
            else
            {
                $displayMessage = TRUE;
                $message = "<p><b>Das Eintragen Deiner Daten ist fehlgeschlagen !</b><br/>Bitte wende Dich an das
                    <a href='mailto:".$mailadress."'>Aufnahmeteam ".$listName."</a>.</p>";
            }
        }

        // bei Daten ändern
        if( isset($_POST['sendRegisterId']) && ($input_OK === TRUE) )
        {
            // Abfrage ob Anmeldenummer existiert und nicht als gelöscht markiert ist
            $result = mysql_query("SELECT * FROM wtl_members WHERE registerId = '".$MYSQL['registerId']."' AND deleted != '1'",$dbId);
            if( mysql_num_rows($result) == 1 )
            {
                while( $daten = mysql_fetch_object($result) )
                {
                    // wenn Person noch nicht aufgenommen wurde
                    if( empty($daten->entryId) )
                    {
                        // Anzahl Wartender errechnen
                        $resultNo = mysql_query("SELECT COUNT(*) FROM wtl_members WHERE listId = '".$listID."' AND entryId = '' AND deleted != '1'",$dbId);
                        $waitingNoArray = mysql_fetch_row($resultNo);
                        $waitingNo = $waitingNoArray[0];
                        // Platz errechnen
                        $SQL_Befehl_Read = "SELECT (SELECT COUNT(*) FROM wtl_members b WHERE (b.tstamp <= a.tstamp AND b.id < a.id)
                            AND listId = '".$listID."' AND entryId = '' AND deleted != '1' ORDER BY b.tstamp DESC, b.id DESC) + 1 AS position FROM wtl_members a
                            WHERE a.registerId = '".$_POST['registerId']."' AND a.listId = '".$listID."' AND a.entryId = '' AND deleted != '1'";
                        $resultPos = mysql_query($SQL_Befehl_Read, $dbId);
                        $waitingPosArray = mysql_fetch_row($resultPos);
                        $waitingPos = $waitingPosArray[0];
                        $_POST['memberId'] = $daten->id;
                        $_POST['firstname'] = $daten->firstname;
                        $_POST['lastname'] = $daten->lastname;
                        $_POST['dateOfBirth'] = date('d.m.Y', $daten->dateOfBirth);
                        $_POST['mail'] = $daten->mail;
                        $_POST['registerDate'] = date('d.m.Y', $daten->tstamp);
                        $_POST = inputfielddata_to_inputfields($daten->inputs,$_POST,'input_');
                        $_POST = inputfielddata_to_inputfields($daten->selected,$_POST,'dropdown_');
                        $registerId_OK = TRUE;
                    }
                    // wenn Person schon aufgenommen wurde
                    else
                    {
                        $displayMessage = TRUE;
                        $message = "<p><b>Die Person mit den nachfolgenden Daten wurde am ".date('d.m.Y', $daten->entryTstamp).
                            " von uns per e-mail aufgenommen !<br/>Die Aufnahme wurde von Dir ";
                        if( (number_to_janein($daten->confirm) == '-') || !(number_to_janein($daten->confirm)) )
                        {
                            $message .= "nicht";
                        }
                        else
                        {
                            $message .= "mit ".number_to_janein($daten->confirm);
                        }
                        $message .= " bestätigt.</b></p>
                            <div class='border'>
                            <table>
                                <tr><td>Vorname :</td><td>".$daten->firstname."</td></tr>
                                <tr><td>Name :</td><td>".$daten->lastname."</td></tr>
                                <tr><td>Geurtsdatum :</td><td>".date('d.m.Y', $daten->dateOfBirth)."</td></tr>
                            </table>
                            </div>
                            <p><a href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>Einen weiteren Datensatz ändern.</a></p>";
                    }
                }
            }
            else
            {
                $displayMessage = TRUE;
                $message = "<p><b>Ungültige Meldenummer !</b><br/><br/>Es sind keine Daten zu dieser Meldenummer hinterlegt.<br/>
                    <a href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>Erneut veruchen.</a></p>";
            }
        }

        // bei Daten updaten
        if( isset($_POST['sendEdit']) && ($input_OK === TRUE) )
        {
            if( $authority === TRUE )
            {
                $SQL_Befehl_Write = "UPDATE wtl_members SET tstamp = '".$tstamp."', firstname = '".$firstname."', lastname = '".$lastname."',
                    dateOfBirth = '".$dateOfBirth."', mail = '".$MYSQL['mail']."', inputs = '".$inputs."', selected = '".$selects."',
                    lastEditor = '".$username."' WHERE id = '".$MYSQL['memberId']."'";
                $requestURL = "<br/><br/><a href='".substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'='))."=wtl_view&amp;listID=".$listID."'>
                    Einen weiteren Datensatz ändern.</a>"; 
            }
            else
            {
                $SQL_Befehl_Write = "UPDATE wtl_members SET firstname = '".$firstname."', lastname = '".$lastname."',
                    dateOfBirth = '".$dateOfBirth."', mail = '".$MYSQL['mail']."', inputs = '".$inputs."', selected = '".$selects."',
                    lastEditor = '".$username."' WHERE id = '".$MYSQL['memberId']."'";
                $requestURL = "<br/><br/><a href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>Einen weiteren Datensatz ändern.</a>";
            }
            $result = mysql_query($SQL_Befehl_Write,$dbId);
            if( mysql_affected_rows($dbId) == 1)
            {
                $displayMessage = TRUE;
                $message = "<p><b>Deine Daten wurden geändert !</b>".$requestURL."</p>";
            }
            elseif( mysql_affected_rows($dbId) == 0)
            {
                $displayMessage = TRUE;
                $message = "<p><b>Es wurden keine Daten geändert !</b>".$requestURL."</p>";
            }
            else
            {
                $displayMessage = TRUE;
                $message = "<p><b>Das Ändern Deiner Daten ist fehlgeschlagen !<br/>Bitte wende Dich an das
                    <a href='mailto:".$mailadress."'>Aufnahmeteam ".$listName."</a>.</p>";
            }
        }

        // bei Daten löschen vorbereiten
        if( isset($_POST['sendPredelete']) )
        {
            $displayMessage = TRUE;
            $message = "<p><b>Willst Du die Daten wirklich löschen ?</b></p>
                <form name='wtl_del' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                <div class='border'>
                <table>
                    <tr>
                        <td><input name='memberId' type='hidden' value='".$_POST['memberId']."'/>
                            <input name='listId' type='hidden' value='".$listID."'/>
                            <input class='button' type='submit' name='sendDelete' value='Löschen'/></td>
                        <td><input class='button' type='reset' name='cancel' value='Abbrechen' 
                            onclick=\"location.href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'\"/></td>
                    </tr>
                </table>
                </div>
                </form>
            ";
        }
        // bei Daten löschen
        if( isset($_POST['sendDelete']) )
        {
            $result = mysql_query("UPDATE wtl_members SET deleted = '1' WHERE id = '".$MYSQL['memberId']."'",$dbId);
            if( mysql_affected_rows($dbId) == 1)
            {
                $displayMessage = TRUE;
                $message = "<p><b>Deine Daten wurden aus der Warteliste gelöscht !</b><br/><br/><a href='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>Einen weiteren Datensatz ändern.</a></p>";
            }
            else
            {
                $displayMessage = TRUE;
                $message = "<p><b>Das Löschen Deiner Daten ist fehlgeschlagen !<br/>Bitte wende Dich an das
                    <a href='mailto:".$mailadress."'>Aufnahmeteam ".$listName."</a>.</p>";
            }
        }

        // bei Aufnahmebestätigungslink
        if( $data == 'confirm' )
        {
            $confirmData = base64_decode($entryToken);
            $SQL_Befehl_Read = "SELECT * FROM wtl_members WHERE registerId = '".substr($confirmData,0,6)."'
                AND entryId = '".substr($confirmData,6,6)."' AND deleted != '1'";
            $result = mysql_query($SQL_Befehl_Read, $dbId);
            if( mysql_num_rows($result) == 1 )
            {
                while( $daten = mysql_fetch_object($result) )
                {
                    $id = $daten->listId;
                    $confirm = $daten->confirm;
                    $answerdate = $daten->answerTstamp;
                    $entryTstamp = $daten->entryTstamp;
                    $entryUserId = $daten->entryUserId;
                    $entryUser = $daten->entryUsername;
                    $entryConfMail = $daten->entryConfMail;
                    $_POST['firstname'] = $daten->firstname;
                    $_POST['lastname'] = $daten->lastname;
                }
                $result = mysql_query("SELECT setName FROM wtl_lists WHERE id = '".$id."'",$dbId);
                $listNameArray = mysql_fetch_row($result);
                $confirmOK = TRUE;
                $message = "<p>Hier bestätigst Du die Aufnahme aus der Warteliste ".$listNameArray[0]." für<br/>".
                    $_POST['firstname']." ".$_POST['lastname']."<br/>
                    laut der Aufnahmemail vom ".date('d.m.Y',$entryTstamp)."</p>";
                if( $confirm != '0' )
                {
                    $confirmOK = FALSE;
                    $displayMessage = TRUE;
                    $message = "<p><b>Die Bestätigung ist bereits erfolgt!<br/>Ein Ändern ist nicht möglich!</b></p>";
                }
                if( ($confirm == '0') && ($answerdate < mktime(0,0,0,date('m'), date('d'), date('Y'))) )
                {
                    $confirmOK = FALSE;
                    $displayMessage = TRUE;
                    $message = "<p><b>Das Rückmeldedatum ist bereits verstrichen!</b></p>";
                }
            }
            else
            {
                $displayMessage = TRUE;
                $message = "<p><b>Der Bestätigungslink ist ungültig!</b></p>";
            }
        }
        // nach Aufnahmebestätigung
        if( isset($_POST['sendConfirm']) && ($confirmOK === TRUE) )
        {
            $confirmData = base64_decode($entryToken);
            $SQL_Befehl_Write = "UPDATE wtl_members SET confirm = '".$_POST['confirm']."', confirmTstamp = '".time()."'
                WHERE registerId = '".substr($confirmData,0,6)."' AND entryId = '".substr($confirmData,6,6)."' AND deleted != '1'";
            $result = mysql_query($SQL_Befehl_Write,$dbId);
            if( mysql_affected_rows($dbId) == 1)
            {
                $displayMessage = TRUE;
                $message = "<p><b>Deine Rückmeldung wurde erfolgreich in die Datenbank eingetragen!</b></p>";
                if( $entryConfMail == '1' )
                {
                    $result = mysql_query("SELECT mail FROM wtl_user WHERE id = '".$entryUserId."'",$dbId);
                    $entryUserMailArray = mysql_fetch_row($result);
                    $mailtext = "Hallo ".$entryUser."\n\nDie folgende Person Deiner Aufnahme aus der Warteliste ".
                        $listName." vom ".date('d.m.Y',$entryTstamp)." hat sich gemeldet:\n".$_POST['firstname']." ".$_POST['lastname']."\n".
                        "Teilnahme: ".number_to_janein($_POST['confirm'])."\n\n\n".
                        "Diese Mail erhältst Du aufgrund einer Aufnahme aus dem Wartelistensystem der ".$dlrgName;
                    send_mail($mailadress,$entryUserMailArray[0],$dlrgName.' Aufnahmerückmeldung '.$listName,$mailtext);
                }
            }
            else
            {
                $displayMessage = TRUE;
                $message = "<p><b>Das Bestätigen der Aufnahme ist fehlgeschlagen !<br/>Bitte wende Dich bitte an das
                    <a href='mailto:".$mailadress."'>Aufnahmeteam ".$listName."</a>.</p>";
            }
        }

        // Seitentext
        if( $displayMessage === FALSE )
        {
            // bei Daten eintragen
            if( ($data == 'input') )
            {
                $message = "<p>Für jede Person ist ein eigenes Formular auszufüllen!<br/>
                    Bitte alle Felder dieses Formulars ausfüllen.</p>
                    <p>Nach der erfolgreichen Eintragung erhälst Du eine Bestätigungs e-mail !</p>
                    <p>Bei Fragen zum Ausfüllen des Formulars oder sonstigen Schwierigkeiten wende Dich bitte an das
                    <a href='mailto:".$mailadress."'>Aufnahmeteam ".$listName."</a>.</p>";
            }
            // bei einloggen mit Register-Id
            if( ($data == 'edit') && ($registerId_OK === FALSE) )
            {
                $message = "<p>Um Deinen Anmeldestatus abzufragen oder die bestehenden Daten zu ändern, trage hier Deine<br/>6-stellige Anmeldenummer ein.<br/>
                    Diese Nummer wurde Dir bei der Eintragung der Daten per e-mail zugeschickt.</p>";
            }
            // bei Daten ändern
            if( ($data == 'edit') && ($registerId_OK === TRUE) )
            {
                $message = "<p>Hier kannst Du die bestehenden Daten einsehen bzw. aktualisieren.<br/>
                    Solltest Du bzw. Dein Kind kein Interesse mehr haben, oder zwischenzeitlich woanders<br/>
                    untergekommen sein, so bitten wir Dich Deine Daten hier zu löschen.</p>";
            }
            // headertext (wenn nicht leer)
            if( !empty($headerText) && ($data == 'input') )
            {
                $message .= "<p>".nl2br($headerText)."</p>";
            }
        }
        $message .= "<p></p>";
        include_once('wtl_register_site.php');
        // footertext (wenn nicht leer)
        if( !empty($footerText) && ($data == 'input') && ($displayMessage === FALSE) )
        {
            echo "<p>".nl2br($footerText)."</p>";
        }
    }
    else
    {
        echo "<h1>Warteliste ".$listName."</h1>
            <p><b>Diese Warteliste ist zur Zeit nicht verfügbar !</b></p>
        ";
    }
    echo "</div></div>";
?>