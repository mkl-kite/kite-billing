<?php
include_once("classes.php");
include_once("table.php");

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : 'stdtable';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : "";
$in['id'] = (isset($_REQUEST['id']))? str(preg_replace('/[^_]*_/','',$_REQUEST['id'])) : '0';

include_once("{$in['do']}.cfg.php");

if(!isset($tables[$in['do']])){
	stop(array('result'=>'ERROR','desc'=>"Не найдены данные для таблицы {$in['do']}!"));
}
$t = $tables[$in['do']];
$t['name'] = $in['do'];
if(!isset($t['module'])) $t['module'] = 'stdform';

foreach($t['fields'] as $k=>$f) if(isset($t['fields'][$k]['style'])) unset($t['fields'][$k]['style']);
$table = new table($t);
stop(array('result'=>'OK','table'=>$table->get()));

?>
