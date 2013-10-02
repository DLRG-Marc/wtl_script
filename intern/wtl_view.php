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
 * @WTL version  1.5.0
 * @date - time  01.10.2013 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_sets.php');
    require_once('f_wtl.php');
    $listID = mysql_real_escape_string($_GET['listID']);
    $delID = mysql_real_escape_string($_GET['delID']);
    $mailID = mysql_real_escape_string($_GET['mailID']);
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

    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }
    if( empty($listID) )
    {
        $listID = $MYSQL['listId'];
    }

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','viewAuth',$listID);
    $authorityDelete = checkAuthority($dbId,'wtl_user','deleteAuth',$listID);
    $authorityMail = checkAuthority($dbId,'wtl_user','admin',$listID);

    echo "<div id='wtl_view'>
          <div class='waitinglist'>";
    // Daten der wtl_lists lesen
    $result = mysql_query("SELECT * FROM wtl_lists WHERE id = '".$listID."'",$dbId);
    while( $daten = mysql_fetch_object($result) )
    {
        $listName = $daten->setName;
        $dlrgName = $daten->dlrgName;
        $mailadress = $daten->mailadress;
        $registerMail = html_entity_decode($daten->registerMail,ENT_QUOTES,'UTF-8');
    }

    if( $authority === TRUE )
    {
        // nach Anmeldemail erneut senden
        if( (!empty($mailID)) && ($authorityMail === TRUE) )
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
        if( (!empty($delID)) && ($authorityDelete === TRUE) )
        {
            echo "<h1>Person aus der Warteliste ".$listName." löschen</h1>";
            $result = mysql_query("SELECT * FROM wtl_members WHERE id = '".$delID."'",$dbId);
            $quantity = mysql_num_rows($result);
            $headline = "<p><b>Willst Du diese Person wirklich aus der Warteliste löschen ?</b></p>";
            $buttons = "<p><input class='button' type='submit' name='sendDelete' value='Löschen'/>
                <input class='button' type='button' name='cancel' value='Abbrechen' onclick=\"location.href='".$script_url."&amp;listID=".$listID."'\"/>
                <input name='deleteId' type='hidden' value='".$delID."'/>
                <input name='listId' type='hidden' value='".$listID."'/></p>";
            wtl_make_site_view($dbId,'REGISTER',$result,$listID,$quantity,$quantity,'-1','',$headline,$buttons,FALSE,FALSE);
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
            $result = mysql_query($SQL_Befehl_Write,$dbId);
            if( mysql_affected_rows($dbId) == 1)
            {
                echo "<p><b>Die Person wurde aus der Warteliste gelöscht !</b></p>";
            }
            else
            {
                echo "<p><b>Das Löschen ist fehlgeschlagen !<br/>Bitte wende Dich an den Webmaster.</b></p>";
            }
        }
        // nach erfolgter Auswahl
        if( (!empty($listID)) && (empty($delID)) && !(isset($_POST['sendDelete'])) )
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
            $headline = "Einen Wartenden suchen";
            $hiddenFields = "<input name='listId' type='hidden' value='".$listID."'/>";
            $condition = "entryId = '' AND deleted != '1'";
            $result = searchText('wtl_members',$headline,$hiddenFields,$condition,'lastname','firstname','Nachname','Vorname');
            // wenn suchen aktiviert
            if( isset($_POST['search']) )
            {
                $quantity = mysql_num_rows($result);
                if( $quantity == 1)
                {
                    $headline_pdf = 'Die folgende Person entspricht den Suchkriterien :';
                }
                else
                {
                    $headline_pdf = 'Die folgenden '.$quantity.' Personen entsprechen den Suchkriterien :';
                }
            }
            else
            {
                $headline_pdf = '';
                $result = mysql_query("SELECT * FROM wtl_members WHERE entryId = '' AND deleted != '1' AND listId = '".$listID."'
                    ORDER by tstamp DESC, id DESC",$dbId);
                $quantity = mysql_num_rows($result);
                if( $quantity == 1)
                {
                    $headline_pdf = "Die folgende Person wartet auf Aufnahme:";
                }
                else
                {
                    $headline_pdf = "Die folgenden ".$quantity." Personen warten auf Aufnahme:";
                }
            }
            $headline = "<p><b>".$headline_pdf."</b></p>";
            // Seitenaufbau aufrufen
            wtl_make_site_view($dbId,'REGISTER',$result,$listID,$quantity,$quantity,'-1','',$headline,$buttons,$authorityDelete,$authorityMail);
        }
    }

    if( (empty($listID)) && (empty($delID)) && (!isset($_POST['sendDelete'])) )
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