<?php if($_DLRG_SYS['mode']){
/* Dokument Konfiguration */

 $_DLRG_DOC = array (
         "id"=>"01",
         "titel"=>"",
#         "author"=>"",
#         "email"=>"",
         "keyword"=>"",
         "description"=>"",
         "access"=>"",
         "session"=>"WTLSSID" )+$_DLRG_DOC;
 $_DLRG_MENU = array (
        "main"=>"menu_reg",
        "sub"=>"",
        "top"=>"",
        "item"=>"" )+$_DLRG_MENU;
 $_DLRG_CRUMB = array (
        "Anmelden"=>"wtl_reg&amp;data=input" );

 $_DLRG_DOC['style'] = <<<CSS
        /* Beginn Seitenstyle */

        /* Ende Seitenstyle */
CSS;

/* Ende Konfiguration */
return;} ?>

<?php
  include_once('wtl_register.php');
?>