<?php
$TOP = "Заявки";
$menu = "claims.php";
$CSSfile=array(
	"base-admin.css",
	"js/leaflet.css",
	"base-map.css",
);
$myscript=array(
	"js/leaflet-src.js",
	"js/jquery.json-2.3.min.js",
	"js/leaflet.buttons.js",
	"js/jquery.popupForm.js",
);
include("top.php");
$do=(isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
if(!$go) $go="claimopen";

include("claims_menu.php");

?><CENTER><?php
if($go=="claimopen") { ?>
	<h3>Заявления клиентов</h3>
	<div class="tablecontainer" query="go=stdtable&do=claims&tname=claims" style="max-width:90%"></div><?php
}
if($go=="claimplane")	{ include("claim_plan_list.php"); }
if($go=="worder")		{ include("workorder.php"); }
if($go=="workdays")	{ include("workdays_list.php"); }
if($go=="worderlist") { ?>
	<h3>Наряды</h3>
	<div class="tablecontainer" query="go=stdtable&do=workorders&tname=workorders" style="max-width:90%"></div><?php
}
?></CENTER><?php
include("bottom.php");
?>
