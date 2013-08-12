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
 * @WTL version  1.2
 * @date - time  01.02.2013 - 19:00
 * @copyright    Marc Busse 2012-2016
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    require_once('f_fields.php');
    $memberID = mysql_real_escape_string($_GET['memberID']);
    $listID = mysql_real_escape_string($_GET['listID']);
    $data = mysql_real_escape_string($_GET['data']);
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
    $NotView = FALSE;
    $authority = FALSE;
    $authorityView = FALSE;
    $readonly = " readonly='readonly' ";
    $username = $_SESSION['intern']['realname'];

    // Benutzerberechtigungen
    $authorityView = checkAuthority($dbId,'wtl_user','viewAuth',$listID);
    $authority = checkAuthority($dbId,'wtl_user','registerAuth',$listID);

    // Daten der wtl_lists lesen
    $result = mysql_query("SELECT * FROM wtl_lists WHERE id = '".$listID."'",$dbId);
    while( $daten = mysql_fetch_object($result) )
    {
        $listName = $daten->setName;
        $inputfields = unserialize($daten->inputfields);
        $selectfields = unserialize($daten->selectfields);
    }
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

    if( isset($_POST['sendEdit']) )
    {
        $_POST['memberId'] = $memberID;
        include_once('wtl_register.php');
    }
    else
    {
        echo "<div id='wtl_view_member'>
              <div class='waitinglist'>";
        if( $authorityView === TRUE )
        {
            $result = mysql_query("SELECT * FROM wtl_members WHERE id = '".$memberID."' AND deleted != '1'",$dbId);
            if( mysql_num_rows($result) == 1 )
            {
                while( $daten = mysql_fetch_object($result) )
                {
                    $_POST['firstname'] = $daten->firstname;
                    $_POST['lastname'] = $daten->lastname;
                    $_POST['dateOfBirth'] = date('d.m.Y', $daten->dateOfBirth);
                    $_POST['mail'] = $daten->mail;
                    $_POST['registerDate'] = date('d.m.Y', $daten->tstamp);
                    $_POST = inputfielddata_to_inputfields($daten->inputs,$_POST,'input_');
                    $_POST = inputfielddata_to_inputfields($daten->selected,$_POST,'dropdown_');
                    $entry = $daten->entryId;
                    $registerId_OK = TRUE;
                }
                $location_back = "\"location.href='".substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'='))."=wtl_view&amp;listID=".$listID."'\"";
                // keine Daten ändern bei bereits aufgenmmenen Personen
                if( !empty($entry) )
                {
                    $authority = FALSE;
                    $location_back = "'history.back();'";
                }
                include_once('wtl_register_site.php');
                echo "
                    <form name='wtl_view_member_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                        <p><input class='button' type='button' name='back' value='Zurück' 
                            onclick=".$location_back."/></p>
                    </form>
                ";
            }
            else
            {
                echo "<h1>Daten ansehen</h1>";
                echo "<p><b>Es sind keine Daten zum ansehen vorhanden!</b></p>";
            }
        }
        else
        {
            echo "<h1>Daten ansehen</h1>";
            echo "<p><b>Du hast keine Berechtigung zum Ansehen der Daten für diese Warteliste!</b></p>";
        }
        echo "</div></div>";
    }
?>