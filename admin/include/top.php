<?php
include_once("authorize.php");
if(!isset($go)) $go=(array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : '';
if(!isset($do)) $do=(array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
?>
<!DOCTYPE html>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8 ;no-cashe">
  <link rel="icon" type="image/png" href="logo.png" sizes="16x16">
<?php 
if(isset($CSSfile)) { 
	if(gettype($CSSfile)=='string'){
		echo "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"$CSSfile\">";
	}elseif(gettype($CSSfile)=='array'){
		foreach($CSSfile as $v) echo "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"$v\">\n";
	}
}
if(isset($META)) echo "$META";
include_once("defines.php"); 
?>
	<SCRIPT language="JavaScript">
		autonom_name = "<?php echo AUTONOMOUS_MAP_NAME; ?>"
		autonom_url = "<?php echo AUTONOMOUS_MAP_URL; ?>"
		company_url = "<?php echo COMPANY_SITE; ?>"
		SocketEnable = "<?php echo ((WEBSOCKET_ENABLE)? "true" : "false"); ?>";
		default_position = "<?php echo $config['map']['default_position']; ?>";
	</SCRIPT>
	<TITLE><?php echo "$COMPANY_NAME"; ?> - <?php echo "$TOP"; ?></TITLE>
	<SCRIPT type="text/javascript" src="js/jquery-1.10.2.min.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.mousewheel.min.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.json-2.3.min.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.cookie.min.js"></SCRIPT>
	<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
	<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="js/jquery-ui/autocomplete.css">
	<SCRIPT type="text/javascript" src="js/jquery.loader.js?ver=2020071700"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.popupForm.js"></SCRIPT>
<?php
if(isset($myscript)) { 
	if(gettype($myscript)=='string'){
		echo $myscript;
	}elseif(gettype($myscript)=='array'){
		foreach($myscript as $v) echo "\t<script type=\"text/javascript\" src=\"$v\"></script>\n";
	}
}
?>
</HEAD>
<BODY <?php echo (isset($onload))? "ONLOAD=\"$onload\"" : ""; ?> LINK=blue VLINK=blue>
<DIV id="all" style="padding:10px">
<?php 
if (@$showmenu!='no') {
  require("top_menu.php"); }
?>
