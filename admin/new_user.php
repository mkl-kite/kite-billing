<?php
$TOP = "Создание нового пользователя";
$CSSfile="base-admin.css";

$rid=(array_key_exists('rid',$_REQUEST))? $_REQUEST['rid'] : '';

include("top.php");
include("new_user_menu.php");
include("bottom.php"); 
?>
