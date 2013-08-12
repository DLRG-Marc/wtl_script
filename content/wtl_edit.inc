<?php if($_DLRG_SYS['mode']){
/* Dokument Konfiguration */

 $_DLRG_DOC = array (
         "id"=>"0302",
         "titel"=>"Wartestatus",
#         "author"=>"",
#         "email"=>"",
         "description"=>"",
         "access"=>"",
         "session"=>"" )+$_DLRG_DOC;
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
  include_once('wtl_register.php');
?>