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
 * @WTL version  1.2.1
 * @date - time  19.04.2013 - 19:00
 * @copyright    Marc Busse 2012-2016
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


//@$dbId int
//@sqlTable string
//@$right string
//@$listId int
//@return boolean
function checkAuthority($dbId,$sqlTable,$right,$listId)
{
    $authority = FALSE;
    $result = mysql_query("SELECT * FROM ".$sqlTable." WHERE id = '".$_SESSION['intern']['userId']."' LIMIT 1",$dbId);
    if( $result !== FALSE )
    {
        $daten = mysql_fetch_object($result);
        if( ($right == 'admin') || ($right == 'sAdmin') )
        {
            if( ($right == 'admin' && ($daten->admin == '1' || $daten->sAdmin == '1')) || ($right == 'sAdmin' && $daten->sAdmin == '1') )
            {
                $authority = TRUE;
            }
        }
        else
        {
            if( $listId != '' && (in_array($listId,unserialize($daten->$right)) || $daten->admin == '1' || $daten->sAdmin == '1') )
            {
                $authority = TRUE;
            }
        }
    }
    return $authority;
}

//@$dbId int
//@sqlTable string
//@$listID int
//@$headerText string
// NO RETURN
function login($dbId,$sqlTable,$listID,$headerText)
{
    // Eingabefelder auf Gültigkeit prüfen
    if( isset($_POST['sendLogin']) )
    {
        $input_OK = TRUE;
        if( (strlen($_POST['username']) < 3) || (preg_match('/[^a-zA-Z\-äÄöÖüÜß.\s]/',$_POST['username'])) )
        {
            $input_OK = FALSE;
        }
        if( (strlen($_POST['userpw']) < 6) || (strlen($_POST['userpw']) > 12) )
        {
            $input_OK = FALSE;
        }
        if( $input_OK )
        {
            if( $listID == 0 )
            {
                $SQL_Befehl_Read = "SELECT id, userpw, realname FROM ".$sqlTable." WHERE isSet = '1' AND disable != '1' AND
                    username = '".mysql_real_escape_string($_POST['username'])."' LIMIT 1";
                $intExt = 'intern';
            }
            else
            {
                // Benutzerset anhand der id suchen
                $result = mysql_query("SELECT setNo FROM ".$sqlTable." WHERE isSet = '1'  AND id = '".$listID."'",$dbId);
                $resultArray = mysql_fetch_row($result);
                $SQL_Befehl_Read = "SELECT id, userpw, realname FROM ".$sqlTable." WHERE setNo = '".$resultArray[0]."' AND isSet !='1' AND disable != '1'
                    AND username = '".mysql_real_escape_string($_POST['username'])."' LIMIT 1";
                $intExt = 'extern';
            }
            if( ($listID == 0) && ($GLOBALS['INSTALL']['FIRSTINSTALL'] === TRUE) )
            {
                if( ($GLOBALS['INSTALL']['USERNAME'] == $_POST['username']) && ($GLOBALS['INSTALL']['USERPW'] == md5($_POST['userpw'])) )
                {
                    $_SESSION[$intExt]['loggedIn'] = TRUE;
                    $_SESSION[$intExt]['userId'] = 0;
                }
            }
            else
            {
                $result = mysql_query($SQL_Befehl_Read,$dbId);
                $quantity = mysql_num_rows($result);
                if( (($quantity == 1) && ($result !== FALSE)) )
                {
                    $daten = mysql_fetch_object($result);
                    if( $daten->userpw == md5($_POST['userpw']) )
                    {
                        $_SESSION[$intExt]['loggedIn'] = TRUE;
                        $_SESSION[$intExt]['userId'] = $daten->id;
                        $_SESSION[$intExt]['realname'] = $daten->realname;
                    }
                }
            }
        }
    }
    if( !isset($_POST['sendLogin']) )
    {
        // HTML Seite bauen
        echo "<div class='login'>";
        echo $headerText;
        if( $_GET['login'] == 'false' )
        {
            echo "<p><b>Benutzername oder Passwort falsch !</b></p>";
        }
        echo "
            <form name='login_form' method='post' action='".str_replace('index','login',$_SERVER['SCRIPT_NAME'])."?listID=".$listID."'>
            <div class='border'>
            <table>
                <tr>
                    <td>Benutzername :</td>
                    <td><input class='Field' type='text' name='username' size='20' 
                        title='Nur die Zeichen A-Z, a-z incl. Umlaute sowie - und Leerzeichen sind zulässig!'/></td>
                </tr>
                <tr>
                    <td>Passwort :</td>
                    <td><input class='Field' type='password' name='userpw' size='20' 
                        title='Das Passwort muß zwischen 6 und 12 Zeichen lang sein!' maxlength='12'/></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input class='button' type='submit' name='sendLogin' value='Login'/></td>
                </tr>
            </table>
            </div>
            </form>
        </div>";
        echo "<p></p>
            <p>Wenn Du Dein <a href='".str_replace('intern_','',$_SERVER['SCRIPT_NAME'])."?doc=swm_forg_pw&amp;listID=".$listID."'>Passwort</a> vergessen hast, dann kannst Du ein neues beantragen.</p>";
    }
}

//@$dbId int
//@sqlTable string
//@userId int
// NO RETURN
function checkAutologout($dbId,$sqlTable,$userId)
{
    if( $GLOBALS['INSTALL']['FIRSTINSTALL'] !== TRUE )
    {
        $result = mysql_query("SELECT lastAction FROM ".$sqlTable." WHERE id = '".$userId."'",$dbId);
        $lastActArray = mysql_fetch_row($result);
        // check auf 10 min
        if( ($lastActArray[0] + ($GLOBALS['SYSTEM_SETTINGS']['AUTOLOGOUTTIME']*60)) < time() )
        {
            $_SESSION = array(); session_destroy();
        }
        else
        {
            $result = mysql_query("UPDATE ".$sqlTable." SET lastAction ='".time()."' WHERE id = '".$userId."'",$dbId);
        }
    }
}

//@$dbId int
//@sqlTable string
//@$listID int
//@$headerText string
// NO RETURN
function forgottenPw($dbId,$sqlTable,$listID,$headerText)
{
    $fieldClass = array('username'=>'Field');
    // Eingabefelder auf Gültigkeit prüfen
    if( isset($_POST['resetPW']) )
    {
        $input_OK = TRUE;
        if( (strlen($_POST['username']) < 3) || (preg_match('/[^a-zA-Z\-äÄöÖüÜß.\s]/',$_POST['username'])) )
        {
            $input_OK = FALSE;
            $fieldClass['username'] = 'errorField';
            $errorTitle['username'] = 'Nur die Zeichen A-Z, a-z incl. Umlaute sowie - . und Leerzeichen sind zulässig!';
        }
        if( $input_OK )
        {
            if( $listID == 0 )
            {
                $sqlCond_1 = "";
                $sqlCond_2 = "";
            }
            else
            {
                $sqlCond_1 = " AND id = '".$listID."'";
                $sqlCond_2 = " AND isSet != '1'";
            }
            // Benutzerset anhand der id suchen
            $result = mysql_query("SELECT setNo FROM ".$sqlTable." WHERE isSet = '1'".$sqlCond_1);
            $resultArray = mysql_fetch_row($result);
            $SQL_Befehl_Read = "SELECT mail FROM ".$sqlTable." WHERE setNo = '".$resultArray[0]."'
                AND username = '".mysql_real_escape_string($_POST['username'])."'".$sqlCond_2;
            $result = mysql_query($SQL_Befehl_Read,$dbId);
            if( (mysql_num_rows($result) == 1) && ($result !== FALSE) )
            {
                while( $daten = mysql_fetch_object($result) )
                {
                    $mMail = $daten->mail;
                }
                $newPW = md5(buildPassword(8));
                $SQL_Befehl_Write = "UPDATE ".$sqlTable." SET userpw = '".$newPW."' WHERE isSet != '1' AND setNo = '".$resultArray[0]."'
                    AND username = '".mysql_real_escape_string($_POST['username'])."'";
                $result = mysql_query($SQL_Befehl_Write,$dbId); 
                if( (mysql_affected_rows($dbId) == 1) && ($result !== FALSE) )
                {
                    $mText = "Du hast ein neues Passwort für den Zugang zur Warteliste der ".$GLOBALS['HOME']['NAME']." angefordert.\n";
                    $mText .= "Das neue Passwort lautet : ".$newPW."\n";
                    $mText .= "Das alte Passwort wurde gelöscht.\n\n";
                    $mText .= "Bitte ändere das Passwort sofort nach dem Login.\n";
                    send_mail($GLOBALS['HOME']['MAIL'],$mMail,'neues Passwort angefordert',$mText);
                    $message = 'Dir wurde ein neues Passwort an Deine hinterlegte mailadresse zugeschickt.';
                }
                else
                {
                    $message = 'Es konnte Dir kein neues Passwort zugeschickt werden !<br>Bitte wende dich an den Admin dieser Seite.';
                }
            }
            else
            {
                $message = 'Dein Benutzername ist falsch !<br>Bitte wende dich an den Admin dieser Seite.';
            }
        }
        else
        {
            $errorMessage = errorNote();
        }
    }
    // HTML Seite bauen
    echo $headerText;
    if( $message != '' )
    {
        echo "<p><b>".$message."</b></p>";
    }
    echo "<p>Hier kannst Du ein neues Passwort anforden.<br>Dies wird an Deine hinterlegte E-Mail-Adresse geschickt.</p>";
    echo "
        <form name='resetPW_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
        <div class='border'>
        <table>
            <tr>
                <td colSpan='2'>".$errorMessage."</td>
            </tr>
            <tr>
                <td>Benutzername :</td>
                <td><input class='".$fieldClass['username']."' type='text' name='username' size='20' 
                    title='".$errorTitle['username']."' value='".$_POST['username']."'/></td>
            </tr>
            <tr>
                <td></td>
                <td><input class='button_long' type='submit' name='resetPW' value='neues Passwort anfordern'/></td>
            </tr>
        </table>
        </div>
        </form>";
}

//@$dbId int
//@sqlTable string
//@userId int
//@$headerText string
// NO RETURN
function changePw($dbId,$sqlTable,$userId,$headerText)
{
    // Eingabefelder auf Gültigkeit prüfen
    if( isset($_POST['sendChange']) )
    {
        $input_OK = TRUE;
        if( (strlen($_POST['userpw_1']) < 6) || (strlen($_POST['userpw_1']) > 12) || (strlen($_POST['userpw_2']) < 6) || (strlen($_POST['userpw_2']) > 12))
        {
            $message = "Das Passwort muß zwischen 6 und 12 Zeichen lang sein!";
            $input_OK = FALSE;
        }
        if( $_POST['userpw_1'] != $_POST['userpw_2'] )
        {
            $message = "Das Passwort und die Wiederholung müssen übereinstimmen!";
            $input_OK = FALSE;
        }
    }
    if( $input_OK )
    {
        $only_message = TRUE;
        $SQL_Befehl_Write = "UPDATE ".$sqlTable." SET userpw ='".md5(mysql_real_escape_string($_POST['userpw_1']))."' WHERE id = '".$userId."'";
        $result = mysql_query($SQL_Befehl_Write,$dbId);
        if( (mysql_affected_rows($dbId) == 1) && ($result !== FALSE) )
        {
            $message = "Dein Passwort wurde geändert!";
        }
        else
        {
            $message = "Fehler, das Passwort wurde nicht geändert!";
        }
    }
    // HTML Seite bauen
    echo $headerText;
    if( $message != '')
    {
        echo "<p><b>".$message."</b></p>";
    }
    if( $only_message !== TRUE )
    {
        echo "<p>Hier kannst Du Dein Passwort ändern.</p>";
        echo "
            <form name='change_pw_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
            <div class='border'>
            <table>
                <tr>
                    <td>neues Passwort :</td>
                    <td><input class='Field' type='password' name='userpw_1' size='20' 
                        title='Das Passwort muß zwischen 6 und 12 Zeichen lang sein!' maxlength='12'/></td>
                </tr>
                <tr>
                    <td>Passwort wiederholen :</td>
                    <td><input class='Field' type='password' name='userpw_2' size='20' 
                        title='Das Passwort muß zwischen 6 und 12 Zeichen lang sein!' maxlength='12'/></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input class='button' type='submit' name='sendChange' value='Ändern'/></td>
                </tr>
            </table>
            </div>
            </form>";
    }
}

//@$dbId int
//@sqlTable string
//@userId int
//@$headerText string
//@return boolean
function setUserdata($dbId,$sqlTable,$userId,$headerText)
{
    $fieldClass = array('realname'=>'Field','userpw'=>'Field');
    $ret = TRUE;
    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }
    $result = mysql_query("SELECT username, userpw, realname, updated FROM ".$sqlTable." WHERE id = '".$userId."' LIMIT 1",$dbId);
    $daten = mysql_fetch_object($result);
    if( $_POST['realname'] == '' )
    {
        $_POST['realname'] = $daten->realname;
    }
    if( $daten->updated != '1' )
    {
        $ret = FALSE;
        if( isset($_POST['sendData']) )
        {
            $input_OK = TRUE;
            if( (strlen($_POST['realname']) < 4) || (preg_match('/[^a-zA-Z\-äÄöÖüÜß\s]/',$_POST['realname'])) )
            {
                $input_OK = FALSE;
                $fieldClass['realname'] = 'errorField';
                $errorTitle['realname'] = 'Nur die Zeichen A-Z, a-z incl. Umlaute sowie - und Leerzeichen sind zulässig!';
            }
            if( (strlen($_POST['userpw_1']) < 6) || (strlen($_POST['userpw_1']) > 12) )
            {
                $input_OK = FALSE;
                $fieldClass['userpw_1'] = 'errorField';
                $errorTitle['userpw_1'] = 'Das Passwort muß zwischen 6 und 12 Zeichen lang sein!';
            }
            if( $_POST['userpw_1'] != $_POST['userpw_2'] )
            { 
                $input_OK = FALSE;
                $fieldClass['userpw_2'] = 'errorField';
                $errorTitle['userpw_2'] .= "Das Passwort und die Wiederholung müssen übereinstimmen!";
            }
            if( md5($_POST['userpw_1']) == $daten->userpw )
            {
                $input_OK = FALSE;
                $fieldClass['userpw_1'] = 'errorField';
                $errorTitle['userpw_1'] .= 'Das Passwort darf nicht das Startpasswort sein!';
            }
            if( $input_OK )
            {
                $SQL_Befehl_Write = "UPDATE ".$sqlTable." SET userpw = '".md5($MYSQL['userpw_1'])."', realname = '".$MYSQL['realname']."', updated = '1'
                    WHERE isSet != '1' AND id = '".$userId."'";
                $result = mysql_query($SQL_Befehl_Write,$dbId); 
                if( (mysql_affected_rows($dbId) == 1) && ($result !== FALSE) )
                {
                    $ret = TRUE;
                }
            }
            else
            {
                $errorMessage = errorNote();
            }
        }
        if( !$ret )
        {
            // HTML Seite bauen
            echo "<h1>Benuterdaten ändern</h1>";
            echo $headerText;
            echo "
            <form name='userdata_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
            <div class='border'>
            <table>
                <tr>
                    <td colspan='2'>".$errorMessage."</td>
                </tr>
                <tr>
                    <td>Benutzername :</td>
                    <td>".$daten->username."</td>
                </tr>
                <tr>
                    <td>Vor und Nachname :</td>
                    <td><input class='".$fieldClass['realname']."' type='text' name='realname' size='20' 
                        title='".$errorTitle['realname']."' value='".$_POST['realname']."'/></td>
                </tr>
                <tr>
                    <td>neues Passwort :</td>
                    <td><input class='".$fieldClass['userpw_1']."' type='password' name='userpw_1' size='20' 
                        title='".$errorTitle['userpw_1']."' maxlength='12'/></td>
                </tr>
                <tr>
                    <td>Passwort wiederholen :</td>
                    <td><input class='".$fieldClass['userpw_2']."' type='password' name='userpw_2' size='20' 
                        title='".$errorTitle['userpw_2']."' maxlength='12'/></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input class='button' type='submit' name='sendData' value='weiter'/></td>
                </tr>
            </table>
            </div>
            </form>";
        }
    }
    return $ret;
}

?>