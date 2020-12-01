<?php
include_once("classes.php");

$tables['wifi']=array(
	'title'=>'Устройство',
	'name'=>'wifi',
	'target'=>"html",
	'limit'=>'yes',
	'module'=>"devices",
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'no',
	'add' => 'no',
	'query'=>"
		SELECT 
			d.id,
			d.name,
			d.ip,
			d.community,
			m.address as node,
			d.macaddress,
			d.note
		FROM
			devices d JOIN map m ON m.id = d.node1
		WHERE
			d.type='wifi' AND ip!=''
		",
	'table_query'=>"
		SELECT d.id, m.address, d.name, d.ip, d.community, d.ssid, d.macaddress, d.note 
		FROM devices d, map m
		WHERE d.node1=m.id AND d.type='wifi' :FILTER:
		ORDER BY :SORT:
	",
	'field_alias'=>array('address'=>'m', 'subtype'=>'d'),
	'defaults'=>array(
		'sort'=>'ip',
		'filter'=>'build_filter_for_wifi',
		'numports'=>1
	),
	'filters'=>array(
		'address'=>array(
			'type'=>'text',
			'label'=>'адрес',
			'style'=>"width:110px",
			'title'=>'выбор по адресу',
			'value'=>''
		),
		'subtype'=>array(
			'type'=>'select',
			'label'=>'тип',
			'title'=>'тип Wi-Fi станции',
			'style'=>'width:130px',
			'list'=>all2array($config['map']['typewifi']),
			'keep'=>true,
			'value'=>isset($_REQUEST['subtype'])? $_REQUEST['subtype']:""
		),
	),
 	'table_footer'=>array(
		'address'=>'Всего:',
		'note'=>'fcount',
 	),
	'checks'=>array(
	),
	'table_triggers'=>array(
	),
	'before_edit'=>'before_edit_',
	'before_save'=>'before_save_wifi',
	'form_onsave'=>'onsave_wifi',
	'before_delete'=>'before_delete_wifi',
	'sort'=>'',
	'group'=>'',
	'form_triggers'=>array(
	),
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form'),
		'showmap'=>array('label'=>"<img src=\"pic/usr.png\"> показать на карте",'to'=>'map','query'=>"go=devices&do=get_xy&table=devices"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=devices"),
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
			'native'=>true,
			'class'=>'nowr',
			'access'=>array('r'=>3,'w'=>5)
		),
		'ssid'=>array(
			'label'=>'SSID',
			'style'=>'width:120px',
			'type'=>'text',
			'native'=>true,
			'class'=>'nowr',
			'access'=>array('r'=>3,'w'=>5)
		),
		'numports'=>array(
			'label'=>'кол-во портов',
			'style'=>'width:40px;text-align:right',
			'table_style'=>'width:40px',
			'type'=>'text',
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
		'address'=>array(
			'label'=>'адрес',
			'style'=>'width:120px',
			'type'=>'text',
			'class'=>'nowr',
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
		'note'=>array(
			'label'=>'примечание',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
	)
);

function before_edit_wifi($f) {
	global $DEBUG, $config, $q, $dev_fields_filter;
	return $f;
}

function before_save_wifi($c,$o) {
	global $DEBUG, $config, $newcable, $modcable, $movedevice, $dev_fields_filter;
	if(!$q) $q = new sql_query($config['db']);
	$r=array_merge($o,$c);
	return $c;
}

function onsave_wifi($id,$save) {
	global $DEBUG, $config, $_REQUEST, $tables, $q;
	if(!$q) $q = new sql_query($config['db']);
	return $out;
}

function before_delete_wifi($r) {
	global $config;
	$q = new sql_query($config);
	$mp = $q->select("SELECT d2.* FROM devports d1, devports d2 WHERE d1.link=d2.id AND d1.device='{$r['id']}'",3);
	foreach($mp as $k=>$v) { $v['link'] = null; $mp[$k] = $v; }
	$out = array('result'=>'OK','modify'=>array('ports'=>$mp));
	if($r['type']=='cable') $out['delete'] = array('objects'=>array($r['id']));
	return $out;
}

function build_filter_for_wifi($t) {
	return filter2db('wifi');
}
?>
