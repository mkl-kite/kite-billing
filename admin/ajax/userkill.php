<?php
include_once("defines.php");
include_once("log.php");
include_once("classes/classes.php");
if(!$q) $q = new sql_query($config['db']);

$id=(isset($_REQUEST['id']))? preg_replace('/[^0-9A-Za-z]/','',$_REQUEST['id']) : false;
$user=(isset($_REQUEST['user']))? str($_REQUEST['user']) : false;
$uid=(isset($_REQUEST['uid']))? numeric($_REQUEST['uid']) : false;

$filter = false;
if(!$id) {
	if($user) $filter = "AND username='{$user}'";
	elseif($uid) $filter = "AND uid='{$uid}'";
}else{
	$filter="AND acctsessionid='{$id}'";
}

if($filter) {
	$r = $q->select(sqltrim("
		SELECT 
			acctsessionid,
			uid,
			username,
			framedipaddress, 
			nasipaddress, 
			nasportid,
			callingstationid
		FROM radacct
		WHERE
			acctstoptime is NULL $filter
	"));
	foreach($r as $online) $s[]=$online['acctsessionid'];
	
	$dropped = $q->select("SELECT * FROM raddropuser WHERE acctsessionid in (".implode(',',quote($s)).")");
	if($dropped) {
		log_txt("userkill: Задвоенное обращение");
		$out=array('result'=>'INFO','desc'=>'Повторное обращение!');
	}else{
		if($r) {
			$out['result']='OK';
			foreach($r as $k=>$drop) {
				$csid[$k]=$drop['callingstationid']; unset($drop['callingstationid']);
				$q->insert('raddropuser',$drop);
			}
			@exec("/usr/bin/sudo -u root ".USERKILL,$output,$res);
			if($res!==0) {
				$out['desc']='Ошибка при вызове внешней функции'."<br><pre>".@implode("\n",$output)."</pre>";
				log_txt("userkill: ERROR ($res) for extfunc (".USERKILL.") \n\t".@implode("\n\t",$output));
			}else{
				$out['delete']=$s;
				foreach($r as $k=>$drop) {
					if($drop['uid']=='') $drop['uid']=0;
					nibs_log($drop['username'],$drop['uid'],"закрыл соединение","NAS={$drop['nasipaddress']}:{$drop['nasportid']} MAC={$csid[$k]}");
				}
			}
		}else{
			$out=array('result'=>'INFO','desc'=>"Пользователь <b>$user</b> не подключен!");
		}
	}
}else{
	$out=array('result'=>'ERROR','desc'=>'Подключение не найдено!');
}
stop($out);
?>
