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


//@$file string
// NO RETURN
function convertFileToUTF8($file)
{
    // Dateiinhalt nach utf-8 convertieren
    $handle = fopen($file,"r+");
    fwrite($handle,utf8_encode(file_get_contents($file)));
    fclose($handle);
}

//@$dbId int
//@$tablePrefix string
//@$tableDownloadFrom string
//@$listId int
//@$downloadFile string
//@$extraConditions string
// NO RETURN
function downloadTableDataToCSVfile($dbId,$tablePrefix,$tableDownloadFrom,$listId,$downloadFile,$extraConditions)
{
    if( !empty($extraConditions) )
    {
        $extraConditions = " AND ".$extraConditions;
    }
    $retArray = array();
    $returnValues = namesFromSelFields($dbId,$tablePrefix,$listId,'viewDownloads',$retArray);
    $dataDownloadCSVmembers = '"Nachname","Vorname","Geb.Datum","email","Anmeldedatum"';
    $fieldNames = implode('","',$returnValues[1]);
    if( $fieldNames )
    {
        $fieldNames = ',"'.$fieldNames.'"';
    }
    $dataDownloadCSVmembers .= $fieldNames."\n";
    $result = mysql_query("SELECT * FROM ".$tableDownloadFrom." WHERE listId = '".$listId."'".$extraConditions,$dbId);
    while( $daten = mysql_fetch_object($result) )
    {
        $viewFields = array_merge(explode('##',$daten->inputs),explode('##',$daten->selected));
        $fieldData = implode('","',dataFromSelFields($dbId,$tablePrefix,$returnValues[0],$viewFields,$retArray));
        if( !empty($fieldData) )
        {
            $fieldData = ',"'.trim($fieldData).'"';
        }
        $dataDownloadCSVmembers .= '"'.htmlspecialchars_decode($daten->lastname).'","'.htmlspecialchars_decode($daten->firstname).'","'.date('d.m.Y',$daten->dateOfBirth).
            '","'.htmlspecialchars_decode($daten->mail).'","'.date('d.m.Y',$daten->tstamp).'"'.$fieldData."\n";
    }
    // Daten für csv in Datei schreiben
    $file = fOpen($downloadFile,'w-');
    fWrite($file, utf8_decode($dataDownloadCSVmembers));
    fClose($file);
}

//@$headText string
//@$text string
//@$charTest string
//@$maxFilesize int
//@$fileOnServer string
//@$hiddenFields string
//@return boolean
function uploadFile($headText,$text,$charTest,$maxFilesize,$fileOnServer,$hiddenFields)
{
    // Settings
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $base_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $base_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }
    $error = FALSE;
    $uploded = FALSE;
    $regExp = "/^[a-z_]([a-z0-9_-]*\.?[a-z0-9_-])*\.[a-z]{3,4}$/i";
    $sendName = substr(md5($fileOnServer),0,12);
    if( isset($_POST[$sendName]) )
    {
        // Datei muss einen gültigen Dateinamen haben und kleiner maxFilesize sein
        if( preg_match($regExp,$_FILES['datei']['name']) && ($_FILES['datei']['size'] > 0) && ($_FILES['datei']['size'] < $maxFilesize) )
        {
            // Dateiinhalt prüfen
            $lines = file($_FILES['datei']['tmp_name']);
            $lineLen = strlen($charTest);
            if( strncmp($lines[0], $charTest, $lineLen) == 0 )
            {
                if( move_uploaded_file($_FILES['datei']['tmp_name'],$fileOnServer) )
                {
                    $uploded = TRUE;
                }
                else
                {
                    $error = TRUE;
                    $errorText = "Datei konnte nicht hochgeladen werden !";
                }
            }
            else
            {
                $error = TRUE;
                $errorText = "Falsche Datei !";
            }
            unset($lines);
        }
        else
        {
            $error = TRUE;
            $errorText = "Ungültiger Dateiname oder Datei zu groß !";
        }
        if( $error )
        {
            $errorMessage .= errorNote();
        }
    }
    // HTML Seite bauen
    echo "<div class='upload_file'>";
    echo $headText;
    echo "
        <form name='upload_file_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."' enctype='multipart/form-data'>
        <div class='border'>
        <table>
            <tr>
                <td class='firstline' colspan='2'>".$text."</td>
            </tr>
            <tr>
                <td colspan='2'>".$errorMessage;if($error){echo $errorText;}echo"</td>
            </tr>
            <tr>
                <td colspan='2'><input name='datei' type='file' size='53'/></td>
            </tr>
            <tr>
                <td colspan='2'><input class='button_long' type='submit' name='".$sendName."' value='Datenbank aktualisieren'/>
                    <input class='button' type='reset' name='abort' value='Abbrechen'
                    onclick=\"location.href='".$base_url."'\"/>".$hiddenFields."</td>
            </tr>
        </table>
        </div>
        </form>
    </div>";
    return $uploded;
}

//@$dbId int
//@$tablePrefix string
//@$tableDataTo string
//@$fixFieldNameArray array
//@$listId int
//@$uploadedFile string
//@$tableExtraFields string
function insertCSVdataIntoTable($dbId,$tablePrefix,$tableDataTo,$fixFieldNameArray,$listId,$uploadedFile,$tableExtraFields)
{
    $uploded = FALSE;
    if( !empty($tableExtraFields) )
    {
        $tableExtraFields = ", ".$tableExtraFields;
    }
    $file = fopen($uploadedFile,'r');
    // erste Zeile (Überschriften) lesen
    $fields = fgetcsv($file);
    foreach( $fields as $key => $val )
    {
        $result = mysql_query("SELECT setNo, fieldType FROM ".$tablePrefix."_fields WHERE isSet = '1' AND caption ='".$val."'",$dbId);
        $resultArray = mysql_fetch_row($result);
        $fieldName = array();
        if( !empty($resultArray[0]) )
        {
            $fieldname[$key] = $resultArray[0];
            $fieldtype[$key] = $resultArray[1];
        }
        else
        {
            $fieldname[$key] = $fixFieldNameArray[$val];
            $fieldtype[$key] = 'field';
        }
    }
    // alle weiteren Zeilen (Inhalte) lesen
    while( ($fields = fgetcsv($file)) !== FALSE )
    {
        $inputdata = '';
        $selectdata = '';
        $fielddata = '';
        foreach( $fields as $key => $val )
        {
            if( preg_match('/\d+/',$fieldname[$key]) )
            {
                if( $fieldtype[$key] == 'input' )
                {
                    $inputdata .= "#".$fieldname[$key].";".$val."#";
                }
                if( $fieldtype[$key] == 'dropdown' )
                {
                    $result = mysql_query("SELECT data FROM ".$tablePrefix."_fields WHERE isSet = '0' AND setNo = '".$fieldname[$key]."' AND dataLabel ='".$val."'",$dbId);
                    $resultArray = mysql_fetch_row($result);
                    $selectdata .= "#".$fieldname[$key].";".$resultArray[0]."#";
                }
            }
            if( $fieldtype[$key] == 'field' )
            {
                if( preg_match('/[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}/',$val) )
                {
                    $val = strtotime(date_german2mysql($val));
                }
                $fielddata .= $fieldname[$key]." = '".$val."', ";
            }
        }
        $fielddata .= "inputs = '".$inputdata."', ";
        $fielddata .= "selected = '".$selectdata."', ";
        $SQL_Befehl_Write = "INSERT INTO ".$tableDataTo." SET ".$fielddata." listId = '".$listId."'".$tableExtraFields;
        if( (mysql_query($SQL_Befehl_Write,$dbId)!==FALSE) )
        {
            $uploded = TRUE;
        }
    }
    // File schliessen, erfolgreichen upload prüfen
    fclose($file);
    return $uploded;
}


function mysqlTableUpdateByCsvFile($dbId,$fileOnServer,$sqlTable,$sqlCondition,$sqlSeperator,$sqlEncloser,$sqlLineTerminator,$sqlIgnoreLines,$sqlFieldList,$delBevore)
{
    $result_OK = FALSE;
    // Tabelle in der DB aktualisieren
    //$SQL_reset = "ALTER TABLE ".$sqlTable." AUTO_INCREMENT = 1";
    if( $sqlCondition != '' )
    {
        $sqlCondition = " WHERE ".$sqlCondition;
    }
    if( $delBevore === TRUE )
    {
        if( mysql_query("DELETE FROM ".$sqlTable.$sqlCondition,$dbId) )
        {
            $result_OK = TRUE;
        }
    }
    $SQL_sql = "LOAD DATA LOCAL INFILE '".$fileOnServer."' REPLACE INTO TABLE ".$sqlTable." FIELDS TERMINATED BY '".
        $sqlSeperator."' ENCLOSED BY '".$sqlEncloser."' LINES TERMINATED BY '".$sqlLineTerminator."' IGNORE ".$sqlIgnoreLines." LINES ".$sqlFieldList;
    if( mysql_query($SQL_del,$dbId) )
    {
        $result_OK = TRUE;
    }
    if( $result_OK === TRUE )
    {
        $message = "<p><b>Aktualisierung der Tabelle in der Datenbank erfolgreich !</b></p>";
        //$message .= "<p>".mysql_info()."</p>";
    }
    else
    {
        $message = "<p><b>Aktualisierung der Tabelle in der Datenbank fehlgeschlagen !<br/>Grund: ".mysql_error()."</b></p>";
    }
    return $message;
}

//@$headText string
//@$text string
//@$imagetypes array
//@$maxFilesize int
//@$fileOnServer string
//@$hiddenFields string
//@return boolean
//$datatypes => ( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_SWF, IMAGETYPE_PSD, IMAGETYPE_BMP, IMAGETYPE_TIFF_II (intel-Bytefolge),
//IMAGETYPE_TIFF_MM (motorola-Bytefolge), IMAGETYPE_JPC, IMAGETYPE_JP2, IMAGETYPE_JPX, IMAGETYPE_JB2, IMAGETYPE_SWC, IMAGETYPE_IFF, IMAGETYPE_WBMP,
//IMAGETYPE_XBM, IMAGETYPE_ICO )
function uploadGraphic($headText,$text,$imagetypes,$maxFilesize,$fileOnServer,$hiddenFields)
{
    // Settings
    if( strpos($_SERVER['REQUEST_URI'],'&') === FALSE )
    {
        $base_url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $base_url = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&'));
    }
    $error = FALSE;
    $uploded = FALSE;
    $regExp = "/^[a-z_]([a-z0-9_-]*\.?[a-z0-9_-])*\.[a-z]{3,4}$/i";
    $sendName = substr(md5($fileOnServer),0,12);
    if( isset($_POST[$sendName]) )
    {
        // Datei muss einen gültigen Dateinamen haben
        if( preg_match($regExp,$_FILES['datei']['name']) )
        {
            // Datei muss kleiner maxFilesize sein
            if( ($_FILES['datei']['size'] > 0) && ($_FILES['datei']['size'] < $maxFilesize) )
            {
                // Dateitype prüfen
                $imagetype = exif_imagetype($_FILES['datei']['tmp_name']);
                if( in_array(exif_imagetype($_FILES['datei']['tmp_name']),$imagetypes) )
                {
                    if( move_uploaded_file($_FILES['datei']['tmp_name'],$fileOnServer.image_type_to_extension($imagetype)) )
                    {
                        $uploded = TRUE;
                    }
                    else
                    {
                        $error = TRUE;
                        $errorText = "Datei konnte nicht hochgeladen werden !";
                    }
                }
                else
                {
                    $error = TRUE;
                    $errorText = "Falscher Dateityp !";
                }
            }
            else
            {
                $error = TRUE;
                $errorText = "Datei zu groß !";
            }
        }
        else
        {
            $error = TRUE;
            $errorText = "Ungültiger Dateiname !";
        }
        if( $error )
        {
            $errorMessage .= errorNote();
        }
    }
    // HTML Seite bauen
    echo "<div class='upload_file'>";
    echo $headText;
    echo "
        <form name='upload_file_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."' enctype='multipart/form-data'>
        <div class='border'>
        <table>
            <tr>
                <td class='firstline' colspan='2'>".$text."</td>
            </tr>
            <tr>
                <td colspan='2'>".$errorMessage;if($error){echo $errorText;}echo"</td>
            </tr>
            <tr>
                <td colspan='2'><input name='datei' type='file' size='53'/></td>
            </tr>
            <tr>
                <td colspan='2'><input class='button' type='submit' name='".$sendName."' value='Foto hochladen'/>
                    <input class='button' type='reset' name='abort' value='Abbrechen'
                    onclick=\"location.href='".$base_url."'\"/>".$hiddenFields."</td>
            </tr>
        </table>
        </div>
        </form>
    </div>";
    return $uploded;
}

?>