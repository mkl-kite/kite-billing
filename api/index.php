<?php
include "api.php";
include "providers.php";
include "users.cfg.php";
include 'config.php';

$q = new sql_query($config['db']);
$opdata = $q->get('operators',$provider['name'],'login');
$input = "IP: {$_SERVER['REMOTE_ADDR']} URL: {$_SERVER['REQUEST_URI']}";

// Проверяем разрешены ли запросы с этого ip
$provider = $q->select("SELECT * FROM providers WHERE ip = '{$_SERVER['REMOTE_ADDR']}' LIMIT 1",1);

if($provider && isset($_REQUEST['ticket'])){
	$ticket = substr(preg_replace('/[^0-9A-Za-z]/','',$_REQUEST['ticket']),0,128);
	$provider = ($ticket)? $q->select("SELECT * FROM providers WHERE ticket = '$ticket' LIMIT 1",1) : false;
	if($provider) $q->query("UPDATE providers SET ip='{$_SERVER['REMOTE_ADDR']}' ticket='' WHERE id='{$provider['id']}'");
}

if(!$provider){
	header("HTTP/1.0 404 Not Found");
	log_txt("Попытка соединения ".$input);
	die();
}

$conf = $config['api']['options'][$provider['protocol']];
$conf['db'] = $config['db'];
$conf['db_cards'] = $config['db_cards'];

if(!$opdata) $opdata = array('login'=>$provider['name'],'status'=>1,'level'=>1);

log_txt($input);

$service = new API($provider,$conf);
$service->perform($_REQUEST);

?>
