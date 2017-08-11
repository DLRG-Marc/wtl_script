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
 * @WTL version  1.7.3
 * @date - time  11.08.2017 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_sets.php');
    require_once('f_wtl.php');
    $listID = mysqli_real_escape_string($dbId,$_GET['listID']);
    $delID = mysqli_real_escape_string($dbId,$_GET['delID']);
    $mailID = mysqli_real_escape_string($dbId,$_GET['mailID']);
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $script_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $script_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }
    $authority = FALSE;
    $authorityDelete = FALSE;
    $authorityMail = FALSE;
    $username = $_SESSION['intern']['realname'];
    $buttons = array();
    $textBefore = '';

    // Auswahlarray sichern bevor durch Cross-Side Script schutz zerstört
    $_POST['selected'] = serialize($_POST['selected']);
    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysqli_real_escape_string($dbId,$_POST[$index]);
    }
    $_POST['selected'] = unserialize($_POST['selected']);
    if( empty($listID) )
    {
        $listID = $MYSQL['listId'];
    }

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','viewAuth',$listID);
    $authorityDelete = checkAuthority($dbId,'wtl_user','deleteAuth',$listID);
    $authorityMail = checkAuthority($dbId,'wtl_user','admin',$listID);

    if( $authorityDelete === TRUE )
    {
        $buttons[] = array('headline'=>'', 'class'=>'button', 'name'=>'predelete', 'value'=>'Person löschen',
                            'action'=>array('value'=>$script_url."&amp;listID=".$listID."&amp;delID=", 'mysqlcol'=>'id'));
    }
    if( $authorityMail === TRUE )
    {
        $buttons[] = array('headline'=>'', 'class'=>'button_long', 'name'=>'sendmail', 'value'=>'Anmeldemail erneut senden',
                            'action'=>array('value'=>$script_url."&amp;listID=".$listID."&amp;mailID=", 'mysqlcol'=>'id'));
    }

    echo "<div id='wtl_view'>
          <div class='waitinglist'>";
    // Daten der wtl_lists lesen
    $result = mysqli_query($dbId,"SELECT setName, dlrgName, mailadress, registerMail, infoMail FROM wtl_lists WHERE id = '".$listID."'");
    while( $daten = mysqli_fetch_object($result) )
    {
        $listName = $daten->setName;
        $dlrgName = $daten->dlrgName;
        $mailadress = $daten->mailadress;
        $registerMail = html_entity_decode($daten->registerMail,ENT_QUOTES,'UTF-8');
        $infoMail = html_entity_decode($daten->infoMail,ENT_QUOTES,'UTF-8');
    }

    if( $authority === TRUE )
    {
        // nach verschieben in andere Liste vorbereiten
        if( isset($_POST['preMoveList']) && ($authorityMail === TRUE) )
        {
            echo "<h1>Personen in eine andere Warteliste verschieben</h1>";
            $id_all = array_to_text_with_trenner($_POST['selected'], "' OR id = '");
            $result = mysqli_query($dbId,"SELECT * FROM wtl_members WHERE listId = '".$listID."' AND (id = '".$id_all."')");
            $quantity = mysqli_num_rows($result);
            $headline = "<p><b>Diese Personen werden in die folgende auszuwählende Warteliste verschoben:</b></p>";
            $textBefore = "<p><select name='newListID' size='1'>";
            $result_lists = mysqli_query($dbId,"SELECT id, setName FROM wtl_lists WHERE id != '".$listID."'");
            while( $data = mysqli_fetch_object($result_lists) )
            {
                $textBefore .= "<option value='".$data->id."'>".$data->setName."</option>";
            }
            $textBefore .= "</select></p>
                <p><input class='button' type='submit' name='sendMoveList' value='verschieben'/>
                <input class='button' type='button' name='cancel' value='Abbrechen' onclick=\"location.href='".$script_url."&amp;listID=".$listID."'\"/>
                <input name='listId' type='hidden' value='".$listID."'/>
                <input name='selected' type='hidden' value='".serialize($_POST['selected'])."'/></p>";
            $buttons = array();
            wtl_make_site_view($dbId,'REGISTER',$result,$listID,$quantity,$quantity,-1,'',$headline,$textBefore,'',$buttons);
        }

        // nach verschieben bestätigen
        if( isset($_POST['sendMoveList']) && ($authorityMail === TRUE) )
        {
            echo "<h1>Personen in eine andere Warteliste verschieben</h1>";
            $result_lists = mysqli_query($dbId,"SELECT setName FROM wtl_lists WHERE id = '".$MYSQL['newListID']."'");
            $newListName = mysqli_fetch_row($result_lists);
            $id_all = array_to_text_with_trenner(unserialize(stripslashes($_POST['selected'])), "' OR id = '");
            $SQL_Befehl_Write = "UPDATE wtl_members SET listId = '".$MYSQL['newListID']."', lastEditor = '".$username."'
                WHERE id = '".$id_all."'";
            $result = mysqli_query($dbId,$SQL_Befehl_Write);
            $quantity = mysqli_affected_rows($dbId);
            if( $quantity >= 0 )
            {
                echo "<p><b>Es wurden ".$quantity." Personen in die Warteliste ".$newListName[0]." verschoben.</b></p>";
                echo "<p><a href='".$script_url."'>zurück zur Wartelistenauswahl.</a></p>";
            }
            else
            {
                echo "<p><b>Das Verschieben ist fehlgeschlagen !<br/>Bitte wende Dich an den Webmaster.</b></p>";
            }
        }

        // Email vorbereiten
        if( isset($_POST['writeMail']) && ($authorityMail === TRUE) )
        {
            if( isset($_POST['selected']) )
            {
                echo "<h1>Email vorbereiten</h1>";
                echo "<p>Emailtext:</p>";
                echo "<form name='mailwrite_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>";
                echo "<p><textarea class='".$fieldClass['infoMail']."' name='infoMail' cols='34' rows='5'
                    title='".$errorTitle['infoMail']."'>".$infoMail."</textarea></p>";
                echo "<p><input class='button' type='submit' name='saveMail' value='Mail speichern'/>
                    <input class='button' type='button' name='cancel' value='Abbrechen' onclick=\"location.href='".$script_url."&amp;listID=".$listID."'\"/>
                    <input name='listId' type='hidden' value='".$listID."'/>
                    <input name='selected' type='hidden' value='".serialize($_POST['selected'])."'/></p>";
                echo "</form>";
            }
            else
            {
                echo "<h1>Personen eine Email schreiben</h1>";
                echo"<p><b>Es sind wurden keine Daten ausgewählt !</b></p>";
                echo "<p><a href='".$script_url."'>zurück zur Wartelistenauswahl.</a></p>";
            }
        }

        // Email in DB speichern und Vorschau
        if( isset($_POST['saveMail']) && ($authorityMail === TRUE) )
        {
            echo "<h1>Personen eine Email senden</h1>";
            $SQL_Befehl_Write = "UPDATE wtl_lists SET infoMail = '".$MYSQL['infoMail']."' WHERE id = '".$listID."'";
            $result = mysqli_query($dbId,$SQL_Befehl_Write);
            $infoMail = html_entity_decode($_POST['infoMail'],ENT_QUOTES,'UTF-8');
            if( $result != FALSE )
            {
                $id_all = array_to_text_with_trenner(unserialize(stripslashes($_POST['selected'])), "' OR id = '");
                $result_view = mysqli_query($dbId,"SELECT * FROM wtl_members WHERE listId = '".$listID."' AND (id = '".$id_all."')");
                $quantity = mysqli_num_rows($result_view);
                $headline = "<p><b>An diese Personen wird die folgende Email gesendet:<br>Infomail am Beispiel des ersten Empfängers.</b></p>";
                // Daten der wtl_user lesen
                $result_user = mysqli_query($dbId,"SELECT id, mail, phone FROM wtl_user WHERE id = '".$_SESSION['intern']['userId']."'");
                while( $daten = mysqli_fetch_object($result_user) )
                {
                    $usermail = $daten->mail;
                    $userphone = $daten->phone;
                }
                $sendOK = send_entry_mail($dbId,FALSE,$listID,$MYSQL,$infoMail,$mailadress,$result_view,'Information zur Warteliste',$listName,$dlrgName,$username,$usermail,$userphone);
                $headline .= '<p>'.$sendOK[0].'<br>';
                $headline .= $sendOK[1].'</p>';
                $textBefore .= "<p><input class='button' type='submit' name='sendMail' value='Mail senden'/>
                    <input class='button' type='button' name='cancel' value='Abbrechen' onclick=\"location.href='".$script_url."&amp;listID=".$listID."'\"/>
                    <input name='listId' type='hidden' value='".$listID."'/>
                    <input name='selected' type='hidden' value='".stripslashes($_POST['selected'])."'/></p>";
                mysqli_data_seek($result_view,0);
                wtl_make_site_view($dbId,'REGISTER',$result_view,$listID,$quantity,$quantity,-1,'',$headline,$textBefore,'',$buttons);
            }
            else
            {
                echo "<p><b>Fehler beim speichern der Mail !</b></p>";
                echo "<p><a href='".$script_url."'>zurück zur Wartelistenauswahl.</a></p>";
            }
        }

        // Mail senden
        if( isset($_POST['sendMail']) && ($authorityMail === TRUE) )
        {
            // Daten der wtl_user lesen
            $result = mysqli_query($dbId,"SELECT id, mail, phone FROM wtl_user WHERE id = '".$_SESSION['intern']['userId']."'");
            while( $daten = mysqli_fetch_object($result) )
            {
                $usermail = $daten->mail;
                $userphone = $daten->phone;
            }
            echo "<h1>Personen eine Infomail zusenden</h1>";
            $id_all = array_to_text_with_trenner(unserialize(stripslashes($_POST['selected'])), "' OR id = '");
            $result_view = mysqli_query($dbId,"SELECT * FROM wtl_members WHERE listId = '".$listID."' AND (id = '".$id_all."')");
            $sendOK = send_entry_mail($dbId,TRUE,$listID,$MYSQL,$infoMail,$mailadress,$result_view,'Information zur Warteliste',$listName,$dlrgName,$username,$usermail,$userphone);
            if( ($sendOK[0] == TRUE) && ($sendOK[1] > 0) )
            {
                echo "<p><b>Es wurde ".$sendOK[1]." Personen eine Infomail gesendet.</b></p>";
            }
            else
            {
                echo "<p><b>Das Senden der Infomail ist fehlgeschlagen !<br/>Bitte wende Dich an den Webmaster.</b></p>";
            }
        }

        // nach Anmeldemail erneut senden
        if( !empty($mailID) && ($authorityMail === TRUE) )
        {
            $sendOK = send_register_mail($dbId,$mailID,$mailadress,$registerMail,$dlrgName,$listName);
            echo "<h1>Anmeldemail erneut zusenden</h1>";
            if( $sendOK[0] === TRUE )
            {
                echo "<p><b>Die Anmeldemail für ".$sendOK[1]." wurde erfolgreich abgesendet.</b></p>";
            }
            else
            {
                echo "<p><b>Das Senden der Anmeldemail für ".$sendOK[1]." ist fehlgeschlagen!</b></p>";
            }
        }

        // nach löschen vorbereiten
        if( !empty($delID) && !isset($_POST['sendDelete']) && ($authorityDelete === TRUE) )
        {
            echo "<h1>Person aus der Warteliste ".$listName." löschen</h1>";
            $result = mysqli_query($dbId,"SELECT * FROM wtl_members WHERE id = '".$delID."'");
            $quantity = mysqli_num_rows($result);
            $headline = "<p><b>Willst Du diese Person wirklich aus der Warteliste löschen ?</b></p>";
            $textBefore = "<p><input class='button' type='submit' name='sendDelete' value='Löschen'/>
                <input class='button' type='button' name='cancel' value='Abbrechen' onclick=\"location.href='".$script_url."&amp;listID=".$listID."'\"/>
                <input name='deleteId' type='hidden' value='".$delID."'/>
                <input name='listId' type='hidden' value='".$listID."'/></p>";
            $buttons = array();
            wtl_make_site_view($dbId,'REGISTER',$result,$listID,$quantity,1,-1,'',$headline,$textBefore,'',$buttons);
        }
        if( (!empty($delID)) && ($authorityDelete !== TRUE) )
        {
            echo "<h1>Personen aus der Warteliste '".$listName."' löschen</h1>
                <p><b>Du hast keine Berechtigung zum Löschen aus dieser Warteliste!</b></p>";
        }

        // nach löschen bestätigen
        if( isset($_POST['sendDelete']) )
        {
            echo "<h1>Person aus der Warteliste ".$listName." löschen</h1>";
            $SQL_Befehl_Write = "UPDATE wtl_members SET deleted = '1', lastEditor = '".$username."'
                WHERE id = '".$MYSQL['deleteId']."'";
            $result = mysqli_query($dbId,$SQL_Befehl_Write);
            if( mysqli_affected_rows($dbId) == 1)
            {
                echo "<p><b>Die Person wurde aus der Warteliste gelöscht !</b></p>";
            }
            else
            {
                echo "<p><b>Das Löschen ist fehlgeschlagen !<br/>Bitte wende Dich an den Webmaster.</b></p>";
            }
        }

        // nach erfolgter Auswahl
        if( !empty($listID) && empty($delID) && empty($mailID) && !isset($_POST['sendDelete']) && !isset($_POST['preMoveList'])
            && !isset($_POST['sendMoveList']) && !isset($_POST['writeMail']) && !isset($_POST['saveMail']) && !isset($_POST['sendMail']) )
        {
            if( isset($_POST['search']) )
            {
                $titel = "Suchergebnis aus der Warteliste ".$listName;
            }
            else
            {
                $titel = "Ansicht aller Personen der Warteliste ".$listName;
            }
            echo "<h1>".$titel."</h1>";
            $hiddenFields = "<input name='listId' type='hidden' value='".$listID."'/>";
            $condition = "entryId = '' AND deleted != '1'";
            $result = searchText('wtl_members','Einen Wartenden suchen',$hiddenFields,$condition,'lastname','firstname','Nachname','Vorname');
            // wenn suchen aktiviert
            if( isset($_POST['search']) )
            {
                $quantity = mysqli_num_rows($result);
                if( $quantity == 1)
                {
                    $headline = 'Die folgende Person entspricht den Suchkriterien :';
                }
                else
                {
                    $headline = 'Die folgenden '.$quantity.' Personen entsprechen den Suchkriterien :';
                }
            }
            else
            {
                $result = mysqli_query($dbId,"SELECT * FROM wtl_members WHERE entryId = '' AND deleted != '1' AND listId = '".$listID."'
                    ORDER by tstamp DESC, id DESC");
                $quantity = mysqli_num_rows($result);
                if( $quantity == 1)
                {
                    $headline = "Die folgende Person wartet auf Aufnahme:";
                }
                else
                {
                    $headline = "Die folgenden ".$quantity." Personen warten auf Aufnahme:";
                }
            }
            $headline = "<p><b>".$headline."</b></p>";
            // Seitenaufbau aufrufen
            if( $authorityMail === TRUE )
            {
                // mit Funktion in andere Warteliste verschieben
                $textBefore = "<p><input class='button_long' type='submit' name='preMoveList' value='Ausgewählte in andere&#xA;Warteliste verschieben'/>
                                  <input name='listId' type='hidden' value='".$listID."'/>";
                $textBefore .= "<input class='button_long' type='submit' name='writeMail' value='Ausgewählten eine&#xA;Email schreiben'/>
                                  <input name='listId' type='hidden' value='".$listID."'/></p>";
                wtl_make_site_view($dbId,'MOVELIST',$result,$listID,$quantity,$quantity,-1,'',$headline,$textBefore,'',$buttons);
            }
            else
            {
                wtl_make_site_view($dbId,'REGISTER',$result,$listID,$quantity,$quantity,-1,'',$headline,$textBefore,'',$buttons);
            }
        }
    }

    if( empty($listID) && empty($delID) && !isset($_POST['sendDelete']) )
    {
        if( isset($_POST['sendSelected']) && ($authority !== TRUE) )
        {
            echo "<h1>Ansicht der Warteliste ".$listName."</h1>
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