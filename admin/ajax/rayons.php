<?php
include_once("map.cfg.php");
include_once("rayon.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'rayons';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
$in['latitude'] = (array_key_exists('latitude',$_REQUEST))? flt($_REQUEST['latitude']) : 0;
$in['longitude'] = (array_key_exists('longitude',$_REQUEST))? flt($_REQUEST['longitude']) : 0;
$in['zoom'] = (array_key_exists('zoom',$_REQUEST))? numeric($_REQUEST['zoom']) : 0;

if(!$q) $q = new sql_query($config['db']);
$t = $tables['rayon'];

switch($in['do']){

	case 'get':
		if($d=get_rayons()){
			stop(array('result'=>'OK','rayons'=>$d));
		}else{
			stop(array('result'=>'ERROR','desc'=>"Районы не найдены"));
		}
		break;

	case 'get_rayon':
		$sql="SELECT latitude, longitude, zoom FROM rayon WHERE rid = '{$in['id']}'";
		if($d=$q->select($sql,1)){
			stop(array('result'=>'OK','rayon'=>$d));
		}else{
			stop(array('result'=>'OK','rayon'=>array()));
		}
		break;

	case 'add':
	case 'new':
		$form = new form($config);
		$out = (@$m)? $form->get($t) : $form->getnew($t);
		stop($out);
		break;

	case 'setview':
		$form = new form($config);
		$t['id']='new';
		$t['defaults']=array('latitude'=>$in['latitude'],'longitude'=>$in['longitude'],'zoom'=>$in['zoom']);
		$t['force_submit']=1;
		unset($t['layout']);
		unset($t['fields']['r_name']);
		unset($t['fields']['packets']);
		$t['fields']['id']=array(
			'label'=>'район',
			'type'=>'select',
			'list'=>'list_of_rayons',
			'native'=>true,
			'access'=>array('r'=>5,'w'=>5)
		);
		$out = $form->get($t);
		$out['form']['fields']['do']['value']='savelatlng';
		stop($out);
		break;

	case 'savelatlng':
		$new = array_intersect_key($_REQUEST,array('id','latitude', 'longitude', 'zoom'));
		$old = $q->select("SELECT rid as id, latitude, longitude, zoom FROM rayon WHERE rid = '{$in['id']}'",1);
		$t['query']="SELECT rid as id, latitude, longitude, zoom FROM rayon";
		$form = new form($config);
// 		log_txt("ajax/rayons.php: form.name = {$t['name']}");
		stop($form->save($t,$new,$old));
		break;

	case 'edit':
		$form = new form($config);
		stop($form->get($t));
		break;

	case 'save':
		$form = new form($config);
		stop($form->save($t));
		break;

	case 'delete':
	case 'remove':
		if($rayon=$q->select("SELECT * FROM rayon WHERE rid={$in['id']}",1)){
			$form = new form($config);
			stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить район '{$rayon['r_name']}' ?"));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанный район отсутствует в базе!'));
		}
		break;

	case 'realremove':
		$form = new form($config);
		$t['id']=$in['id'];
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
