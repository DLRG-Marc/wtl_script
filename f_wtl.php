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
 * @WTL version  1.4.3
 * @date - time  01.10.2013 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


function wtl_make_site_view($dbId,$site,$result,$listId,$quantity,$number,$updown,$titel,$headline,$button,$delButton,$mailButton)
{
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $script_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $script_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }
    echo "<div class='wtl_moveable_table'>";
    echo $titel;
    if( ($quantity != 0) && $result )
    {
        echo $headline;
        // Daten für html-Tabelle (Überschrift)
        // Felder die immer angezeigt werden
        $rows = array();
        switch( $site )
        {
            case 'REGISTER':
                $rows[0] = array('Nr','Datum','Name','Vorname','Alter');
                $listedFields = 'viewRegister';
            break;
            case 'ENTRY':
                $rows[0] = array('','Nr','Datum','Name','Vorname','Alter');
                $listedFields = 'viewEntry';
            break;
            case 'STATISTIC':
                $rows[0] = array('Nr','Anzahl','Datum','Aufnehmender');
                $listedFields = 'viewStatistic';
                $exportButton = TRUE;
            break;
            case 'STATISTIC_DETAILS':
                $rows[0] = array('Nr','Eingetragen','Name','Vorname','Antw.','e-mail');
                $listedFields = 'viewStatDetails';
            break;
        }
        // Felder die zum anzeigen ausgewählt wurden
        $returnValues = namesFromSelFields($dbId,'wtl',$listId,$listedFields,$rows[0]);
        $rows[0] = $returnValues[1];
        // ggf. Leerfeld für Button zum Löschen
        if( ($delButton === TRUE) || ($exportButton === TRUE) )
        {
            $rows[0][] = '';
        }
        if( $mailButton === TRUE )
        {
            $rows[0][] = '';
        }
        // Daten für html-Tabelle (Inhalt)
        // Felder die immer angezeigt werden
        $i = 1;
        while( $daten = mysql_fetch_object($result) )
        {
            switch( $site )
            {
                case'REGISTER':
                    $rows[$i] = array($number,date('d.m.y',$daten->tstamp),"<a href='".substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'=')).
                        "=wtl_view_member&amp;data=view&amp;listID=".$listId."&amp;memberID=".$daten->id."'>".htmlspecialchars_decode($daten->lastname)."</a>",
                        htmlspecialchars_decode($daten->firstname),calcAge(date('Y-m-d',$daten->dateOfBirth)));
                break;
                case 'ENTRY':
                    $rows[$i] = array("<input type='checkbox' name='selected[]' value='".$daten->id."' checked='checked'/>",$number,
                        date('d.m.y',$daten->tstamp),htmlspecialchars_decode($daten->lastname),htmlspecialchars_decode($daten->firstname),
                        calcAge(date('Y-m-d',$daten->dateOfBirth)));
                break;
                case 'STATISTIC':
                    $result_count = mysql_query("SELECT COUNT(*) FROM wtl_members WHERE deleted != '1' AND entryId = '".$daten->entryId."'");
                    $quantityStatisticArray = mysql_fetch_row($result_count);
                    $rows[$i] = array($number,"<a href='".$script_url."&amp;listId=".$listId."&amp;detno=".$number."&amp;entryId=".$daten->entryId."'>".
                        $quantityStatisticArray[0]."&nbsp;&nbsp;(Details)</a>",date('d.m.y',$daten->entryTstamp),htmlspecialchars_decode($daten->entryUsername));
                break;
                case 'STATISTIC_DETAILS':
                    $rows[$i] = array($number,date('d.m.y',$daten->tstamp),"<a href='".substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'=')).
                    "=wtl_view_member&amp;data=view&amp;listID=".$listId."&amp;memberID=".$daten->id."'>".htmlspecialchars_decode($daten->lastname)."</a>",
                    htmlspecialchars_decode($daten->firstname),number_to_janein($daten->confirm),htmlspecialchars_decode($daten->mail));
                break;
            }
            // Felder die zum anzeigen ausgewählt wurden
            $viewFields = array_merge(explode('##',$daten->inputs),explode('##',$daten->selected));
            $rows[$i] = dataFromSelFields($dbId,'wtl',$returnValues[0],$viewFields,$rows[$i]);
            if( $delButton === TRUE )
            {
                $rows[$i][] = "<input class='button' type='button' name='predelete' value='Person löschen' onclick=\"location.href='".$script_url."&amp;listID=".$listId."&amp;delID=".$daten->id."'\"/>";
            }
            if( $exportButton === TRUE )
            {
                $rows[$i][] = "<input class='button' type='button' name='export' value='exportieren' onclick=\"location.href='".str_replace('wtl_stat','wtl_upload',$_SERVER['REQUEST_URI'])."&amp;listID=".$listId."&amp;entryID=".$daten->entryId."'\"/>";
            }
            if( $mailButton === TRUE )
            {
                $rows[$i][] = "<input class='button_long' type='button' name='sendmail' value='Anmeldemail erneut senden' onclick=\"location.href='".$script_url."&amp;listID=".$listId."&amp;mailID=".$daten->id."'\"/>";
            }
            $number = $number + $updown;
            $i++;
        }
        echo "<form name='preview_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>";
        makeTable($rows,$button,'');
        echo "</form>";
    }
    else
    {
        echo"<p><b>Es sind keine Daten vorhanden, die der Anfrage entsprechen !</b></p>";
        echo "<p><a href='".$script_url."'>zurück zur Wartelistenauswahl.</a></p>";
    }
    echo "</div>";
}


function wtl_make_site_confirmed($conf,$result,$quantity,$headline,$button)
{
    if( ($quantity != 0) && $result )
    {
        echo $headline;
        // Daten für html-Tabelle (Überschrift)
        $rows = array();
        $rows[0] = array('Nr','Datum','Name','Vorname','Teilnahme');
        // Daten für html-Tabelle (Inhalt)
        $number = $quantity;
        $i = 1;
        while( $daten = mysql_fetch_object($result) )
        {
            if( $conf == 0 )
            {
                $confdate = '-';
            }
            else
            {
                $confdate = date('d.m.y',$daten->confirmTstamp);
            }
            $rows[$i] = array($number,$confdate,htmlspecialchars_decode($daten->lastname),
                htmlspecialchars_decode($daten->firstname),number_to_janein($daten->confirm));
            $number--;
            $i++;
        }
        makeTable($rows,$button,'');
        echo "<p></p>";
    }
}


function send_register_mail($dbId,$memberID,$senderadress,$registerMail,$dlrgName,$listName)
{
    $retarray = array();
    $result = mysql_query("SELECT * FROM wtl_members WHERE Id = '".$memberID."' AND deleted != '1'",$dbId);
    while( $data = mysql_fetch_object($result) )
    {
        // email vorbereiten
        $mailWildcardArray = array('#VORNAME#','#NACHNAME#','#LISTENNAME#','#MELDEDATUM#','#MELDENR#','#DLRGNAME#');
        $mailVariableArray = array($data->firstname,$data->lastname,$listName,date('d.m.Y',$data->tstamp),$data->registerId,$dlrgName);
        preg_match_all('/#\w+#/',$registerMail,$treffer,PREG_SET_ORDER);
        foreach( $treffer as $wert )
        {
            if( !in_array($wert[0],$mailWildcardArray) )
            {
                $result = mysql_query("SELECT setNo, fieldType FROM wtl_fields WHERE isSet = '1' AND setName = '".trim($wert[0],'#')."'", $dbId);
                while( $daten = mysql_fetch_object($result) )
                {
                    $setNo = $daten->setNo;
                }
                $start = (strpos($data->selected,"#".$setNo.";")+2+strlen($setNo));
                $length = (strpos($data->selected,"#",1)-$start);
                $data_search = substr($data->selected,$start,$length);
                $result_selectField = mysql_query("SELECT dataLabel FROM wtl_fields WHERE setNo = '".$setNo."'
                    AND data = '".$data_search."'", $dbId);
                $selectDataLabelArray = mysql_fetch_row($result_selectField);
                $registerMail = str_replace($wert[0],$selectDataLabelArray[0],$registerMail);
            }
        }
        $mailtext = str_replace($mailWildcardArray,$mailVariableArray,$registerMail);
        $retarray[0] = send_mail($senderadress,$data->mail,$dlrgName.' Wartelisteneintrag '.$listName,$mailtext);
        $retarray[1] = $data->firstname." ".$data->lastname;
    }
    return $retarray;
}
?>