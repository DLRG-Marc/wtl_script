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
 * @WTL version  1.5.0
 * @date - time  01.10.2013 - 19:00
 * @copyright    Marc Busse 2012-2020
 * @author       Marc Busse <http://www.eutin.dlrg.de>
 * @license      GPL
 */


        error_reporting(0);
        if( file_exists('wtl_globals_local.php') )
        {
            require_once('wtl_globals_local.php');
        }
        else
        {
            require_once('wtl_globals.php');
        }
        require_once('f_global.php');
        require_once('f_login.php');
        $dbId = connectDatebase();
        require_once("location.inc");
        require_once(preg_replace('/^(\/)+/',$_SERVER['DOCUMENT_ROOT'].'/',glob_sys."function.inc.php"));
        $_DLRG_SYS['mode']=true;
        $_DLRG_SYS['get']=check_input($_GET,'doc',60,'^\/|[^a-zA-Z0-9_\/]','',$_DLRG_PATH['content']."###.inc",$_DLRG_INC['start'],$_DLRG_INC['error']);
        require_once("config.inc");
        include_once($_DLRG_PATH['content'].$_DLRG_SYS['get'].".inc");
        $_DLRG_MENU=control_menu($_DLRG_DOC['id'], $_DLRG_MENU);
        $_DLRG_STYLE=style_switch(glob_lyt.$_DLRG_PATH['style'], $_DLRG_STYLE);
        session_name('WTLSSID'); session_start();
        if($_SESSION['intern']['loggedIn']===TRUE){ checkAutologout($dbId,'wtl_user',$_SESSION['intern']['userId']); }
        if(($_DLRG_DOC['access']=="") && ($_DLRG_DOC['session']=='intern')){ if($_SESSION['intern']['loggedIn']===TRUE){
            $_DLRG_SYS['get']='wtl_view'; include_once($_DLRG_PATH['content'].$_DLRG_SYS['get'].".inc"); $_DLRG_MENU=control_menu($_DLRG_DOC['id'], $_DLRG_MENU); } }
        if(($_DLRG_DOC['access']!="") && ($_DLRG_DOC['session']=='intern')){ if($_SESSION['intern']['loggedIn']!==TRUE){
            $_DLRG_SYS['get']='wtl_intern'; include_once($_DLRG_PATH['content'].$_DLRG_SYS['get'].".inc"); } }
        if($_GET['login']=='logout'){ $_SESSION = array(); session_destroy();
            $_DLRG_SYS['get']='wtl_logout'; include_once($_DLRG_PATH['content'].$_DLRG_SYS['get'].".inc"); }
?>


<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="content-language" content="de"/>
    <meta name="robots" content="index, follow"/>
    <meta name="keywords" content="DLRG,Deutsche Lebens-Rettungs-Gesellschaft,Wasserrettung,<?php echo $_DLRG_DOC['keyword']; ?>"/>
    <meta name="description" content="DLRG - <?php echo $_DLRG_DOC['description']; ?>"/>

     <!-- DLRG Webvorlage - Ausführung php - Version 2.5.0 -->

        <title>DLRG - <?php echo $_DLRG_DOC['glied']." - ".$_DLRG_DOC['titel']; ?></title>
        <style type="text/css">
        <!--@import url("<?php echo glob_lyt.$_DLRG_PATH['style']; ?>style.css");
            @import url("<?php echo $_DLRG_PATH['style']; ?>custom.css");
            <?php if($_DLRG_STYLE['switch']) { echo '@import url("'.glob_lyt.$_DLRG_PATH['style'].'media/'.$_DLRG_STYLE['switch'].'.css");'; } ?>

                <?php echo $_DLRG_DOC['style']; ?>

                <?php echo '#s'.preg_replace("/,[ ]?/", ", #s",$_DLRG_MENU['sub']) ?> {
                        display:block; }
                <?php echo '#i'.preg_replace("/,[ ]?/", ", #i",$_DLRG_MENU['top']) ?> {
                        background:transparent url(<?php echo glob_lyt.$_DLRG_PATH['grafik'].$_DLRG_STYLE['switch'].'/' ?>tag_dash.gif) no-repeat center right; }
                <?php echo '#i'.preg_replace("/,[ ]?/", ", #i",$_DLRG_MENU['item']) ?> {
                        background:transparent url(<?php echo glob_lyt.$_DLRG_PATH['grafik'].$_DLRG_STYLE['switch'].'/' ?>tag_solid.gif) no-repeat center right; }
                <?php echo '#menutop #i'.preg_replace("/,[ ]?/", ", #i",$_DLRG_MENU['item']) ?> {
                        background:transparent url(<?php echo glob_lyt.$_DLRG_PATH['grafik'].$_DLRG_STYLE['switch'].'/' ?>tag_white_solid.gif) no-repeat center right; }

        //-->
        </style>
        <link rel="home" title="Home" href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['start']; ?>" />
</head>

<body>
  <div id="top"><a id="oben" name="oben"></a>
  <a class="aural" accesskey="1" href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['start']; ?>">zur Startseite</a>
        <a class="aural" accesskey="2" href="#con">zum Inhalt</a>
        <a class="aural" accesskey="3" href="#nav">zur Navigation</a>
        <a class="aural" accesskey="9" href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['contact']; ?>">Kontakt</a>
        <a class="aural" accesskey="0" href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['access']; ?>">Tastatur Befehle</a>
        <div id="topname">
        <p id="dlrgname"><a href="http://www.dlrg.de" title="www.dlrg.de">Deutsche Lebens-Rettungs-Gesellschaft</a></p>
        <p id="gliedname">
          <?php
             if($_DLRG_DOC['gliedlv_link']!=""){echo "<a href=\"http://".$_DLRG_DOC['gliedlv_link']."\"  title=\"".$_DLRG_DOC['gliedlv_link']."\">".$_DLRG_DOC['gliedlv']."</a>";}else { echo $_DLRG_DOC['gliedlv'];}
             if($_DLRG_DOC['gliedbz']!=""){if($_DLRG_DOC['gliedbz_link']!=""){echo "&nbsp;-&nbsp;<a href=\"http://".$_DLRG_DOC['gliedbz_link']."\"  title=\"".$_DLRG_DOC['gliedbz_link']."\">".$_DLRG_DOC['gliedbz']."</a>";}else { echo "&nbsp;-&nbsp;".$_DLRG_DOC['gliedbz'];}}
             if($_DLRG_DOC['glied']!=""){ echo "&nbsp;-&nbsp;".$_DLRG_DOC['glied'];} ?>
        </p>

        </div>
        <div id="menulogo"><a href="http://www.dlrg.de/" title="www.dlrg.de"></a></div>

        <div id="printlogo">
          <img src="<?=glob_lyt.$_DLRG_PATH['style']; ?>../grafik/print_kopf.gif" alt="printlogo" />
        </div>

        <div id="menustyle">
                <a class="menubutton aural" id="switchaural" accesskey="a" href="<?php echo $_DLRG_SYS['call'].$_DLRG_SYS['get']; ?>&amp;style=<?php echo ($_DLRG_STYLE['switch']=='aural')?'normal':'aural'; ?>" title="optimiert fuer Screen Reader">Sprachausgabe optimiert</a>
        </div>
        <div id="menutop">
                <a class="menuitem" id="i9001" accesskey="4" href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['search'];  ?>" <?php echo $_DLRG_INC['search'] ==""?"style='display:none;'":""; ?>>Suche</a>
                <a class="menuitem" id="i9002" accesskey="5" href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['sitemap']; ?>" <?php echo $_DLRG_INC['sitemap']==""?"style='display:none;'":""; ?>>&Uuml;bersicht</a>
                <a class="menuitem" id="i9003" accesskey="6" href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['impress']; ?>" <?php echo $_DLRG_INC['impress']==""?"style='display:none;'":""; ?>>Impressum</a></div>
  </div>
  <div id="page">
    <div id="menu"><a name="nav"></a>
                <div id="menuquick"><?php include($_DLRG_PATH['content'].$_DLRG_MENU['quick'].".inc"); ?></div>
                 <?php if(isset($_DLRG_DOC['spendenID']) && $_DLRG_DOC['spendenID']!=""){?>
                   <a id="menuextra" href="https://www.dlrg.de/Onlinespenden/?gid=<?php echo $_DLRG_DOC['spendenID'];?>" title="Online Spenden" target="_blank" >&nbsp;</a>
                   <div id="abstand">&nbsp;</div>
                 <?php }; ?>
                 <?php if(isset($_DLRG_DOC['wappen']) && $_DLRG_DOC['wappen']!=""){?>
                   <div id="wappen"><img src="<?php echo $_DLRG_PATH['image'].$_DLRG_DOC['wappen']; ?>" alt="<?php echo $_DLRG_DOC['wappen']; ?>"/></div>
                 <?php }; ?>
                <div id="menumain"><?php include($_DLRG_PATH['content'].$_DLRG_MENU['main'].".inc"); ?>
                 <?php if($_SESSION['extern']['loggedIn']===TRUE){?>
                   <a class="menuitem" id="i0101" href="index.php?doc=swm_change_pw">Passwort ändern</a>
                 <?php }; ?></div>
                 <?php if( ($_SESSION['intern']['loggedIn']===TRUE) || ($_SESSION['extern']['loggedIn']===TRUE) ){?>
                   <div id="userlogout"><input class='button' type='button' name='sendLogout' value='Logout' onclick="location.href='<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>&amp;login=logout'"/></div>
                 <?php }; ?>
                 <?php if(isset($_DLRG_MENU['other']) && $_DLRG_MENU['other']!=""){?>
                   <div id="menuads"><?php include($_DLRG_PATH['content'].$_DLRG_MENU['other'].".inc"); ?></div>
                 <?php }; ?>
                 <?php if(isset($_DLRG_MENU['ads']) && $_DLRG_MENU['ads']!=""){?>
                <div id="menuads"><?php include($_DLRG_PATH['content'].$_DLRG_MENU['ads'].".inc"); ?></div>
                 <?php }; ?>
                 <?php if(isset($_DLRG_INC['bank']) && $_DLRG_INC['bank']!=""){include($_DLRG_PATH['content'].$_DLRG_INC['bank'].".inc");}?>
        </div>
    <div id="body">
          <div id="header">
            <div id="headerpicbar"><img class="headerpic" src="<?php echo $_DLRG_PATH['image'].$_DLRG_DOC['picbar']; ?>" alt="<?php echo $_DLRG_DOC['pictext']; ?>"/></div>
                <div id="headercrumbs">
                 <div id="box_header_left">
                  <a href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['start']; ?>"><?php if($_DLRG_DOC['glied']!=""){ echo $_DLRG_DOC['glied'];}if($_DLRG_DOC['glied']=="" && $_DLRG_DOC['gliedbz']!=""){ echo $_DLRG_DOC['gliedbz'];}
                     if($_DLRG_DOC['gliedbz']=="" && $_DLRG_DOC['gliedlv']!=""){ echo $_DLRG_DOC['gliedlv'];} ?></a>&nbsp;&gt;&nbsp;<?php foreach($_DLRG_CRUMB as $name=>$link) { echo("<a href='".$_DLRG_SYS['call'].$link."'>".$name."</a>");
                     if(count($_DLRG_CRUMB)<>null){echo "&nbsp;&gt;&nbsp;";} } echo($_DLRG_DOC['titel']); ?>
                 </div>
                 <div id="box_header_right">
                  <a class="menubutton" id="switchcolor" accesskey="k" href="<?php echo $_DLRG_SYS['call'].$_DLRG_SYS['get']; ?>&amp;style=<?php echo ($_DLRG_STYLE['switch']=='mono')?'normal':'mono'; ?>" title="monochrom Ansicht an/aus">&nbsp;</a>
                  <a class="menubutton" id="switchsize" accesskey="g" href="<?php echo $_DLRG_SYS['call'].$_DLRG_SYS['get']; ?>&amp;style=<?php echo ($_DLRG_STYLE['switch']=='large')?'normal':'large'; ?>" title="grosse Schrift an/aus">&nbsp;</a>
            <!--      <a class="menubutton" id="switchger" href="#" title="Deustche Sprache">&nbsp;</a>
                  <a class="menubutton" id="switcheng" href="#" title="Englische Sprache">&nbsp;</a>   -->

                 </div>
                </div>
          </div>
          <div id="content"><a name="con"></a>
                          <?php $_DLRG_SYS['mode']=false; include($_DLRG_PATH['content'].$_DLRG_SYS['get'].".inc"); ?>
          <div id="footerspace"></div></div>
          <div id="footer">
                  <!-- Fusszeile -->
                  <div class="footertext">
                                Ansprechpartner: <a class="white" href="mailto:<?php echo $_DLRG_DOC['email']; ?>"><?php echo $_DLRG_DOC['author']; ?></a><br/>
                                Letzte Änderung: <?php echo $_DLRG_DOC['datum']; ?>
                        <span id="bottext">Adresse: <?php echo $_SERVER['SERVER_NAME'].preg_replace('/&(?![a-z]+?;)/', '&amp;',$_SERVER['REQUEST_URI']); ?></span></div>
                <div id="menubot">
                        <a class="menuitem" id="i9901" href="javascript:print()">drucken</a>
                        <a class="menuitem" id="i9902"  href="<?php echo $_DLRG_SYS['call'].$_DLRG_INC['tell']; ?>" <?php echo $_DLRG_INC['tell']==""?"style='display:none;'":""; ?>>weiterempfehlen</a>
                        <a class="menuitem" id="i9903"   href="#oben">Seitenanfang</a></div>
          </div>
        </div>
        <div id="bot">&nbsp;</div>
  </div>


  <?php
  // Dieser Bereich dient zum einblenden des zusätzlichen Contentbereiches auf der rechten Seite
  // Eer kann durch die Variable $_DLRG_INC['banner'] ein-/ausgeblendet werden.
    if(isset($_DLRG_INC['banner']) && $_DLRG_INC['banner']!="")
     {
     echo "<div class=\"banner\">
            <div class=\"corner\">
            <b class=\"cornertop\">&nbsp;<b class=\"b1\">&nbsp;</b><b class=\"b2\">&nbsp;</b><b class=\"b3\">&nbsp;</b><b class=\"b4\">&nbsp;</b>&nbsp;</b>
            <div class=\"cornercontent\">";
     include($_DLRG_PATH['content'].$_DLRG_INC['banner'].".inc");
     echo "  </div>
            <b class=\"cornerbottom\">&nbsp;<b class=\"b4b\">&nbsp;</b><b class=\"b3b\">&nbsp;</b><b class=\"b2b\">&nbsp;</b><b class=\"b1b\">&nbsp;</b>&nbsp;</b>
           </div>
           </div>";
     };
  ?>


</body>
</html>