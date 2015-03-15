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


    // Settings
    require_once('f_sets.php');
    require_once('f_wtl.php');
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
    $file_pdf = 'pdf_view';
    $out_pdf = 'pdf_view.pdf;I';
    $fieldClass = array('age'=>'Selectfield','answerdate'=>'Field','startdate'=>'Field','limit'=>'Field');

    // Auswahlarray sichern bevor durch Cross-Side Script schutz zerstört
    $_POST['selected'] = serialize($_POST['selected']);

    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }
    $_POST['selected'] = unserialize($_POST['selected']);
    $listID = $_POST['listId'];

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','entryAuth',$listID);

    echo "<div id='wtl_entry'>
          <div class='waitinglist'>";
    // Daten der wtl_lists lesen
    $result = mysql_query("SELECT * FROM wtl_lists WHERE id = '".$listID."'",$dbId);
    while( $daten = mysql_fetch_object($result) )
    {
        $dlrgName = $daten->dlrgName;
        $listName = $daten->setName;
        $mailadress = $daten->mailadress;
        $entryMail = html_entity_decode($daten->entryMail,ENT_QUOTES,'UTF-8');
        $entryLimit = $daten->entryLimit;
        $connectFields = unserialize($daten->connectFields);
    }
    // class aller Auswahlfelder
    foreach( $connectFields[1] as $id )
    {
        $fieldClass[$id] = 'Selectfield';
    }
    // Aufnahme-ID generieren
    if( empty($_POST['entryId']) )
    {
        $_POST['entryId'] = buildPassword(6);
    }

    if( $authority === TRUE )
    {
        // Eingabefelder auf Gültigkeit prüfen
        if( isset($_POST['preview']) )
        {
            $input_OK = TRUE;
            if( empty($_POST['age']) )
            {
                $input_OK = FALSE;
                $fieldClass['age'] = 'errorSelectfield';
                $errorTitle['age'] = 'Es muß ein Feld ausgewählt werden!';
            }
            if( (!check_date($_POST['answerdate'],'.')) || (days_between(trim($_POST['answerdate']),date('d.m.Y'),'dmY','.') <= 0) )
            {
                if( !check_date($_POST['answerdate'],'.') )
                {
                    $errortext = 'Datum ungültig!';
                }
                if( days_between(trim($_POST['answerdate']),date('d.m.Y'),'dmY','.') <= 0 )
                {
                    $errortext .= 'Es muss min. 1 Tag zwischen Antwortdatum und heute liegen!';
                }
                $input_OK = FALSE;
                $fieldClass['answerdate'] = 'errorField';
                $errorTitle['answerdate'] = $errortext;
            }
            if( (!check_date($_POST['startdate'],'.')) || (days_between($_POST['startdate'],date('d.m.Y'),'dmY','.') <= 0) )
            {
                if( !check_date($_POST['startdate'],'.') )
                {
                    $errortext = 'Datum ungültig!';
                }
                if( days_between(trim($_POST['startdate']),date('d.m.Y'),'dmY','.') <= 0 )
                {
                    $errortext .= 'Es muss min. 1 Tag zwischen Schwimmstart und heute liegen!';
                }
                $input_OK = FALSE;
                $fieldClass['startdate'] = 'errorField';
                $errorTitle['startdate'] = $errortext;
            }
            if( (days_between(trim($_POST['startdate']),trim($_POST['answerdate']),'dmY','.') < 7) && ($_POST['answerdate_ok'] != TRUE) )
            {
                $min_answerdate = TRUE;
                $input_OK = FALSE;
                $fieldClass['answerdate'] = 'errorField';
                $fieldClass['startdate'] = 'errorField';
                $errorTitle['answerdate'] = 'Es sind weniger als 7 Tage zwischen Schwimmstart und Antwortdatum! Ändern oder Antwortdatum bestätigen.' ;
            }
            if( (empty($_POST['limit'])) || ($_POST['limit'] > $entryLimit) || (preg_match('/[\D]/',$_POST['limit'])) )
            {
                $errortext = 'Es sind nur Zahlenwerte zulässig!';
                if( $_POST['limit'] > $entryLimit )
                {
                    $errortext .= 'Anzahl zu gross!';
                }
                $input_OK = FALSE;
                $fieldClass['limit'] = 'errorField';
                $errorTitle['limit'] = $errortext;
            }
            foreach( $connectFields[1] as $id )
            {
                if( empty($_POST[$id]) )
                {
                    $input_OK = FALSE;
                    $fieldClass[$id] = 'errorSelectfield';
                    $errorTitle[$id] = 'Es muß ein Feld ausgewählt werden!';
                }
            }
        }

        // wenn Vorschau
        if( isset($_POST['preview']) )
        {
            // wenn Eingaben OK
            if( $input_OK )
            {
                // Auswahlfelder durchsuchen Teil der SQL-Abfrage
                foreach( $connectFields[1] as $key => $id )
                {
                    if( $_POST[$id] != 'EGAL' )
                    {
                        $str = preg_replace('/\s+/','',$_POST[$id]);
                        if( strpos($str,'ODER') !== FALSE )
                        {
                            $str = str_replace("ODER","#%' OR selected LIKE '%#".$key.";",$str);
                        }
                        $query .= " AND (selected LIKE '%#".$key.";".$str."#%' OR selected LIKE '%#".$key.";EGAL#%')";
                    }
                }
                // SQL-Abfrage zusammenbauen
                $ageCompare = htmlspecialchars_decode($_POST['age']);
                if( $ageCompare != 'EGAL' )
                {
                    preg_match('/\d+/',$ageCompare,$numberMatches);
                    preg_match('/\D+/',$ageCompare,$charMatches);
                    $compare = strtr($charMatches[0],array('<'=>'>','>'=>'<','<='=>'>=','>='=>'<='));
                    $query .= " AND dateOfBirth ".$compare." '".mktime(0,0,0,date('m'),date('d'),date('Y')-$numberMatches[0])."'";
                }
                $query .= " ORDER by tstamp ASC, id ASC LIMIT ".$MYSQL['limit'];
                $SQL_Befehl_Read = "SELECT * FROM wtl_members WHERE entryId = '' AND deleted != '1'
                    AND listId = '".$listID."'".$query;
                $result = mysql_query($SQL_Befehl_Read, $dbId);
                $quantity = mysql_num_rows($result);
                // Aufnahmevorschau vorbereiten
                $titel_pdf = "Aufnahmevorschau der Warteliste ".$listName;
                $titel = "<h1>".$titel_pdf."</h1>"; 
                $headline_pdf = '';
                $headline = "<p><b>".$headline_pdf."</b></p>";
                $textBefore = "<p><input class='button' type='submit' name='register' value='Aufnehmen'/>";
                $textBefore .= "<input class='button' type='reset' name='cancel' value='abbrechen' onclick=\"location.href='".$script_url."'\"/>";
                $textBefore .= "<input type='hidden' name='age' value='".$_POST['age']."'/>";
                $textBefore .= "<input type='hidden' name='limit' value='".$_POST['limit']."'/>";
                $textBefore .= "<input type='hidden' name='answerdate' value='".$_POST['answerdate']."'/>";
                $textBefore .= "<input type='hidden' name='startdate' value='".$_POST['startdate']."'/>";
                $textBefore .= "<input type='hidden' name='listId' value='".$listID."'/>";
                $textBefore .= "<input type='hidden' name='entryId' value='".$_POST['entryId']."'/>";
                foreach( $connectFields[1] as $id )
                {
                    $textBefore .= "<input type='hidden' name='".$id."' value='".$_POST[$id]."'/>";
                }
                $textBefore .= "</p><p>Jede Rückmeldung der Aufnahme sofort per Mail zusenden :&nbsp;&nbsp;&nbsp;
                    <input class='".$fieldClass['entryConfMail']."' type='checkbox' name='entryConfMail' value='1' checked='checked'/></p>";
                $displayMessage = TRUE;
                $buttons = array();
                wtl_make_site_view($dbId,'ENTRY',$result,$listID,$quantity,'1','+1',$titel,$headline,$textBefore,'',$buttons);
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $errorMessage .= errorNote();
            }
        }

        // wenn aufnehmen
        if( isset($_POST['register']) )
        {
            // Prüfen, ob Aufnahme schon erfolgt
            $SQL_Befehl_Read = "SELECT id FROM wtl_members WHERE entryId = '".$MYSQL['entryId']."'";
            $result = mysql_query($SQL_Befehl_Read, $dbId);
            $quantity = mysql_num_rows($result);
            if( $quantity != 0 )
            {
                $displayMessage = TRUE;
                $message = "<p><b>Die Aufnahme ist bereits erfolgt !</b><br/>Ein 2. Mal Aufnehmen ist nicht möglich !<br/><br/>
                   <a href='".$script_url."'>Eine weitere Aufnahme vornehmen.</a></p>";
            }
            else
            {
                // Daten der wtl_user lesen
                $result = mysql_query("SELECT id, mail, phone FROM wtl_user WHERE id = '".$_SESSION['intern']['userId']."'",$dbId);
                while( $daten = mysql_fetch_object($result) )
                {
                    $userId = $daten->id;
                    $usermail = $daten->mail;
                    $userphone = $daten->phone;
                }
                $startdate = strtotime(date_german2mysql($_POST['startdate']));
                $answerdate = strtotime(date_german2mysql($_POST['answerdate']));
                $id_all = array_to_text_with_trenner($_POST['selected'], "' OR id = '");
                $SQL_Befehl_Write = "UPDATE wtl_members SET entryId = '".$MYSQL['entryId']."', entryTstamp = '".time()."',
                    startTstamp = '".$startdate."', answerTstamp = '".$answerdate."', entryConfMail = '".$MYSQL['entryConfMail']."',
                    entryUsername = '".$username."', entryUserId = '".$userId."' WHERE id = '".$id_all."'";
                $result = mysql_query($SQL_Befehl_Write, $dbId);
                $quantity = mysql_affected_rows($dbId);
                if( ($quantity != 0) && ($result != 0) )
                {
                    // Daten für Seite 3 bereitstellen
                    $titel_pdf = "Neu Aufgenommene aus der Warteliste ".$listName;
                    $titel = "<h1>".$titel_pdf."</h1>";
                    $headline_pdf = "Aufnahmezeit : ".date("d.m.Y  H:i:s").";Aufnehmender : ".$username.";Antwortdatum : ".$_POST['answerdate'].
                        ";Schwimmstart : ".$_POST['startdate'].";";
                    $headline = "<p><b>".str_replace(';','<br/>',$headline_pdf)."</b></p>";
                    $textBefore .= "<p><input class='button' type='button' name='to_start' value='weitere&#10;aufnehmen'
                        onclick=\"window.location.href='".$script_url."'\"/></p>";
                    $result_view = mysql_query("SELECT * FROM wtl_members WHERE entryId != '' AND id = '".$id_all."'", $dbId);
                    // email vorbereiten
                    $PhFix = array('#MELDEDATUM#','#VORNAME#','#NACHNAME#','#GEBDATUM#','#LISTENNAME#','#DLRGNAME#',
                        '#STARTDATUM#','#ANTWORTDATUM#','#AUFNEHMER#','#AUFNEHMERMAIL#','#AUFNEHMERTEL#','#BESTAETIGUNGSLINK#');
                    $matchcount = preg_match_all('/#\w+#/',$entryMail,$matches);
                    $fieldmatches = array_diff($matches[0],$PhFix);
                    if( count($fieldmatches) > 0 )
                    {
                        $field = array();
                        foreach( $fieldmatches as $setName )
                        {
                            $result = mysql_query("SELECT id, setNo, xChecked, fieldType FROM wtl_fields WHERE isSet = '1' AND
                                setName = '".trim($setName,'#')."'", $dbId);
                            $field[] = mysql_fetch_row($result);
                        }
                    }
                    while( $daten = mysql_fetch_object($result_view) )
                    {
                        $confirmLink = '<http://www.'.str_replace(array('www.','http://'),'',$_SERVER['SERVER_NAME']).
                            substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'/',1)+1).$GLOBALS['SYSTEM_SETTINGS']['WTL_REGISTER_URL'].
                            $listID.'&data=confirm&entryToken='.base64_encode($daten->registerId.$daten->entryId).'>';
                        $fixReplace = array(date('d.m.Y',$daten->tstamp),$daten->firstname,$daten->lastname,date('d.m.Y',$daten->dateOfBirth),
                            $listName,$dlrgName,date('d.m.Y',$daten->startTstamp),date('d.m.Y',$daten->answerTstamp),
                            $username,$usermail,$userphone,$confirmLink);
                        $mailtext = str_replace($PhFix,$fixReplace,$entryMail);
                        $fc = 0;
                        while( count($field) > $fc )
                        {
                            if( $field[$fc][2] == '1' )
                            {
                                if( $field[$fc][3] == 'input' )
                                {
                                    $value = $MYSQL[$field[$fc][0]];
                                }
                                else
                                {
                                    $result = mysql_query("SELECT dataLabel FROM wtl_fields WHERE isSet != '1' AND
                                        setNo = '".$field[$fc][1]."' AND data = '".$MYSQL[$field[$fc][0]]."'", $dbId);
                                    $label = mysql_fetch_row($result);
                                    $value = $label[0];
                                }
                            }
                            else
                            {
                                $result = mysql_query("SELECT inputs, selected FROM wtl_members WHERE id = '".$daten->id."'", $dbId);
                                $data_m = mysql_fetch_row($result);
                                if( $field[$fc][3] == 'input' )
                                {
                                    $inputs = parse_inputs($data_m[0]);
                                    $value = $inputs[$field[$fc][0]];
                                }
                                else
                                {
                                    $inputs = parse_inputs($data_m[1]);
                                    $result = mysql_query("SELECT dataLabel FROM wtl_fields WHERE isSet != '1' AND
                                        setNo = '".$field[$fc][1]."' AND data = '".$inputs[$field[$fc][0]]."'", $dbId);
                                    $label = mysql_fetch_row($result);
                                    $value = $label[0];
                                }
                            }
                            $varReplace[] = $value;
                            $fc++;
                        }
                        $mailtext = str_replace($fieldmatches,$varReplace,$mailtext);
                        send_mail($mailadress,$daten->mail,$dlrgName.' Aufnahme aus der Wartelist '.$listName,$mailtext);
                    }
                    if( preg_match('/#BESTAETIGUNGSLINK#/',$entryMail) == 1 )
                    {
                        $confirmedLink = '<http://www.'.str_replace(array('www.','http://'),'',$_SERVER['SERVER_NAME']).
                            substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'/',1)+1).$GLOBALS['SYSTEM_SETTINGS']['WTL_CONFIRMED_URL'].
                            '&confirmedId='.$_POST['entryId'].'>';
                        $mailtext = "Hallo ".$username."\n\nMit dem folgenden Link kannst Du die Rückmeldungen Deiner Aufnahme vom ".date('d.m.Y').
                            " aus der Warteliste ".$listName." einsehen:\n".$confirmedLink."\n\nACHTUNG: Dieser Link funktioniert nur dann korrekt,".
                            " wenn du NICHT in der Wartelistensystem angemeldet bist!\n\n\n".
                            "Diese Mail erhältst Du aufgrund einer Aufnahme aus dem Wartelistensystem der ".$dlrgName;
                        send_mail($mailadress,$usermail,$dlrgName.' Deine Aufnahmen aus der Warteliste '.$listName,$mailtext);
                        $headline .= "<p><b>Dir wurde eine Mail zugesandt, mit der Du die Rückmeldungen zu dieser Aufnahme einsehen kannst.</b></p>";
                    }
                    if( $quantity == 1 )
                    {
                        $text = "An die folgende Person wurde eine Benachrichtigungs-Mail verschickt,;".
                            "und sie wurde aus der Warteliste ausgetragen :";
                        $headline_pdf .= $text;
                        $headline .= "<p><b>".str_replace(';','<br/>',$text)."</b></p>";
                    }
                    else
                    {
                        $text = "An die folgenden ".$quantity." Personen wurden Benachrichtigungs-Mails verschickt,;".
                            "und sie wurden aus der Warteliste ausgetragen :";
                        $headline_pdf .= $text;
                        $headline .= "<p><b>".str_replace(';','<br/>',$text)."</b></p>";
                    }
                    $displayMessage = TRUE;
                    mysql_data_seek($result_view,0);
                    $buttons = array();
                    wtl_make_site_view($dbId,'ENTRY',$result_view,$listID,$quantity,'1','+1',$titel,$headline,$textBefore,'',$buttons);
                }
            }
        }

        // Seitentext
        if( !$displayMessage )
        {
            $message = "<h1>Aufnahme aus der Warteliste ".$listName."</h1>
                <p>Es müssen alle Felder dieses Formulars ausgefüllt werden!<br/>
                Das Schwimmstartdatum sollte min.14 Tage in der Zukunft liegen.<br/>
                Das Antwortdatum und das Schwimmstartdatum sollten min.7 Tage auseinander liegen.<br/>
                Es können max. ".$entryLimit." Kinder auf einmal aufgenommen werden.";
            $message .= "</p>
                <p>Bei Fragen zum Ausfüllen des Formulars oder sonstigen Schwierigkeiten wende Dich bitte an deinen Webmaster.</p>";
        }
        // nach erfolgter Auswahl
        if( ((isset($_POST['sendSelected']) && $listID) || $listID) )
        {
            // HTML Seite bauen
            // Text der Seite
            echo $message;
            // Formular, wenn nicht nur message anzeigen
            if( !$displayMessage )
            {
                // Formularfelder bauen und ausfüllen
                echo "
                <form name='wtl_entry_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                <div class='border'>
                <table>
                    <tr>
                        <td colspan='3'>".$errorMessage."</td>
                    </tr>
                    <tr>
                        <td>Aufnehmender :</td>
                        <td colspan='2'><b>".$username."</b></td>
                    </tr>
                ";
                if( $connectFields )
                {
                    // Altersauswahl
                    echo"<tr>";
                        $result = mysql_query("SELECT setNo, caption FROM wtl_fields WHERE id = '".$connectFields[0]['Age']."'",$dbId);
                        while( $daten = mysql_fetch_object($result) )
                        {
                            echo "<td>".$daten->caption." :</td>";
                            $setNo = $daten->setNo;
                        }
                        echo "
                            <td colspan='2'><select name='age' class='".$fieldClass['age']."' size='3' title='".$errorTitle['age']."'>";
                            $SQL_Befehl_Read = "SELECT data, dataLabel FROM wtl_fields WHERE isSet != '1' AND setNo = ".$setNo."
                                ORDER BY dataLabel ASC";
                            $result = mysql_query($SQL_Befehl_Read, $dbId);
                            while( $daten = mysql_fetch_object($result) )
                            {
                                echo "<option ";if($_POST['age']==$daten->data){echo "selected='selected'";}
                                echo" value='".$daten->data."'>".$daten->dataLabel."</option>";
                            }
                            echo "
                            </select></td>
                        </tr>";
                    // Auswahlfelder
                    foreach( $connectFields[1] as $id )
                    {
                        echo"<tr>";
                            $result = mysql_query("SELECT setNo, caption FROM wtl_fields WHERE id = '".$id."'",$dbId);
                            $field = mysql_fetch_row($result);
                            echo "<td>".$field[1]." :</td>";
                            echo "
                                <td colspan='2'><select name='".$id."' class='".$fieldClass[$id]."' size='3' title='".$errorTitle[$id]."'>";
                                $SQL_Befehl_Read = "SELECT data, dataLabel FROM wtl_fields WHERE isSet != '1' AND setNo = '".$field[0]."'
                                    ORDER BY id ASC";
                                $result = mysql_query($SQL_Befehl_Read, $dbId);
                                while( $daten = mysql_fetch_object($result) )
                                {
                                    echo "<option ";if($_POST[$id]==$daten->data){echo "selected='selected'";}
                                    echo" value='".$daten->data."'>".$daten->dataLabel."</option>";
                                }
                                echo "
                                </select></td>
                            </tr>";
                    }
                }
                echo "
                    <tr>
                        <td>Antwortdatum (TT.MM.JJJJ):</td>
                        <td colspan='2'><input class='".$fieldClass['answerdate']."' type='text' name='answerdate' size='10'
                            title='".$errorTitle['answerdate']."' value='".$_POST['answerdate']."'/></td>
                    </tr>
                ";
                if( $min_answerdate )
                {
                  echo "
                    <tr>
                        <td>Antwortdatum bestätigt:</td>
                        <td><input type='checkbox' name='answerdate_ok' ";if($_POST['answerdate_ok']==TRUE){echo "checked='checked'";}
                        echo"/></td>
                    </tr>
                  ";
                }
                echo "
                    <tr>
                        <td>Trainingsstart (TT.MM.JJJJ):</td>
                        <td colspan='2'><input class='".$fieldClass['startdate']."' type='text' name='startdate' size='10'
                            title='".$errorTitle['startdate']."' value='".$_POST['startdate']."'/></td>
                    </tr>
                    <tr>
                        <td>Anzahl :</td>
                        <td colspan='2'><input class='".$fieldClass['limit']."' type='text' name='limit' size='6'
                            title='".$errorTitle['limit']."' value='".$_POST['limit']."'/></td>
                    </tr>
                    <tr>
                        <td><input name='entryId' type='hidden' value='".$_POST['entryId']."'/>
                            <input name='listId' type='hidden' value='".$listID."'/></td>
                        <td colspan='2'><input class='button' type='submit' name='preview' value='Vorschau'/>
                                        <input class='button' type='reset' name='cancel' value='Abbrechen' 
                                        onclick=\"location.href='".$script_url."'\"/></td>
                    </tr>
                </table>
                </div>
                </form>
                ";
            }
        }
    }

    // Wartelistenauswahlformular
    if( (!isset($_POST['sendSelected'])) && (!isset($_POST['preview'])) && (!isset($_POST['register'])) )
    {
        echo "<h1>Aufnehmen aus einer Warteliste</h1>";
        select_list_formular($dbId,'wtl_lists','Eine Warteliste auswählen','Warteliste');
    }
    else
    {
        if( isset($_POST['sendSelected']) && ($authority !== TRUE) )
        {
            echo "<h1>Aufnehmen aus der Warteliste ".$listName."</h1>
                <p><b>Du hast keine Berechtigung zum aufnehmen aus dieser Warteliste!</b></p>";
        }
    }
    echo "</div></div>";
?>