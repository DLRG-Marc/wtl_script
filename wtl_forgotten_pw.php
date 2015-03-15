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
 * @date - time  03.05.2014 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


    // Settings
    $listID = mysql_real_escape_string($_GET['listID']);

    echo "<div id='wtl_forgotten_pw'>
          <div class='waitinglist'>";
    if( $listID == 0 )
    {
        forgottenPw($dbId,'wtl_user',$listID,'<h1>Passwort vergessen</h1>');
    }
    else
    {
        forgottenPw($dbId,'wtl_public_user',$listID,'<h1>Passwort vergessen</h1>');
    }
    echo "</div></div>";
?>