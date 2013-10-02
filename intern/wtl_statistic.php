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


    // Settings
    require_once('f_sets.php');
    require_once('f_wtl.php');
    $entryId = mysql_real_escape_string($_GET['entryId']);
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
    $fieldClass = array('startdate'=>'Field','enddate'=>'Field');

    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }
    $listID = $_POST['listId'];
    if( !empty($entryId) )
    {
        $listID = mysql_real_escape_string($_GET['listId']);
    }

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','viewAuth',$listID);

    echo "<div id='wtl_entry_statistic'>
          <div class='waitinglist'>";
    // Daten der wtl_lists lesen
    $result = mysql_query("SELECT setName FROM wtl_lists WHERE id = '".$listID."'",$dbId);
    while( $daten = mysql_fetch_object($result) )
    {
        $listName = $daten->setName;
    }

    if( $authority === TRUE )
    {
        if( isset($_POST['send_dates']) )
        {
            $input_OK = TRUE;
            if( !check_date($_POST['startdate'],'.') && !empty($_POST['startdate']) )
            {
                $input_OK = FALSE;
                $fieldClass['startdate'] = 'errorField';
                $errorTitle['startdate'] = 'Datum ungültig!';
            }
            if( !check_date($_POST['enddate'],'.') && !empty($_POST['enddate']) )
            {
                $input_OK = FALSE;
                $fieldClass['enddate'] = 'errorField';
                $errorTitle['enddate'] = 'Datum ungültig!';
            }
            if( $input_OK )
            {
                if( empty($_POST['startdate']) )
                {
                    $startdate = 0;
                }
                else
                {
                    $startdate = strtotime(date_german2mysql($_POST['startdate']));
                }
                if( empty($_POST['enddate']) )
                {
                    $enddate = time();
                }
                else
                {
                    $enddate = strtotime(date_german2mysql($_POST['enddate'])) + 86400;
                }
            }
            else
            {
                $errorMessage .= errorNote();
            }
        }

        if( !empty($listID) )
        {
            echo "<h1>Ansicht aller getätigten Aufnahmen aus der Warteliste ".$listName."</h1>";
            // Bei Detailabfrage
            if( !empty($entryId) )
            {
                // Abfrage der Details aus der DB
                $result = mysql_query("SELECT * FROM wtl_members WHERE deleted != '1' AND entryId = '".$entryId."' ORDER BY entryTstamp", $dbId);
                $quantity_details = mysql_num_rows($result);
                $headline = "<p><b>Details für die Aufnahmen Nr. ".$_GET['detno']."</b></p>";
                wtl_make_site_view($dbId,'STATISTIC_DETAILS',$result,$listID,$quantity_details,'1','+1','',$headline,'',FALSE,FALSE);
                echo "<p></p><p></p>";
            }
            // Alle Aufgenommenen der ausgewählten Warteliste zählen
            $result_count = mysql_query("SELECT COUNT(*) FROM wtl_members WHERE entryId != '' AND deleted != '1' AND listId = '".$listID."'", $dbId);
            $entryNuArray = mysql_fetch_row($result_count);
            $entryNu = $entryNuArray[0];
            // Zusammenfassung der Aufnahmen
            $result = mysql_query("SELECT * FROM wtl_members WHERE entryId != '' AND deleted != '1' AND
                listId = '".$listID."' GROUP BY entryId HAVING entryId != '' ORDER BY entryTstamp DESC", $dbId);
            $quantity_data = mysql_num_rows($result);
            if( $quantity_data == 1 )
            {
                $headline = "<p><b>Es ist bisher ".$quantity_data." Aufnahme mit ingesamt ".$entryNu.
                    " Personen aus der Warteliste ".$listName." erfolgt.</b></p>";
            }
            else
            {
                $headline = "<p><b>Es sind bisher ".$quantity_data." Aufnahmen mit ingesamt ".$entryNu.
                    " Personen aus der Warteliste ".$listName." erfolgt.</b></p>";
            }
            // Seitenaufbau aufrufen
            wtl_make_site_view($dbId,'STATISTIC',$result,$listID,$quantity_data,$quantity_data,'-1','',$headline,'',FALSE,FALSE);
            // grafische Statistik
            // Einschränkung des Datums
            if( ($quantity_data != 0) && $result )
            {
                echo "<p></p><p><b>Grafische Auswertung anzeigen:</b></p>";
                echo "
                    <form name='wtl_entry_statistic_graphic' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                    <table>
                        <tr>
                            <td colspan='3'>".$errorMessage."</td>
                        </tr>
                        <tr>
                            <td>vom</td>
                            <td>bis</td>
                            <td><input name='listId' type='hidden' value='".$listID."'/></td>
                        </tr>
                        <tr>
                            <td><input class='".$fieldClass['startdate']."' type='text' name='startdate' size='10'
                                title='".$errorTitle['startdate']."' value='".$_POST['startdate']."'/></td>
                            <td><input class='".$fieldClass['enddate']."' type='text' name='enddate' size='10'
                                title='".$errorTitle['enddate']."' value='".$_POST['enddate']."'/></td>
                            <td><input class='button' type='submit' name='send_dates' value='Übernehmen'/></td>
                        </tr>
                    </table>
                    </form>
                ";
                if( isset($_POST['send_dates']) )
                {
                    // Rückmeldungen zählen
                    $result_count = mysql_query("SELECT COUNT(*) FROM wtl_members WHERE entryId != '' AND deleted != '1' AND confirm = '2'
                        AND listId = '".$listID."' AND entryTstamp >= '".$startdate."' AND entryTstamp < '".$enddate."'", $dbId);
                    $confYesArray = mysql_fetch_row($result_count);
                    $confYesNu = $confYesArray[0];
                    $result_count = mysql_query("SELECT COUNT(*) FROM wtl_members WHERE entryId != '' AND deleted != '1' AND confirm = '1'
                        AND listId = '".$listID."' AND entryTstamp >= '".$startdate."' AND entryTstamp < '".$enddate."'", $dbId);
                    $confNoArray = mysql_fetch_row($result_count);
                    $confNoNu = $confNoArray[0];
                    $result_count = mysql_query("SELECT COUNT(*) FROM wtl_members WHERE entryId != '' AND deleted != '1' AND confirm = '0'
                        AND listId = '".$listID."' AND entryTstamp >= '".$startdate."' AND entryTstamp < '".$enddate."'", $dbId);
                    $confNotArray = mysql_fetch_row($result_count);
                    $confNotNu = $confNotArray[0];
                    // grafischer Aufbau
                    kreisdiagramm(10,'nicht gemeldet:'.$confNotNu.':gelb,Teilnahme Nein:'.$confNoNu.':rot,Teilnahme Ja:'.$confYesNu.':gruen','',350,150);
                    echo "<img alt='diagramm_1.gif' src='temp/diagramm_1.gif'/>";
                }
            }
        }
    }

    // Wartelistenauswahlformular
    if( !$listID )
    {
        if( isset($_POST['sendSelected']) && ($authority !== TRUE) )
        {
            echo "<h1>Ansicht der Aufnahmen aus der Warteliste ".$listName."</h1>
                <p><b>Du hast keine Berechtigung zum ansehen der Aufnahmen aus dieser Warteliste!</b></p>";
        }
        else
        {
            echo "<h1>Übersicht der Aufgenommenen</h1>";
            select_list_formular($dbId,'wtl_lists','Eine Warteliste auswählen','Warteliste');
        }
    }
    echo "</div></div>";
?>