<?php if($_DLRG_SYS['mode']){
/* Dokument Konfiguration */

 $_DLRG_DOC = array (
         "id"=>"05",
         "titel"=>"Logout",
#         "author"=>"",
#         "email"=>"",
         "keyword"=>"",
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
    echo "<h1>Logout</h1>";
    if( $_GET['login'] == 'logout' )
    {
        echo "<p><b>Du wurdest erfolgreich abgemeldet.</b></p>";
    }
?>