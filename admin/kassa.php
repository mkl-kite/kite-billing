<?php
$TOP = "Касса";
$menu="kassa.php";
$CSSfile="base-admin.css";

$uid=(key_exists('uid',$_REQUEST))? numeric($_REQUEST['uid']) : 0;
$kid=(key_exists('kid',$_REQUEST))? numeric($_REQUEST['kid']) : 0;

include("top.php");

$table = isset($_REQUEST['table'])? strict($_REQUEST['table']) : '';
$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : 0;

if ($go=="") $go="dailypay";
include("kassa_menu.php");

?><center><?php

if ($go=="places") { ?>
	<h3>Список офисов</h3>
	<div class="tablecontainer" query="go=stdtable&do=kassa&tname=kassa" style="max-width:800px"></div></center><?php
}
if ($go=="orders") { ?>
	<h3>Платёжные ведомости</h3><center>
	<div class="tablecontainer" query="go=stdtable&do=orders&tname=orders" style="max-width:900px"></div></center><?php
}
if ($go=="stdform" && $table=='orders' && $id>0) {
	if(!$q) $q = new sql_query($config['db']);
	$order = $q->get('orders',$id);
	$odate = cyrdate($order['open'],'%d %b %Y'); ?>
	<h3>Платёжная ведомость &#8470; <?php echo "<b>$id</b> от <b>{$odate}</b> г."; ?></h3><center>
	<div class="tablecontainer" query="go=stdtable&do=pay&tname=pay&oid=<?php echo $id; ?>" style="max-width:95%"></div></center><?php
}
if ($go=="pay_log") {
	include("usr_pay_list.php"); 
	}
if ($go=="dailypay") {
	include("dailypay_list.php"); 
	}
include("bottom.php");
?>
