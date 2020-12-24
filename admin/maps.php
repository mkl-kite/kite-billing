<?php
$TOP = "Карты";
$menu = "maps.php";
$CSSfile = array(
	"js/leaflet.css",
	"js/leaflet.draw.css",
	"base-admin.css",
	"base-map.css",
);
$myscript=array(
	"js/leaflet-src.js",
	"js/leaflet.draw-src.js",
	"js/leaflet.buttons.js",
	"js/jquery.popupForm.js",
	"js/map.js",
);


include_once("authorize.php");
include_once("utils.php");
$go = (key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : '';
$do = (key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';

if ($do!='print') {
	include("top.php");
?>
<div id="mapbox" style="display:flex">
	<div id="map"></div>
	<div id="targets" style="box-sizing:border-box">
		<div id="objects" class="tree"></div>
	</div>
</div>
<?php
	include("bottom.php");
}elseif($go=='nodes' || $go=='devices') { ?>
<!DOCTYPE html><HTML><HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8 ;no-cashe">
<link rel="icon" type="image/png" href="tl-logo.png" sizes="16x16">
<SCRIPT type="text/javascript" src="js/jquery-1.10.2.min.js"></SCRIPT>
<BODY style="background-color:#fff">
<?php
include("print_dev.php");
include("bottom.php");
}
?>
