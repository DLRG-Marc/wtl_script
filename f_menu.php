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


//@$lines string
//@$searchtext string
//@return int
function searchLineText($lines,$searchtext)
{
    $linenumber = FALSE;
    foreach( $lines as $lineNum => $line )
    {
        if( strpos($line,$searchtext) !== FALSE )
        {
            $linenumber = $lineNum;
        }
    } 
    return $linenumber;
}

//@$lines string
//@$searchtext string
//@$addtext string
//@return string
function addTextBefore($lines,$searchtext,$addtext)
{
    $newlines = array();
    $i = 0;
    foreach( $lines as $lineNum => $line )
    {
        $newlines[$i] = $line;
        if( strpos($line,$searchtext) !== FALSE )
        {
            if( strpos($lines[$lineNum-1],$addtext) === FALSE )
            {
                $newlines[$i+1] = $line;
                $newlines[$i] = $addtext;
                $i++;
            }
        }
        $i++;
    }
    return $newlines;
}

//@$lines string
//@$searchtext string
//@$addtext string
//@return string
function addTextAfter($lines,$searchtext,$addtext)
{
    $newlines = array();
    $i = 0;
    foreach( $lines as $lineNum => $line )
    {
        $newlines[$i] = $line;
        if( strpos($line,$searchtext) !== FALSE )
        {
            if( strpos($lines[$lineNum+1],$addtext) === FALSE )
            {
                $newlines[$i+1] = $addtext;
                $i++;
            }
        }
        $i++;
    }
    return $newlines;
}

//@$lines string
//@$searchtext string
//@return string
function deleteText($lines,$searchtext)
{
    foreach( $lines as $lineNum => $line )
    {
        if( strpos($line,$searchtext) !== FALSE )
        {
            $lines[$lineNum] = "";
        }
    }
    return $lines;
}

//@$menuId int
//@$fileIndex string
//@$fileSubMenu string
//@$fileMenu string
//@$setID int
//@$indexName string
//@$menuName string
// NO RETURN
function makeMenuIndex($menuId,$fileIndex,$fileSubMenu,$fileMenu,$setID,$indexName,$menuName)
{
    $searchText = "id=\"i".$menuId."\" href=\"index.php";
    $newText = "    <a class=\"menuitem\" id=\"i".$menuId."\" href=\"index.php?doc=".$fileIndex."_".$setID."&amp;data=input&amp;listID=".$setID."\">".$menuName."</a>\n";
    // Dateien erstellen und modifizieren für neuen Eintrag
    // neue .inc erstellen
    copy($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'].$fileIndex.".inc",
        $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'].$fileIndex."_".$setID.".inc");
    $filename = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'].$fileIndex."_".$setID.".inc";
    $searchtexts = array("\"id\"=>\"".$menuId."\"","\"titel\"=>\"\"");
    $addtexts = array("\"id\"=>\"".$menuId.($setID + 10)."\"","\"titel\"=>\"".$indexName."\"");
    $lines = file($filename);
    $lines = str_replace($searchtexts,$addtexts,$lines);
    file_put_contents($filename,implode('',$lines));
    // Eintrag in menu_reg erstellen
    $addtext = "        <a class=\"menuitem\" id=\"i".$menuId.($setID + 10)."\" href=\"index.php?doc=".$fileIndex."_".$setID."&amp;data=input&amp;listID=".$setID."\">".$indexName."</a>\n";
    $filename = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'].$fileSubMenu.".inc";
    $lines = file($filename);
    if( searchLineText($lines,$addtext) === FALSE )
    {
        $lines = addTextAfter($lines,"<div class=\"menusub\"",$addtext);
    }
    $lines[searchLineText($lines,$searchText)] = $newText;
    file_put_contents($filename,implode('',$lines));
    // Eintrag in menu erstellen
    $filename = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'].$fileMenu.".inc";
    $lines = file($filename);
    $lines[searchLineText($lines,$searchText)] = $newText;
    file_put_contents($filename,implode('',$lines));
}

//@$menuId int
//@$fileIndex string
//@$fileSubMenu string
//@$fileMenu string
//@$setID int
//@$menuName string
// NO RETURN
function delMenuIndex($menuId,$fileIndex,$fileSubMenu,$fileMenu,$setID,$menuName)
{
    $searchText = "id=\"i".$menuId."\" href=\"index.php";
    $searchText_1 = "id=\"i".$menuId."\" href=\"index.php?doc=".$fileIndex."_".$setID."&amp;data=input&amp;listID=".$setID."\">".$menuName."</a>\n";
    $oldText = "    <a class=\"menuitem\" id=\"i".$menuId."\" href=\"index.php?doc=".$fileIndex."&amp;data=input\">".$menuName."</a>\n";
    // .inc löschen
    unlink($GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'].$fileIndex."_".$setID.".inc");
    // aus menu_reg löschen bzw. ändern
    $filename = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'].$fileSubMenu.".inc";
    $lines = file($filename);
    $lines = deleteText($lines,"<a class=\"menuitem\" id=\"i".$menuId.($setID + 10)."\"");
    $linenum = searchLineText($lines,"<div class=\"menusub\"");
    if( strpos($lines[$linenum+1],"</div>") === FALSE )
    {
        if( strpos($lines[$linenum+2],"</div>") !== FALSE )
        {
            $lines[searchLineText($lines,$searchText)] = $oldText;
            $menuText = $oldText;
        }
        else
        {
            if( $lines[$linenum+1] == "" )
            {
                $menuText = $lines[$linenum+2];
            }
            else
            {
                $menuText = $lines[$linenum+1];
            }
            $menuText = "    <a class=\"menuitem\" id=\"i".$menuId."\" ".substr($menuText,strpos($menuText,"href"),(strpos($menuText,">")-strpos($menuText,"href")+1)).$menuName."</a>\n";
            $lines[searchLineText($lines,$searchText)] = $menuText;
        }
    }
    file_put_contents($filename,implode('',$lines));
    // Eintrag in menu ändern
    $filename = $GLOBALS['SYSTEM_SETTINGS']['GLOBAL_PATH']."/".$GLOBALS['SYSTEM_SETTINGS']['CONTENT_PATH'].$fileMenu.".inc";
    $lines = file($filename);
    if( searchLineText($lines,$searchText_1) !== FALSE )
    {
        $lines[searchLineText($lines,$searchText)] = $menuText;
    }
    file_put_contents($filename,implode('',$lines));
}

?>