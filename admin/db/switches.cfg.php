<?php
include_once("classes.php");

$tables['switches']=array(
	'title'=>'Устройство',
	'target'=>"html",
	'module'=>"switches",
	'key'=>'id',
	'class'=>'normal',
	'limit'=>'yes',
	'delete'=>'no',
	'add' => 'no',
	'query'=>"
		SELECT 
			d.id,
			d.name,
			d.ip,
			d.community,
			m.address as node,
			d.numports,
			d.note
		FROM
			devices d JOIN map m ON m.id = d.node1
		WHERE
			d.type='switch' AND ip!='' :FILTER:
		ORDER BY :SORT:
		",
	'table_query'=>"
		SELECT d.id, d.name, d.ip, d.community, m.address as node, d.numports, d.macaddress, d.note 
		FROM devices d, map m 
		WHERE m.id = d.node1 AND d.type='switch' AND ip!='' :FILTER:
		ORDER BY :SORT:
	",
	'table_query1'=>"
		SELECT d.id, d.name, d.ip, d.ssid, m.address as node, d.note 
		FROM devices d, map m 
		WHERE m.id = d.node1 AND d.type='wifi' AND d.subtype='ap' :FILTER:
		ORDER BY :SORT:
	",
	'table_query2'=>"
		SELECT d.id, d.name, d.ip, d.community, m.address as node, d.numports, d.note 
		FROM devices d, map m 
		WHERE m.id = d.node1 AND d.type='server' :FILTER:
		ORDER BY :SORT:
	",
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=devices"),
		'connections'=>array('label'=>"<img src=\"pic/doc.png\"> соединения",'to'=>'references.php','query'=>"go=node_0&do=show&table=devices"),
		'showmap'=>array('label'=>"<img src=\"pic/usr.png\"> показать на карте",'to'=>'map','query'=>"go=clients&do=mapobject&table=devices"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=devices"),
	),
	'filters'=>array(
		'rayon'=>array(
			'type'=>'checklist',
			'label'=>'районы',
			'title'=>'выбор районов',
			'list'=>$q->fetch_all("SELECT concat('_',rid) as id, r_name as name FROM rayon ORDER BY r_name"),
			'value'=>'_'.(isset($_REQUEST['rid'])? numeric($_REQUEST['rid']): ""),
		),
		'address'=>array(
			'type'=>'text',
			'label'=>'адрес',
			'style'=>"width:110px",
			'title'=>'выбор по адресу',
			'value'=>''
		),
	),
 	'table_footer'=>array(
		'name'=>'Всего:',
		'note'=>'fcount',
 	),
	'defaults'=>array(
		'sort'=>'ip',
		'filter'=>'build_filter_for_switches',
		'numports'=>1
	),
// 	'footer'=>array(),
	'checks'=>array(
	),
	'table_triggers'=>array(
	),
	'before_edit'=>'before_edit_',
	'before_save'=>'before_save_switch',
	'form_onsave'=>'onsave_switch',
	'before_delete'=>'before_delete_switch',
	'sort'=>'',
	'group'=>'',
	'form_triggers'=>array(
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'type'=>array(
			'label'=>'тип',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'subtype'=>array(
			'label'=>'подтип',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'name'=>array(
			'label'=>'название',
			'type'=>'text',
			'table_class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'numports'=>array(
			'label'=>'кол-во портов',
			'style'=>'width:40px;text-align:right',
			'table_style'=>'width:40px',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5),
		),
		'ip'=>array(
			'label'=>'ip адрес',
			'style'=>'width:120px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'community'=>array(
			'label'=>'community',
			'style'=>'width:120px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'macaddress'=>array(
			'label'=>'мак адрес',
			'style'=>'width:120px',
			'table_style'=>'width:170px;font-size:11pt',
			'class'=>'csid',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'ssid'=>array(
			'label'=>'SSID',
			'style'=>'width:120px',
			'type'=>'text',
			'native'=>true,
			'class'=>'nowr',
			'access'=>array('r'=>3,'w'=>5)
		),
		'node1'=>array(
			'label'=>'узел 1',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'node2'=>array(
			'label'=>'узел 2',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'node'=>array(
			'label'=>'адрес',
			'style'=>'width:120px',
			'type'=>'text',
			'table_class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'note'=>array(
			'label'=>'примечание',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
	)
);

function before_edit_switch($f) {
	global $DEBUG, $config, $q, $dev_fields_filter;
	if($DEBUG>0) log_txt(__function__.": start for {$f['name']}[{$f['id']}]");
	$f['fields']['type']['type']='nofield';
	if(isset($f['id'])){
		if(!$q) $q = new sql_query($config['db']);
		$r = $q->get('devices',$f['id']);

		$all=array();
		foreach($dev_fields_filter as $k=>$v) $all=array_unique(array_merge($all,$v));
		if(@$dev_fields_filter[$r['type']]) foreach(array_diff($all,$dev_fields_filter[$r['type']]) as $fname) $un[] = $fname;
		if(isset($un)) foreach($un as $fn) unset($f['fields'][$fn]);
		if($DEBUG>0) log_txt(__FUNCTION__.": убраны поля: ".arrstr($un));
		if($r['type'] == 'switch'){
			$f['fields']['bandleports']['label'] = 'порт uplink';
		}
	}
	return $f;
}

function before_save_switch($c,$o) {
	global $DEBUG, $config, $newcable, $modcable, $movedevice, $dev_fields_filter;
	if(!$q) $q = new sql_query($config['db']);
	$r=array_merge($o,$c);
	return $c;
}

function onsave_switch($id,$save) {
	global $DEBUG, $config, $_REQUEST, $tables, $q;
	if(!$q) $q = new sql_query($config['db']);
	return $out;
}

function before_delete_switch($r) {
	global $config;
	$q = new sql_query($config);
	$mp = $q->select("SELECT d2.* FROM devports d1, devports d2 WHERE d1.link=d2.id AND d1.device='{$r['id']}'",3);
	foreach($mp as $k=>$v) { $v['link'] = null; $mp[$k] = $v; }
	$out = array('result'=>'OK','modify'=>array('ports'=>$mp));
	if($r['type']=='cable') $out['delete'] = array('objects'=>array($r['id']));
	return $out;
}

function build_filter_for_switches($t) {
	return filter2db('switches');
/*
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".str($_REQUEST[$k])."'";
		}
		$s .= implode(' ',$r);
	}
	return $s;
*/
}
?>
