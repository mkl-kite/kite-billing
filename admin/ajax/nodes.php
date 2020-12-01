<?php
include_once("geodata.php");
include_once("map.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'nodes';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : "";
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : '0';

$q = new sql_query($config['db']);
$t = $tables['map'];
$filter = array('hostname','service','mrtg','name','floors','entrances','apartments');
$form = new form($config);

switch($in['do']){

	case 'get_table':
		$t['header'] = 'Узлы';
		$t['delete'] = 'no';
		$t['module'] = 'nodes';
		$t['add'] = 'no';
		$t['table_name'] = 'map';
		$t['target'] = 'html';
		unset($t['filters']['type']);
		$t['table_query']="
			SELECT m.id, m.id as mapid, m.address, m.note,
				sum(IF(d.type='switch',1,0)) as sw,
				sum(IF(d.type='patchpanel',1,0)) as pp,
				sum(IF(d.type='cable',1,0)) as cab
			FROM map m LEFT OUTER JOIN devices d ON m.id = d.node1 OR m.id = d.node2
			WHERE m.type='node' :FILTER:
			GROUP BY m.id
			ORDER BY :SORT:
		";
		foreach($t['fields'] as $k=>$v){ if(isset($t['fields'][$k]['style'])) unset($t['fields'][$k]['style']); }
		$t['fields']['address']['style'] = 'white-space:nowrap';
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'list_devices':
		include_once 'devices.cfg.php';
		$t = $tables['devices'];
		$t['header'] = 'Узел ';
		$t['delete'] = 'no';
		$t['module'] = "node_{$in['id']}";
		$t['add'] = 'no';
		$t['table_name'] = 'devices';
		$t['table_triggers']['type'] = 'device_type';
		$t['table_triggers']['name'] = 'device_name';
		$t['target'] = 'html';
		$t['fields']['node1']['type'] = 'hidden';
		$t['fields']['node2']['type'] = 'hidden';
		$t['fields']['subtype']['type'] = 'hidden';
		$t['fields']['numports']['type'] = 'hidden';
		$t['fields']['colorscheme']['type'] = 'hidden';
		$t['table_query']="
			SELECT d.id, d.id as devid,  d.type, 
			concat(if(d.type is null,'',d.type),':',if(d.subtype is null,'',d.subtype),':',
			if(d.name is null,'',d.name),':',if(d.numports is null,'',d.numports),':',
			if(d.node1 is null,'',d.node1),':',if(d.node2 is null,'',d.node2),':',if(d.ip is null,'',d.ip),':',
			if(m1.address is null,'',m1.address),':',if(m2.address is null,'',m2.address)) as name,
			d.note
			FROM devices d LEFT OUTER JOIN map m1 ON d.node1=m1.id LEFT OUTER JOIN map m2 ON d.node2=m2.id
			WHERE d.node1='{$in['id']}' OR d.node2='{$in['id']}'
			ORDER BY :SORT:
		";
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'add':
	case 'new':
		if($GeoJSON && $m=save_new_geodata($GeoJSON)) {
			$t['id']=$m;
			$t['name']='node_'.$in['do'];
			$t['do']='firstsave';
			$t['form_triggers']['type']='get_objecttype';
			foreach($filter as $k=>$v) unset($t['fields'][$v]);
			stop($form->get($t));
		}else{
			log_txt("nodes: new: GeoJSON=".sprint_r($GeoJSON));
			stop(array('result'=>'ERROR','desc'=>"Гео Данные не были сохранены!"));
		}
		break;

	case 'edit':
		if($GeoJSON && !$m=save_geodata($GeoJSON)) {
			log_txt("nodes: save: GeoJSON=".sprint_r($GeoJSON));
			stop(array('result'=>'ERROR','desc'=>'ГеоДанные не были сохранены!'));
		}
		$t['id']=($m)? $m : $in['id'];
		$t['name']='map';
		foreach($filter as $k=>$v) unset($t['fields'][$v]);
		$t['fields']['type']['type']='nofield';
		$t['form_triggers']['type']='get_objecttype';
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
			$all=implode(',',$all);
			$q->query("DELETE FROM map WHERE id in ($all)");
			stop(array('result'=>'OK','delete'=>array('objects'=>$all),'desc'=>'Данные удалены!'));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанные бъекты не найдены в базе!'));
		}
		break;

	case 'remove':
		$cnt=$q->select("SELECT count(*) FROM devices WHERE node1={$in['id']} OR node2={$in['id']}",4);
		if($cnt==0){
			$form = new form($config);
			$node=$q->select("SELECT * FROM map WHERE id={$in['id']}",1);
			stop($form->confirmForm($in['id'],'realremove',"{$node['address']}<BR>Вы действительно хотите удалить этот узел?"));
		}else{
			$c=preg_replace('/.*([0-9])$/','\1',"$cnt"); if($c==1&&$cnt!=11) $e="о"; elseif($c>1&&$c<5) $e="а"; else $e="";
			stop(array('result'=>'ERROR','desc'=>"На этом узле еще числятся $cnt устройств$e!"));
		}
		break;

	case 'realremove':
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
