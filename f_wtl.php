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
 * @WTL version  1.7.4
 * @date - time  08.09.2017 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


function wtl_make_site_view($dbId,$site,$result,$listId,$quantity,$number,$updown,$titel,$headline,$textBefore,$textAfter,$buttons)
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
    if( ($quantity != 0) && ($result != FALSE) )
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
            case 'MOVELIST':
                $rows[0] = array('<input type="checkbox" name="checkall" onclick="check_all(\'selected[]\', this)"/></legend>','Nr','Datum','Name','Vorname','Alter');
                $listedFields = 'viewRegister';
            break;
            case 'ENTRY':
                $rows[0] = array('','Nr','Datum','Name','Vorname','Alter');
                $listedFields = 'viewEntry';
            break;
            case 'STATISTIC':
                $rows[0] = array('Nr','Anzahl','Datum','Aufnehmender');
                $listedFields = 'viewStatistic';
            break;
            case 'STATISTIC_DETAILS':
                $rows[0] = array('Nr','Eingetragen','Name','Vorname','Antw.','e-mail');
                $listedFields = 'viewStatDetails';
            break;
        }
        // Felder die zum anzeigen ausgewählt wurden
        $returnValues = namesFromSelFields($dbId,'wtl',$listId,$listedFields,$rows[0]);
        $rows[0] = $returnValues[1];
        // ggf. Leerfelder für Buttons
        foreach( $buttons as $button )
        {
            $rows[0][] = $button['headline'];
        }
        // Daten für html-Tabelle (Inhalt)
        // Felder die immer angezeigt werden
        $i = 1;
        while( $data = mysqli_fetch_object($result) )
        {
            switch( $site )
            {
                case'REGISTER':
                    $rows[$i] = array($number,date('d.m.y',$data->tstamp),"<a href='".substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'=')).
                        "=wtl_view_member&amp;data=view&amp;listID=".$listId."&amp;memberID=".$data->id."'>".htmlspecialchars_decode($data->lastname)."</a>",
                        htmlspecialchars_decode($data->firstname),calcAge(date('Y-m-d',$data->dateOfBirth)));
                break;
                case'MOVELIST':
                    $rows[$i] = array("<input type='checkbox' name='selected[]' value='".$data->id."'/>",$number,date('d.m.y',$data->tstamp),
                        "<a href='".substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'='))."=wtl_view_member&amp;data=view&amp;listID=".$listId.
                        "&amp;memberID=".$data->id."'>".htmlspecialchars_decode($data->lastname)."</a>",htmlspecialchars_decode($data->firstname),
                        calcAge(date('Y-m-d',$data->dateOfBirth)));
                break;
                case 'ENTRY':
                    $rows[$i] = array("<input type='checkbox' name='selected[]' value='".$data->id."' checked='checked'/>",$number,
                        date('d.m.y',$data->tstamp),htmlspecialchars_decode($data->lastname),htmlspecialchars_decode($data->firstname),
                        calcAge(date('Y-m-d',$data->dateOfBirth)));
                break;
                case 'STATISTIC':
                    $result_count = mysqli_query($dbId,"SELECT COUNT(*) FROM wtl_members WHERE deleted != '1' AND entryId = '".$data->entryId."'");
                    $entryNuArray = mysqli_fetch_row($result_count);
                    $entryNu = $entryNuArray[0];
                    $rows[$i] = array($number,"<a href='".$script_url."&amp;listId=".$listId."&amp;detno=".$number."&amp;entryId=".$data->entryId."'>".
                        $entryNu."&nbsp;&nbsp;(Details)</a>",date('d.m.y',$data->entryTstamp),htmlspecialchars_decode($data->entryUsername));
                break;
                case 'STATISTIC_DETAILS':
                    $rows[$i] = array($number,date('d.m.y',$data->tstamp),"<a href='".substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'=')).
                    "=wtl_view_member&amp;data=view&amp;listID=".$listId."&amp;memberID=".$data->id."'>".htmlspecialchars_decode($data->lastname)."</a>",
                    htmlspecialchars_decode($data->firstname),number_to_janein($data->confirm),htmlspecialchars_decode($data->mail));
                break;
            }
            // Felder die zum anzeigen ausgewählt wurden
            $viewFields = array_merge(explode('##',$data->inputs),explode('##',$data->selected));
            $rows[$i] = dataFromSelFields($dbId,'wtl',$returnValues[0],$viewFields,$rows[$i]);
            foreach( $buttons as $button )
            {
                $rows[$i][] = "<input class='".$button['class']."' type='button' name='".$button['name']."' value='".$button['value']."' onclick=\"location.href='".$button['action']['value'].$data->$button['action']['mysqlcol']."'\"/>";
            }
            $number = $number + $updown;
            $i++;
        }
        echo "<form name='preview_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>";
        makeTable($rows,$textBefore,$textAfter);
        echo "</form>";
    }
    else
    {
        echo"<p><b>Es sind keine Daten vorhanden, die der Anfrage entsprechen !</b></p>";
        echo "<p><a href='".$script_url."'>zurück zur Wartelistenauswahl.</a></p>";
    }
    echo "</div>";
}


function wtl_make_site_confirmed($conf,$result,$quantity,$headline,$textBefore)
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
        while( $data = mysqli_fetch_object($result) )
        {
            if( $conf == 0 )
            {
                $confdate = '-';
            }
            else
            {
                $confdate = date('d.m.y',$data->confirmTstamp);
            }
            $rows[$i] = array($number,$confdate,htmlspecialchars_decode($data->lastname),
                htmlspecialchars_decode($data->firstname),number_to_janein($data->confirm));
            $number--;
            $i++;
        }
        makeTable($rows,$textBefore,'');
        echo "<p></p>";
    }
}


function send_register_mail($dbId,$memberID,$senderadress,$registerMail,$dlrgName,$listName)
{
    $retarray = array();
    $result = mysqli_query($dbId,"SELECT * FROM wtl_members WHERE Id = '".$memberID."' AND deleted != '1'");
    while( $data = mysqli_fetch_object($result) )
    {
        // email vorbereiten
        $data_inp_sel = $data->inputs.$data->selected;
        $mailWildcardArray = array('#VORNAME#','#NACHNAME#','#LISTENNAME#','#MELDEDATUM#','#MELDENR#','#DLRGNAME#');
        $mailVariableArray = array($data->firstname,$data->lastname,$listName,date('d.m.Y',$data->tstamp),$data->registerId,$dlrgName);
        $mailtext = str_replace($mailWildcardArray,$mailVariableArray,$registerMail);
        $matchcount = preg_match_all('/#\w+#/',$mailtext,$matches);
        if( ($matchcount > 0) && ($matchcount !== FALSE) )
        {
            foreach( $matches[0] as $value )
            {
                $result = mysqli_query($dbId,"SELECT id, setNo FROM wtl_fields WHERE isSet = '1' AND setName = '".trim($value,'#')."'");
                $field = mysqli_fetch_row($result);
                $start = (strpos($data_inp_sel,"#".$field[0].";")+2+strlen($field[0]));
                $length = (strpos($data_inp_sel,"#",$start+1)-$start);
                $data_search = substr($data_inp_sel,$start,$length);
                $result = mysqli_query($dbId,"SELECT dataLabel FROM wtl_fields WHERE setNo = '".$field[1]."' AND data = '".$data_search."'");
                $label = mysqli_fetch_row($result);
                $varReplace[] = $label[0];
            }
            $mailtext = str_replace($matches[0],$varReplace,$mailtext);
        }
        $retarray[0] = send_mail($senderadress,$data->mail,$dlrgName.' Wartelisteneintrag '.$listName,$mailtext);
        $retarray[1] = $data->firstname." ".$data->lastname;
    }
    return $retarray;
}

function send_entry_mail($dbId,$send,$listID,$MYSQL,$entryMail,$mailadress,$result_view,$subtext,$listName,$dlrgName,$username,$usermail,$userphone)
{
    $retarray = array();
    $count = 1;
    // email vorbereiten
    $PhFix = array('#HEUTE#','#MELDEDATUM#','#VORNAME#','#NACHNAME#','#GEBDATUM#','#LISTENNAME#','#DLRGNAME#',
        '#MELDENR#','#STARTDATUM#','#ANTWORTDATUM#','#AUFNEHMER#','#AUFNEHMERMAIL#','#AUFNEHMERTEL#','#BESTAETIGUNGSLINK#');
    $matchcount = preg_match_all('/#\w+#/',$entryMail,$matches);
    $fieldmatches = array_diff($matches[0],$PhFix);
    if( count($fieldmatches) > 0 )
    {
        $field = array();
        foreach( $fieldmatches as $setName )
        {
            $result = mysqli_query($dbId,"SELECT id, setNo, xChecked, fieldType FROM wtl_fields WHERE isSet = '1' AND
                setName = '".trim($setName,'#')."'");
            $field[] = mysqli_fetch_row($result);
        }
    }
    while( $daten = mysqli_fetch_object($result_view) )
    {
        $confirmLink = 'https://'.str_replace(array('www.','http://','https://'),'',$_SERVER['SERVER_NAME']).
            substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'/',1)+1).$GLOBALS['SYSTEM_SETTINGS']['WTL_REGISTER_URL'].
            $listID.'&data=confirm&entryToken='.base64_encode($daten->registerId.$daten->entryId);
        $fixReplace = array(date('d.m.Y'),date('d.m.Y',$daten->tstamp),$daten->firstname,$daten->lastname,
            date('d.m.Y',$daten->dateOfBirth),$listName,$dlrgName,$daten->registerId,date('d.m.Y',$daten->startTstamp),
            date('d.m.Y',$daten->answerTstamp),$username,$usermail,$userphone,$confirmLink);
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
                    $result = mysqli_query($dbId,"SELECT dataLabel FROM wtl_fields WHERE isSet != '1' AND
                        setNo = '".$field[$fc][1]."' AND data = '".$MYSQL[$field[$fc][0]]."'");
                    $label = mysqli_fetch_row($result);
                    $value = $label[0];
                }
            }
            else
            {
                $result = mysqli_query($dbId,"SELECT inputs, selected FROM wtl_members WHERE id = '".$daten->id."'");
                $data_m = mysqli_fetch_row($result);
                if( $field[$fc][3] == 'input' )
                {
                    $inputs = parse_inputs($data_m[0]);
                    $value = $inputs[$field[$fc][0]];
                }
                else
                {
                    $inputs = parse_inputs($data_m[1]);
                    $result = mysqli_query($dbId,"SELECT dataLabel FROM wtl_fields WHERE isSet != '1' AND
                        setNo = '".$field[$fc][1]."' AND data = '".$inputs[$field[$fc][0]]."'");
                    $label = mysqli_fetch_row($result);
                    $value = $label[0];
                }
            }
            $varReplace[] = $value;
            $fc++;
        }
        $mailtext = str_replace($fieldmatches,$varReplace,$mailtext);
        if( $send === TRUE )
        {
            $retarray[0] = send_mail($mailadress,$daten->mail,$dlrgName.' '.$subtext.' '.$listName,$mailtext);
            $retarray[1] = $count;
        }
        else
        {
            if( $count == 1 )
            {
                $searcharr = array('<','>');
                $mailtext = str_replace($searcharr,'',$mailtext);
                $retarray[0] = 'BETREFF:<br>'.$dlrgName.' '.$subtext.' '.$listName.'<br>';
                $retarray[1] = 'MAILTEXT:<br>'.nl2br($mailtext).'<br>';
            }
        }
        $count += 1;
    }
    return $retarray;
}

?>