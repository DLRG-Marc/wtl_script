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


function checkInputfields($dbId,$sqlTable,$fields,$returnArray)
{
    // wenn Eingabefelder ausgewählt
    foreach( $fields as $id )
    {
        if( $id != '' )
        {
            $result = mysql_query("SELECT notRequ, charReg, charLength, regEx FROM ".$sqlTable." WHERE id = '".$id."'",$dbId);
            while( $daten = mysql_fetch_object($result) )
            {
                $notRequ = $daten->notRequ;
                $charLengthArray = unserialize($daten->charLength);
                $charRegArray = unserialize($daten->charReg);
                $exCharReg = $daten->regEx;
            }
            if( !$notRequ )
            {
                // Zeichenlänge des Eingabefeldes auf Gültigkeit prüfen
                if( $charLengthArray[0] != '' && (strlen($_POST['input_'.$id]) < $charLengthArray[0]) )
                {
                    $returnArray[0] = FALSE;
                    $returnArray['class']['input_'.$id] = 'errorField';
                    $returnArray['text']['input_'.$id] = 'Es müssen min. '.$charLengthArray[0].' Zeichen eingegeben werden!';
                }
                if( $charLengthArray[1] != '' && (strlen($_POST['input_'.$id]) > $charLengthArray[1]) )
                {
                    $returnArray[0] = FALSE;
                    $returnArray['class']['input_'.$id] = 'errorField';
                    $returnArray['text']['input_'.$id] = 'Es dürfen max. '.$charLengthArray[0].' Zeichen eingegeben werden!';
                }
                // Inhalt des Eingabefeldes auf Buchstaben bzw. Zahlen prüfen
                $checkboxes = 0;
                $pattern = '';
                foreach( $charRegArray as $value )
                {
                    $checkboxes += $value;
                }
                if( $checkboxes == '1' )
                {
                    $pattern = "/[^a-zA-Z\-äÄöÖüÜß\s]/";
                    $errortext = 'Nur die Zeichen A-Z, a-z incl. Umlaute sowie - und Leerzeichen sind zulässig!';
                }
                if( $checkboxes == '2' )
                {
                    $pattern = "/[^\d]/";
                    $errortext = 'Nur die Zeichen 0-9 sind zulässig!';
                }
                if( $checkboxes == '3' )
                {
                    $pattern = "/[^\w\-äÄöÖüÜß\s]/";
                    $errortext = 'Nur die Zeichen 0-9, A-Z, a-z incl. Umlaute sowie - und Leerzeichen sind zulässig!';
                }
                if( $exCharReg != '' )
                {
                    $meta = array("*","+","-","?",".","(",")","[","]","{","}","/","|","^","$");
                    $metaChar = array("\*","\+","\-","\?","\.","\(","\)","\[","\]","\{","\}","\/","\|","\^","\$");
                    $regEx = str_replace($meta,$metaChar,$exCharReg);
                    $pattern = str_replace(']',$regEx.']',$pattern);
                    $errortext = str_replace('sind','und '.$exCharReg.' sind',$errortext);
                }
                if( preg_match($pattern,$_POST['input_'.$id]) )
                {
                    $returnArray[0] = FALSE;
                    $returnArray['class']['input_'.$id] = 'errorField';
                    $returnArray['text']['input_'.$id] = $errortext;
                }
            }
        }
    }
    return $returnArray;
}

function checkSelectfields($fields,$returnArray)
{
    // wenn Auswahlfelder ausgewählt
    foreach( $fields as $id )
    {
        if( $id != '' )
        {
            if( empty($_POST['dropdown_'.$id]) )
            {
                $returnArray[0] = FALSE;
                $returnArray['class']['dropdown_'.$id] = 'errorSelectfield';
                $returnArray['text']['dropdown_'.$id] = 'Es muß eine Auswahl erfolgen!';
            }
        }
    }
    return $returnArray;
}

function checkOptionfields($fields,$returnArray)
{
    // wenn Optionsfelder ausgewählt
    foreach( $fields as $id )
    {
        if( $id != '' )
        {
            if( !isset($_POST['option_'.$id]) )
            {
                $returnArray[0] = FALSE;
                $returnArray['class']['option_'.$id] = 'errorField';
                $returnArray['text']['option_'.$id] = 'Es muß eine Auswahl erfolgen!';
            }
        }
    }
    return $returnArray;
}

function checkCheckboxfields($dbId,$sqlTable,$fields,$returnArray)
{
    // wenn Optionsfelder ausgewählt
    foreach( $fields as $id )
    {
        $result = mysql_query("SELECT notRequ FROM ".$sqlTable." WHERE id = '".$id."'",$dbId);
        $notRequArray = mysql_fetch_row($result);
        if( ($id != '') && !($notRequArray[0]) )
        {
            if( (!isset($_POST['check_'.$id])) || ($_POST['check_'.$id] == '') )
            {
                $returnArray[0] = FALSE;
                $returnArray['class']['check_'.$id] = 'errorField';
                $returnArray['text']['check_'.$id] = 'Es muß eine Auswahl erfolgen!';
            }
        }
    }
    return $returnArray;
}

//@$field string
//@$allowedChars string
//@$errorText string
//@$returnArray array
//@return array
function checkFieldChars($fieldName,$allowedChars,$errorText,$returnArray)
{
    if( (strlen($_POST[$fieldName]) < 2) || (preg_match($allowedChars,$_POST[$fieldName])) )
    {
        $returnArray[0] = FALSE;
        $returnArray['class'][$fieldName] = 'errorField';
        $returnArray['text'][$fieldName] = $errorText;
    }
    return $returnArray;
}

//@$fielddata array
//@$dataarray array
//@$fieldtype string
//@return string
function inputfielddata_to_inputdata($fielddata,$dataarray,$fieldtype)
{
    foreach( $fielddata as $id )
    {
        if( $id != '' )
        {
            $inputdata .= "#".$id.";".$dataarray[$fieldtype.$id]."#";
        }
    }
    return $inputdata;
}

//@$fielddata string
//@$dataarray array
//@$fieldtype string
//@return array
function inputfielddata_to_inputfields($fielddata,$dataarray,$fieldtype)
{
    foreach( explode('##',$fielddata) as $value )
    {
        $dataarray[$fieldtype.substr(trim($value,'#'),'0',strpos(trim($value,'#'),';'))] = ltrim(strstr(trim($value,'#'),';'),';');
    }
    return $dataarray;
}
?>