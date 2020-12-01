<?php
DEFINE("MAIN_DIR",'/var/www/admin/');
set_include_path(MAIN_DIR."include:".MAIN_DIR."classes:".MAIN_DIR."menu:".MAIN_DIR."ajax:".MAIN_DIR."lists:".MAIN_DIR."db:/usr/local/share/jpgraph");
include("utils.php");
include("geodata.php");
if(!@$q) $q = new sql_query($config['db']);
?>
