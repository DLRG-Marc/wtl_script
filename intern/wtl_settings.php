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
    require_once('f_sets.php');
    require_once('f_menu.php');
    $setID = mysql_real_escape_string($_GET['setID']);
    $pageNo = mysql_real_escape_string($_GET['pageNo']);
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $script_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $script_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }
    $errorPage = FALSE;
    $authority = FALSE;
    $username = $_SESSION['intern']['realname'];
    $fieldClass = array('published'=>'Field','dlrgName'=>'Field','mailadress'=>'Field','headerText'=>'Field','footerText'=>'Field','registerMail'=>'Field',
        'inputfields'=>'Selectfield','selectfields'=>'Selectfield','entryMail'=>'Field','entryLimit'=>'Field','selectAge'=>'Selectfield','ageLimit'=>'Field',
        'viewRegister'=>'Selectfield','viewEntry'=>'Selectfield','viewStatistic'=>'Selectfield','viewStatDetails'=>'Selectfield','viewDownloads'=>'Selectfield');

    // Benutzerberechtigungen
    $authority = checkAuthority($dbId,'wtl_user','admin',$setID);

    // Auswahlarray serialisieren bevor durch Cross-Side Script schutz zerstört
    $_POST['ageMin'] = $_POST['ageLimit'][0];
    $_POST['ageMax'] = $_POST['ageLimit'][1];
    $_POST['ageLimit'] = serialize($_POST['ageLimit']);
    $_POST['inputfields'] = serialize($_POST['inputfields']);
    $_POST['selectfields'] = serialize($_POST['selectfields']);
    $_POST['viewRegister'] = serialize($_POST['viewRegister']);
    $_POST['viewEntry'] = serialize($_POST['viewEntry']);
    $_POST['viewStatistic'] = serialize($_POST['viewStatistic']);
    $_POST['viewStatDetails'] = serialize($_POST['viewStatDetails']);
    $_POST['viewDownloads'] = serialize($_POST['viewDownloads']);
    $connectFields = array();
    $connectFields[0] = array('Age'=>$_POST['selectAge']);
    $connectFields[1] = array();
    foreach( unserialize(stripslashes($_POST['serSelectfields'])) as $setNo )
    {
        $fieldClass['dropdown_'.$setNo] = 'Selectfield';
        $connectFields[1][$setNo] = $_POST['dropdown_'.$setNo];
    }
    $selConnectFields = serialize($connectFields);

    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }

    echo "<div id='wtl_settings'>
          <div class='waitinglist'>";
    if( $authority === TRUE )
    {
        if( !$setID )
        {
            // Seitenüberschrift
            echo "<h1>Einstellungen Wartelisten</h1>";
            // aufruf der Set-Funktion
            makeSets($dbId,'wtl_lists','',FALSE);
        }
        if( !$pageNo )
        {
            $pageNo = 1;
        }

        // Eingabefelder auf Gültigkeit prüfen
        if( isset($_POST['sendInputRegister']) )
        {
            $inputRegister_OK = TRUE;
            if( (strlen($_POST['dlrgName']) < 2) || (preg_match('/[^a-zA-Z\-äÄöÖüÜß.\s]/', $_POST['dlrgName'])) )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['dlrgName'] = 'errorField';
                $errorTitle['dlrgName'] = 'Nur die Zeichen A-Z, a-z incl. Umlaute sowie - . und Leerzeichen sind zulässig!';
            }
            if( !check_email($_POST['mailadress']) )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['mailadress'] = 'errorField';
                $errorTitle['mailadress'] = 'Ungültige e-mail Adresse!';
            }
            if( (strlen($_POST['registerMail']) < 2) )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['registerMail'] = 'errorField';
                $errorTitle['registerMail'] = 'Es muß ein Mailtext eingegeben werden!';
            }
            if( count(unserialize($_POST['inputfields'])) == 0 )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['inputfields'] = 'errorSelectfield';
                $errorTitle['inputfields'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['selectfields'])) == 0 )
            {
                $inputRegister_OK = FALSE;
                $fieldClass['selectfields'] = 'errorSelectfield';
                $errorTitle['selectfields'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( preg_match('/[\D]/',$_POST['ageMin']) )
            {
                $input_OK = FALSE;
                $fieldClass['ageMin'] = 'errorField';
                $errorTitle['ageMin'] = 'Es sind nur Zahlen zulässig!';
            }
            if( preg_match('/[\D]/',$_POST['ageMax']) )
            {
                $fieldClass['ageMax'] = 'errorField';
                $errorTitle['ageMax'] = 'Es sind nur Zahlen zulässig!';
                $input_OK = FALSE;
            }
             if( ($_POST['ageMax'] < $_POST['ageMin']) && !empty($_POST['ageMin']) && !empty($_POST['ageMax']) )
            {
                $input_OK = FALSE;
                $fieldClass['ageMax'] = 'errorField';
                $errorTitle['ageMax'] = 'Das Höchstalter darf nicht kleiner als das Mindestalter sein!';
            }
        }
        if( isset($_POST['sendInputEntry']) )
        {
            $inputEntry_OK = TRUE;
            if( (strlen($_POST['entryMail']) < 2) )
            {
                $inputEntry_OK = FALSE;
                $fieldClass['entryMail'] = 'errorField';
                $errorTitle['entryMail'] = 'Es muß ein Mailtext eingegeben werden!';
            }
            if( (!$_POST['entryLimit']) || (preg_match('/[^\d]/', $_POST['entryLimit'])) )
            {
                $inputEntry_OK = FALSE;
                $fieldClass['entryLimit'] = 'errorField';
                $errorTitle['entryLimit'] = 'Es sind nur Zahlenwerte zulässig!';
            }
            if( empty($_POST['selectAge']) )
            {
                $inputEntry_OK = FALSE;
                $fieldClass['selectAge'] = 'errorSelectfield';
                $errorTitle['selectAge'] = 'Es muß ein Altersfeld eingegeben werden!';
            }
            foreach( unserialize(stripslashes($_POST['serSelectfields'])) as $setNo )
            {
                if( empty($_POST['dropdown_'.$setNo]) )
                {
                    $inputEntry_OK = FALSE;
                    $fieldClass['dropdown_'.$setNo] = 'errorSelectfield';
                    $errorTitle['dropdown_'.$setNo] = 'Es muß eine Zuordnung ausgewählt werden!';
                }
            }
        }
        if( isset($_POST['sendInputView']) )
        {
            $inputView_OK = TRUE;
            if( count(unserialize($_POST['viewRegister'])) == 0 )
            {
                $inputView_OK = FALSE;
                $fieldClass['viewRegister'] = 'errorSelectfield';
                $errorTitle['viewRegister'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['viewEntry'])) == 0 )
            {
                $inputView_OK = FALSE;
                $fieldClass['viewEntry'] = 'errorSelectfield';
                $errorTitle['viewEntry'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['viewStatistic'])) == 0 )
            {
                $inputView_OK = FALSE;
                $fieldClass['viewStatistic'] = 'errorSelectfield';
                $errorTitle['viewStatistic'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['viewStatDetails'])) == 0 )
            {
                $inputView_OK = FALSE;
                $fieldClass['viewStatDetails'] = 'errorSelectfield';
                $errorTitle['viewStatDetails'] ='Es muß min. ein Feld ausgewählt werden!';
            }
            if( count(unserialize($_POST['viewDownloads'])) == 0 )
            {
                $inputView_OK = FALSE;
                $fieldClass['viewDownloads'] = 'errorSelectfield';
                $errorTitle['viewDownloads'] ='Es muß min. ein Feld ausgewählt werden!';
            }
        }

        // wenn Daten geändert wurden
        if( isset($_POST['sendInputRegister']) )
        {
            // wenn Eingaben OK
            if( $inputRegister_OK )
            {
                $SQL_Befehl_Write = "UPDATE wtl_lists SET published = '".$MYSQL['published']."', dlrgName = '".$MYSQL['dlrgName']."',
                    mailadress = '".$MYSQL['mailadress']."', headerText = '".$MYSQL['headerText']."', footerText = '".$MYSQL['footerText']."',
                    inputfields = '".$MYSQL['inputfields']."', selectfields = '".$MYSQL['selectfields']."', registerMail = '".$MYSQL['registerMail']."',
                    ageLimit = '".$MYSQL['ageLimit']."', lastEditor = '".$username."' WHERE id = '".$setID."'";
                $result = mysql_query($SQL_Befehl_Write,$dbId);
                if( (mysql_affected_rows($dbId) == 1) && ($result === TRUE) )
                {
                    $pageNo = 2;
                }
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $errorPage = TRUE;
                $pageNo = 1;
            }
        }
        // wenn Daten geändert wurden
        if( isset($_POST['sendInputEntry']) )
        {
            // wenn Eingaben OK
            if( $inputEntry_OK )
            {
                $SQL_Befehl_Write = "UPDATE wtl_lists SET entryMail = '".$MYSQL['entryMail']."', entryLimit = '".$MYSQL['entryLimit']."',
                    connectFields = '".$selConnectFields."', lastEditor = '".$username."' WHERE id = '".$setID."'";
                $result = mysql_query($SQL_Befehl_Write, $dbId);
                if( (mysql_affected_rows($dbId) == 1) && ($result === TRUE) )
                {
                    $pageNo = 3;
                }
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $errorPage = TRUE;
                $pageNo = 2;
            }
        }
        // wenn Daten geändert wurden
        if( isset($_POST['sendInputView']) )
        {
            // wenn Eingaben OK
            if( $inputView_OK )
            {
                $SQL_Befehl_Write = "UPDATE wtl_lists SET viewRegister = '".$MYSQL['viewRegister']."', viewEntry = '".$MYSQL['viewEntry']."',
                    viewStatistic = '".$MYSQL['viewStatistic']."', viewStatDetails = '".$MYSQL['viewStatDetails']."',
                    viewDownloads = '".$MYSQL['viewDownloads']."', lastEditor = '".$username."' WHERE id = '".$setID."'";
                $result = mysql_query($SQL_Befehl_Write, $dbId);
                if( (mysql_affected_rows($dbId) == 1) && ($result === TRUE) )
                {
                    $pageNo = 4;
                }
            }
            // wenn Eingaben fehlerhaft
            else
            {
                $errorPage = TRUE;
                $pageNo = 3;
            }
        }

        // wenn kein Fehler
        if( !$errorPage )
        {
            // Daten neu einlesen
            $SQL_Befehl_Read = "SELECT * FROM wtl_lists WHERE id = '$setID'";
            $result = mysql_query($SQL_Befehl_Read, $dbId);
            while( $daten = mysql_fetch_object($result) )
            {
                $_POST['published'] = $daten->published;
                $_POST['dlrgName'] = $daten->dlrgName;
                $_POST['setName'] = $daten->setName;
                $_POST['mailadress'] = $daten->mailadress;
                $_POST['headerText'] = html_entity_decode($daten->headerText,ENT_QUOTES,'UTF-8');
                $_POST['footerText'] = html_entity_decode($daten->footerText,ENT_QUOTES,'UTF-8');
                $_POST['inputfields'] = $daten->inputfields;
                $_POST['selectfields'] = $daten->selectfields;
                $ageLimitArray = unserialize($daten->ageLimit);
                $_POST['registerMail'] = html_entity_decode($daten->registerMail,ENT_QUOTES,'UTF-8');
                $_POST['entryMail'] = html_entity_decode($daten->entryMail,ENT_QUOTES,'UTF-8');
                $_POST['entryLimit'] = $daten->entryLimit;
                $connectFields = unserialize($daten->connectFields);
                $_POST['viewRegister'] = $daten->viewRegister;
                $_POST['viewEntry'] = $daten->viewEntry;
                $_POST['viewStatistic'] = $daten->viewStatistic;
                $_POST['viewStatDetails'] = $daten->viewStatDetails;
                $_POST['viewDownloads'] = $daten->viewDownloads;
            }
            $_POST['ageMin'] = $ageLimitArray[0];
            $_POST['ageMax'] = $ageLimitArray[1];
            // Class der Zuordnungsauswahlfelder
            foreach( unserialize($_POST['selectfields']) as $setNo )
            {
                $fieldClass['dropdown_'.$setNo] = 'Selectfield';
                $_POST['dropdown_'.$setNo] = $connectFields[1][$setNo];
            }
            // Eingabefelder und Auswahlfelder zusammenführen für Ansicht
            $viewFieldArray = array_merge(unserialize($_POST['inputfields']),unserialize($_POST['selectfields']));
        }
        else
        {
            $errorMessage .= errorNote();
            if( isset($_POST['sendInputEntry']) )
            {
                $_POST['selectfields'] = stripslashes($_POST['serSelectfields']);
            }
        }

        if( $setID )
        {
            // HTML Seite bauen
            // Überschrift
            echo "<h1>Einstellungen Warteliste ".$_POST['setName']."</h1>";
            // Meldung bei erfolgreicher Änderung
            if( $pageNo == 4 )
            {
                echo "<p><b>Du hast erfolgreich die Einstellungen der Warteliste ".$_POST['listName']."geändert !</b></p>";
                echo "<p><a href='".$script_url."'>zurück zu den Einstellungen.</a></p>";
            }
            else
            {
                // Einstellungen Wartelistenformular
                if( $pageNo == 1 )
                {
                    // Überschrift
                    echo "
                        <p style='float:right;'><a href='".$script_url."&amp;setID=".$setID."&amp;pageNo=2'>nächste Seite</a></p>
                        <p style='clear:both;'>Hier werden die Einstellungen für das Wartelistenformular vorgenommen.</p>
                    ";
                    // Formularfelder bauen und ausfüllen
                    echo "
                    <form name='wtl_settings_register_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                    <div class='border'>
                    <table>
                        <tr>
                            <td colspan='3'>".$errorMessage."</td>
                        </tr>
                        <tr>
                            <td>Liste aktiv :</td>
                            <td colspan='2'><input class='".$fieldClass['published']."' type='checkbox' name='published'";
                                if($_POST['published']=='1'){echo " checked='checked'";} echo" value='1'/></td>
                        </tr>
                        <tr>
                            <td>Gliederungungsname :</td>
                            <td colspan='2'><input class='".$fieldClass['dlrgName']."' type='text' name='dlrgName' size='37'
                                title='".$errorTitle['dlrgName']."' value='".$_POST['dlrgName']."'/></td>
                        </tr>
                        <tr>
                            <td>Mailadresse der Ansprechpartner :</td>
                            <td colspan='2'><input class='".$fieldClass['mailadress']."' type='text' name='mailadress' size='37'
                                title='".$errorTitle['mailadress']."' value='".$_POST['mailadress']."'/></td>
                        </tr>
                        <tr>
                            <td>Header-Text im Anmeldeformular<br/>(optional) :</td>
                            <td colspan='2'><textarea class='".$fieldClass['headerText']."' name='headerText' cols='34' rows='5'>"
                                .$_POST['headerText']."</textarea></td>
                        </tr>
                        <tr>
                            <td>Footer-Text im Anmeldeformular<br/>(optional) :</td>
                            <td colspan='2'><textarea class='".$fieldClass['footerText']."' name='footerText' cols='34' rows='5'>"
                                .$_POST['footerText']."</textarea></td>
                        </tr>
                        <tr>
                            <td>Emailtext der Anmeldung :</td>
                            <td colspan='2'><textarea class='".$fieldClass['registerMail']."' name='registerMail' cols='34' rows='5'
                                title='".$errorTitle['registerMail']."'>".$_POST['registerMail']."</textarea></td>
                        </tr>
                        <tr>
                            <td>Eingabefelder :</td>
                            <td colspan='2'><select name='inputfields[]' class='".$fieldClass['inputfields']."' size='3'
                                title='".$errorTitle['inputfields']."' multiple='multiple'>";
                                $condition = "WHERE isSet = '1' AND fieldType = 'input'";
                                make_dropdown_list($dbId,'wtl_fields',$condition,unserialize($_POST['inputfields']),TRUE);
                            echo "
                            </select></td>
                        </tr>
                        <tr>
                            <td>Auswahlfelder :</td>
                            <td colspan='2'><select name='selectfields[]' class='".$fieldClass['selectfields']."' size='3'
                                title='".$errorTitle['selectfields']."' multiple='multiple'>";
                                $condition = "WHERE isSet = '1' AND fieldType = 'dropdown' AND xChecked !='1'";
                                make_dropdown_list($dbId,'wtl_fields',$condition,unserialize($_POST['selectfields']),TRUE);
                            echo "
                            </select></td>
                        </tr>
                        <tr>
                            <td>Mindestalter :</td>
                            <td colspan='2'><input class='".$fieldClass['ageMin']."' type='text' name='ageLimit[]' size='5'
                                title='".$errorTitle['ageMin']."' value='".$_POST['ageMin']."'/></td>
                        </tr>
                        <tr>
                            <td>Höchstalter :</td>
                            <td colspan='2'><input class='".$fieldClass['ageMax']."' type='text' name='ageLimit[]' size='5'
                                title='".$errorTitle['ageMax']."' value='".$_POST['ageMax']."'/></td>
                        </tr>
                        <tr>
                            <td><input name='setName' type='hidden' value='".$_POST['setName']."'/></td>
                            <td><input class='button' type='submit' name='sendInputRegister' value='Übernehmen'/></td>
                            <td><input class='button' type='reset' name='cancel' value='Abbrechen' 
                                onclick=\"location.href='".$script_url."'\"/></td>
                        </tr>
                    </table>
                    </div>
                    </form>
                    ";
                }

                // Einstellungen Aufnahmeformular
                if( $pageNo == 2 )
                {
                    // Überschrift
                    echo "
                        <p style='float:left;'><a href='".$script_url."&amp;setID=".$setID."&amp;pageNo=1'>vorherige Seite</a></p>
                        <p style='float:right;'><a href='".$script_url."&amp;setID=".$setID."&amp;pageNo=3'>nächste Seite</a></p>
                        <p style='clear:both;'>Hier werden die Einstellungen für das Aufnahmeformular vorgenommen.</p>
                    ";
                    // Formularfelder bauen und ausfüllen
                    echo "
                    <form name='wtl_settings_entry_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                    <div class='border'>
                    <table>
                        <tr>
                            <td colspan='3'>".$errorMessage."</td>
                        </tr>
                        <tr>
                            <td>Emailtext der Aufnahme :</td>
                            <td colspan='2'><textarea class='".$fieldClass['entryMail']."' name='entryMail' cols='34' rows='5'
                                title='".$errorTitle['entryMail']."'>".$_POST['entryMail']."</textarea></td>
                        </tr>
                        <tr>
                            <td>Maximal auf einmal<br/>aufzunehmen:</td>
                            <td colspan='2'><input class='".$fieldClass['entryLimit']."' type='text' name='entryLimit' size='10'
                                title='".$errorTitle['entryLimit']."' value='".$_POST['entryLimit']."'/></td>
                        </tr>
                        <tr>
                            <td><b>Altersauswahl</b><br/>zuordnen zu :</td>
                            <td colspan='2'><select name='selectAge' class='".$fieldClass['selectAge']."' size='3'
                                title='".$errorTitle['selectAge']."'>";
                                $condition = "WHERE isSet = '1' AND fieldType = 'dropdown' AND xChecked ='1' AND setName LIKE 'Alter%'";
                                make_dropdown_list($dbId,'wtl_fields',$condition,$connectFields[0],FALSE);
                            echo "
                            </select></td>
                        </tr>";
                        foreach( unserialize($_POST['selectfields']) as $id )
                        {
                            echo "
                            <tr>
                            <td>Auswahlfeld<br/><b>";
                                $result = mysql_query("SELECT setName FROM wtl_fields WHERE id = $id ORDER BY setName ASC",$dbId);
                                while( $daten = mysql_fetch_object($result) )
                                {
                                    echo $daten->setName;
                                }
                            echo "</b><br/>zuordnen zu :</td>
                            <td colspan='2'><select name='dropdown_".$id."' class='".$fieldClass['dropdown_'.$id]."' size='3' title='".$errorTitle['dropdown_'.$id]."'>";
                                $result = mysql_query("SELECT id, setName FROM wtl_fields WHERE isSet = 1 AND fieldType = 'dropdown' AND xChecked ='1'
                                    AND setName NOT LIKE 'Alter%' ORDER BY setName ASC",$dbId);
                                while( $daten = mysql_fetch_object($result) )
                                {
                                    echo "<option ";if($_POST['dropdown_'.$id]==$daten->id){echo " selected='selected'";}
                                    echo" value='".$daten->id."'>".$daten->setName."</option>";
                                }
                                echo "<option ";if($_POST['dropdown_'.$id]==$id){echo "selected='selected'";}
                                echo" value='".$id."'>nichts auswählen</option>";

                            echo "
                            </select></td>
                            </tr>";
                        }
                        echo "
                        <tr>
                            <td><input name='serSelectfields' type='hidden' value='".$_POST['selectfields']."'/>
                                <input name='setName' type='hidden' value='".$_POST['setName']."'/></td>
                            <td><input class='button' type='submit' name='sendInputEntry' value='Übernehmen'/></td>
                            <td><input class='button' type='reset' name='cancel' value='Abbrechen' 
                                onclick=\"location.href='".$script_url."'\"/></td>
                        </tr>
                    </table>
                    </div>
                    </form>
                    ";
                }

                // Einstellungen der Ansichten
                if( $pageNo == 3)
                {
                    // Überschrift
                    echo "
                        <p style='float:left;'><a href='".$script_url."&amp;setID=".$setID."&amp;pageNo=2'>vorherige Seite</a></p>
                        <p style='clear:both;'>Hier werden die Einstellungen für die Ansichten vorgenommen.</p>
                    ";
                    // Formularfelder bauen und ausfüllen
                    echo "
                    <form name='wtl_settings_view_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                    <div class='border'>
                    <table>
                        <tr>
                            <td colspan='3'>".$errorMessage."</td>
                        </tr>
                        <tr>
                            <td>Felder der<br/>Wartelistenansicht :</td>
                            <td colspan='2'><select name='viewRegister[]' class='".$fieldClass['viewRegister']."' size='3'
                                title='".$errorTitle['viewRegister']."' multiple='multiple'>";
                                $ids = array_to_text_with_trenner($viewFieldArray,"' OR id = '");
                                $condition = "WHERE isSet = '1' AND (id = '".$ids."')";
                                make_dropdown_list($dbId,'wtl_fields',$condition,unserialize($_POST['viewRegister']),TRUE);
                            echo "
                            </select></td>
                        </tr>
                        <tr>
                            <td>Felder der<br/>Aufnahmevorschau :</td>
                            <td colspan='2'><select name='viewEntry[]' class='".$fieldClass['viewEntry']."' size='3'
                                title='".$errorTitle['viewEntry']."' multiple='multiple'>";
                                $ids = array_to_text_with_trenner($viewFieldArray,"' OR id = '");
                                $condition = "WHERE isSet = '1' AND (id = '".$ids."')";
                                make_dropdown_list($dbId,'wtl_fields',$condition,unserialize($_POST['viewEntry']),TRUE);
                            echo "
                            </select></td>
                        </tr>
                        <tr>
                            <td>Felder der<br/>Aufnahmestatistik :</td>
                            <td colspan='2'><select name='viewStatistic[]' class='".$fieldClass['viewStatistic']."' size='3'
                                title='".$errorTitle['viewStatistic']."' multiple='multiple'>";
                                $ids = array_to_text_with_trenner($viewFieldArray,"' OR id = '");
                                $condition = "WHERE isSet = '1' AND (id = '".$ids."')";
                                make_dropdown_list($dbId,'wtl_fields',$condition,unserialize($_POST['viewStatistic']),TRUE);
                            echo "
                            </select></td>
                        </tr>
                        <tr>
                            <td>Felder der<br/>Aufnahmestatistik-Details :</td>
                            <td colspan='2'><select name='viewStatDetails[]' class='".$fieldClass['viewStatDetails']."' size='3'
                                title='".$errorTitle['viewStatDetails']."' multiple='multiple'>";
                                $ids = array_to_text_with_trenner($viewFieldArray,"' OR id = '");
                                $condition = "WHERE isSet = '1' AND (id = '".$ids."')";
                                make_dropdown_list($dbId,'wtl_fields',$condition,unserialize($_POST['viewStatDetails']),TRUE);
                            echo "
                            </select></td>
                        </tr>
                        <tr>
                            <td>Felder der<br/>Download-Dateien :</td>
                            <td colspan='2'><select name='viewDownloads[]' class='".$fieldClass['viewDownloads']."' size='3'
                                title='".$errorTitle['viewDownloads']."' multiple='multiple'>";
                                $ids = array_to_text_with_trenner($viewFieldArray,"' OR id = '");
                                $condition = "WHERE isSet = '1' AND (id = '".$ids."')";
                                make_dropdown_list($dbId,'wtl_fields',$condition,unserialize($_POST['viewDownloads']),TRUE);
                            echo "
                            </select></td>
                        </tr>
                        ";
                        echo "
                        <tr>
                            <td><input name='setName' type='hidden' value='".$_POST['setName']."'/></td>
                            <td><input class='button' type='submit' name='sendInputView' value='Übernehmen'/></td>
                            <td><input class='button' type='reset' name='cancel' value='Abbrechen' 
                                onclick=\"location.href='".$script_url."'\"/></td>
                        </tr>
                    </table>
                    </div>
                    </form>
                    ";
                }

                echo "
                <form name='wtl_settings_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
                    <p><input class='button' type='button' name='viewFieldSets' value='Setübersicht'
                    onclick=\"location.href='".$script_url."'\"/></p>
                </form>
                ";
            }
        }
        // wenn Daten geändert wurden, Menüeinträge ändern
        if( isset($_POST['sendInputRegister']) && $inputRegister_OK )
        {
            // wenn Formular veröffentlicht, Menüeinträge erstellen
            if( $_POST['published'] == '1' )
            {
                makeMenuIndex('01','wtl_reg','menu_reg','menu',$setID,$_POST['setName'],'Warteliste');
            }
            else
            {
                delMenuIndex('01','wtl_reg','menu_reg','menu',$setID,'Warteliste');
            }
        }
    }
    else
    {
        echo "<h1>Einstellungen Wartelisten</h1>
            <p><b>Du hast keine Berechtigung zum ändern der Einstellungen dieser Warteliste!</b></p>";
    }
    echo "</div></div>";
?>