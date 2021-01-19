<?php
if(preg_match('/mobile/i',$_SERVER['']))
$mobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
include_once("defines.php");
include_once("authorize.php"); 
?>
<HTML>
<HEAD>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<TITLE>Личный кабинет</TITLE>
	<link rel="stylesheet" type="text/css" href="stat.css">
	<link rel="stylesheet" type="text/css" href="js/forms.css">
	<SCRIPT langage="text/javascript">SocketEnable = false;</SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery-1.10.2.min.js"></SCRIPT>
	<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
	<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="js/jquery-ui/autocomplete.css">
	<SCRIPT type="text/javascript" src="js/jquery.json-2.3.min.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.cookie.min.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.loader.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/jquery.popupForm.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/swipe.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="js/stat.js"></SCRIPT>
</HEAD>
<BODY>
<?php
$h = 530; $w = 320;
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
<div class="container"><div class="main"><div>

<div style="margin-bottom:10px;position:relative">
	<h2 class="logo"><?php echo $COMPANY_NAME; ?>
	<span id="togglemenu" style="position:absolute;right:10px;top:5px"><img src="pic/menu.svg"></span></h2>
</div>
<?php if($guest) echo "<DIV class=\"efforts".(($efforts==0)?'_red':'')."\">$msg</DIV>"; ?>
<div class="outer">
	<div class="topinfo">
		<span id="fio"><?php echo $client['fio']." (".$client['contract'].")"; ?></span>
		<span id="address"><?php echo $client['address']; ?></span>
	</div>

	<div class="inner">
		<div id="menu">
<?php
	echo "<p class=\"menu\" go=\"menu\" do=\"news\">Новости компании</p>";
	foreach($config['menu'] as $k=>$v) {
		if($v['enable']) echo "<p class=\"menu\" go=\"{$v['module']}\" do=\"$k\">{$v['label']}</p>\n";
	}
	echo "<p class=\"menu\"><a href=\"index.php?go=logout\">выйти</a></p>";
?>
		</div>
		<div class="data">
			<div id="content"></div>
		</div>
	</div>
	<div class="bottominfo">
		<span id="phone"><img src="pic/oldphone.png" style="position:relative;top:3px"> <?php echo $client['phone']; ?></span>
		<span id="deposit"><?php echo sprintf("%.2f {$valute['short']}.",$client['deposit']); ?></span>
	</div>
</div></div></div>
</BODY>
</HTML>
