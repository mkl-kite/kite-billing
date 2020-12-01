<?php
$menu="users.php";
$CSSfile=array("js/leaflet.css","base-admin.css","base-map.css","switches.css");
$myscript=array(
	"js/leaflet-src.js",
	"js/leaflet.draw-src.js",
	"js/leaflet.buttons.js",
	"js/popuptraf.js",
	"popupdhcp.js",
);
include_once("authorize.php");

$do = (key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$uid = (key_exists('uid',$_REQUEST))? numeric($_REQUEST['uid']) : '';
$user = (key_exists('user',$_REQUEST))? strict($_REQUEST['user']) : '';
$phone = (key_exists('phone',$_GET))? numeric($_GET['phone']) : '';

if(!$uid && $phone){
	$phone = preg_replace('/.*(\d\d\d)(\d\d\d)(\d\d)(\d\d)\s*$/','\1-\2-\3-\4',$phone);
	$users = $q->select("SELECT * FROM `users` WHERE phone like '%{$phone}%'");
	if (count($users) == 1) {
		$client = $users[0];
		$uid = $client['uid'];
	}elseif(count($users) > 1){
		include_once ('ajax/search.php');
		echo searchUsers($phone);
		exit(0);
	}
}

$table = isset($_REQUEST['table'])? strict($_REQUEST['table']) : false;
if($table){
	if(!isset($tables[$table])) include_once("{$table}.cfg.php");
	if(isset($tables[$table])){
		if(!isset($q)) $q = new sql_query($config['db']);
		$id = isset($_REQUEST['id'])? strict($_REQUEST['id']) : false;
		if($id){
			$rec = $q->get($table,$id);
			if(isset($rec['uid']) && $rec['uid']>0) $uid = $rec['uid'];
			elseif(isset($rec['user']) && $rec['user']!='') $user = $rec['user'];
			elseif(isset($rec['username']) && $rec['username']!='') $user = $rec['username'];
		}else log_txt("users.php: id ($id) is not found!");
		if(@$uid>0 || @$user!='') $go='usrstat';
	}
}
if(!isset($client)) {
	if($uid) $client = $q->get('users',$uid);
	elseif($user) $client = $q->get('users',$user,'user');
}
if(!($client = normalize_client($client))) $error = "Пользователь не найден!";

if(!isset($go)) $go=(key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : '';
if(!isset($do)) $do=(key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
if($go=="") $go="usrstat";

if($go=='usrcard') $TOP = "Заявления по {$client['address']}";
elseif($go=='usrstat') $TOP = "Статистика подключений по {$client['address']}";
elseif($go=='usrcard') $TOP = "Работа по заявлениям для {$client['address']}";
elseif($go=='usrchart') $TOP = "Траффик по {$client['address']}";
elseif($go=='usrlogpay') $TOP = "Платежи {$client['address']}";
elseif($go=='usrlog') $TOP = "Действия операторов по {$client['address']}";
elseif($go=='usrradius') $TOP = "Лог биллинга по {$client['address']}";
elseif($go=='onu_signal'||$go=='wifi_signal'||$go=='switches') $TOP = "Тех.данные по {$client['address']}";
elseif($go=='documents') $TOP = "Документы по {$client['address']}";
else $TOP = "Клиент {$client['address']}";

include_once("top.php");

include("usr_menu.php");

if ($go=="usrcard")     {	include("usr_edit.php"); }
if ($go=="usrstat")     {	include("usr_stat_list.php"); }
if ($go=="usrchart")    {	include("usr_chart_list.php"); }
if ($go=="usrlogpay")   {	?>
	<br><center>
	<div class="tablecontainer" query="go=stdtable&do=pay&uid=<?php echo $uid; ?>&tname=pay" style="max-width:1200px"></div>
	</center><?php
}
if ($go=="usrlog")      { ?>
	<br><center>
	<div class="tablecontainer" query="go=stdtable&do=log&uid=<?php echo $uid; ?>&tname=log" style="max-width:1200px"></div>
	</center><?php
}
if ($go=="usrradius")   {	include("radius_log_list.php"); }
if ($go=="switches")    {	include("ajax/switches.php"); }
if ($go=="onu_signal")  {	include("lists/onu_signal.php"); }
if ($go=="wifi_signal")  {	include("lists/wifi_signal.php"); }
if ($go=="documents")	{   ?>
	<br><center>
	<div class="tablecontainer" query="go=stdtable&do=documents&tname=documents&uid=<?php echo $uid; ?>" style="max-width:900px"></div>
	</center><?php
}
include("bottom.php");
?>
