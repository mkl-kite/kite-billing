<?php
$menu = "references.php";
$CSSfile=array("base-admin.css","switches.css","js/leaflet.css","base-map.css",);
$myscript=array("js/leaflet-src.js","js/leaflet.buttons.js","js/switches.js","js/popuptraf.js");
include("authorize.php");
if($opdata['status']<2) exit(0);
if(!isset($q)) $q = new sql_query($config['db']);
$go=strict($_REQUEST['go']); $do=strict($_REQUEST['do']); $table=strict($_REQUEST['table']);
if ($go=="devices" && $do=="show" && $table=='wifi')		{
	$wifi = $q->get("devices",numeric($_REQUEST['id']));
	if($wifi){
		if($wifi['ip']) {
			header("Location: http://".preg_replace('/[^0-9\.]/','',$wifi['ip'])."/");
			exit(0);
		}elseif($c = $q->select("SELECT * FROM map WHERE type='client' AND id='{$wifi['node1']}'",1)) {
			$acc = $q->select("SELECT * FROM radacct WHERE username='{$c['name']}' AND acctstoptime is NULL",1);
			if($acc){
				header("Location: http://".preg_replace('/[^0-9\.]/','',$acc['framedipaddress'])."/");
				exit(0);
			}
		}
		include "top.php";
		stop(array('result'=>"ERROR",'desc'=>"Станция Wi-Fi не найдена в сети"));
	}
}
if (!$go) if($opdata['status']>2) $go="packetlist"; else $go="rayon";


if ($go=="rayon") { 
	$head = "Список районов";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=rayon\" style=\"width:800px\"></div>";
}
if ($go=="povod")			{
	$head = "Список статей для внесения денег";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=povod&tname=povod\" style=\"max-width:1000px\"></div>";
}
if ($go=="currency")		{
	$head = "Список валют";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=currency&tname=currency\" style=\"max-width:500px\"></div>";
}
if ($go=="curredit")		{
	ob_start();
	include("currency_edit.php");
	$body = ob_get_clean();
}
if ($go=="cards")			{
	$head = "Серии карточек";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=cardseries&tname=cardseries\" style=\"max-width:1000px\"></div>";
}
if ($go=="stdform" && $table == 'cardseries') { 
	$id = numeric($_REQUEST['id']);
	$head = "Список карточек";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=cards&tname=cards&series=$id\" style=\"max-width:900px\"></div>";
}
if ($go=="framedip")		{
	ob_start();
	include("framedip_list.php");
	$body = ob_get_clean();
}
if ($go=="profiles" && !$table)		{
	$head = "Профили";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=radusergroup&tname=radusergroup\" style=\"max-width:500px\"></div>";
}
if (($go=="stdform" || $go=="profiles") && $table=='radusergroup') {
	$id = isset($_REQUEST['id'])? str($_REQUEST['id']) : '';
	if(is_numeric($id)) $id = $q->select("SELECT groupname FROM packets WHERE pid='{$id}'",4);
	$head = "Профиль <b style=\"color:#00f\">$id</b>";
	$body = "<h4>Проверяемые атрибуты</h4>";
	$body .= "<div class=\"tablecontainer\" query=\"go=stdtable&do=radgroupcheck&tname=radgroupcheck&groupname=$id\" style=\"max-width:900px\"></div>";
	$body .= "<h4>Отсылаемые атрибуты</h4>";
	$body .= "<div class=\"tablecontainer\" query=\"go=stdtable&do=radgroupreply&tname=radgroupreply&groupname=$id\" style=\"max-width:900px\"></div>";
}
if ($go=="ippools")			{
	$head = "Список пулов";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=radippool&table=radippool&tname=radippool\" style=\"max-width:600px\"></div>";
} 
if ($go=="poolrange")		{
	ob_start();
	include("pool_range.php");
	$body = ob_get_clean();
}
if ($go=="naslist")			{
	$head = "Список серверов доступа";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=nas&tname=nas\" style=\"max-width:900px\"></div>";
}
if ($go=="nodelist")			{
	$head = "Список узлов";
	$body = "<div class=\"tablecontainer\" query=\"go=nodes&do=get_table&tname=nodes\" style=\"max-width:900px\"></div>";
}
if ($go=="nodes" && $do=="show" && $table=='map'){
	$id = numeric($_REQUEST['id']);
	$node = $q->get('map',$id);
	$head = "Список оборудования на узле: &emsp; <b>{$node['address']}</b>";
	$body = "<div class=\"tablecontainer\" query=\"go=nodes&do=list_devices&id=$id\" style=\"max-width:900px\"></div>";
}
if (preg_match('/^node_(\d+)$/',$go,$m) && $do=="show" && $table=='devices'){
	$_REQUEST['nodeid'] = $m[1];
	$body = "<div style=\"width:90%\">";
	ob_start();
	include "print_dev.php";
	$html = ob_get_clean();
	$body .= $html;
	$body .= "</div>";
}
if ($go=="switches" && $do=="show")		{
	ob_start();
	include("switches.php");
	$body = ob_get_clean();
}
if ($go=="switches" && $do=="list")			{
	$head = "Список свичей";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=switches&tname=switches\" style=\"max-width:1100px\"></div>";
}
if ($go=="wifi" && $do=="list")			{
	$head = "Список станций Wi-Fi";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=wifi&tname=wifi\" style=\"max-width:1200px\"></div>";
}
if ($go=="packetlist")		{
	$head = "Тарифные планы ".COMPANY_NAME." ".COMPANY_UNIT."";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=packets&tname=packets\" style=\"max-width:1000px\"></div>";
}
if ($go=='stdform' && $do=='show' && $table=="packets") {
	$id = numeric($_REQUEST['id']);
	$head = "Тарифный план: <b style=\"color:#00f\">".$q->select("SELECT name FROM packets WHERE pid='$id'",4)."</b>";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=users&tname=users&pid=$id\" style=\"max-width:95%\"></div>";
}
if ($go=='stdform' && $do=='show' && $table=="rayon") {
	$id = numeric($_REQUEST['id']);
	$head = "Список пользователей: <b style=\"color:#00f\">р-н.".$q->select("SELECT r_name FROM rayon WHERE rid='$id'",4)."</b>";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=users&tname=users&rid=$id\" style=\"max-width:95%\"></div>";
}
if ($go=='stdtable' && $do=='users') {
	$var = '';
	if(isset($_REQUEST['key']) && isset($_REQUEST['id'])){
		$key = strict($_REQUEST['key']); $id = numeric($_REQUEST['id']);
		$var = "&{$key}=_{$id}";
	}
	$head = "Список пользователей:";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=users&tname=users$var\" style=\"max-width:95%\"></div>";
}
if ($go=="prices" && $table=="prices" && $_REQUEST['id']) {
	$id = numeric($_REQUEST['id']);
	$head = "Тарифные позиции пакета: <b style=\"color:#00f\">".$q->select("SELECT name FROM packets WHERE pid='$id'",4)."</b>";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=prices&tname=prices&pid=$id\" style=\"max-width:800px\"></div>";
}
if ($go=="news")			{
	$head = "Новости компании ".COMPANY_NAME." ".COMPANY_UNIT."";
	$body = "<div class=\"tablecontainer\" query=\"go=news&do=get_table&tname=news\" style=\"max-width:900px\"></div>";
}
if ($go=="usrdocs")			{
	$head = "Клиентские документы";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=documents&tname=documents\" style=\"max-width:1050px\"></div>";
}
if ($go=="operators")		{
	$head = "Список операторов ".COMPANY_NAME." ".COMPANY_UNIT."";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=operators&tname=operators\" style=\"max-width:1000px\"></div>";
}
if ($go=="employers")		{
	$head = "Список служащих";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=employers&tname=employers\" style=\"max-width:1250px\"></div>";
}
if ($go=="homes")		{
	$head = "Список домов (из карты)";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=homes&tname=homes\" style=\"max-width:900px\"></div>";
}
if ($go=="leases")			{
	$head = "Аренда ".COMPANY_NAME." ".COMPANY_UNIT."";
	$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=leases&tname=leases\" style=\"max-width:1000px\"></div>";
}
if ($go=="stdform" && $do=="show" && $table=='radippool') {
	$id = isset($_REQUEST['id'])? str($_REQUEST['id']) : '';
	if(preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$id,$m)){
		$pool_name = $q->select("SELECT pool_name FROM radippool WHERE framedipaddress='{$m[1]}'",4);
		$head = "Пул ip адресов <b>$pool_name</b>";
		$body = "<div class=\"tablecontainer\" query=\"go=stdtable&do=radippool&tname=radippool&table=radippool&pool_name=$pool_name&slice=$id\" style=\"max-width:1100px\"></div>";
	}
}
if(isset($head)) $header = $head;
$TOP = preg_replace('/<[^>]*>/','',$header);
include("top.php");
include("refer_menu.php");
echo "<CENTER>\n";
if(isset($head)) echo "<h3>$head</h3>\n";
echo $body;
echo "</CENTER>\n";
include("bottom.php");
?>
