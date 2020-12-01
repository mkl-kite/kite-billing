<?php
include_once("classes.php");
include_once("form.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'photos';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : "";
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : '0';
if(array_key_exists('file',$_GET)) $_GET['file'] = str($_REQUEST['file']);
$in['file'] = (array_key_exists('file',$_REQUEST))? str($_REQUEST['file']) : '';

$q = new sql_query($config['db']);
$t = $tables[''];

switch($in['do']){

	case 'add':
	case 'new':
		break;

	case 'edit':
		$form = new Form($config);
		stop($form->get($t));
		break;

	case 'save':
		if(!($myfile = loadFile())) stop(array('result'=>'ERROR','desc'=>"ошибка записи файла"));
		stop(array('result'=>'OK','file'=>PHOTO_FOLDER.$myfile));
		break;

	case 'delete':
		break;

	case 'remove':
		break;

	case 'realremove':
		if(!fdelete($in['file'])) stop(array('result'=>'WARNING','desc'=>"ошибка удаления файла"));
		stop(array('result'=>'OK','file'=>$in['file']));
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
?>
