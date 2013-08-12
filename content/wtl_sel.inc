<?php if($_DLRG_SYS['mode']){
/* Dokument Konfiguration */

 $_DLRG_DOC = array (
         "id"=>"0307",
         "titel"=>"Auswahlfelder",
#         "author"=>"",
#         "email"=>"",
         "keyword"=>"",
         "description"=>"",
         "access"=>"requiered",
         "session"=>"intern" )+$_DLRG_DOC;
 $_DLRG_MENU = array (
        "main"=>"menu_intern",
        "sub"=>"",
        "top"=>"",
        "item"=>"" )+$_DLRG_MENU;
 $_DLRG_CRUMB = array (
        "Wartelisten verwalten"=>"wtl_view" );

 $_DLRG_DOC['style'] = <<<CSS
        /* Beginn Seitenstyle */

        /* Ende Seitenstyle */
CSS;

/* Ende Konfiguration */
return;} ?>

<?php
  include_once('intern/wtl_selectfields.php');
?>