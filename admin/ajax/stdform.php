<?php
include_once("classes.php");
include_once("form.php");

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : $in['table'];
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : "";
$in['id'] = (isset($_REQUEST['id']))? str(preg_replace('/[^_]*_/','',$_REQUEST['id'])) : '';
$in['table'] = (isset($_REQUEST['table']))? strict($_REQUEST['table']) : '';

include_once("{$in['table']}.cfg.php");

$q = new sql_query($config['db']);

if(!isset($tables[$in['table']])){
	stop(array('result'=>'ERROR','desc'=>"Не найдены настройки для таблицы {$table}!"));
}

$t = $tables[$in['table']];
if(($in['id']==0 || $in['id']=='') && isset($t['key']) && isset($_REQUEST[$t['key']]))
	$in['id'] = str(preg_replace('/[^_]*_/','',$_REQUEST[$t['key']]));

if(!isset($t['name'])) $t['name'] = $in['table'];

$form = new form($config);

switch($in['do']){

	case 'add':
	case 'realnew':
	case 'new':
		$out = $form->getnew($t);
		stop($out);
		break;

	case 'clone':
		$t['clone'] = $in['id'];
		stop($form->getnew($t));
		break;

	case 'edit':
		$t['id'] = $in['id'];
		stop($form->get($t));
		break;

	case 'realsave':
	case 'save':
		stop($form->save($t));
		break;

	case 'switch':
        if(!($f = strict($_REQUEST['field']))) stop(array('result'=>'ERROR','desc'=>"Не указано поле!"));
        if(!$t) stop(array('result'=>'ERROR','desc'=>"Не указана таблица!"));
        if(arrfld($q->table_fields($in['table']),$f) === false) stop(array('result'=>'ERROR','desc'=>"Нет такого поля!"));
        $r = $q->get($in['table'],$in['id'],$t['key'],array($t['key'],$f));
        if($t['fields'][$f]['type'] == 'checkbox'){
			$r[$f] = ($r[$f])? 0 : 1;
			stop($form->save($t,$r));
		}elseif($t['fields'][$f]['type'] == 'date'){
			$r[$f] = ($r[$f])? null : date2db();
			stop($form->save($t,$r));
		}
		stop(array('result'=>'ERROR','desc'=>"Данный параметр не подлежит переключению!"));
		break;

	case 'delete':
	case 'remove':
		if(isset($t['real_name'])) $rec = true;
		if(!$rec && isset($t['allow_delete']) && function_exists($t['allow_delete'])){
			$ad = $t['allow_delete']($in['id'],false);
			if($DEBUG>0) log_txt("stdform::{$in['do']} {$t['allow_delete']}({$rec[$t['key']]}) = $ad");
			if($ad != 'yes') stop(array('result'=>'ERROR','desc'=>$ad));
			$rec = true;
		}
		if(!$rec) $rec = $q->get($in['table'],$in['id'],$t['key']);
		if($rec){
			$form = new form($config);
			stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить эту запись?",$in['table']));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Данная запись отсутствует в базе!'));
		}
		break;

	case 'realremove':
		$form = new form($config);
		$t['id']=$in['id'];
		stop($form->delete($t));
		break;

	default:
		if(preg_match('/^auto_([a-z][a-z]*)$/',$in['do'],$m) && function_exists($t['form_autocomplete'][$m[1]])){
			stop($t['form_autocomplete'][$m[1]]());
		}elseif(preg_match('/^reload_([a-z][a-z]*)$/',$in['do'],$m) && function_exists($t['form_reloadselect'][$m[1]])){
			$l = $t['form_reloadselect'][$m[1]]();
			if(!$l) stop("Ошибка вызова функции: ".$t['form_reloadselect'][$m[1]]);
			stop($l);
		}else{
			stop(array(
			'result'=>'ERROR',
			'desc'=>"неверные данные
				go=$_REQUEST[go]
				do=$_REQUEST[do]
				id=$_REQUEST[id]"
			));
		}
}
?>
