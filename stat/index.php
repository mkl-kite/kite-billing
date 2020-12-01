<?php
include_once("defines.php");
include_once("authorize.php"); ?>
<HTML>
<HEAD>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
	<TITLE>Личный кабинет</TITLE>
	<link rel="stylesheet" type="text/css" href="stat.css">
	<link rel="stylesheet" type="text/css" href="js/forms.css">
	<SCRIPT type="text/javascript" src="js/jquery-1.10.2.min.js"></SCRIPT>
	<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
	<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="js/jquery-ui/autocomplete.css">
	<SCRIPT type="text/javascript" src="js/jquery.json-2.3.min.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.cookie.min.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.loader.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.popupForm.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/stat.js"></SCRIPT>
</SCRIPT>
</HEAD>
<BODY>
<?php
if(!isset($q)) $q = new sql_query($config['db']);

$valutes = $q->fetch_all("SELECT * FROM currency");
$valute = $q->select("SELECT * FROM currency WHERE rate=1.0",1);
if(!$valute) $valute=array('id'=>1,'name'=>'рубль','rate'=>1.00,'short'=>'руб');
if($guest) {
	if((ip2long($guest['framedipaddress']) & 0xFFFFE000) == ip2long('192.168.192.0')){
		$fld = array('acctsessionid','uid','username','framedipaddress','nasipaddress','nasportid','callingstationid');
		$q->insert('raddropuser',array_intersect_key($guest,array_flip($fld)));
		$msg = "Ваша сессия будет перезагружена в течении 2 мин.";
	}else{
		$msg = "Новые данные о Вашем подключении были сохранены!";
	}
}
?>
<div class="container"><div><div>

<div style="margin-bottom:10px">
	<h2 class="logo"><?php echo $COMPANY_NAME; ?></h2>
</div>
<?php if($guest) echo "<DIV class=\"efforts".(($efforts==0)?'_red':'')."\">$msg</DIV>"; ?>
<table class="outer"><tr><td>
<span id="fio" style="float:left"><?php echo $client['fio']." (".$client['contract'].")"; ?></span>
<span id="address" style="float:right"><?php echo $client['address']; ?></span>
</td></tr><tr><td>

<table class="inner">
<tr>
<td style="vertical-align:top">
    <br>
<?php
	echo "<p class=\"menu\" go=\"menu\" do=\"news\">Новости компании</p>";
	foreach($config['menu'] as $k=>$v) {
		if($v['enable']) echo "<p class=\"menu\" go=\"{$v['module']}\" do=\"$k\">{$v['label']}</p>\n";
	}
	echo "<p class=\"menu\"><a href=\"index.php?go=logout\">выйти</a></p>";
?>
</td><td><div id="content" style="display:hidden"></div></td></tr>
</table>

</td></tr><tr><td><img src="pic/oldphone.png" style="position:relative;top:3px"> <span id="phone"><?php echo $client['phone']; ?></span>
<span id="deposit" style="float:right"><?php echo sprintf("%.2f {$valute['short']}.",$client['deposit']); ?></span></td></tr></table>
</div></div></div>
</BODY>
</HTML>
