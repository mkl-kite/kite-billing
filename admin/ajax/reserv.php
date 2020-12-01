<?php
include_once("geodata.php");
include_once("map.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'reserves';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : "";
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : '0';

$q = new sql_query($config['db']);
$t = $tables['map'];
$t['form_query']="SELECT id, type, gtype, address, connect, note FROM map WHERE type='reserv'";
$t['fields']['connect']['label']='Кабель';
$t['fields']['connect']['type']='select';
$t['fields']['connect']['list']='select_cables_for_reserv';
$t['fields']['name']['type']='autocomplete';
$form = new form($config);
$m = false;

switch($in['do']){

	case 'add':
	case 'new':
		if($GeoJSON && $m=save_new_geodata($GeoJSON)) {
			$t['form_query']="SELECT id,type,gtype,name,address,connect,note FROM map";
			$t['id']=$m;
			$t['name']='reserv';
			$t['do']='firstsave';
			$t['fields']['type']['type']='nofield';
			stop($form->get($t));
		}else{
			log_txt("reserv: new: GeoJSON=".sprint_r($GeoJSON));
			stop(array('result'=>'ERROR','desc'=>"Гео Данные не были сохранены!"));
		}
		break;

	case 'edit':
		if($GeoJSON && !$m=save_geodata($GeoJSON)) {
			log_txt("reserv: save: GeoJSON=".sprint_r($GeoJSON));
			stop(array('result'=>'ERROR','desc'=>'ГеоДанные не были сохранены!'));
		}
		$t['id']=($m)? $m : $in['id'];
		$t['name']='map';
		$t['fields']['type']['type']='nofield';
		stop($form->get($t));
		break;

	case 'firstsave':
	case 'save':
		$t['name']='map';
		$t['key']='id';
		$t['id']=$in['id'];
		stop($form->save($t));
		break;

	case 'delete':
		$ids=preg_split('/,/',$_REQUEST['ids']);
		foreach($ids as $k=>$v) $ids[$k]=numeric($v);
		$id=implode(',',$ids);
		$all=$q->fetch_all("SELECT id FROM map WHERE id in ($id)");
		if(count($all)>0){
			$str_all=implode(',',$all);
			$q->query("DELETE FROM map_xy WHERE object in ($str_all)");
			$q->query("DELETE FROM map WHERE id in ($str_all)");
			stop(array('result'=>'OK','delete'=>array('objects'=>$all),'desc'=>'Данные удалены!'));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанные бъекты не найдены в базе!'));
		}
		break;

	case 'remove':
		$form = new form($config);
		$reserv = $q->select("SELECT * FROM map WHERE id={$in['id']}",1);
		stop($form->confirmForm($in['id'],'realremove',"{$reserv['address']}<BR>Вы действительно хотите убрать запас?"));
		break;

	case 'realremove':
		$q->query("DELETE FROM map_xy WHERE object={$in['id']}");
		$q->query("DELETE FROM map WHERE id={$in['id']}");
		stop(array('result'=>'OK','delete'=>array('objects'=>array($in['id']))));
		break;

	case 'auto_address':
		stop(auto_address());
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
