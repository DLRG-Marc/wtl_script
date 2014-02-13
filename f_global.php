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
 * @WTL version  1.5.1
 * @date - time  13.02.2014 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


//@return string
function errorNote()
{
    return '<div class="border"><table class="errorTable">
        <tr><td><div class="error_img"><img src="'.$GLOBALS['SYSTEM_SETTINGS']['GRAPHIC_PATH'].'achtung_gross.gif" alt="Achtung"/></div></td>
        <td><b>Achtung!<br/>Bitte alle farblich markierten Felder richtig ausfüllen.<br/></b></td>
        </tr></table></div><br/>';
}

//@return int
function connectDatebase()
{
    // Verbindung zur Datenbank herstellen
    $dbId = mysql_connect($GLOBALS['DB_SETTINGS']['HOST'].":".$GLOBALS['DB_SETTINGS']['PORT'],$GLOBALS['DB_SETTINGS']['USER'],
        $GLOBALS['DB_SETTINGS']['PASSWORD']) OR die ("Keine Verbindung zum Server möglich: " .mysql_error());
    @mysql_select_db($GLOBALS['DB_SETTINGS']['DATABASE'],$dbId);
    @mysql_query("SET NAMES 'utf8'");
    return $dbId;
}

//@$name string
//@$oldValue string
//@$newValue string
// NO RETURN
function changeGlobals($name,$oldValue,$newValue)
{
    $filename = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH'].'/'.$GLOBALS['SYSTEM_SETTINGS']['GLOBAL_FILENAME'];
    $lines = file($filename);
    $lines = str_replace($name." = ".$oldValue,$name." = ".$newValue,$lines);
    file_put_contents($filename,implode('',$lines));
}

//@$rows array
//@$textBeforeTable string
//@$textAfterTable string
//@return array
function makeTable($rows,$textBeforeTable,$textAfterTable)
{
    echo "<div>";
    echo $textBeforeTable;
    echo "<div class='border highlight block'>";
    // Head der Tabelle
    echo "
        <table>
        <thead>
        <tr>
    ";
    foreach( $rows[0] as $ci=>$v )
    {
        $cl_th = '';
        if( $ci == 0 )
        {
            $cl_th = " col_first";
        }
        if( $ci == (count($rows[0])-1) )
        {
            $cl_th = " col_last";
        }
        echo "<th class='head_".$ci.$cl_th."'>".(strlen($v) ? nl2br($v) : '&nbsp;')."</th>";
    }
    array_shift($rows);
    echo "</tr>";
    echo "</thead>";
    // Body der Tabelle
    $rowLimit = count($rows);
    echo "<tbody>";
    for( $ri=0; $ri<$rowLimit; $ri++ )
    {
        $cl_tr = '';
        if( $ri == 0 )
        {
            $cl_tr = " row_first";
        }
        if( $ri == ($rowLimit-1) )
        {
            $cl_tr = " row_last";
        }
        if( ($ri%2) == 0 )
        {
            $cl_eo = " even";
        }
        if( ($ri%2) != 0 )
        {
            $cl_eo = " odd";
        }
        echo "<tr class='row_".$ri.$cl_tr.$cl_eo."'>";
        foreach( $rows[$ri] as $ci=>$v )
        {
            $cl_td = '';
            if( $ci == 0 )
            {
                $cl_td = " col_first";
            }
            if( $ci == (count($rows[$ri])-1) )
            {
                $cl_td = " col_last";
            }
            echo "<td class='col_".$ci.$cl_td."'>".
                (strlen($v) ? ((strpos($v,'<')!==FALSE) ? preg_replace('/[\n\r]+/i', '<br />', $v) : preg_replace(array('/[\n\r]+/i', '/@/'),
                 array('<br />', '@<br />'), $v)) : '&nbsp;')."</td>";
        }
        echo "</tr>";
    }
    echo "
        </tbody>
        </table>
    ";
    echo "</div>";
    echo $textAfterTable;
    echo "</div>";
}

//@$sqlTable string
//@$headline string
//@$hiddenFields string
//@$sqlCond string
//@$searchField_1 string
//@$searchField_2 string
//@$searchTextHead_1 string
//@$searchTextHead_2 string
//@return result
function searchText($sqlTable,$headline,$hiddenFields,$sqlCond,$searchField_1,$searchField_2,$searchTextHead_1,$searchTextHead_2)
{
        // Suchformular erstellen
        echo "<p><b>".$headline." :</b></p>";
        echo "
            <form name='search_form' method='post' action='".htmlspecialchars($_SERVER['REQUEST_URI'])."'>
            <div class='border'>
            <table>
                <tr>
                    <td>".$searchTextHead_1." :</td>
                    <td>&nbsp;</td>
                    <td>".$searchTextHead_2." :</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><input type='text' name='searchText_1' size='17' value='".$_POST['searchText_1']."'/></td>
                    <td>".$hiddenFields."oder</td>
                    <td><input type='text' name='searchText_2' size='17' value='".$_POST['searchText_2']."'/></td>
                    <td><input class='button' type='submit' name='search' value='suchen'/></td>
                </tr>
            </table>
            </div>
            </form>
        ";
        echo "<p></p><p></p>";
    // Schutzmechanismus gegen Cross-Side Scripting
    foreach( $_POST as $index => $val )
    {
        $_POST[$index] = trim(htmlspecialchars( $val, ENT_NOQUOTES, UTF-8 ));
        $MYSQL[$index] = mysql_real_escape_string($_POST[$index]);
    }
    // wenn suchen ausgelöst
    if( isset($_POST['search']) )
    {
        if( $sqlCond != '' )
        {
            $cond = $sqlCond." AND";
        }
        if( $_POST['searchText_1'] && $_POST['searchText_2'] )
        {
            $result = mysql_query("SELECT * FROM ".$sqlTable." WHERE ".$cond." (".$searchField_1." LIKE '".$MYSQL['searchText_1']."%' OR ".
                $searchField_2." LIKE '".$MYSQL['searchText_2']."%') ORDER BY id ASC");
        }
        elseif( $_POST['searchText_1'] )
        {
            $result = mysql_query("SELECT * FROM ".$sqlTable." WHERE ".$cond." (".$searchField_1." LIKE '".$MYSQL['searchText_1']."%')
                ORDER BY ".$searchField_1." ASC, ".$searchField_2." ASC");
        }
        elseif( $_POST['searchText_2'] )
        {
            $result = mysql_query("SELECT * FROM ".$sqlTable." WHERE ".$cond." (".$searchField_2." LIKE '".$MYSQL['searchText_2']."%')
                ORDER BY ".$searchField_2." ASC, ".$searchField_1." ASC");
        }
        else
        {
            if( $sqlCond != '' )
            {
                $cond = " WHERE ".$sqlCond;
            }
            $result = mysql_query("SELECT * FROM ".$sqlTable.$cond." ORDER BY ".$searchField_1." ASC, ".$searchField_2." ASC");
        }
    }
    return $result;
}

//@$sender string
//@$receiver string
//@$subject string
//@$text string
//@return result
function send_mail($sender,$receiver,$subject,$text)
{
    $result = FALSE;
    mb_internal_encoding('UTF-8');
    // baut header der mail zusammen
    $headers  = 'From: '.$sender. "\n";
    $headers .= 'Reply-To: '.$sender. "\n";
    $headers .= 'X-Mailer: PHP/'. phpversion(). "\n";
    $headers .= 'MIME-Version: 1.0'. "\n";
    $headers .= 'Content-type: text/plain; charset=utf-8;'. "\n";
    $headers .= 'Content-Transfer-Encoding: 8bit'. "\n";
    // Mail versenden
    //echo $receiver.'<br/>'.$subject.'<br/>'.$text.'<br/>'.$headers;
    $result = mail($receiver, mb_encode_mimeheader($subject,'UTF-8','Q'), $text, $headers);
    return $result;
}

//@$date string
//@$separator string
//@return boolean
function check_date($date,$separator)
{
    $result = FALSE;
    $date_1 = explode($separator,trim($date));
    if( $date_1[0]!='' && $date_1[1]!='' && $date_1[2]!='' )
    {
        if( checkdate($date_1[1], $date_1[0], $date_1[2]) )
        {
            $result = TRUE;
        }
    }
    return $result;
}

//@$email string
//@return boolean
function check_email($email)
{
    $result = FALSE;
    if(preg_match('/^[^\x00-\x20()<>@,;:\\".[\]\x7f-\xff]+(?:\.[^\x00-\x20()<>@,;:\\".[\]\x7f-\xff]+)*\@[^\x00-\x20()<>@,;:\\".[\]\x7f-\xff]+(?:\.[^\x00-\x20()<>@,;:\\".[\]\x7f-\xff]+)+$/i', $email))
    {
        $result = TRUE;
    }
    return $result;
}

//@$birthday string
//@return int
function calcAge($birthday)
{
    $date_now = explode('-',date('Y-m-d'));
    $date = explode('-',$birthday);
    $age = $date_now[0] - $date[0];
    if( ($date_now[1] < $date[1]) || (($date_now[1] == $date[1]) && ($date_now[2] < $date[2])) )
    {
        $age--;
    }
    return $age;
}

//@$length int
//@return sring
function buildPassword($length)
{
    $strUniqueID = uniqid(mt_rand (),TRUE);
    $strMD5Hash = md5($strUniqueID);
    return substr($strMD5Hash,0,$length);
}

//@$date string
//@return string
function date_mysql2german($date)
{
    $d = explode('-',$date);
    return sprintf('%02d.%02d.%04d', $d[2], $d[1], $d[0]);
}

//@$date string
//@return string
function date_german2mysql($date)
{
    $d = explode('.',$date);
    return sprintf('%04d-%02d-%02d', $d[2], $d[1], $d[0]);
}

//@$begin string
//@$end string
//@$format string
//@$separator string
//@return string
function days_between($begin,$end,$format,$separator)
{
    $result = FALSE;
    $pos1 = strpos($format, 'd');
    $pos2 = strpos($format, 'm');
    $pos3 = strpos($format, 'Y');
    $begin = explode($separator,trim($begin));
    $end = explode($separator,trim($end));
    if( $begin[0]!='' && $begin[1]!='' && $begin[2]!='' && $end[0]!='' && $end!='' && $end[2]!='' )
    {
      $first = GregorianToJD($end[$pos2],$end[$pos1],$end[$pos3]);
      $second = GregorianToJD($begin[$pos2],$begin[$pos1],$begin[$pos3]);
      $result = $second - $first;
    }
    return $result;
}

//@$array array
//@$trenner string
//@return string
function array_to_text_with_trenner($array,$trenner)
{
    foreach( $array as $value )
    {
        if( trim($value) != '')
        {
            $text .= "$trenner".trim($value);
        }
    }
    return substr($text,strlen($trenner));
}

//@$words string
//@return string
function words_to_words_first_capital_letter($words)
{
    return str_replace(' - ','-',ucwords(str_replace('-',' - ',mb_strtolower($words,"UTF-8"))));
}

//@$number int
//@return string
function number_to_janein($number)
{
    if( $number == 0 )
    { return '-'; }
    if( $number == 1 )
    { return 'Nein'; }
    if( $number == 2 )
    { return 'Ja'; }
    else
    { return false; }
}

//@$s string
//@return array
function parse_inputs($s)
{
    $vars = array();
    $offset = 0;
    while( $offset < strlen($s) )
    {
        $ib = strpos($s,'#',$offset)+1;
        $ie = strpos($s,';',$offset);
        $vb = $ie + 1;
        $ve = strpos($s,'#',$offset+1);
        $offset = $ve + 1;
        $vars[substr($s,$ib,$ie-$ib)] = substr($s,$vb,$ve-$vb);
    }
    return $vars;
}

function kreisdiagramm($abstand,$daten,$einheit,$breite,$hoehe)
{
    $schrift = 3;
    $legende_abstand = 10;
    $daten = explode(',',$daten);
    $werte = array();
    $bezeichnungen = array();
    $farben = array();
    for($i=0; $i<sizeof($daten); $i++)
    {
        $temp = explode(':',$daten[$i]);
        array_push($bezeichnungen, $temp[0]);
        array_push($werte, $temp[1]);
        array_push($farben, $temp[2]);
        if( $abstand_text < imagefontwidth($schrift) * strlen($temp[0]) )
        {
            $abstand_text = imagefontwidth($schrift) * strlen($temp[0]);
        }
    }
    $abstand_text_h = imagefontheight($schrift);
    $bild = imagecreatetruecolor($breite, $hoehe);
    $farbe_hintergrund = imagecolorexact($bild, 235, 242, 245);
    $farbe_text = imagecolorexact($bild, 0, 0, 0);
    $farbe_zwischen = imagecolorexact($bild, 220, 220, 220);
    $farbe_rot = imagecolorexact($bild, 255, 0, 0);
    $farbe_gruen = imagecolorexact($bild, 0, 255, 0);
    $farbe_schwarz = imagecolorexact($bild, 0, 0, 0);
    $farbe_gelb = imagecolorexact($bild, 255, 255, 0);
    $farbe_lila = imagecolorexact($bild, 255, 0, 255);
    imagefill($bild, 0, 0, $farbe_hintergrund);
    // Kreisdiagramm
    $diagramm_durchmesser = $hoehe - 2 * $abstand;
    $diagramm_x = $diagramm_durchmesser / 2 + $abstand;
    $diagramm_y = $diagramm_x;
    $diagramm_winkel1 = 0;
    $legende_x = $diagramm_durchmesser + 3 * $abstand;
    $legende_y = $hoehe - $abstand - $legende_abstand;
    $legende_b = $legende_x + $legende_abstand;
    $legende_h = $legende_y + $legende_abstand;
    $legende_versatz = 0;
    for($i=0; $i<sizeof($werte); $i++)
    {
        $prozent = 100 / array_sum($werte) * $werte[$i];
        $grad = 360 / 100 * $prozent;
        $diagramm_winkel2 = $grad + $diagramm_winkel1;
        $wert = $werte[$i]." ".$einheit;
        $farbe = "farbe_".$farben[$i];
        if($werte[$i] != 0)
        {
            imagefilledarc($bild,$diagramm_x,$diagramm_y,$diagramm_durchmesser,$diagramm_durchmesser,$diagramm_winkel1,$diagramm_winkel2,${$farbe},IMG_ARC_PIE);
        }
        imagefilledrectangle($bild,$legende_x,$legende_y - $legende_versatz,$legende_b,$legende_h - $legende_versatz,${$farbe});
        imagestring($bild,$schrift,$legende_x + 2 * $legende_abstand,$legende_y - $legende_versatz,$bezeichnungen[$i],$farbe_text);
        imagestring($bild,$schrift,$legende_x + 3 * $legende_abstand + $abstand_text,$legende_y - $legende_versatz,$wert,$farbe_text);
        $diagramm_winkel1 = $diagramm_winkel1 + $grad;
        $legende_versatz = $legende_versatz + 2 * $legende_abstand;
    }
    imagegif($bild,$GLOBALS['SYSTEM_SETTINGS']['TEMP_PATH'].'diagramm_1.gif');
    imagedestroy($bild);
}

function progressGirder($type,$width,$GW,$PW,$round,$border,$colors)
{
    if($PW > $GW)
    {
        $PW = $GW;
    }
    $girderwidth = ($PW / $GW) * $width;
    $prozent = round((($PW / $GW) * 100), $round);
    foreach($colors AS $interval => $intervalColor)
    {
        $value = explode("-", $interval);
        if(($value[0] <= $prozent) && ($prozent <= $value[1]))
        {
            $color = $intervalColor;
            break;
        }
    }
    if($type == 0)
    {
        $progressGirder  = "<div style='text-align: left; border: $border; width: ".$width."px; height: auto; padding: 0px;'>\n";
        $progressGirder .= "<div style='text-align: center; width: ".$girderwidth."px; background-color: $color;'>&nbsp;</div>\n";
        $progressGirder .= "</div>\n";
    }
    elseif($type == 1)
    {
        if($girderwidth <= 10)
        {
            $wertbreite = $girderwidth;
        }
        else
        {
            $wertbreite = $girderwidth - 10;
        }
        $progressGirder  = "<div style='text-align: left; border: $border; width: ".$width."px; height: auto; padding: 0px;'>\n";
        $progressGirder .= "<div style='text-align: center; width: ".$girderwidth."px; background-color: $color; height: 15px;'></div>\n";
        $progressGirder .= "</div>\n";
        $progressGirder .= "<span style='position: relative; left: ".$wertbreite."px;'>".$prozent."%</span>";
    }
    else
    {
        $progressGirder  = "<div style='text-align: left; border: $border; width: ".$width."px; height: auto; padding: 0px;'>\n";
        $progressGirder .= "<div style='text-align: center; width: ".$girderwidth."px; background-color: $color;'>".$prozent."%</div>\n";
        $progressGirder .= "</div>\n";
    }
    return $progressGirder;
}

?>