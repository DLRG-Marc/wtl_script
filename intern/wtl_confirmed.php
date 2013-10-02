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
    require_once('f_wtl.php');
    $confirmedId = mysql_real_escape_string($_GET['confirmedId']);
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $script_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $script_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }

    echo "<div id='wtl_confirmed'>
          <div class='waitinglist'>";
    // nach erfolgter Auswahl
    echo "<h1>Rückmeldungen Deiner getätigten Aufnahme</h1>";
    if( $confirmedId != '' )
    {
        function confirmed($conf,$confirmedId,$dbId)
        {
            $result = mysql_query("SELECT * FROM wtl_members WHERE entryId = '".$confirmedId."'
                AND deleted != '1' AND confirm = '".$conf."' ORDER by confirmTstamp DESC", $dbId);
            $quantity = mysql_num_rows($result);
            if( (number_to_janein($conf) == '-') || !(number_to_janein($conf)) )
            {
                $text = "nicht";
            }
            else
            {
                $text = "mit ".number_to_janein($conf);
            }
            if( $quantity == 1)
            {
                $headline = "<p><b>Die folgende ".$quantity." Person hat ".$text." geantwortet:</b></p>";
            }
            else
            {
                $headline = "<p><b>Die folgenden ".$quantity." Personen haben ".$text." geantwortet:</b></p>";
            }
            // Seitenaufbau aufrufen
            wtl_make_site_confirmed($conf,$result,$quantity,$headline,'');
            $conf--;
            if( $conf >= 0 )
            {
                confirmed($conf,$confirmedId,$dbId);
            }
        }
        confirmed(2,$confirmedId,$dbId);
    }
    else
    {
        echo "<h1>Rückmeldungen Deiner getätigten Aufnahme</h1>
            <p><b>Du hast keine Berechtigung zum ansehen dieser Rückmeldungen!</b></p>";
    }
    echo "</div></div>";
?>