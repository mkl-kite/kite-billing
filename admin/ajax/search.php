<?php
include_once 'classes.php';
include_once("search.cfg.php");

$query=(key_exists('q',$_REQUEST))? str(mb_substr($_REQUEST['q'],0,32)):"";
$tabname=(key_exists('tabname',$_REQUEST))? str($_REQUEST['tabname']) : "searchtab"; 
if(!isset($q)) $q = new sql_query($config['db']);

$brline=array();
$t=$tables['search'];

if($_REQUEST['go']=='search' && @$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	if($html = searchUsers($query)) {
		$out['result']="OK";
		$out['query']=$query;
		$out['tab']['link'] = $go;
		$out['tab']['name'] = $tabname;
		$out['tab']['title'] = 'Поиск';
		$out['tab']['content'] = $html;
		stop($out);
	}else{
		$out['result']="error";
		$out['desc']="Неизвестная операция";
		stop($out);
	}
}
?>
