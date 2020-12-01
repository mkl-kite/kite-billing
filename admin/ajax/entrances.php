<?php
include_once("entrances.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'map';
$in['do'] = (key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
$q = new sql_query($config['db']);
$tname = 'entrances';
$t = $tables[$tname];
$t['name'] = 'entrances';
$t['header'] = $q->select("SELECT address FROM homes h, entrances e WHERE h.id=e.home AND e.id='{$in['id']}'",4);
$form = new form($config);

switch($in['do']){

	case 'add':
	case 'new':
		stop($form->getnew($t));
		break;

	case 'edit':
		stop($form->get($t));
		break;

	case 'save':
		stop($form->save($t));
		break;

	case 'remove':
		stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить?"));
		break;

	case 'realremove':
		stop($form->delete($t));
		break;

	default:
		stop(array(
		'result'=>'ERROR',
		'desc'=>"</pre><center>неверные данные<br>
			go={$in['go']}<br>
			do={$in['do']}<br>
			id={$in['id']}<br>
			</center><pre>"
		));
}
?>
