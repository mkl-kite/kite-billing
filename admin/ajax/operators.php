<?php
include_once("operators.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'operators';
$in['do'] = (key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : "get";
$in['id'] = (key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : '0';

if(!isset($q)) $q = new sql_query($config['db']);

switch($in['do']){

	case 'get':
		$out['list'] = get_senders();
		$out['sender'] = $opdata['login'];
		stop($out);
		break;

	case 'history':
		$to = isset($_REQUEST['to'])? strict($_REQUEST['to']) : "";
		$send = isset($_REQUEST['send'])? date2db(preg_replace('/[^0-9]/','',$_REQUEST['send'])) : date2db();
		$sender = $opdata['login'];
		$to = isset($_REQUEST['to'])? strict($_REQUEST['to']) : "";
		$out = array();
		$out['sender'] = $sender;
		$out['list'] = $q->select("
			SELECT status, UNIX_TIMESTAMP(send) as send, sender, `to`, replace(message,0xa,'<br>') as message FROM messages WHERE (sender='$sender' AND `to`='$to' OR sender='$to' AND `to`='$sender') AND send<'$send' ORDER BY send DESC LIMIT 10
		");
		stop($out);
		break;

	default:
		stop(array(
		'result'=>'ERROR',
		'desc'=>"неверные данные
			go=$_REQUEST[go]
			do=$_REQUEST[do]
			id=$_REQUEST[id]"
		));
}

function get_senders($id=false) {
	global $cache, $config, $q, $DEBUG;
	if(!isset($cache['senders'])) {
		if(!$q) $q = new sql_query($config['db']);
		$tmp = $q->fetch_all("SELECT login, fio FROM operators WHERE blocked=0");
		foreach($tmp as $k=>$v) $tmp[$k] = shortfio($v);
		$cache['senders'] = array_merge(array('all'=>'всем','ALL'=>'ВСЕМ+'),$tmp);
	}
	if($id !== false) return $cache['senders'][$id];
	if($DEBUG>0) log_txt(__function__.": return:".arrstr($cache['senders']));
	return $cache['senders'];
}
?>
