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
 * @WTL version  1.7.3
 * @date - time  11.08.2017 - 19:00
 * @copyright    Marc Busse 2012-2020
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
            $result = mysqli_query($dbId,"SELECT notRequ, charReg, charLength, regEx FROM ".$sqlTable." WHERE id = '".$id."'");
            while( $daten = mysqli_fetch_object($result) )
            {
                $notRequ = $daten->notRequ;
                $charLengthArray = unserialize($daten->charLength);
                $charRegArray = unserialize($daten->charReg);
                $exCharReg = $daten->regEx;
            }
            if( !$notRequ )
            {
                // Zeichenlänge des Eingabefeldes auf Gültigkeit prüfen
                if( $charLengthArray[0] != '' && (strlen($_POST[$id]) < $charLengthArray[0]) )
                {
                    $returnArray[0] = FALSE;
                    $returnArray['class'][$id] = 'errorField';
                    $returnArray['text'][$id] = 'Es müssen min. '.$charLengthArray[0].' Zeichen eingegeben werden!';
                }
                if( $charLengthArray[1] != '' && (strlen($_POST[$id]) > $charLengthArray[1]) )
                {
                    $returnArray[0] = FALSE;
                    $returnArray['class'][$id] = 'errorField';
                    $returnArray['text'][$id] = 'Es dürfen max. '.$charLengthArray[0].' Zeichen eingegeben werden!';
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
                if( preg_match($pattern,$_POST[$id]) )
                {
                    $returnArray[0] = FALSE;
                    $returnArray['class'][$id] = 'errorField';
                    $returnArray['text'][$id] = $errortext;
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
            if( empty($_POST[$id]) )
            {
                $returnArray[0] = FALSE;
                $returnArray['class'][$id] = 'errorSelectfield';
                $returnArray['text'][$id] = 'Es muß eine Auswahl erfolgen!';
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
        $result = mysqli_query($dbId,"SELECT notRequ FROM ".$sqlTable." WHERE id = '".$id."'");
        $notRequArray = mysqli_fetch_row($result);
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
//@return string
function inputfielddata_to_inputdata($fielddata,$dataarray)
{
    foreach( $fielddata as $id )
    {
        if( $id != '' )
        {
            $inputdata .= "#".$id.";".$dataarray[$id]."#";
        }
    }
    return $inputdata;
}

//@$fielddata string
//@$dataarray array
//@return array
function inputfielddata_to_inputfields($fielddata,$dataarray)
{
    foreach( explode('##',$fielddata) as $value )
    {
        $dataarray[substr(trim($value,'#'),'0',strpos(trim($value,'#'),';'))] = ltrim(strstr(trim($value,'#'),';'),';');
    }
    return $dataarray;
}

//@$fieldarry array
//@$id sring
//@$MYSQL string
//@return array
function findFieldsFromDB($dbId,$fieldarry,$id,$MYSQL)
{
    $fc = 0;
    while( count($fieldarry) > $fc )
    {
        if( $fieldarry[$fc][2] == '1' )
        {
            if( $fieldarry[$fc][3] == 'input' )
            {
                $value = $MYSQL[$fieldarry[$fc][0]];
            }
            else
            {
                $result = mysqli_query($dbId,"SELECT dataLabel FROM wtl_fields WHERE isSet != '1' AND
                    setNo = '".$fieldarry[$fc][1]."' AND data = '".$MYSQL[$fieldarry[$fc][0]]."'");
                $label = mysqli_fetch_row($result);
                $value = $label[0];
            }
        }
        if( $id != 'NULL' )
        {
            if( $fieldarry[$fc][2] != '1' )
            {
                $result = mysqli_query($dbId,"SELECT inputs, selected FROM wtl_members WHERE id = '".$id."'");
                $data_m = mysqli_fetch_row($result);
                if( $fieldarry[$fc][3] == 'input' )
                {
                    $inputs = parse_inputs($data_m[0]);
                    $value = $inputs[$fieldarry[$fc][0]];
                }
                else
                {
                    $inputs = parse_inputs($data_m[1]);
                    $result = mysqli_query($dbId,"SELECT dataLabel FROM wtl_fields WHERE isSet != '1' AND
                        setNo = '".$fieldarry[$fc][1]."' AND data = '".$inputs[$fieldarry[$fc][0]]."'");
                    $label = mysqli_fetch_row($result);
                    $value = $label[0];
                }
            }
        }
        $var[] = $value;
        $fc++;
    }
    return $var;
}
?>