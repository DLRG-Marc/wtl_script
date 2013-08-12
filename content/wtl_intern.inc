<?php if($_DLRG_SYS['mode']){
/* Dokument Konfiguration */

 $_DLRG_DOC = array (
         "id"=>"03",
         "titel"=>"Wartelisten verwalten",
#         "author"=>"",
#         "email"=>"",
         "keyword"=>"",
         "description"=>"",
         "access"=>"",
         "session"=>"intern" )+$_DLRG_DOC;
 $_DLRG_MENU = array (
        "main"=>"menu",
        "sub"=>"",
        "top"=>"",
        "item"=>"" )+$_DLRG_MENU;
 $_DLRG_CRUMB = array ();

 $_DLRG_DOC['style'] = <<<CSS
        /* Beginn Seitenstyle */

        /* Ende Seitenstyle */
CSS;

/* Ende Konfiguration */
return;} ?>

<?php
  include_once('wtl_intern.php');
?>