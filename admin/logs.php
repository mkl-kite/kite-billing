<?php
$TOP = "Журналы";
$menu="logs.php";
$CSSfile=array(
	"base-admin.css",
	"js/leaflet.css",
	"base-map.css",
);
$myscript=array(
	"js/leaflet-src.js",
	"js/jquery.json-2.3.min.js",
	"js/leaflet.buttons.js",
	"popupform.js"
);

	$show_log=(array_key_exists('show_log',$_REQUEST))? $_REQUEST['show_log'] : '';
    $uid=(array_key_exists('uid',$_REQUEST))? $_REQUEST['uid'] : '';
    $date_begin=(array_key_exists('date_begin',$_REQUEST))? $_REQUEST['date_begin'] : '';
    $date_end=(array_key_exists('date_end',$_REQUEST))? $_REQUEST['date_end'] : '';
    $call_to=(array_key_exists('call_to',$_REQUEST))? $_REQUEST['call_to'] : '';

include("top.php");

if ($go=="") $go="logs";
include("logs_menu.php");

echo "<center>";
if ($go=="logs")			{ ?>
	<h3>Журнал действий операторов</h3>
	<div class="tablecontainer" query="go=stdtable&do=log&tname=log" style="max-width:1300px"></div><?php
}
if ($go=="radius") { include("radius_log_list.php"); }
if ($go=="smslist")			{ ?>
	<h3>Журнал отправки SMS</h3>
	<div class="tablecontainer" query="go=stdtable&do=sms&tname=sms" style="max-width:1300px"></div><?php
}
echo "</center>";

include("bottom.php");
?>
