<?php
include "api.php";
include "providers.php";
include "users.cfg.php";
include 'config.php';

$input = "IP: {$_SERVER['REMOTE_ADDR']} URL: {$_SERVER['REQUEST_URI']}";

// Проверяем разрешены ли запросы с этого ip
$provider = $config['api']['providers'][$_SERVER['REMOTE_ADDR']];

if(!$provider){
	header("HTTP/1.0 404 Not Found");
	log_txt("Попытка соединения ".$input);
	die();
}

$q = new sql_query($config['db']);
$opdata = $q->get('operators',$provider['name'],'login');
if(!$opdata) $opdata = array('login'=>$provider['name'],'status'=>1,'level'=>1);

log_txt($input);

$service = new API($provider,$config['api']);
$service->perform($_REQUEST);

?>
