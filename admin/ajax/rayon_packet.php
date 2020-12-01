<?php
include_once("classes.php");
include_once("rayon_packet.cfg.php");
include_once("form.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'rayons';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;

$q = new sql_query($config['db']);
$t = $tables['rayon_packet'];
$t['name']='rayon_packet';
$t['key']='unique_id';
$t['filter']='';
if($in['id']>0) $t['id']=$in['id'];

switch($in['do']){

	case 'add':
	case 'new':
		$form = new form($config);
		stop($form->getnew($t));
		break;

	case 'edit':
		$form = new form($config);
		stop($form->get($t));
		break;

	case 'save':
		$form = new form($config);
		stop($form->save($t));
		break;

	case 'remove':
		$form = new form($config);
		stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить?"));
		break;

	case 'realremove':
		$form = new form($config);
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
