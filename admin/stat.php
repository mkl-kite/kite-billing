<?php
include_once ("authorize.php");
$menu="stat.php";
$CSSfile="base-admin.css";
$myscript=array("js/popuptraf.js");
$go=(key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : '';
$do=(key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
if ($go=="") $go="credit";

if($go=='credit') $TOP = "Кредиты у клиентов";
elseif($go=='tearing') $TOP = "Статистика завершения коротких сессий";
elseif($go=='homes') $TOP = "Список многоквартирных домов";
elseif($go=='dhcp') $TOP = "Аренда ip адресов по DHCP";
elseif($go=='cables') $TOP = "Статистика по кабелям";
elseif($go=='cables_ext') $TOP = "Подробная статистика по кабелям";
elseif($go=='usrbyhome') $TOP = "Клиенты по дому";
elseif($go=='usrmac') $TOP = "MAC адреса клиентов";
elseif($go=='usrnomac') $TOP = "Клиенты без MAC адресов";
elseif($go=='dolg') $TOP = "Должники";
elseif($go=='dohod') $TOP = "Статистика по долгам";
elseif($go=='credit') $TOP = "Статистика по кредитам";
elseif($go=='create') $TOP = "Список подключенных клиентов";
elseif($go=='worders') $TOP = "Статистика по нарядам";
elseif($go=='users') $TOP = "Движения";
elseif($go=='lostusers') $TOP = "Ушедшие";
elseif($go=='diagramms') $TOP = "Диаграммы";
elseif($go=="stdform" && $do=="show" && $_REQUEST['table']=="homes") {
	$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : "";
	$h = $q->select("SELECT * FROM homes WHERE id='$id'",1);
	if($h) { $home = $h['address']; $rayon = $q->select("SELECT rayon FROM map WHERE id='{$h['object']}'",4); }
	if(isset($home)) $TOP = "Список клиентов по {$h['address']}";
}else $TOP = "Статистика";

if ($go=="stat") {
	if($id = numeric($_REQUEST['id'])) header("Location: users.php?go=usrcard&uid=".$id);
	exit(0);
}
if ($opdata['status']<=2) { exit(0); }
if ($go=="dhcp2html" && ($id = numeric($_REQUEST['id']))) {
	include_once "dhcp.cfg.php";
	$cl = get_dhcp();
	if(isset($cl[$id])){
		$host = trim($cl[$id]['host']);
		if($cl[$id]['host'] && gethostbyname($host) == $cl[$id]['ip']) header('Location: '."http://$host.".$_SERVER['HTTP_HOST']);
		else header('Location: '."http://{$cl[$id]['ip']}");
		exit(0);
	}
	
}

include("top.php");
include("stat_menu.php");
?><center><?php
if ($go=="tearing")		{ ?>
	<h3>Статистика завершения коротких сессий</h3>
	<div class="tablecontainer" query="go=stat&do=tearing&tname=radacct" style="max-width:90%"></div></center><?php
}
if ($go=="homes") 		{ ?>
	<h3>Список многоквартирных домов</h3>
	<div class="tablecontainer" query="go=stat&do=homes&tname=homes" style="max-width:1000px"></div></center><?php
}
if ($go=="dhcp") 		{ ?> 
	<h3>Аренда ip адресов (DHCP)</h3>
	<div class="tablecontainer" query="go=stdtable&do=dhcp&tname=dhcp" style="max-width:1100px"></div></center><?php
}
if ($go=="cables") 		{include("cable_length_list.php"); }
if ($go=="cables_ext") 		{include("cable_length_ext_list.php"); }
if ($go=="stdform" && $do=="show" && $_REQUEST['table']=="homes") { include("usrbyhome_list.php"); }
if ($go=="usrmac")		{ ?>
	<h3>Список клиентов с закреплёнными мак адресами</h3>
	<div class="tablecontainer" query="go=stat&do=usrmac&tname=usrmac" style="max-width:90%"></div></center><?php
}
if ($go=="usrnomac")	{include("usrnomac_list.php"); }
if ($go=="dolg") 		{ ?>
	<h3>Список должников</h3>
	<div class="tablecontainer" query="go=stat&do=debtors&tname=dolg" style="max-width:90%"></div></center><?php
}
if ($go=="dohod")		{include("stat_dohod_list.php");}
if ($go=="credit")		{ ?>
	<h3>Список выданных кредитов клиентам</h3>
	<div class="tablecontainer" query="go=stat&do=credits&tname=credit" style="max-width:90%"></div></center><?php
}
if ($go=="create")		{ ?>
	<h3>Список подключений клиентов</h3>
	<div class="tablecontainer" query="go=stat&do=create&tname=create" style="max-width:90%"></div></center><?php
}
if ($go=="worders")		{include("wo_stat_list.php");}
if ($go=="users")		{ ?>
	<h3>Оплаты клиентов за период</h3>
	<div class="tablecontainer" query="go=stat&do=paid&tname=paid" style="max-width:700px"></div></center><?php
}
if ($go=="lostusers")	{ ?>
	<h3>Список давно не подключавшихся (более 2 мес.)</h3>
	<div class="tablecontainer" query="go=stat&do=lostusers&tname=lost" style="max-width:70%"></div></center><?php
}
if ($go=="diagramms")	{include("dia_list.php");}

?></center><?php
include("bottom.php");
?>
